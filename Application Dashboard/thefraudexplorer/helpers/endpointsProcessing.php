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
 * Description: Code for paint main endpoints list
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

if ($size == "all") $size = 100000;  

$search = $_GET['filter'];
$offset = ($page-1) * $size;
$orderParam = $_GET['col'];

/* Process column sorting */

if ($_GET['col'] != "")
{
    foreach($orderParam as $key => $value)
    {
        $orderType = $value;
        $orderColumn = $key;
    }

    if ($orderType == 0) $sortOrderSelected = "ASC";
    else $sortOrderSelected = "DESC";

    switch ($orderColumn) 
    {
        case "1":
            $sortColumnSelected = "agents.agent";
            break;
        case "3":
            $sortColumnSelected = "agents.ruleset";
            break;
        case "6":
            $sortColumnSelected = "agents.heartbeat";
            break;
        case "7":
            $sortColumnSelected = "agents.pressure";
            break;
        case "8":
            $sortColumnSelected = "agents.opportunity";
            break;
        case "9":
            $sortColumnSelected = "agents.rationalization";
            break;
    }
}
else
{
    $sortOrderSelected = "DESC";
    $sortColumnSelected = "SUM(agents.pressure+agents.opportunity+agents.rationalization)/3";
}

/* Search option */

if (isset($_GET['filter']) && $_GET['filter'] != "")
{
    /* SQL queries */

    $searchString = filter($search[14]);
    $queryConfig = "SELECT * FROM t_config";
    $queryTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQL = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size."";  
    $queryEndpointsSQL_wOSamplerTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQL_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size.""; 
    $queryEndpointsSQLDomainTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQLDomain = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size.""; 
    $queryEndpointsSQLDomain_wOSamplerTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQLDomain_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent HAVING agent LIKE '".$searchString."%' OR name LIKE '".$searchString."%' OR domain LIKE '".$searchString."%' ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size.""; 
 
    /* Global data variables */
 
    if ($session->domain == "all")
    {
        if (samplerStatus($session->domain) == "enabled") 
        {
            $result_a = mysqli_query($connection, $queryTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQL);
        }
        else 
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQL_wOSamplerTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
        }
    }
    else
    {
        if (samplerStatus($session->domain) == "enabled") 
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQLDomainTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQLDomain);
        }
        else
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQLDomain_wOSamplerTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
        }
    }
 
    $totalRecords = mysqli_num_rows($result_a);

    /* Column names */

    $detailsColumn = '<span class="fa fa-list fa-lg"></span>';
    $endpointsColumn = 'AUDIENCE UNDER FRAUD ANALYTICS';
    $totalWordsColumn = '';
    $rulesetColumn = 'RULE SET';
    $versionColumn = 'VERSION';
    $stateColumn = 'STT';
    $lastColumn = 'LAST';
    $pressureColumn = 'P';
    $opportunityColumn = 'O';
    $rationalizationColumn = 'R';
    $levelColumn = 'L';
    $scoreColumn = 'SCORE';
    $deleteColumn = 'DEL';
    $setupColumn = 'SET';

    $columns = Array(
        $detailsColumn, 
        $endpointsColumn, 
        $totalWordsColumn, 
        $rulesetColumn, 
        $versionColumn, 
        $stateColumn, 
        $lastColumn, 
        $pressureColumn, 
        $opportunityColumn, 
        $rationalizationColumn,
        $levelColumn,
        $scoreColumn,
        $deleteColumn,
        $setupColumn
    );

    if ($totalRecords == 0) 
    {
        /* Return JSON data */

        header('Content-Type: application/json');

        $json = Array("total_rows" => intval($totalRecords), "rows" => 0, "headers" => $columns);
        echo json_encode($json, JSON_PRETTY_PRINT);
        exit;
    }

    while($row_a = mysqli_fetch_assoc($result_b)) 
    {
        /* Details column value */

        $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($row_a["agent"]).'&in='.encRijndael($row_a["domain"]).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="../images/card.svg" class="card-settings"></a>&nbsp;&nbsp;';

        /* Endpoint data retrieval */

        $totalSystemWords = filter($_GET['totalSystemWords']);
        $endpointEnc = encRijndael($row_a["agent"]);
        $domain_enc = encRijndael($row_a["domain"]);
        $flags = $row_a['flags'];

        if($row_a['rationalization'] == NULL) $countRationalization = 0;
        else $countRationalization = $row_a['rationalization'];

        if($row_a['opportunity'] == NULL) $countOpportunity = 0;
        else $countOpportunity = $row_a['opportunity'];

        if($row_a['pressure'] == NULL) $countPressure = 0;
        else $countPressure = $row_a['pressure'];

        if($row_a['totalwords'] == NULL) $totalWordHits = 0;
        else $totalWordHits = $row_a['totalwords'];

        $score=($countPressure+$countOpportunity+$countRationalization)/3;

        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
        else $dataRepresentation = "0";

        $endpointName = $row_a['agent']."@".$row_a['domain'];

        if ($row_a["name"] == NULL || $row_a["name"] == "NULL")
        {
            if ($row_a["gender"] == "male") $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else if ($row_a["gender"] == "female") $endpointsColumnData = endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
        }
        else
        {
            $endpointName = $row_a['name']."@".$row_a['domain'];
            if ($row_a["gender"] == "male") $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else if ($row_a["gender"] == "female") $endpointsColumnData = endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
        }

        /* Total words hidden */

        $totalWordsColumnData = $totalWordHits;

        /* Company, department or group */

        if ($row_a["ruleset"] == NULL || $row_a["ruleset"] == "NYET") $rulsetColumnData = '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>BASELINE</center></div></center></td>';
        else $rulsetColumnData = '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>' . $row_a["ruleset"] . "</center></div></center></td>";

        /* Endpoint software version */

        $versionColumnData = '<span class="fa fa-codepen font-icon-color fa-padding"></span>'.$row_a["version"];

        /* Endpoint status */

        if ($row_a["status"] == "active") $stateColumnData = '<span class="fa fa-power-off fa-lg font-icon-color-green"></span>';
        else $stateColumnData = '<span class="fa fa-power-off fa-lg"></span>';

        /* Last connection to the server */

        $lastColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($row_a["heartbeat"])).'</span><center><div class="date-container">'.date('H:i',strtotime($row_a["heartbeat"])).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($row_a["heartbeat"])).'</div></div></center><div id="fraudCounterHolder"></div>';

        /* Fraud triangle counts and score */

        $scoreQuery = mysqli_query($connection, $queryConfig);
        $scoreResult = mysqli_fetch_array($scoreQuery);
        $level = "low";

        if ($score >= $scoreResult['score_ts_low_from'] && $score <= $scoreResult['score_ts_low_to']) $level="low";
        if ($score >= $scoreResult['score_ts_medium_from'] && $score <= $scoreResult['score_ts_medium_to']) $level="med";
        if ($score >= $scoreResult['score_ts_high_from'] && $score <= $scoreResult['score_ts_high_to']) $level="high";
        if ($score >= $scoreResult['score_ts_critic_from']) $level="critic";

        $pressureColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countPressure;
        $opportunityColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countOpportunity;
        $rationalizationColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countRationalization;
        $levelColumnData = '<center><div class="score-container-underline">'.$level.'</div></center>';

        if ($score != 0) $scoreColumnData = '<a href=eventData?nt='.$endpointEnc.'>'.round($score, 1).'</a>';
        else $scoreColumnData = round($score, 1);

        /* Option for delete the endpoint */

        $deleteColumnData = '<a class="delete-endpoint" data-href="mods/deleteEndpoint?nt='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-delete" href="#"><img src="images/delete-button.svg" onmouseover="this.src=\'images/delete-button-mo.svg\'" onmouseout="this.src=\'images/delete-button.svg\'" alt="" title=""/></a>';	

        /* Endpoint setup */

        $setupColumnData = '<a class="setup-endpoint" href="mods/setupEndpoint?nt='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-setup" href="#"><img src="images/setup.svg" onmouseover="this.src=\'images/setup-mo.svg\'" onmouseout="this.src=\'images/setup.svg\'" alt="" title=""/></a>';

        /* Final ROW constructor */

        $rows[] = Array(
            $detailsColumn => $detailsColumnData,
            $endpointsColumn => $endpointsColumnData,
            $totalWordsColumn => $totalWordsColumnData,
            $rulesetColumn => $rulsetColumnData,
            $versionColumn => $versionColumnData,
            $stateColumn => $stateColumnData,
            $lastColumn => $lastColumnData,
            $pressureColumn => $pressureColumnData,
            $opportunityColumn => $opportunityColumnData,
            $rationalizationColumn => $rationalizationColumnData,
            $levelColumn => $levelColumnData,
            $scoreColumn => $scoreColumnData,
            $deleteColumn => $deleteColumnData,
            $setupColumn => $setupColumnData
        );
    }
}
else
{
    /* SQL queries */

    $queryConfig = "SELECT * FROM t_config";
    $queryTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQL = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size.""; 
    $queryEndpointsSQL_wOSamplerTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQL_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size.""; 
    $queryEndpointsSQLDomainTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQLDomain = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size."";
    $queryEndpointsSQLDomain_wOSamplerTotalRecords = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected."";
    $queryEndpointsSQLDomain_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, flags, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent ORDER BY ".$sortColumnSelected." ".$sortOrderSelected." LIMIT ".$offset.", ".$size."";

    /* Global data variables */

    if ($session->domain == "all")
    {
        if (samplerStatus($session->domain) == "enabled") 
        {
            $result_a = mysqli_query($connection, $queryTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQL);
        }
        else 
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQL_wOSamplerTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
        }
    }
    else
    {
        if (samplerStatus($session->domain) == "enabled") 
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQLDomainTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQLDomain);
        }
        else
        {
            $result_a = mysqli_query($connection, $queryEndpointsSQLDomain_wOSamplerTotalRecords);
            $result_b = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
        }
    }

    $totalRecords = mysqli_num_rows($result_a);
    $recordsFound = mysqli_num_rows($result_b);

    /* Column names */

    $detailsColumn = '<span class="fa fa-list fa-lg"></span>';
    $endpointsColumn = 'AUDIENCE UNDER FRAUD ANALYTICS';
    $totalWordsColumn = '';
    $rulesetColumn = 'RULE SET';
    $versionColumn = 'VERSION';
    $stateColumn = 'STT';
    $lastColumn = 'LAST';
    $pressureColumn = 'P';
    $opportunityColumn = 'O';
    $rationalizationColumn = 'R';
    $levelColumn = 'L';
    $scoreColumn = 'SCORE';
    $deleteColumn = 'DEL';
    $setupColumn = 'SET';

    $columns = Array(
        $detailsColumn, 
        $endpointsColumn, 
        $totalWordsColumn, 
        $rulesetColumn, 
        $versionColumn, 
        $stateColumn, 
        $lastColumn, 
        $pressureColumn, 
        $opportunityColumn, 
        $rationalizationColumn,
        $levelColumn,
        $scoreColumn,
        $deleteColumn,
        $setupColumn
    );

    if ($recordsFound == 0) 
    {
        /* Return JSON data */

        header('Content-Type: application/json');

        $json = Array("total_rows" => intval($totalRecords), "rows" => 0, "headers" => $columns);
        echo json_encode($json, JSON_PRETTY_PRINT);
        exit;
    }

    while($row_a = mysqli_fetch_assoc($result_b)) 
    {
        /* Details column value */

        $detailsColumnData = '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.encRijndael($row_a["agent"]).'&in='.encRijndael($row_a["domain"]).'" data-toggle="modal" data-target="#endpoint-card" href="#"><img src="../images/card.svg" class="card-settings"></a>&nbsp;&nbsp;';

        /* Endpoint data retrieval */

        $totalSystemWords = filter($_GET['totalSystemWords']);
        $endpointEnc = encRijndael($row_a["agent"]);
        $domain_enc = encRijndael($row_a["domain"]);
        $flags = $row_a['flags'];

        if($row_a['rationalization'] == NULL) $countRationalization = 0;
        else $countRationalization = $row_a['rationalization'];

        if($row_a['opportunity'] == NULL) $countOpportunity = 0;
        else $countOpportunity = $row_a['opportunity'];

        if($row_a['pressure'] == NULL) $countPressure = 0;
        else $countPressure = $row_a['pressure'];

        if($row_a['totalwords'] == NULL) $totalWordHits = 0;
        else $totalWordHits = $row_a['totalwords'];

        $score = ($countPressure+$countOpportunity+$countRationalization)/3;

        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
        else $dataRepresentation = "0";

        $endpointName = $row_a['agent']."@".$row_a['domain'];

        if ($row_a["name"] == NULL || $row_a["name"] == "NULL")
        {
            if ($row_a["gender"] == "male") $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else if ($row_a["gender"] == "female") $endpointsColumnData = endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
        }
        else
        {
            $endpointName = $row_a['name']."@".$row_a['domain'];
            if ($row_a["gender"] == "male") $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else if ($row_a["gender"] == "female") $endpointsColumnData = endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
            else $endpointsColumnData = endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName, $flags);
        }

        /* Total words hidden */

        $totalWordsColumnData = $totalWordHits;

        /* Company, department or group */

        if ($row_a["ruleset"] == NULL || $row_a["ruleset"] == "NYET") $rulsetColumnData = '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>BASELINE</center></div></center></td>';
        else $rulsetColumnData = '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>' . $row_a["ruleset"] . "</center></div></center></td>";

        /* Endpoint software version */

        $versionColumnData = '<span class="fa fa-codepen font-icon-color fa-padding"></span>'.$row_a["version"];

        /* Endpoint status */

        if ($row_a["status"] == "active") $stateColumnData = '<span class="fa fa-power-off fa-lg font-icon-color-green"></span>';
        else $stateColumnData = '<span class="fa fa-power-off fa-lg"></span>';

        /* Last connection to the server */

        $lastColumnData = '<span class="hidden-date">'.date('Y/m/d H:i',strtotime($row_a["heartbeat"])).'</span><center><div class="date-container">'.date('H:i',strtotime($row_a["heartbeat"])).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($row_a["heartbeat"])).'</div></div></center><div id="fraudCounterHolder"></div>';

        /* Fraud triangle counts and score */

        $scoreQuery = mysqli_query($connection, $queryConfig);
        $scoreResult = mysqli_fetch_array($scoreQuery);
        $level = "low";

        if (intval($score) >= $scoreResult['score_ts_low_from'] && intval($score) <= $scoreResult['score_ts_low_to']) $level="low";
        if (intval($score) >= $scoreResult['score_ts_medium_from'] && intval($score) <= $scoreResult['score_ts_medium_to']) $level="med";
        if (intval($score) >= $scoreResult['score_ts_high_from'] && intval($score) <= $scoreResult['score_ts_high_to']) $level="high";
        if (intval($score) >= $scoreResult['score_ts_critic_from']) $level="critic";

        $pressureColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countPressure;
        $opportunityColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countOpportunity;
        $rationalizationColumnData = '<span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countRationalization;
        $levelColumnData = '<center><div class="score-container-underline">'.$level.'</div></center>';

        if ($score != 0) $scoreColumnData = '<a href=eventData?nt='.$endpointEnc.'>'.round($score, 1).'</a>';
        else $scoreColumnData = round($score, 1);

        /* Option for delete the endpoint */

        $deleteColumnData = '<a class="delete-endpoint" data-href="mods/deleteEndpoint?nt='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-delete" href="#"><img src="images/delete-button.svg" onmouseover="this.src=\'images/delete-button-mo.svg\'" onmouseout="this.src=\'images/delete-button.svg\'" alt="" title=""/></a>';	

        /* Endpoint setup */

        $setupColumnData = '<a class="setup-endpoint" href="mods/setupEndpoint?nt='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-setup" href="#"><img src="images/setup.svg" onmouseover="this.src=\'images/setup-mo.svg\'" onmouseout="this.src=\'images/setup.svg\'" alt="" title=""/></a>';

        /* Final ROW constructor */

        $rows[] = Array(
            $detailsColumn => $detailsColumnData,
            $endpointsColumn => $endpointsColumnData,
            $totalWordsColumn => $totalWordsColumnData,
            $rulesetColumn => $rulsetColumnData,
            $versionColumn => $versionColumnData,
            $stateColumn => $stateColumnData,
            $lastColumn => $lastColumnData,
            $pressureColumn => $pressureColumnData,
            $opportunityColumn => $opportunityColumnData,
            $rationalizationColumn => $rationalizationColumnData,
            $levelColumn => $levelColumnData,
            $scoreColumn => $scoreColumnData,
            $deleteColumn => $deleteColumnData,
            $setupColumn => $setupColumnData
        );
    }
}

/* Return JSON data */

header('Content-Type: application/json');

$json = Array("total_rows" => intval($totalRecords), "rows" => $rows, "headers" => $columns);
echo json_encode($json, JSON_PRETTY_PRINT);

?>