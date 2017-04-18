<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for paint dashboard
 */

include "lbs/login/session.php";

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

?>

<div class="dashboard-container">
    <div class="container-upper-left">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Fraud Triangle Endpoints (top 50)</p>
            <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
        </h2>
        <div class="container-upper-left-sub table-class">

            <table class="table">
                <thead class="table-head">
                    <tr class="tr">
                        <th class="th" style="padding-left: 10px;">
                            <span class="fa fa-desktop font-icon-color-gray">&nbsp;&nbsp;</span>ENDPOINT
                        </th>
                        <th class="th">
                            <center><span class="fa fa-exclamation-triangle font-icon-color-gray">&nbsp;&nbsp;</span>TRIANGLE</center>
                        </th>
                        <th class="th">
                            <center><span class="fa fa-list-alt font-icon-color-gray">&nbsp;&nbsp;</span>RULESET</center>
                        </th>
                        <th class="th">
                            <center><span class="fa fa-bar-chart font-icon-color-gray">&nbsp;&nbsp;</span>SCORE</center>
                        </th>
                    </tr>
                </thead>

                <tbody class="table-body">

                    <?php

                    $queryEndpointsSQL = "SELECT agent, ruleset, domain, SUM(pressure) AS pressure, SUM(opportunity) AS  opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, heartbeat, domain, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent order by score desc limit 50";
                    $queryEndpointsSQLDomain = "SELECT agent, ruleset, domain, SUM(pressure) AS pressure, SUM(opportunity) AS  opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, heartbeat, domain, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' group by agent order by score desc limit 50";
                    
                    if ($session->domain != "all") $queryEndpoints = mysql_query($queryEndpointsSQLDomain);
                    else $queryEndpoints = mysql_query($queryEndpointsSQL);

                    if($endpointsFraud = mysql_fetch_assoc($queryEndpoints))
                    {
                        do
                        {
                            echo '<tr class="tr">';
                            echo '<td class="td">';
                            echo '<span class="fa fa-laptop font-icon-color-gray">&nbsp;&nbsp;</span>'.$endpointsFraud['agent']."@".$endpointsFraud['domain'];
                            echo '</td>';

                            $triangleSum = $endpointsFraud['pressure']+$endpointsFraud['opportunity']+$endpointsFraud['rationalization'];
                            $triangleScore = round($endpointsFraud['score'], 2);

                            echo '<td class="td">';
                            echo '<center><span class="fa fa-tags font-icon-color-gray">&nbsp;&nbsp;</span>'.str_pad($triangleSum, 4, '0', STR_PAD_LEFT).'</center>';
                            echo '</td>';
                            echo '<td class="td">';
                            echo '<center>'.$endpointsFraud['ruleset'].'</center>';
                            echo '</td>';
                            echo '<td class="td">';
                            echo '<center><span class="fa fa-line-chart font-icon-color-gray">&nbsp;&nbsp;</span>'.str_pad($triangleScore, 6, '0', STR_PAD_LEFT).'</center>';
                            echo '</td>';
                        }
                        while ($endpointsFraud = mysql_fetch_assoc($queryEndpoints));
                    }

                    ?>

                </tbody>
            </table>
        </div>
    </div>

    <div class="container-upper-right">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Words typed and stored by day</p>
            <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
        </h2><br>
        <div class="container-upper-right-sub">
            <canvas id="upper-right"></canvas>
        </div>
    </div>

    <?php
    
    $queryTermsSQL = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'";
    $queryTermsSQLDomain = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='".$session->domain."'";
    $queryTermsSQLSampler = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='thefraudexplorer.com'";
    $samplerStatus = samplerStatus($session->domain);
    
    if ($samplerStatus == "enabled") $queryTerms = mysql_query($queryTermsSQLSampler);
    else
    {
        if ($session->domain != "all") $queryTerms = mysql_query($queryTermsSQLDomain);
        else $queryTerms = mysql_query($queryTermsSQL);
    }
        
    $fraudTerms = mysql_fetch_assoc($queryTerms);
    $fraudScore = ($fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'])/3;
    
    ?>

    <div class="container-bottom-left">
        <h2>
            <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Fraud triangle term statistics</p>
            <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
        </h2><br>
        <div class="container-bottom-left-sub">
            <div class="container-bottom-left-sub-one">
                <div class="container-bottom-left-sub-one-sub">
                    <p class="container-bottom-left-fraud-score"><?php echo round($fraudScore,1); ?></p>
                    </b><i class="fa fa-thermometer-quarter fa-lg font-icon-color-gray" aria-hidden="true">&nbsp;&nbsp;</i>Fraud score
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

<div class="container-bottom-right">
    <h2>
        <p class="container-title"><span class="fa fa-braille fa-lg">&nbsp;&nbsp;</span>Latest alerts by fraud triange (top 50)</p>
        <p class="container-window-icon"><span class="fa fa-window-maximize fa-lg font-icon-color-gray">&nbsp;&nbsp;</span></p>
    </h2>
    <div class="container-bottom-right-sub table-class">

        <table class="table">
            <thead class="thead">
                <tr class="tr">
                    <th class="th">
                        <span class="fa fa-calendar font-icon-color-gray">&nbsp;&nbsp;</span>DATE
                    </th>
                    <th class="th">
                        <span class="fa fa-exclamation-triangle font-icon-color-gray">&nbsp;&nbsp;</span>ALERT TYPE
                    </th>
                    <th class="th">
                        <span class="fa fa-desktop font-icon-color-gray">&nbsp;&nbsp;</span>ENDPOINT
                    </th>
                    <th class="th">
                        <span class="fa fa-font font-icon-color-gray">&nbsp;&nbsp;</span>PHRASE TYPED
                    </th>
                    <th class="th">
                        <span class="fa fa-list-alt font-icon-color-gray">&nbsp;&nbsp;</span>APPLICATION
                    </th>
                </tr>
            </thead>

            <tbody class="tbody">

                <?php

                $configFile = parse_ini_file("config.ini");
                $ESalerterIndex = $configFile['es_alerter_index'];
                
                if ($session->domain != "all") $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, $session->domain);
                else $alertMatches = getAllFraudTriangleMatches($ESalerterIndex, "all");
            
                $alertData = json_decode(json_encode($alertMatches), true);

                foreach ($alertData['hits']['hits'] as $result)
                {
                    echo '<tr class="tr">';
                    echo '<td class="td">';
                    echo '<center><span class="fa fa-clock-o font-icon-color-gray">&nbsp;&nbsp;</span>'.date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp'])).'</center>';
                    echo '</td>';
                    echo '<td class="td">';
                    echo '<span class="fa fa-tags font-icon-color-gray">&nbsp;&nbsp;</span>'.$result['_source']['alertType'];
                    echo '</td>';
                    echo '<td class="td">';

                    $endPoint = explode("_", $result['_source']['agentId']);
                    $queryUserDomain = mysql_query(sprintf("SELECT domain FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, domain FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as tbl2 WHERE agent='%s'", $endPoint[0]));
                    $userDomain = mysql_fetch_assoc($queryUserDomain);

                    echo '<span class="fa fa-laptop font-icon-color-gray">&nbsp;&nbsp;</span>'.$endPoint[0]."@".$userDomain['domain'];
                    echo '</td>';
                    echo '<td class="td">';
                    echo '<span class="fa fa-pencil-square-o font-icon-color-gray">&nbsp;&nbsp;</span>'.decRijndael($result['_source']['wordTyped']);
                    echo '</td>';
                    echo '<td class="td">';
                    echo '<span class="fa fa-list-alt font-icon-color-gray">&nbsp;&nbsp;</span>'.decRijndael($result['_source']['windowTitle']);
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

$queryAllDays = "SELECT * from t_words";
$queryDomainDays = "SELECT * from t_words_".str_replace(".", "_", $session->domain);
$querySampleDomainDays = "SELECT * from t_words_thefraudexplorer_com";

if ($samplerStatus == "disabled")
{
    if ($session->domain != "all") 
    {
        $queryDays = mysql_query($queryDomainDays);
        
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
    else $queryDays = mysql_query($queryAllDays);
}
else $queryDays = mysql_query($querySampleDomainDays);

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
