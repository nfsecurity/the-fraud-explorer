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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for paint dashboard
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
include "../lbs/cryptography.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/openDBconn.php";

/* Load sample data if it does not exist */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
insertSampleData($configFile);

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://localhost:9200/logstash-thefraudexplorer-text-*/_count";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}

$resultWords = json_decode($resultWords, true);

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0";

?>

<div class="dashboard-left-menu">
    <center>
        <table class="menu-table" id="elm-left-menu">
            <tr>
                <td>
                    <center>

                    <?php

                    if ($session->username != "admin") echo '<span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Workflows</div><div class=tooltip-row><div class=tooltip-item>Create fraud triangle flows based on your<br>business rules defined by audit team,<br>committee or investigations area</div></div></div>"><a href="#" id="elm-fraud-flows"><div class="left-menu-button"><div class="menu-title">wfw</div><span class="fa fa-list-ol font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>';
                    else echo '<span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Workflows</div><div class=tooltip-row><div class=tooltip-item>Create fraud triangle flows based on your<br>business rules defined by audit team,<br>committee or investigations area</div></div></div>"><a href="mods/fraudTriangleFlows" data-toggle="modal" data-target="#fraudFlows" href="#" id="elm-fraud-flows"><div class="left-menu-button"><div class="menu-title">wfw</div><span class="fa fa-list-ol font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>';
                    
                    ?>

                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Report</div><div class=tooltip-row><div class=tooltip-item>Make and download a fraud triangle<br>report for your organization with<br>various filters</div></div></div>"><a href="../mods/advancedReports" data-toggle="modal" class="advanced-reports-button" data-target="#advanced-reports" href="#" id="elm-advanced-reports"><div class="left-menu-button"><div class="menu-title">rep</div><span class="fa fa-file-text-o font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>                    
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Endpoint agent</div><div class=tooltip-row><div class=tooltip-item>Make the downloadable and installable<br>agent for your endpoints depending<br>of the platform</div></div></div>"><a href="../mods/buildEndpoint" data-toggle="modal" class="build-endpoint-button" data-target="#build-endpoint" href="#" id="elm-build-endpoint"><div class="left-menu-button"><div class="menu-title">end</div><span class="fa fa-user-plus fa-2x font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>
                    <center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Phrase library</div><div class=tooltip-row><div class=tooltip-item>Modify and improve the fraud triangle<br>phrase library with this tool called<br>library workshop</div></div></div>"><a href="../mods/fraudTriangleRules" data-toggle="modal" class="fraud-triangle-phrases-button" data-target="#fraudTriangleRules" href="#" id="elm-fraud-triangle-rules"><div class="left-menu-button"><div class="menu-title">lib</div><span class="fa fa-book font-icon-color-gray" style="font-size: 18px;"></span></div></a><span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Regionalism words</div><div class=tooltip-row><div class=tooltip-item>Add or delete a set of words related to<br>specific country, city or ethnic groups<br>to improve fraud detection</div></div></div>"><a href="../mods/regionalismWords" data-toggle="modal" class="regionalism-words-button" data-target="#regionalismWords" href="#" id="elm-regionalism-words"><div class="left-menu-button"><div class="menu-title">reg</div><span class="fa fa-language font-icon-color-gray" style="font-size: 18px;"></span></div></a><span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Status of phrase collection</div><div class=tooltip-row><div class=tooltip-item>Enable or disable the phrase collection<br>through a command sent to the<br>endpoints in real time</div></div></div>"><a data-href="mods/switchPhraseCollection" data-toggle="modal" data-target="#switch-phrase-collection" href="#" class="enable-analytics-button" id="elm-switch-phrase-collection"><div class="left-menu-button"><div class="menu-title">sta</div><span class="fa fa-toggle-on font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Vertical analytics</div><div class=tooltip-row><div class=tooltip-item>See the complete table of fraud triangle<br>analytics events with power and<br>useful information</div></div></div>"><a href="mods/graphicData" data-toggle="modal" data-target="#graphicdata" href="#" id="elm-vertical"><div class="left-menu-button"><div class="menu-title">tab</div><span class="fa fa-table font-icon-color-gray" style="font-size: 20px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Business Departments</div><div class=tooltip-row><div class=tooltip-item>Use this option to upload a file<br>in CSV format indicating all the relevant<br>information to clasify endpoints</div></div></div>"><a href="mods/businessUnits" data-toggle="modal" data-backdrop="static" data-keyboard="false" data-target="#business-units" href="#" id="elm-business-units"><div class="left-menu-button"><div class="menu-title">dep</div><span class="fa fa-sitemap font-icon-color-gray" style="font-size: 19px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Artificial intelligence</div><div class=tooltip-row><div class=tooltip-item>See what are the result of the expert<br>system based on artificial intelligence<br>to prescribe corporate fraud</div></div></div>"><a href="mods/expertSystem" data-toggle="modal" data-target="#expertSystem" href="#" id="elm-ai"><div class="left-menu-button"><div class="menu-title">int</div><span class="fa fa-drupal font-icon-color-gray" style="font-size: 19px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Backup and restore</div><div class=tooltip-row><div class=tooltip-item>Generate and download a copy of the<br>entire system for backup and restore<br>purposes. This procedure can take time</div></div></div>"><a href="mods/backupData" data-toggle="modal" data-target="#backupData" href="#" id="elm-backup"><div class="left-menu-button"><div class="menu-title">bck</div><span class="fa fa-database font-icon-color-gray" style="font-size: 18px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
            <tr>
                <td>
                    <center>
                    <span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Email setup</div><div class=tooltip-row><div class=tooltip-item>Define the email configuration for<br>general alerting, system messages and<br>some important announces</div></div></div>"><a href="mods/mailConfig" data-toggle="modal" data-target="#mail-config" href="#" id="elm-mail"><div class="left-menu-button"><div class="menu-title">eml</div><span class="fa fa-wpforms font-icon-color-gray" style="font-size: 19px;"></span></div></a></span>
                    </center>
                </td>
            </tr>
        </table>
    </center>
</div>

<div class="dashboard-container">
    <div class="container-upper-left" id="elm-top50endpoints">
        <h2>
            <p class="container-title"><span class="fa fa-chevron-right fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>Top fraud triangle endpoints</p>
            <p class="container-window-icon">
                <?php echo '<a href="eventData?nt='.encRijndael("all").'" class="button-view-all-events">&nbsp;&nbsp;View all events&nbsp;&nbsp;</a>&nbsp;'; ?>
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2>
        <div class="container-upper-left-sub table-class">

            <table id="top50endpoints" class="table">

                <!-- Hidden table head for CSV purposes -->

                <thead style="display: none;">
                    <tr>
                        <th>Employee and Business</th>
                        <th>Fraud T. events</th>
                        <th>Department/rules</th>
                        <th>Behavior score</th>
                    </tr>
                </thead>

                <tbody class="table-body">

                    <?php

                    $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC LIMIT 50";
                    $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC LIMIT 50";                  
                    $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY score DESC LIMIT 50";
                    $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' GROUP BY agent ORDER BY score DESC LIMIT 50";
                    
                    if ($session->domain == "all")
                    {
                        if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysqli_query($connection, $queryEndpointsSQL);
                        else $queryEndpoints = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
                    }
                    else
                    {
                        if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysqli_query($connection, $queryEndpointsSQLDomain);
                        else $queryEndpoints = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
                    }

                    if($endpointsFraud = mysqli_fetch_assoc($queryEndpoints))
                    {
                        do
                        {
                            $endpointName = $endpointsFraud['agent']."@".$endpointsFraud['domain'];
                            $endpointEnc = encRijndael($endpointsFraud['agent']);
                            $totalWordHits = $endpointsFraud['totalwords'];
                            $countPressure = $endpointsFraud['pressure'];
                            $countOpportunity = $endpointsFraud['opportunity'];
                            $countRationalization = $endpointsFraud['rationalization'];
                            $score = $endpointsFraud['score'];
                            
                            if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
                            else $dataRepresentation = "0";
                            
                            echo '<tr class="tr">';
                            echo '<td class="td-endpoints">';
                            
                            if ($endpointsFraud["name"] == NULL || $endpointsFraud['name'] == "NULL") 
                            {
                                endpointInsights("dashBoard", "na", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                            }
                            else 
                            {
                                $endpointName = $endpointsFraud['name']."@".$endpointsFraud['domain'];
                                endpointInsights("dashBoard", "na", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                            }

                            echo '</td>';

                            $triangleSum = $endpointsFraud['pressure']+$endpointsFraud['opportunity']+$endpointsFraud['rationalization'];
                            $triangleScore = round($endpointsFraud['score'], 2);

                            echo '<td class="td-events">';
                            echo '<center><div class="number-container">'.str_pad($triangleSum, 4, '0', STR_PAD_LEFT).'</div></center>';
                            echo '</td>';
                            echo '<td class="td-ruleset td-with-bg">';
                            echo '<div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>'.$endpointsFraud['ruleset'].'</center></div>';
                            echo '</td>';
                            echo '<td class="td-score">';
                            echo '<center><div class="number-container-underline">'.str_pad($triangleScore, 6, '0', STR_PAD_LEFT).'</div></center>';
                            echo '</td>';
                        }
                        while ($endpointsFraud = mysqli_fetch_assoc($queryEndpoints));
                    }

                    ?>

                </tbody>
                <tfoot class="table-head">
                    <tr class="tr">
                        <th class="th-endpoints" style="padding-left: 7px; border-radius: 0px 0px 0px 3px;">
                            <span class="fa fa-briefcase fa-lg font-icon-color-gray awfont-padding-right"></span>Employee and Business
                        </th>
                        <th class="th-events" style="padding-right: 25px;">
                            <center><span class="fa fa-bookmark fa-lg font-icon-color-gray awfont-padding-right"></span>Fraud T. events</center>
                        </th>
                        <th class="th-ruleset" style="padding-right: 25px;">
                            <center><span class="fa fa-folder-open fa-lg font-icon-color-gray awfont-padding-right"></span>Rules/Units</center>
                        </th>
                        <th class="th-score" style="padding-right: 25px; border-radius: 0px 0px 3px 0px;">
                            <center><span class="fa fa-address-book-o fa-lg font-icon-color-gray awfont-padding-right"></span>Fraud score</center>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="container-upper-right" id="elm-generalstatistics">
        <h2>
            <p class="container-title"><span class="fa fa-chevron-right fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>Endpoints general statistics</p>
            <p class="container-window-icon">
            <a href="mods/fraudTree" class="fraud-tree-button" data-toggle="modal" data-target="#fraudTree" href="#" id="elm-fraud-tree">&nbsp;&nbsp;View Fraud Tree&nbsp;&nbsp;</a>
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2><br>
        <div class="container-upper-right-sub">
            <canvas id="upper-right"></canvas>
        </div>
    </div>

    <?php
    
    $queryTermsSQL = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents;";
    $queryTermsSQL_wOSampler = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'";
    $queryTermsSQLDomain_wOSampler = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='".$session->domain."'";
    $queryTermsSQLDomain = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."'";
    
    $samplerStatus = samplerStatus($session->domain);
    
    if ($session->domain == "all")
    {
        if ($samplerStatus == "enabled") $queryTerms = mysqli_query($connection, $queryTermsSQL);
        else $queryTerms = mysqli_query($connection, $queryTermsSQL_wOSampler);
    }
    else
    {
        if ($samplerStatus == "enabled") $queryTerms = mysqli_query($connection, $queryTermsSQLDomain);
        else $queryTerms = mysqli_query($connection, $queryTermsSQLDomain_wOSampler);
    }
        
    $fraudTerms = mysqli_fetch_assoc($queryTerms);
    $fraudScore = ($fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'])/3;
    
    ?>

    <div class="container-bottom-left" id="elm-termstatistics">
        <h2>
            <p class="container-title"><span class="fa fa-chevron-right fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>Fraud triangle term statistics</p>
            <p class="container-window-icon">
                <a href="../mods/fraudMetrics" data-toggle="modal" class="fraud-metrics-button" data-target="#fraud-metrics" href="#" id="elm-fraud-metrics">&nbsp;&nbsp;Triangle metrics&nbsp;&nbsp;</a>&nbsp;
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2><br>
        <div class="container-bottom-left-sub">
            <div class="container-bottom-left-sub-one">
                <div class="container-bottom-left-sub-one-sub">
                    <p class="container-bottom-left-fraud-score"><?php echo round($fraudScore,1); ?></p>
                    </b><i class="fa fa-thermometer-quarter fa-lg font-icon-color-gray" aria-hidden="true">&nbsp;&nbsp;</i>Behavior score
                </div>
                <canvas id="bottom-left" style="z-index:1;"></canvas>
            </div>
            <div class="container-bottom-left-sub-two">
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-pressure"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">Pressure,</p><p class="content-vertex-text"> personal (addiction, discipline, gambling), corporate (compensation, fear to lose the job) or external (market, ego, image, reputation).</p>
                        </div>
                    </div>
                </div>
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-opportunity"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">Opportunity,</p><p class="content-vertex-text"> araises when the fraudster sees a way to use their position of trust to solve a problem, knowing they are unlikely to be caught.</p>
                        </div>
                    </div>
                </div>
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-rational"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">Rationalization,</p><p class="content-vertex-text"> the final component needed to complete the fraud triangle. It's the ability to persuade yourself that something is really ok.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-bottom-right" id="elm-top50events">
        <h2>
            <p class="container-title"><span class="fa fa-chevron-right fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>Fraud triangle theory latest events</p>
            <p class="container-window-icon">
                <?php echo '<a href="eventData?nt='.encRijndael("all").'" class="button-view-all-events" id="elm-viewallevents">&nbsp;&nbsp;View all events&nbsp;&nbsp;</a>&nbsp;'; ?>
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2>
        <div class="container-bottom-right-sub table-class">

            <table id="top50events" class="table">

                <!-- Hidden table head for CSV purposes -->

                <thead style="display: none;">
                    <tr>
                        <th>Endpoint and Business</th>
                        <th>Event date/time</th>
                        <th>Fraud T. vertice</th>
                        <th>Feeling/expressing</th>
                    </tr>
                </thead>

                <tbody class="table-body">

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $ESalerterIndex = $configFile['es_alerter_index'];
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));
                
                    if ($session->domain != "all") 
                    {
                        if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleMatches($ESalerterIndex, $session->domain, "enabled", "dashboard");
                        else $eventMatches = getAllFraudTriangleMatches($ESalerterIndex, $session->domain, "disabled", "dashboard");
                    }
                    else
                    {
                        if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleMatches($ESalerterIndex, "all", "enabled", "dashboard");
                        else $eventMatches = getAllFraudTriangleMatches($ESalerterIndex, "all", "disabled", "dashboard");
                    }
                
                    $eventData = json_decode(json_encode($eventMatches), true);

                    foreach ($eventData['hits']['hits'] as $result)
                    {
                        echo '<tr class="tr">';
                        echo '<td class="td-endpoints">';
                    
                        $date = date('Y/m/d, H:i', strtotime($result['_source']['sourceTimestamp']));
                        $wordTyped = decRijndael($result['_source']['wordTyped']);
                        $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                        $searchValue = "/".$result['_source']['phraseMatch']."/";
                        $endPoint = explode("_", $result['_source']['agentId']);
                        $endpointDECSQL = $endPoint[0];
                        $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
                        $searchResult = searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset);
                        $regExpression = htmlentities($result['_source']['phraseMatch']);
                        $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));                    
                        $userDomain = mysqli_fetch_assoc($queryUserDomain);
                        $endpointName = $userDomain['agent']."@".$userDomain['domain'];
                        $endpointEnc = encRijndael($userDomain['agent']);
                        $totalWordHits = $userDomain['totalwords'];
                        $countPressure = $userDomain['pressure'];
                        $countOpportunity = $userDomain['opportunity'];
                        $countRationalization = $userDomain['rationalization'];
                        $score = $userDomain['score'];
                            
                        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
                        else $dataRepresentation = "0";

                        if ($userDomain["name"] == NULL || $userDomain['name'] == "NULL") 
                        {
                            endpointInsights("dashBoard", "na", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                        }
                        else 
                        {
                            $endpointName = $userDomain['name']."@".$userDomain['domain'];
                            endpointInsights("dashBoard", "na", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
                        }

                        echo '</td>';
                    
                        echo '<td class="td-date">';
                        
                        echo '<center><div class="date-container">'.date('H:i',strtotime($date)).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($date)).'</div></div></center>';
                        echo '</td>';

                        echo '</td>';
                        echo '<td class="td-phrase">';
                        echo '<div class="phrase-without-app"><span class="fa fa-chevron-right font-icon-color-gray awfont-padding-right" style="vertical-align: middle;"></span><a style="padding-left: 2px;" class="event-phrase-viewer" href="mods/eventPhrases?id='.$result['_id'].'&ex='.encRijndael($result['_index']).'&xp='.encRijndael($regExpression).'&se='.encRijndael($wordTyped).'&te='.encRijndael($date).'&nt='.encRijndael($endpointName).'&pe='.encRijndael(strtoupper($result['_source']['alertType'])).'&le='.encRijndael($windowTitle).'" data-toggle="modal" data-target="#event-phrases" href="#">'.strip_tags(substr($wordTyped, 0, 80)).'</a></div>';
                        echo '</td>';
                    
                        echo '<td class="td-vertice td-with-bg">';
                        echo '<center><div class="behavior-button">'.substr(strtoupper($result['_source']['alertType']), 0, 1).'</div></center>';
                        echo '</td>';
                        
                        
                                              
                        echo '</tr>';
                    }

                    ?>

                </tbody>
                <tfoot class="table-head">
                    <tr class="tr">
                        <th class="th-endpoints" style="border-radius: 0px 0px 0px 3px;">
                            <span class="fa fa-briefcase fa-lg font-icon-color-gray awfont-padding-right"></span>Endpoint and Business
                        </th>
                        <th class="th-phrase">
                            <span class="fa fa-comments-o fa-lg font-icon-color-gray awfont-padding-right"></span>Is expressing or feeling
                        </th>
                        <th class="th-vertice" style="padding-right: 25px;">
                            Vertice&nbsp;&nbsp;&nbsp;
                        </th>
                        <th class="th-date" style="padding-left: 1px; border-radius: 0px 0px 3px 0px;">
                        <center><span class="fa fa-calendar-o fa-lg font-icon-color-gray awfont-padding-right"></span>Date&nbsp;&nbsp;</center>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php

/* Graph data calculation - The Fraud Explorer general statisctics */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled") 
    {                
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM t_agents) AS agents GROUP BY agent) AS totals";
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY)";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM (SELECT agent FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus;";
    }
    else 
    {
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain NOT LIKE 'thefraudexplorer.com'";      
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain NOT LIKE 'thefraudexplorer.com'";
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled") 
    { 
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
    }
    else 
    {
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
    }
}

$countUniques = mysqli_fetch_assoc(mysqli_query($connection, $queryUniqueEndpoints));
$countSessions = mysqli_fetch_assoc(mysqli_query($connection, $queryEndpointSessions));
$countDead = mysqli_fetch_assoc(mysqli_query($connection, $queryDeadEndpoints));
$countTyping = mysqli_fetch_assoc(mysqli_query($connection, $queryTyping));
$countEvents = $fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'];

?>

<!-- Modal for Phrase viewer -->

<script>
    $('#event-phrases').on('show.bs.modal', function(e) {
        $(this).find('.event-phrase-viewer').attr('href', $(e.relatedTarget).data('href'));
    });
    
    $('#event-phrases').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Modal for Advanced Reports -->

<script>
    $('#advanced-reports').on('show.bs.modal', function(e){
        $(this).find('.advanced-reports-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Regionalism Words -->

<script>
    $('#regionalism-words').on('show.bs.modal', function(e){
        $(this).find('.regionalism-words-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Fraud Triangle Workflows -->

<script>
    $('#fraudFlows').on('show.bs.modal', function(e){
        $(this).find('.fraud-flows-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Business Units -->

<script>
    $('#business-units').on('show.bs.modal', function(e){
        $(this).find('.business-units-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Data Backup -->

<script>
    $('#backupData').on('show.bs.modal', function(e){
        $(this).find('.backup-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Fraud Tree -->

<script>
    $('#fraudTree').on('show.bs.modal', function(e){
        $(this).find('.fraud-tree-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Fraud Metrics -->

<script>
    $(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
    });

    $('#fraud-metrics').on('show.bs.modal', function(e){
        $(this).find('.fraud-metrics-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Mail Config -->

<script>
    $('#mail-config').on('show.bs.modal', function(e){
        $(this).find('.mail-config-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for switch phrase collection -->

<script>
    $('#switch-phrase-collection').on('show.bs.modal', function(e){
        $(this).find('.switch-phrase-collection-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for build endpoint -->

<script>
    $('#build-endpoint').on('show.bs.modal', function(e){
        $(this).find('.build-endpoint-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Fraud Triangle Rulest -->

<script>
    $('#fraudTriangleRules').on('show.bs.modal', function(e){
        $(this).find('.fraud-triangle-phrases-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'CFont'"
        }
    }

    var ctx = document.getElementById("upper-right");
    var myChart = new Chart(ctx, {
        type: 'bar',
        defaults: defaultOptions,
        data: {
            labels: [ "Unique", "Events", "Sessions", "Typing", "Dead" ],
            datasets: [
                {
                    label: "Endpoint statistics",
                    type: 'line',
                    fill: false,
                    fillColor: "#13923D",
                    lineTension: 0.1,
                    backgroundColor: "rgb(75, 144, 111, 0.25)",
                    borderColor: "rgb(75, 144, 111, 0.75)",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'round',
                    pointBorderColor: "rgb(75, 144, 111, 1)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgb(75, 144, 111, 0.5)",
                    pointHoverBorderColor: "rgb(75, 144, 111, 0.25)",
                    pointHoverBorderWidth: 2,
                    pointRadius: 5,
                    pointHitRadius: 10,
                    data: [ <?php echo $countUniques['total'] . ", " . $countEvents . ", " . $countSessions['total'] . ", " . $countTyping['total'] . ", " . $countDead['total']; ?> ],
                    spanGaps: false,
                },
                {
                    label: "Endpoint statistics",
                    type: 'bar',
                    backgroundColor: [
                        "#bcdccd",
                        "#bcdccd",
                        "#bcdccd",
                        "#bcdccd",
                        "#bcdccd"
                    ],
                    borderColor: [],
                    hoverBackgroundColor: "#bcdccd",
                    borderWidth: 0,
                    data: [ <?php echo $countUniques['total'] . ", " . $countEvents . ", " . $countSessions['total'] . ", " . $countTyping['total'] . ", " . $countDead['total']; ?> ],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItems, data) {
                        return "General statistics"
                    },
                    label: function(tooltipItems, data) {
                        return "Status " + parseInt(tooltipItems.yLabel);
                    },
                    footer: function(tooltipItems, data) {
                        return data['labels'][tooltipItems[0]['index']] + " Category";
                    }
                },
                enabled: true,
                backgroundColor: "#ededed",
                titleFontColor: "#474747",
                bodyFontColor: "#474747",
                xPadding: 10,
                yPadding: 15,
                cornerRadius: 4,
                titleFontSize: 11,
                bodyFontSize: 11,
                footerFontSize: 11,
                borderColor: "#aaa",
                borderWidth: 2,
                caretPadding: 20,
                displayColors: false,
                titleMarginBottom: 15,
                footerFontColor: "#474747",
                titleFontFamily: "FFont-Bold"
            },
            animation: false,
            scales: {
                xAxes: [{       
                    }, {
                        position: 'top',
                        ticks: {
                            display: false
                        },
                        gridLines: {
                            display: false,
                            drawTicks: false
                        }
                    }],
                yAxes: [{ 
                    ticks: {
                        padding: 10,
                    }
                    }, {
                        position: 'right',
                        ticks: {
                            display: false
                        },
                        gridLines: {
                            display: false,
                            drawTicks: false
                        }
                    }]
            }
        }
    });
</script>

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'CFont'"
        }
    }
    
    var ctx = document.getElementById("bottom-left");
    var myChart = new Chart(ctx, {
        type: 'doughnut',
        defaults: defaultOptions,
        data : {
            labels: [ "Pressure", "Opportunity", "Rationalization" ],
            datasets: [
                {
                    <?php
                    
                    if ($fraudTerms['pressure'] == 0 && $fraudTerms['opportunity'] == 0 && $fraudTerms['rationalization'] == 0)
                    {
                        echo "data : [ 1, 1, 1 ],"; 
                    }
                    else
                    {
                        echo "data: [ ".$fraudTerms['pressure'].", ".$fraudTerms['opportunity'].", ".$fraudTerms['rationalization']."],";
                    }
                    
                    ?>
                    
                    backgroundColor: [
                        "#78ba9a",
                        "#9acbb3",
                        "#bcdccd"
                    ],
                    hoverBackgroundColor: [
                        "#78ba9a",
                        "#9acbb3",
                        "#bcdccd"
                    ],
                    hoverBorderWidth: 0
                }]
        },
        options: {
            cutoutPercentage: 60,
            responsive: true,
            hover: {mode: null},
            maintainAspectRatio: false,
            legend: {
                display: false
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItems, data) {
                        return "Fraud Triangle Score"
                    },
                    label: function(tooltipItems, data) {
                        var indice = tooltipItems.index;                 
                        return  "Status " + data.datasets[0].data[indice];
                    },
                    footer: function(tooltipItems, data) {
                        return data['labels'][tooltipItems[0]['index']] + " Vertice";
                    }
                },
                enabled: true,
                backgroundColor: "#ededed",
                titleFontColor: "#474747",
                bodyFontColor: "#474747",
                xPadding: 10,
                yPadding: 15,
                cornerRadius: 4,
                titleFontSize: 11,
                bodyFontSize: 11,
                footerFontSize: 11,
                borderColor: "#aaa",
                borderWidth: 2,
                caretPadding: 20,
                displayColors: false,
                titleMarginBottom: 15,
                footerFontColor: "#474747",
                titleFontFamily: "FFont-Bold"
            },
            animation: false
        }
    });
</script>

<!-- Dotdotdot script -->

<script>
$(document).ready(function() {
	$(".ellipsis").dotdotdot({
        watch : 'window' 
    });
});
</script>

<!-- Tooltipster -->

<script>
    $(document).ready(function(){
        $('.tooltip-custom').tooltipster({
            theme: 'tooltipster-custom',
            contentAsHTML: true,
            side: 'right',
            delay: 0,
            animationDuration: 0
        });
    });
</script>