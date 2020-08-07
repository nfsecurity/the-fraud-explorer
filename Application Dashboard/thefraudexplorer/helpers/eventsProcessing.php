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
 * Date: 2020-08
 * Revision: v1.4.7-aim
 *
 * Description: Code for paint main events list
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

if ($size == "all") $size = 10000;

$search = $_GET['filter'];
$offset = ($page-1) * $size;
$orderParam = $_GET['col'];
$view = $_GET['view'];
$totalSystemWords = filter($_GET['totalSystemWords']);
$totalEvents = filter($_GET['totalEvents']);

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

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

    if ($view == "all")
    {
        switch ($orderColumn) 
        {
            case "1":
                $sortColumnSelected = "@timestamp";
                break;
            case "2":
                $sortColumnSelected = "alertType";
                break;
            case "3":
                $sortColumnSelected = "agentId";
                break;
        }
    }
    else
    {
        switch ($orderColumn) 
        {
            case "1":
                $sortColumnSelected = "@timestamp";
                break;
            case "2":
                $sortColumnSelected = "alertType";
                break;
        }
    }
}
else
{
    $sortOrderSelected = "desc";
    $sortColumnSelected = "@timestamp";
}

/* Search option */

if (isset($_GET['filter']) && $_GET['filter'] != "")
{
    /* Elasticsearch queries */

    $searchString = filter($search[8]);

    /* View selector */

    if ($view != "all")
    {
        $endpointDECSQL = $view;
        $endpointDECES = $view."*";
        $matchesDataEndpoint = getSpecificAgentIdEvents($endpointDECES, $ESAlerterIndex, "AlertEvent", $size, $offset, $sortOrderSelected, $sortColumnSelected, $searchString);
        $eventCount = countSpecificAgentIdEvents($endpointDECES, $ESAlerterIndex, "AlertEvent", $searchString);
        $endpointData = json_decode(json_encode($matchesDataEndpoint),true);
        $totalEvents = $eventCount['count'];
    }
    else
    {
        if ($session->domain != "all") 
        {
            if (samplerStatus($session->domain) == "enabled") 
            {
                $eventMatches = getSpecificFraudTriangleEvents($ESAlerterIndex, $session->domain, "enabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected, $searchString);
                $eventCount = countSpecificFraudTriangleEvents($ESAlerterIndex, $session->domain, "enabled", "allalerts", $searchString);
            }
            else 
            {
                $eventMatches = getSpecificFraudTriangleEvents($ESAlerterIndex, $session->domain, "disabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected, $searchString);
                $eventCount = countSpecificFraudTriangleEvents($ESAlerterIndex, $session->domain, "disabled", "allalerts", $searchString);
            }
        }
        else 
        {
            if (samplerStatus($session->domain) == "enabled") 
            {
                $eventMatches = getSpecificFraudTriangleEvents($ESAlerterIndex, "all", "enabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected, $searchString);
                $eventCount = countSpecificFraudTriangleEvents($ESAlerterIndex, "all", "enabled", "allalerts", $searchString);
            }
            else 
            {
                $eventMatches = getSpecificFraudTriangleEvents($ESAlerterIndex, "all", "disabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected, $searchString);
                $eventCount = countSpecificFraudTriangleEvents($ESAlerterIndex, "all", "disabled", "allalerts", $searchString);
            }
        }
                    
        $eventData = json_decode(json_encode($eventMatches), true);
        $totalEvents = $eventCount['count'];
    }

    /* If view is all */

    if ($view == "all")
    {
        /* Column names */

        $detailsColumn = '<span class="fa fa-list fa-lg awfont-padding-right"></span>';
        $dateColumn = '<span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE';
        $eventTypeColumn = 'BEHAVIOR';
        $endpointColumn = '<span class="fa fa-briefcase fa-lg font-icon-color-gray-low awfont-padding-right"></span>HUMAN AUDIENCE';
        $windowColumn = '<span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE';
        $metricsColumn = '&nbsp;METRS';
        $phraseColumn = '<span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING';
        $markColumn = '<center>MARK</center>';

        $columns = Array(
            $detailsColumn, 
            $dateColumn, 
            $eventTypeColumn, 
            $endpointColumn, 
            $windowColumn, 
            $metricsColumn, 
            $phraseColumn, 
            $markColumn
        );

        if ($totalEvents == 0) 
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
            
            $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
            $wordTyped = decRijndael($result['_source']['wordTyped']);
            $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
            $searchValue = "/".$result['_source']['phraseMatch']."/";
            $endPoint = explode("_", $result['_source']['agentId']);
            $agentId = $result['_source']['agentId'];
            $endpointDECSQL = $endPoint[0];
            $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
            $searchResult = searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset);
            $regExpression = htmlentities($result['_source']['phraseMatch']);
            $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, gender, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, gender, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
            $userDomain = mysqli_fetch_assoc($queryUserDomain);
                        
            /* Details */
            
            $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($agentId).'&in='.encRijndael($userDomain['domain']).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="../images/card.svg" class="card-settings"></a>&ensp;';
            
            /* Date */
            
            $dateColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($date)).'</span><center><div class="date-container">'.date('H:i',strtotime($date)).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($date)).'</div></div></center>';                
         
            /* Event type */
                        
            $eventType = ($result['_source']['alertType'] == "rationalization" ? "rational" : $result['_source']['alertType']);
            $eventTypeColumnData = '<center><div class="behavior-case"><center><div class="behavior-title">behavior</div></center><center>'.strtoupper($eventType).'</center></div></center>';
            
            /* Endpoint */
            
            $endpointName = $userDomain['agent']."@".$userDomain['domain'];
            $endpointId = $endpointName;
            $endpointDec = encRijndael($userDomain['agent']);
            $totalWordHits = $userDomain['totalwords'];
            $countPressure = $userDomain['pressure'];
            $countOpportunity = $userDomain['opportunity'];
            $countRationalization = $userDomain['rationalization'];
            $score = $userDomain['score'];
                                
            if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
            else $dataRepresentation = "0";

            if ($userDomain["name"] == NULL || $userDomain["name"] == "NULL")
            {
                if ($userDomain["gender"] == "male") $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else if ($userDomain["gender"] == "female") $endpointColumnData = endpointInsights("eventData", "female", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            }
            else
            {
                $endpointName = $userDomain['name']."@".$userDomain['domain'];
                if ($userDomain["gender"] == "male") $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else if ($userDomain["gender"] == "female") $endpointColumnData = endpointInsights("eventData", "female", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            }
            
            /* Application title */
            
            $windowColumnData = '<div class="title-app"><span class="fa fa-chevron-right font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($windowTitle, 0, 80)).'</div>';

            /* Endpoint metrics */

            $metricsColumnData = '<a href="../mods/endpointMetrics?id='. encRijndael($endpointId).'" data-toggle="modal" data-target="#endpoint-metrics" href="#" id="elm-endpoint-metrics" class="btn btn-default btn-metrics"><span class="fa fa-area-chart font-icon-color-gray"></span></a>';
            
            /* Phrase typed */
        
            $phraseColumnData = '<a class="event-phrase-viewer" href="mods/eventPhrases?id='.$result['_id'].'&ex='.encRijndael($result['_index']).'&xp='.encRijndael($regExpression).'&se='.encRijndael($wordTyped).'&te='.encRijndael($date).'&nt='.encRijndael($endpointId).'&pe='.encRijndael(strtoupper($result['_source']['alertType'])).'&le='.encRijndael($windowTitle).'" data-toggle="modal" data-target="#event-phrases" href="#"><span class="fa fa-pencil-square-o fa-lg font-icon-color-gray fa-padding"></span>'.$wordTyped.'</a>';
            
            /* Mark false positive */
            
            $index = $result['_index'];
            $type = $result['_type'];
            $regid = $result['_id'];
            $agentId = $result['_source']['agentId'];
        
            $urlEventValue="http://127.0.0.1:9200/".$index."/".$type."/".$regid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $urlEventValue);
            curl_setopt($ch, CURLOPT_ENCODING, ''); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resultValues=curl_exec($ch);
            curl_close($ch);
        
            $jsonResultValue = json_decode($resultValues);
            @$falsePositiveValue = $jsonResultValue->_source->falsePositive;

            $markColumnData = '<a class="false-positive" href="mods/eventMarking?id='.encRijndael($result['_id']).'&nt='.encRijndael($agentId).'&ex='.encRijndael($result['_index']).'&pe='.encRijndael($result['_type']).'&er=allevents" data-toggle="modal" data-target="#eventMarking" href="#">';
            
            if ($falsePositiveValue == "0") $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-color-green"></span></a>';
            else $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-gray"></span></a>';

            /* Final ROW constructor */

            $rows[] = Array(
                $detailsColumn => $detailsColumnData,
                $dateColumn => $dateColumnData,
                $eventTypeColumn => $eventTypeColumnData,
                $endpointColumn => $endpointColumnData,
                $windowColumn => $windowColumnData,
                $metricsColumn => $metricsColumnData,
                $phraseColumn => $phraseColumnData,
                $markColumn => $markColumnData
            );
        }
    }
    else
    {
        /* Column names */

        $detailsColumn = '<span class="fa fa-list fa-lg awfont-padding-right"></span>';
        $dateColumn = '<span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE';
        $eventTypeColumn = 'BEHAVIOR';
        $windowColumn = '<span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE';
        $metricsColumn = '&nbsp;METRS';
        $phraseColumn = '<span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING';
        $markColumn = '<center>MARK</center>';

        $columns = Array(
            $detailsColumn, 
            $dateColumn, 
            $eventTypeColumn,
            $windowColumn, 
            $metricsColumn, 
            $phraseColumn, 
            $markColumn
        );

        if ($totalEvents == 0) 
        {
            /* Return JSON data */
    
            header('Content-Type: application/json');
    
            $json = Array("total_rows" => intval($totalEvents), "rows" => 0, "headers" => $columns);
            echo json_encode($json, JSON_PRETTY_PRINT);
            exit;
        }

        foreach ($endpointData['hits']['hits'] as $result)
        {        
            $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));   
            $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
            $wordTyped = decRijndael($result['_source']['wordTyped']);
            $searchValue = "/".$result['_source']['phraseMatch']."/";
            $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
            $searchResult = searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset);
            $regExpression = htmlentities($result['_source']['phraseMatch']);
            $index = $result['_index'];
            $type = $result['_type'];
            $regid = $result['_id'];
            $endPoint = explode("_", $result['_source']['agentId']);
            $agentId = $result['_source']['agentId'];
            $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
            $userDomain = mysqli_fetch_assoc($queryUserDomain);
            $endpointName = $userDomain['agent']."@".$userDomain['domain'];
            $endpointId = $endpointName;

            /* Event Details */
        
            $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($agentId).'&in='.encRijndael($userDomain['domain']).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="images/card.svg" class="card-settings"></a>&ensp;';

            /* Timestamp */

            $dateColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($date)).'</span><center><div class="date-container">'.date('H:i',strtotime($date)).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($date)).'</div></div></center>';
            
            /* EventType */

            $eventType = ($result['_source']['alertType'] == "rationalization" ? "rational" : $result['_source']['alertType']);
            $eventTypeColumnData = '<center><div class="behavior-case"><center><div class="behavior-title">behavior</div></center><center>'.strtoupper($eventType).'</center></div></center>';

            /* Application title */

            $windowColumnData = '<div class="title-app"><span class="fa fa-chevron-right font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($windowTitle, 0, 80)).'</div>';

            /* Endpoint metrics */

            $metricsColumnData = '<a href="../mods/endpointMetrics?id='. encRijndael($endpointId).'" data-toggle="modal" data-target="#endpoint-metrics" href="#" id="elm-endpoint-metrics" class="btn btn-default btn-metrics"><span class="fa fa-area-chart font-icon-color-gray"></span></a>';

            /* Phrase typed */

            $phraseColumnData = '<a class="event-phrase-viewer" href="mods/eventPhrases?id='.$result['_id'].'&ex='.encRijndael($result['_index']).'&xp='.encRijndael($regExpression).'&se='.encRijndael($wordTyped).'&te='.encRijndael($date).'&nt='.encRijndael($endpointId).'&pe='.encRijndael(strtoupper($result['_source']['alertType'])).'&le='.encRijndael($windowTitle).'" data-toggle="modal" data-target="#event-phrases" href="#"><span class="fa fa-pencil-square-o fa-lg font-icon-color-gray fa-padding"></span>'.$wordTyped.'</a>';

            /* Mark false positive */
        
            $urlEventValue="http://127.0.0.1:9200/".$index."/".$type."/".$regid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $urlEventValue);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resultValues=curl_exec($ch);
            curl_close($ch);
        
            $jsonResultValue = json_decode($resultValues);
            $falsePositiveValue = $jsonResultValue->_source->falsePositive;
            
            $markColumnData = '<a class="false-positive" href="mods/eventMarking?id='.encRijndael($result['_id']).'&nt='.encRijndael($agentId).'&ex='.encRijndael($result['_index']).'&pe='.encRijndael($result['_type']).'&er=singleevents" data-toggle="modal" data-target="#eventMarking" href="#">';
        
            if ($falsePositiveValue == "0") $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-color-green"></span></a></td>';
            else $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-gray"></span></a></td>';

            /* Final ROW constructor */

            $rows[] = Array(
                $detailsColumn => $detailsColumnData,
                $dateColumn => $dateColumnData,
                $eventTypeColumn => $eventTypeColumnData,
                $windowColumn => $windowColumnData,
                $metricsColumn => $metricsColumnData,
                $phraseColumn => $phraseColumnData,
                $markColumn => $markColumnData
            );
        }
    }
}
else
{
    /* View selector */

    if ($view != "all")
    {
        $endpointDECSQL = $view;
        $endpointDECES = $view."*";
        $matchesDataEndpoint = getAgentIdEvents($endpointDECES, $ESAlerterIndex, "AlertEvent", $size, $offset, $sortOrderSelected, $sortColumnSelected);
        $eventCount = countAgentIdEvents($endpointDECES, $ESAlerterIndex, "AlertEvent");
        $endpointData = json_decode(json_encode($matchesDataEndpoint),true);
        $totalEvents = $eventCount['count'];
    }
    else
    {
        if ($session->domain != "all") 
        {
            if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleEvents($ESAlerterIndex, $session->domain, "enabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected);
            else $eventMatches = getAllFraudTriangleEvents($ESAlerterIndex, $session->domain, "disabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected);
        }
        else 
        {
            if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleEvents($ESAlerterIndex, "all", "enabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected);
            else $eventMatches = getAllFraudTriangleEvents($ESAlerterIndex, "all", "disabled", "allalerts", $size, $offset, $sortOrderSelected, $sortColumnSelected);
        }
                    
        $eventData = json_decode(json_encode($eventMatches), true);
    }

    /* If view is all */

    if ($view == "all")
    {
        /* Column names */

        $detailsColumn = '<span class="fa fa-list fa-lg awfont-padding-right"></span>';
        $dateColumn = '<span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE';
        $eventTypeColumn = 'BEHAVIOR';
        $endpointColumn = '<span class="fa fa-briefcase fa-lg font-icon-color-gray-low awfont-padding-right"></span>HUMAN AUDIENCE';
        $windowColumn = '<span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE';
        $metricsColumn = '&nbsp;METRS';
        $phraseColumn = '<span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING';
        $markColumn = '<center>MARK</center>';

        $columns = Array(
            $detailsColumn, 
            $dateColumn, 
            $eventTypeColumn, 
            $endpointColumn, 
            $windowColumn, 
            $metricsColumn, 
            $phraseColumn, 
            $markColumn
        );

        foreach ($eventData['hits']['hits'] as $result)
        {
            if (isset($result['_source']['tags'])) continue;
            
            $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
            $wordTyped = decRijndael($result['_source']['wordTyped']);
            $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
            $searchValue = "/".$result['_source']['phraseMatch']."/";
            $endPoint = explode("_", $result['_source']['agentId']);
            $agentId = $result['_source']['agentId'];
            $endpointDECSQL = $endPoint[0];
            $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
            $searchResult = searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset);
            $regExpression = htmlentities($result['_source']['phraseMatch']);
            $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, gender, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, gender, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
            $userDomain = mysqli_fetch_assoc($queryUserDomain);
                        
            /* Details */
            
            $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($agentId).'&in='.encRijndael($userDomain['domain']).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="../images/card.svg" class="card-settings"></a>&ensp;';
            
            /* Date */
            
            $dateColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($date)).'</span><center><div class="date-container">'.date('H:i',strtotime($date)).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($date)).'</div></div></center>';                
         
            /* Event type */
                        
            $eventType = ($result['_source']['alertType'] == "rationalization" ? "rational" : $result['_source']['alertType']);
            $eventTypeColumnData = '<center><div class="behavior-case"><center><div class="behavior-title">behavior</div></center><center>'.strtoupper($eventType).'</center></div></center>';
            
            /* Endpoint */
            
            $endpointName = $userDomain['agent']."@".$userDomain['domain'];
            $endpointId = $endpointName;
            $endpointDec = encRijndael($userDomain['agent']);
            $totalWordHits = $userDomain['totalwords'];
            $countPressure = $userDomain['pressure'];
            $countOpportunity = $userDomain['opportunity'];
            $countRationalization = $userDomain['rationalization'];
            $score = $userDomain['score'];
                                
            if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
            else $dataRepresentation = "0";

            if ($userDomain["name"] == NULL || $userDomain["name"] == "NULL")
            {
                if ($userDomain["gender"] == "male") $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else if ($userDomain["gender"] == "female") $endpointColumnData = endpointInsights("eventData", "female", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            }
            else
            {
                $endpointName = $userDomain['name']."@".$userDomain['domain'];
                if ($userDomain["gender"] == "male") $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else if ($userDomain["gender"] == "female") $endpointColumnData = endpointInsights("eventData", "female", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                else $endpointColumnData = endpointInsights("eventData", "male", $endpointDec, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            }
            
            /* Application title */
            
            $windowColumnData = '<div class="title-app"><span class="fa fa-chevron-right font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($windowTitle, 0, 80)).'</div>';

            /* Endpoint metrics */

            $metricsColumnData = '<a href="../mods/endpointMetrics?id='. encRijndael($endpointId).'" data-toggle="modal" data-target="#endpoint-metrics" href="#" id="elm-endpoint-metrics" class="btn btn-default btn-metrics"><span class="fa fa-area-chart font-icon-color-gray"></span></a>';
            
            /* Phrase typed */
        
            $phraseColumnData = '<a class="event-phrase-viewer" href="mods/eventPhrases?id='.$result['_id'].'&ex='.encRijndael($result['_index']).'&xp='.encRijndael($regExpression).'&se='.encRijndael($wordTyped).'&te='.encRijndael($date).'&nt='.encRijndael($endpointId).'&pe='.encRijndael(strtoupper($result['_source']['alertType'])).'&le='.encRijndael($windowTitle).'" data-toggle="modal" data-target="#event-phrases" href="#"><span class="fa fa-pencil-square-o fa-lg font-icon-color-gray fa-padding"></span>'.$wordTyped.'</a>';
            
            /* Mark false positive */
            
            $index = $result['_index'];
            $type = $result['_type'];
            $regid = $result['_id'];
            $agentId = $result['_source']['agentId'];
        
            $urlEventValue="http://127.0.0.1:9200/".$index."/".$type."/".$regid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $urlEventValue);
            curl_setopt($ch, CURLOPT_ENCODING, ''); 
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resultValues=curl_exec($ch);
            curl_close($ch);
        
            $jsonResultValue = json_decode($resultValues);
            @$falsePositiveValue = $jsonResultValue->_source->falsePositive;

            $markColumnData = '<a class="false-positive" href="mods/eventMarking?id='.encRijndael($result['_id']).'&nt='.encRijndael($agentId).'&ex='.encRijndael($result['_index']).'&pe='.encRijndael($result['_type']).'&er=allevents" data-toggle="modal" data-target="#eventMarking" href="#">';
            
            if ($falsePositiveValue == "0") $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-color-green"></span></a>';
            else $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-gray"></span></a>';

            /* Final ROW constructor */

            $rows[] = Array(
                $detailsColumn => $detailsColumnData,
                $dateColumn => $dateColumnData,
                $eventTypeColumn => $eventTypeColumnData,
                $endpointColumn => $endpointColumnData,
                $windowColumn => $windowColumnData,
                $metricsColumn => $metricsColumnData,
                $phraseColumn => $phraseColumnData,
                $markColumn => $markColumnData
            );
        }
    }
    else
    {
        /* Column names */

        $detailsColumn = '<span class="fa fa-list fa-lg awfont-padding-right"></span>';
        $dateColumn = '<span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE';
        $eventTypeColumn = 'BEHAVIOR';
        $windowColumn = '<span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE';
        $metricsColumn = '&nbsp;METRS';
        $phraseColumn = '<span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING';
        $markColumn = '<center>MARK</center>';

        $columns = Array(
            $detailsColumn, 
            $dateColumn, 
            $eventTypeColumn,
            $windowColumn, 
            $metricsColumn, 
            $phraseColumn, 
            $markColumn
        );

        foreach ($endpointData['hits']['hits'] as $result)
        {        
            $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));   
            $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
            $wordTyped = decRijndael($result['_source']['wordTyped']);
            $searchValue = "/".$result['_source']['phraseMatch']."/";
            $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
            $searchResult = searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset);
            $regExpression = htmlentities($result['_source']['phraseMatch']);
            $index = $result['_index'];
            $type = $result['_type'];
            $regid = $result['_id'];
            $endPoint = explode("_", $result['_source']['agentId']);
            $agentId = $result['_source']['agentId'];
            $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
            $userDomain = mysqli_fetch_assoc($queryUserDomain);
            $endpointName = $userDomain['agent']."@".$userDomain['domain'];
            $endpointId = $endpointName;

            /* Event Details */
        
            $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($agentId).'&in='.encRijndael($userDomain['domain']).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="images/card.svg" class="card-settings"></a>&ensp;';

            /* Timestamp */

            $dateColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($date)).'</span><center><div class="date-container">'.date('H:i',strtotime($date)).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($date)).'</div></div></center>';
            
            /* EventType */

            $eventType = ($result['_source']['alertType'] == "rationalization" ? "rational" : $result['_source']['alertType']);
            $eventTypeColumnData = '<center><div class="behavior-case"><center><div class="behavior-title">behavior</div></center><center>'.strtoupper($eventType).'</center></div></center>';

            /* Application title */

            $windowColumnData = '<div class="title-app"><span class="fa fa-chevron-right font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($windowTitle, 0, 80)).'</div>';

            /* Endpoint metrics */

            $metricsColumnData = '<a href="../mods/endpointMetrics?id='. encRijndael($endpointId).'" data-toggle="modal" data-target="#endpoint-metrics" href="#" id="elm-endpoint-metrics" class="btn btn-default btn-metrics"><span class="fa fa-area-chart font-icon-color-gray"></span></a>';

            /* Phrase typed */

            $phraseColumnData = '<a class="event-phrase-viewer" href="mods/eventPhrases?id='.$result['_id'].'&ex='.encRijndael($result['_index']).'&xp='.encRijndael($regExpression).'&se='.encRijndael($wordTyped).'&te='.encRijndael($date).'&nt='.encRijndael($endpointId).'&pe='.encRijndael(strtoupper($result['_source']['alertType'])).'&le='.encRijndael($windowTitle).'" data-toggle="modal" data-target="#event-phrases" href="#"><span class="fa fa-pencil-square-o fa-lg font-icon-color-gray fa-padding"></span>'.$wordTyped.'</a>';

            /* Mark false positive */
        
            $urlEventValue="http://127.0.0.1:9200/".$index."/".$type."/".$regid;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $urlEventValue);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $resultValues=curl_exec($ch);
            curl_close($ch);
        
            $jsonResultValue = json_decode($resultValues);
            $falsePositiveValue = $jsonResultValue->_source->falsePositive;
            
            $markColumnData = '<a class="false-positive" href="mods/eventMarking?id='.encRijndael($result['_id']).'&nt='.encRijndael($agentId).'&ex='.encRijndael($result['_index']).'&pe='.encRijndael($result['_type']).'&er=singleevents" data-toggle="modal" data-target="#eventMarking" href="#">';
        
            if ($falsePositiveValue == "0") $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-color-green"></span></a></td>';
            else $markColumnData = $markColumnData.'<span class="fa fa-check-square fa-lg font-icon-gray"></span></a></td>';

            /* Final ROW constructor */

            $rows[] = Array(
                $detailsColumn => $detailsColumnData,
                $dateColumn => $dateColumnData,
                $eventTypeColumn => $eventTypeColumnData,
                $windowColumn => $windowColumnData,
                $metricsColumn => $metricsColumnData,
                $phraseColumn => $phraseColumnData,
                $markColumn => $markColumnData
            );
        }
    }
}

/* Return JSON data */

header('Content-Type: application/json');

$json = Array("total_rows" => intval($totalEvents), "rows" => $rows, "headers" => $columns);
echo json_encode($json, JSON_PRETTY_PRINT);

?>
