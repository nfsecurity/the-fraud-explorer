<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for paint triggered workflows list
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

require '../vendor/autoload.php';
include "../lbs/globalVars.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

/* GET parameters */

$page = $_GET['page'];
$size = $_GET['size'];
$workflowName = $_GET['workflowname'];
$totalEvents = $_GET['numberofmatches'];

if ($size == "all") $size = 10000;
$offset = ($page-1) * $size;

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESalerterIndex = $configFile['es_alerter_index'];

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

/* Queries */

$queryTriggeredWorkflows = "SELECT * from t_wtriggers WHERE name='".$workflowName."' ORDER BY date DESC LIMIT ".$offset.", ".$size."";        
$result_a = mysqli_query($connection, $queryTriggeredWorkflows);
$numberOfMatches = mysqli_num_rows($result_a);

/* Column names */

$dateColumn = '&ensp;EVENT DATE';
$endpointColumn = 'ENDPOINT';
$windowColumn = 'APPLICATION';
$viewColumn = 'VIEW';

$columns = Array(
    $dateColumn, 
    $endpointColumn, 
    $windowColumn, 
    $viewColumn
);

$agrupation = 0;

if ($row_a = mysqli_fetch_array($result_a))
{
    do
    {           
        $alertIDs = explode(" ", $row_a['ids']);
                    
        foreach ($alertIDs as $alert)
        {
            if (($agrupation % 2) == 0) $coloredClass = "font-icon-color-green";
            else $coloredClass = "font-icon-gray";

            $alertDocument = getAlertIdData($alert, $ESalerterIndex, "AlertEvent");
            $datetime = $alertDocument['hits']['hits'][0]['_source']['eventTime'];
            preg_match('/(.*) (.*),/', $datetime, $eventTime);

            $dateOne = date('M d, Y', strtotime($eventTime[1]));

            $agent = $alertDocument['hits']['hits'][0]['_source']['agentId'];
            preg_match('/([a-z0-9]*)_/', $agent, $endpoint);
            $application = decRijndael($alertDocument['hits']['hits'][0]['_source']['windowTitle']);

            $dateColumnData = '&nbsp;<span class="fa fa-calendar font-icon-gray fa-padding"></span>'.$dateOne . ", ". $eventTime[2];
            $endpointColumnData = '<span class="fa fa-user-circle font-icon-color-green fa-padding"></span>'.$endpoint[1];
            $windowColumnData = '<span class="fa fa-window-maximize font-icon-gray fa-padding"></span>'.$application;
            $viewColumnData = '<a id="viewAlert" href="#" onclick="showAlert(this.id, \''.$alert.'\')"><span class="fa fa-file-text-o fa-custom-size '.$coloredClass.'"></span></a>';

            /* Final ROW constructor */

            $rows[] = Array(
                $dateColumn => $dateColumnData,
                $endpointColumn => $endpointColumnData,
                $windowColumn => $windowColumnData,
                $viewColumn => $viewColumnData
            );
        }

        $agrupation++;
    }
                
    while ($row_a = mysqli_fetch_array($result_a));          
}    

/* Return JSON data */

header('Content-Type: application/json');

$json = Array("total_rows" => intval($totalEvents), "rows" => $rows, "headers" => $columns);
echo json_encode($json, JSON_PRETTY_PRINT);

?>
