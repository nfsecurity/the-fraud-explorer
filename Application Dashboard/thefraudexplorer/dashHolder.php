<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 * Revision: v1.0.1-beta
 *
 * Description: Code for paint dashboard
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

require 'vendor/autoload.php';
include "lbs/global-vars.php";
include "lbs/cryptography.php";
include "lbs/agent_methods.php";
include "lbs/elasticsearch.php";
include "lbs/open-db-connection.php";

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

/* Order connected endpoints */

discoverOnline();

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

            <table id="top50endpoints" class="table tablesorter">
                <thead class="table-head">
                    <tr class="tr">
                        <th class="th" style="padding-left: 10px;">
                            ENDPOINT
                        </th>
                        <th class="th">
                            <center>TRIANGLE</center>
                        </th>
                        <th class="th">
                            <center>RULESET</center>
                        </th>
                        <th class="th">
                            <center>SCORE</center>
                        </th>
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
                            $agentName = $endpointsFraud['agent']."@".$endpointsFraud['domain'];
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
                            echo '<span class="fa fa-laptop font-icon-color-gray awfont-padding-right"></span>';
                            
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
                            echo '<td class="td">';
                            echo '<center>'.$endpointsFraud['ruleset'].'</center>';
                            echo '</td>';
                            echo '<td class="td">';
                            echo '<center><span class="fa fa-line-chart font-icon-color-gray awfont-padding-right"></span>'.str_pad($triangleScore, 6, '0', STR_PAD_LEFT).'</center>';
                            echo '</td>';
                        }
                        while ($endpointsFraud = mysql_fetch_assoc($queryEndpoints));
                    }

                    ?>

                </tbody>
            </table>
        </div>
    </div>

    <div class="container-upper-right" id="elm-wordstyped">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Words typed and stored by day</p>
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

        <table id="top50alerts" class="table tablesorter">
            <thead class="thead">
                <tr class="tr">
                    <th class="th">
                        DATE
                    </th>
                    <th class="th">
                        ALERT TYPE
                    </th>
                    <th class="th">
                        ENDPOINT
                    </th>
                    <th class="th">
                        PHRASE TYPED
                    </th>
                    <th class="th">
                        APPLICATION
                    </th>
                </tr>
            </thead>

            <tbody class="tbody">

                <?php

                $configFile = parse_ini_file("config.ini");
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
                    
                    alertDetails("dashBoard", $date, $wordTyped, $windowTitle, $searchResult, $regExpression, $result);
                    echo $date;
                    
                    echo '</td>';
                    
                    echo '<td class="td">';
                    echo '<span class="fa fa-tags font-icon-color-gray awfont-padding-right"></span>'.$result['_source']['alertType'];
                    echo '</td>';
                    echo '<td class="td">';
                 
                    $queryUserDomain = mysql_query(sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
                    
                    $userDomain = mysql_fetch_assoc($queryUserDomain);
                    $agentName = $userDomain['agent']."@".$userDomain['domain'];
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
                    
                    echo '</td>';
                    echo '<td class="td">';
                    echo '<span class="fa fa-pencil-square-o font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($wordTyped,0,80));
                    echo '</td>';
                    echo '<td class="td">';
                    echo '<span class="fa fa-list-alt font-icon-color-gray awfont-padding-right"></span>'.strip_tags(substr($windowTitle,0,80));
                    echo '</td>';
                    echo '</tr>';
                }

                ?>

            </tbody>
        </table>
    </div>
</div>
</div>

<?php

$queryAllDays_wOSampler = "SELECT * from t_words";
$queryAllDay_wSampler = "SELECT SUM(monday) AS monday, SUM(tuesday) AS tuesday, SUM(wednesday) AS wednesday, SUM(thursday) AS thursday, SUM(friday) AS friday, SUM(saturday) AS saturday, SUM(sunday) AS sunday FROM (SELECT * FROM t_words UNION SELECT * FROM t_words_thefraudexplorer_com) as tbl";
$queryDomainDays_wSampler = "SELECT SUM(monday) AS monday, SUM(tuesday) AS tuesday, SUM(wednesday) AS wednesday, SUM(thursday) AS thursday, SUM(friday) AS friday, SUM(saturday) AS saturday, SUM(sunday) AS sunday FROM (SELECT * FROM t_words_".str_replace(".", "_", $session->domain)." UNION SELECT * FROM t_words_thefraudexplorer_com) as tbl";
$queryDomainDays_wOSampler = "SELECT * from t_words_".str_replace(".", "_", $session->domain);

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled") $queryDays = mysql_query($queryAllDay_wSampler);
    else $queryDays = mysql_query($queryAllDays_wOSampler);
}
else
{
    if (samplerStatus($session->domain) == "enabled") $queryDays = mysql_query($queryDomainDays_wSampler);
    else $queryDays = mysql_query($queryDomainDays_wOSampler);
    
    if(empty($queryDays))
    {
        $query = "CREATE TABLE t_words_".str_replace(".", "_", $session->domain)." (
        monday int DEFAULT NULL,
        tuesday int DEFAULT NULL,
        wednesday int DEFAULT NULL,
        thursday int DEFAULT NULL,
        friday int DEFAULT NULL,
        saturday int DEFAULT NULL,
        sunday int DEFAULT NULL)";
            
        $insert = "INSERT INTO t_words_".str_replace(".", "_", $session->domain)." (
        monday, tuesday, wednesday, thursday, friday, saturday, sunday) VALUES ('0', '0', '0', '0', '0', '0', '0')";
            
        $resultQuery = mysql_query($query);
        $resultInsert = mysql_query($insert);
    }
}

$rows = array();
while($row = mysql_fetch_assoc($queryDays)) $rows[] = $row;
$daysOfWeek = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");

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
            labels: ["Monday", "Tuesday", "Wednesday", "Thuersday", "Friday", "Saturday", "Sunday"],
            datasets: [
                {
                    label: "Words by Day",
                    type: 'bar',
                    backgroundColor: [
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)',
                        'rgba(19, 146, 61, 0.25)'
                    ],
                    borderColor: [],
                    borderWidth: 1,
                    data: [ <?php foreach ($daysOfWeek as $day) { if ($day != "sunday") echo $rows[0][$day].", "; else echo $rows[0][$day]; } ?> ],
                },
                {
                    label: "Words by Day",
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
                    data: [ <?php foreach ($daysOfWeek as $day) { if ($day != "sunday") echo $rows[0][$day].", "; else echo $rows[0][$day]; } ?> ],
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
                enabled: true,
                backgroundColor: "#ededed",
                titleFontColor: "#474747",
                bodyFontColor: "#474747",
                xPadding: 10,
                yPadding: 10,
                cornerRadius: 3,
                titleFontSize: 11,
                bodyFontSize: 11
            },
            animation: false,
            scales: {
                xAxes: [{
                    gridLines: {
                        offsetGridLines: false
                    }
                }],
                yAxes: [{
                    ticks: {
                        min: 0
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
                enabled: true,
                backgroundColor: "#ededed",
                titleFontColor: "#474747",
                bodyFontColor: "#474747",
                xPadding: 10,
                yPadding: 10,
                cornerRadius: 3,
                titleFontSize: 11,
                bodyFontSize: 11
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

<!-- Table sorting -->

<script>
    $(document).ready(function(){
        
        $('.download-csv-top50alerts').click(function(){
            $("#top50alerts").trigger('outputTable');
        });
        
        $('.download-csv-top50endpoints').click(function(){
            $("#top50endpoints").trigger('outputTable');
        });
        
        $("#top50alerts").tablesorter({
            widgets: [ 'filter', 'output' ],
            widgetOptions : 
            {
                filter_external: '.search_text',
                filter_columnFilters : false,
                output_separator: ',',
                output_dataAttrib: 'data-name',
                output_headerRows: false,
                output_delivery: 'download',
                output_saveRows: 'all',
                output_replaceQuote: '\u201c;',
                output_includeHTML: false,
                output_trimSpaces: true,
                output_wrapQuotes: false,
                output_saveFileName: 'top50Alerts.csv',
                output_callback: function (data) {
                    return true;
                },
                output_callbackJSON: function ($cell, txt, cellIndex) {
                    return txt + '(' + (cellIndex + col) + ')';
                }
            },
            headers:
            {
                3:
                {
                    sorter: false
                },
                4:
                {
                    sorter: false
                }
            },
            sortList: [[0,1]] 
        });
        
        $("#top50endpoints").tablesorter({
            widgets: [ 'filter', 'output' ],
            widgetOptions : 
            {
                filter_external: '.search_text',
                filter_columnFilters : false,
                output_separator: ',',
                output_dataAttrib: 'data-name',
                output_headerRows: false,
                output_delivery: 'download',
                output_saveRows: 'all',
                output_replaceQuote: '\u201c;',
                output_includeHTML: false,
                output_trimSpaces: true,
                output_wrapQuotes: false,
                output_saveFileName: 'top50Endpoints.csv',
                output_callback: function (data) {
                    return true;
                },
                output_callbackJSON: function ($cell, txt, cellIndex) {
                    return txt + '(' + (cellIndex + col) + ')';
                }
            },
            headers:
            {
                0:
                {
                    sorter: false
                }
            },
            sortList: [[3,1]] 
        });
    
    }); 
</script>

<!-- Tooltipster -->

<script>
    $(document).ready(function(){
        $('.tooltip-custom').tooltipster({
            theme: 'tooltipster-light',
            contentAsHTML: true,
            side: 'right'
        });
    });
</script>