<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for paint audit trail event list
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
$orderParam = $_GET['col'];
$totalEvents = $_GET['numberofmatches'];
$view = $_GET['view'];

if ($size == "all") $size = 10000;
$offset = ($page-1) * $size;

/* Elasticsearch querys */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAuditIndex = $configFile['es_audit_trail_index'];

/* Process column sorting */

if ($_GET['col'] != "")
{
    foreach($orderParam as $key => $value)
    {
        $orderType = $value;
        $orderColumn = $key;
    }

    if ($orderType == 0) $sortOrderSelected = "asc";
    else $sortOrderSelected = "desc";

    switch ($orderColumn) 
    {
        case "1":
            $sortColumnSelected = "eventUser";
            break;
        case "2":
            $sortColumnSelected = "eventIP";
            break;
        case "4":
            $sortColumnSelected = "eventModule";
            break;
    }
}
else
{
    $sortOrderSelected = "desc";
    $sortColumnSelected = "@timestamp";
}

/* View selector */

if ($view != "admin")
{
    $matchesAuditEvents = getAuditTrailEvents($view."*", $ESAuditIndex, "AuditEvent", $size, $offset, $sortOrderSelected, $sortColumnSelected);
    $eventCount = countAuditTrailEvents($view."*", $ESAuditIndex, "AuditEvent");
    $eventData = json_decode(json_encode($matchesAuditEvents),true);
    $totalEvents = $eventCount['count'];
}
else
{
    $matchesAuditEvents = getAuditTrailEvents("*", $ESAuditIndex, "AuditEvent", $size, $offset, $sortOrderSelected, $sortColumnSelected);
    $eventCount = countAuditTrailEvents("*", $ESAuditIndex, "AuditEvent");
    $eventData = json_decode(json_encode($matchesAuditEvents),true);
    $totalEvents = $eventCount['count'];
}

/* Column names */

$dateColumn = '<span class="fa fa-calendar font-icon-gray fa-padding"></span>DATE';
$eventUserColumn = 'USER';
$eventIPColumn = 'IPADDRESS';
$eventBrowserColumn = '<span class="fa fa-id-card-o font-icon-gray fa-padding"></span>';
$eventModuleColumn = 'MODULE';
$eventActionColumn = '<span class="fa fa-hand-pointer-o font-icon-gray fa-padding"></span>ACTION EXECUTED';

$columns = Array(
    $dateColumn, 
    $eventUserColumn, 
    $eventIPColumn, 
    $eventBrowserColumn, 
    $eventModuleColumn, 
    $eventActionColumn
);

$recordsFound = count($eventData['hits']['hits']);

if ($recordsFound == 0) 
{
    /* Return JSON data */
  
    header('Content-Type: application/json');
  
    $json = Array("total_rows" => intval($totalEvents), "rows" => 0, "headers" => $columns);
    echo json_encode($json, JSON_PRETTY_PRINT);
    exit;
}

foreach ($eventData['hits']['hits'] as $result)
{
    if (isset($result['_source']['tags'])) continue;
          
    $date = date('Y-m-d H:i', strtotime($result['_source']['eventDate']));
    $user = $result['_source']['eventUser'];
    $ip = $result['_source']['eventIP'];
    $browser = $result['_source']['eventBrowser'];
    $module = $result['_source']['eventModule'];
    $action = $result['_source']['eventAction'];
        
    /* Date */
          
    $dateColumnData = $date; 
       
    /* User name */
                      
    $userColumnData = '<span class="fa fa-user font-icon-gray fa-padding"></span>'.$user;
          
    /* IP Address */
          
    $ipColumnData = $ip;
    
    /* Browser */
          
    $browserColumnData = '<p class="mightOverflow" style="width: 13px;"><span class="fa fa-id-card-o font-icon-gray fa-padding"></span>'.$browser.'</p>';

    /* Module */

    $moduleColumnData = $module;
          
    /* Action */
      
    $actionColumnData = '<p class="mightOverflow"><span class="fa fa-chevron-circle-right font-icon-gray fa-padding"></span>'.$action.'</p>';
    
    /* Final ROW constructor */

    $rows[] = Array(
        $dateColumn => $dateColumnData,
        $eventUserColumn => $userColumnData,
        $eventIPColumn => $ipColumnData,
        $eventBrowserColumn => $browserColumnData,
        $eventModuleColumn => $moduleColumnData,
        $eventActionColumn => $actionColumnData
    );
}

/* Return JSON data */

header('Content-Type: application/json');

$json = Array("total_rows" => intval($totalEvents), "rows" => $rows, "headers" => $columns);
echo json_encode($json, JSON_PRETTY_PRINT);