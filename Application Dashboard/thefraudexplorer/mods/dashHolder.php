<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 * 
 * Date: 2018-12
 * Revision: v1.2.1
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

require '../vendor/autoload.php';
include "../lbs/globalVars.php";
include "../lbs/cryptography.php";
include "../lbs/agentMethods.php";
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}

$resultWords = json_decode($resultWords, true);

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0";

?>

<div class="dashboard-container">
    <div class="container-upper-left" id="elm-top50endpoints">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Fraud Triangle Endpoints (top 50)</p>
            <p class="container-window-icon">
                <?php echo '&nbsp;<button type="button" class="download-csv-top50endpoints">Download as CSV</button>'; ?>&nbsp;
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2>
        <div class="container-upper-left-sub table-class">

            <table id="top50endpoints" class="table">

                <!-- Hidden table head for CSV purposes -->

                <thead style="display: none;">
                    <tr>
                        <th>COMPANY PEOPLE</th>
                        <th>TRIANGLE ALERTS</th>
                        <th>RULESET</th>
                        <th>BEHAVIOR SCORE</th>
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
                        if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysql_query($queryEndpointsSQL);
                        else $queryEndpoints = mysql_query($queryEndpointsSQL_wOSampler);
                    }
                    else
                    {
                        if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysql_query($queryEndpointsSQLDomain);
                        else $queryEndpoints = mysql_query($queryEndpointsSQLDomain_wOSampler);
                    }

                    if($endpointsFraud = mysql_fetch_assoc($queryEndpoints))
                    {
                        do
                        {
                            $agentName = $endpointsFraud['agent']."@".between('@', '.', "@".$endpointsFraud['domain']);
                            $agent_enc = base64_encode(base64_encode($endpointsFraud['agent']));
                            $totalWordHits = $endpointsFraud['totalwords'];
                            $countPressure = $endpointsFraud['pressure'];
                            $countOpportunity = $endpointsFraud['opportunity'];
                            $countRationalization = $endpointsFraud['rationalization'];
                            $score = $endpointsFraud['score'];
                            
                            if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
                            else $dataRepresentation = "0";
                            
                            echo '<tr class="tr">';
                            echo '<td class="td">';
                            echo '<span class="fa fa-laptop font-icon-color-green awfont-padding-right"></span>';
                            
                            if ($endpointsFraud["name"] == NULL || $endpointsFraud['name'] == "NULL") agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
                            else 
                            {
                                $agentName = $endpointsFraud['name'];
                                agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
                            }

                            echo '</td>';

                            $triangleSum = $endpointsFraud['pressure']+$endpointsFraud['opportunity']+$endpointsFraud['rationalization'];
                            $triangleScore = round($endpointsFraud['score'], 2);

                            echo '<td class="td">';
                            echo '<center><span class="fa fa-tags font-icon-color-gray awfont-padding-right"></span>'.str_pad($triangleSum, 4, '0', STR_PAD_LEFT).'</center>';
                            echo '</td>';
                            echo '<td class="td td-with-bg">';
                            echo '<center>'.$endpointsFraud['ruleset'].'</center>';
                            echo '</td>';
                            echo '<td class="td">';
                            echo '<center><span class="fa fa-line-chart font-icon-color-green awfont-padding-right"></span>'.str_pad($triangleScore, 6, '0', STR_PAD_LEFT).'</center>';
                            echo '</td>';
                        }
                        while ($endpointsFraud = mysql_fetch_assoc($queryEndpoints));
                    }

                    ?>

                </tbody>
                <tfoot class="table-head">
                    <tr class="tr">
                        <th class="th" style="padding-left: 7px;">
                            <span class="fa fa-users fa-lg font-icon-color-gray awfont-padding-right"></span>COMPANY PEOPLE 
                        </th>
                        <th class="th" style="padding-right: 25px;">
                            <center><span class="fa fa-diamond fa-lg font-icon-color-gray awfont-padding-right"></span>TRIANGLE ALERTS</center>
                        </th>
                        <th class="th" style="padding-right: 25px;">
                            <center><span class="fa fa-folder-open fa-lg font-icon-color-gray awfont-padding-right"></span>RULESET</center>
                        </th>
                        <th class="th" style="padding-right: 25px;">
                            <center><span class="fa fa-bell fa-lg font-icon-color-gray awfont-padding-right"></span>BEHAVIOR SCORE</center>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="container-upper-right" id="elm-generalstatistics">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Endpoints general statistics</p>
            <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
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
        if ($samplerStatus == "enabled") $queryTerms = mysql_query($queryTermsSQL);
        else $queryTerms = mysql_query($queryTermsSQL_wOSampler);
    }
    else
    {
        if ($samplerStatus == "enabled") $queryTerms = mysql_query($queryTermsSQLDomain);
        else $queryTerms = mysql_query($queryTermsSQLDomain_wOSampler);
    }
        
    $fraudTerms = mysql_fetch_assoc($queryTerms);
    $fraudScore = ($fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'])/3;
    
    ?>

    <div class="container-bottom-left" id="elm-termstatistics">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Fraud triangle term statistics</p>
            <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
        </h2><br>
        <div class="container-bottom-left-sub">
            <div class="container-bottom-left-sub-one">
                <div class="container-bottom-left-sub-one-sub">
                    <p class="container-bottom-left-fraud-score"><?php echo round($fraudScore,1); ?></p>
                    </b><i class="fa fa-thermometer-quarter fa-lg font-icon-color-gray" aria-hidden="true">&nbsp;&nbsp;</i>Behavioral score
                </div>
                <canvas id="bottom-left" style="z-index:1;"></canvas>
            </div>
            <div class="container-bottom-left-sub-two">
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-pressure"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">[Pressure]</p><p class="content-vertex-text"> personal (addiction, discipline, gambling), corporate (compensation, fear to lose the job) or external (market, ego, image, reputation).</p>
                        </div>
                    </div>
                </div>
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-opportunity"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">[Opportunity]</p><p class="content-vertex-text"> araises when the fraudster sees a way to use their position of trust to solve a problem, knowing they are unlikely to be caught.</p>
                        </div>
                    </div>
                </div>
                <div class="container-bottom-left-sub-two-sub">
                    <div class="container-bottom-left-sub-two-sub-one">
                        <div class="container-bottom-left-sub-two-sub-one-rational"></div>
                        <div class="block-with-text ellipsis">
                            <p class="title-text">[Rationalization]</p><p class="content-vertex-text"> the final component needed to complete the fraud triangle. It's the ability to persuade yourself that something is really ok.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-bottom-right" id="elm-top50alerts">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Latest alerts by fraud triange (top 50)</p>
            <p class="container-window-icon">
                <?php echo '<a href="alertData?agent='.base64_encode(base64_encode("all")).'" class="button-view-all-alerts" id="elm-viewallalerts">&nbsp;&nbsp;View all alerts&nbsp;&nbsp;</a>'; ?>
                <?php echo '&nbsp;<button type="button" class="download-csv-top50alerts">Download as CSV</button>'; ?>&nbsp;
                <span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span>
            </p>
        </h2>
        <div class="container-bottom-right-sub table-class">

            <table id="top50alerts" class="table">

                <!-- Hidden table head for CSV purposes -->

                <thead style="display: none;">
                    <tr>
                        <th>ALERT MOMENTUM</th>
                        <th>COMPANY PEOPLE</th>
                        <th>FRAUD VERTICE</th>
                        <th>PHRASE ORIGIN</th>
                    </tr>
                </thead>

                <tbody class="table-body">

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $ESalerterIndex = $configFile['es_alerter_index'];
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));
                
                    if ($session->domain != "all") 
                    {
                        if (samplerStatus($session->domain) == "enabled") $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, $session->domain, "enabled", "dashboard");
                        else $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, $session->domain, "disabled", "dashboard");
                    }
                    else
                    {
                        if (samplerStatus($session->domain) == "enabled") $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, "all", "enabled", "dashboard");
                        else $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, "all", "disabled", "dashboard");
                    }
                
                    $alertData = json_decode(json_encode($alertMatches), true);

                    foreach ($alertData['hits']['hits'] as $result)
                    {
                        echo '<tr class="tr">';
                        echo '<td class="td">';
                    
                        $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
                        $wordTyped = decRijndael($result['_source']['wordTyped']);
                        $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                        $searchValue = "/".$result['_source']['phraseMatch']."/";
                        $endPoint = explode("_", $result['_source']['agentId']);
                        $agent_decSQ = $endPoint[0];
                        $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
                        $searchResult = searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset);
                        $regExpression = htmlentities($result['_source']['phraseMatch']);

                        echo '<span class="fa fa-id-card-o font-icon-color-green awfont-padding-right"></span>'.$date;
                    
                        echo '</td>';
                    
                        echo '<td class="td">';
                 
                        $queryUserDomain = mysql_query(sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
                    
                        $userDomain = mysql_fetch_assoc($queryUserDomain);
                        $agentName = $userDomain['agent']."@".between('@', '.', "@".$userDomain['domain']);
                        $agent_enc = base64_encode(base64_encode($userDomain['agent']));
                        $totalWordHits = $userDomain['totalwords'];
                        $countPressure = $userDomain['pressure'];
                        $countOpportunity = $userDomain['opportunity'];
                        $countRationalization = $userDomain['rationalization'];
                        $score = $userDomain['score'];
                            
                        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
                        else $dataRepresentation = "0";
                    
                        echo '<span class="fa fa-laptop font-icon-color-gray awfont-padding-right"></span>';
                                    
                        if ($userDomain["name"] == NULL || $userDomain['name'] == "NULL") agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
                        else 
                        {
                            $agentName = $userDomain['name'];
                            agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
                        }
                    
                        echo '<td class="td td-with-bg">';
                        echo '<center>'.strtoupper($result['_source']['alertType']).'</center>';
                        echo '</td>';
                        
                        echo '</td>';
                        echo '<td class="td">';
                        echo '<span class="fa fa-pencil-square-o font-icon-color-green awfont-padding-right"></span>'.strip_tags(substr($wordTyped,0,80));
                        echo '</td>';
                                              
                        echo '</tr>';
                    }

                    ?>

                </tbody>
                <tfoot class="table-head">
                    <tr class="tr">
                        <th class="th">
                            <span class="fa fa-calendar-o fa-lg font-icon-color-gray awfont-padding-right"></span>ALERT MOMENTUM
                        </th>
                        <th class="th">
                            <span class="fa fa-users fa-lg font-icon-color-gray awfont-padding-right"></span>COMPANY PEOPLE
                        </th>
                        <th class="th" style="padding-right: 25px;">
                            <center><span class="fa fa-diamond fa-lg font-icon-color-gray awfont-padding-right"></span>FRAUD VERTICE</center>
                        </th>
                        <th class="th" style="padding-left: 1px;">
                            <span class="fa fa-pencil-square-o fa-lg font-icon-color-gray awfont-padding-right"></span>PHRASE ORIGIN
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
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM ((SELECT agent FROM t_agents WHERE totalwords <> '0') AS typing)) AS totals GROUP BY agent) AS totalplus";
    }
    else 
    {
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain NOT LIKE 'thefraudexplorer.com'";      
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM ((SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing)) AS totals GROUP BY agent) AS totalplus WHERE domain NOT LIKE 'thefraudexplorer.com'";
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled") 
    { 
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM ((SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing)) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
    }
    else 
    {
        $queryUniqueEndpoints = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryEndpointSessions = "SELECT COUNT(*) AS total FROM t_agents WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM ((SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing)) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
    }
}

$countUniques = mysql_fetch_assoc(mysql_query($queryUniqueEndpoints));
$countSessions = mysql_fetch_assoc(mysql_query($queryEndpointSessions));
$countDead = mysql_fetch_assoc(mysql_query($queryDeadEndpoints));
$countTyping = mysql_fetch_assoc(mysql_query($queryTyping));
$countAlerts = $fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'];

?>

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
            labels: [ "Unique", "Alerts", "Sessions", "Dead", "Typing" ],
            datasets: [
                {
                    label: "Endpoint statistics",
                    type: 'bar',
                    backgroundColor: [
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)'
                    ],
                    borderColor: [],
                    borderWidth: 1,
                    data: [ <?php echo $countUniques['total'] . ", " . $countAlerts . ", " . $countSessions['total'] . ", " . $countDead['total'] . ", " . $countTyping['total']; ?> ],
                },
                 {
                    label: "Endpoint statistics",
                    type: 'line',
                    fill: true,
                    fillColor: "#13923D",
                    lineTension: 0.1,
                    backgroundColor: "rgba(19, 146, 61, 0.25)",
                    borderColor: "rgba(19, 146, 61, 0.75)",
                    borderCapStyle: 'butt',
                    borderDash: [],
                    borderDashOffset: 0.0,
                    borderJoinStyle: 'miter',
                    pointBorderColor: "rgba(19, 146, 61, 1)",
                    pointBackgroundColor: "#fff",
                    pointBorderWidth: 1,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: "rgba(19, 146, 61, 0.75)",
                    pointHoverBorderColor: "rgba(19, 146, 61, 0.25)",
                    pointHoverBorderWidth: 2,
                    pointRadius: 5,
                    pointHitRadius: 10,
                    data: [ <?php echo $countUniques['total'] . ", " . $countAlerts . ", " . $countSessions['total'] . ", " . $countDead['total'] . ", " . $countTyping['total']; ?> ],
                    spanGaps: false,
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
                    ticks: {
                        min: 0
                    },
                    gridLines: {
                        offsetGridLines: true
                    }
                }],
                yAxes: [{
                    ticks: {
                        min: 0
                    },
                    gridLines: {
                        offsetGridLines: true
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
                        "#48A969",
                        "#BDDAC7",
                        "#94C9A5"
                    ],
                    hoverBackgroundColor: [
                        "#48A969",
                        "#BDDAC7",
                        "#94C9A5"
                    ]
                }]
        },
        options: {
            cutoutPercentage: 60,
            responsive: true,
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