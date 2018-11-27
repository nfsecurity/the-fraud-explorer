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
 * Date: 2019-01
 * Revision: v1.2.2-ai
 *
 * Description: Code for Chart
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

require '../vendor/autoload.php';
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";

?>

<!-- Styles -->

<style>
    .font-aw-color
    {
        color: #B4BCC2;
    }
</style>

<!-- Chart -->

<center>
    <div class="content-graph">
        <div class="graph-insights">

            <!-- Graph scope -->

            <form name="scope" method="post" id="elm-scope">
                <select class="select-scope-styled" name="ruleset" id="ruleset">
                    <option selected="selected"> <?php echo $_SESSION['rulesetScope']; ?></option>

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                    $GLOBALS['listRuleset'] = null;

                    echo '<option value="ALL">ALL</option>';

                    foreach ($jsonFT['dictionary'] as $ruleset => $value)
                    {
                        echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                    }

                    ?>

                </select>

                <span style="line-height: 0.7"><br><br></span>
                <input type="submit" name="submit" id="submit" value="Refresh graph" class="btn btn-default" style="width: 100%; outline:0 !important;" />
            </form>

            <!-- SQL Queries -->

            <?php

            
            if ($session->domain == "all")
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    $queryEndpointsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryEndpointsGraphSQL = "SELECT agent, ruleset, pressure, rationalization FROM (SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                }
                else
                {
                    $queryEndpointsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryEndpointsGraphSQL = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                }
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    $queryEndpointsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryEndpointsGraphSQLDomain = "SELECT agent, ruleset, pressure, rationalization FROM (SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRulesetDomain = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."' AND ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQL = "SELECT agent, ruleset, pressure, rationalization FROM (SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                }
                else
                {
                    $queryEndpointsGraphSQLLeyend = "SELECT * FROM t_config_".str_replace(".", "_", $session->domain);                                     
                    $queryEndpointsGraphSQLDomain = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='".$session->domain."' GROUP BY agent) AS duplicates WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY pressure, rationalization";            
                    $queryEndpointsGraphSQLRulesetDomain = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='".$session->domain."' AND ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";  
                    $queryEndpointsGraphSQL = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                }
            }
                   
            ?>

            <!-- Leyend -->

            <?php

            $scoreQuery = mysql_query($queryEndpointsGraphSQLLeyend);
            $scoreResult = mysql_fetch_array($scoreQuery);

            ?>

            <span style="line-height: 0.3"><br></span>
            <table class="table-leyend" id="elm-legend">
                <th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-aw-color">&nbsp;&nbsp;</span>Score legend</th>
                <tr>
                    <td class="table-leyend-point"><span class="point-red"></span><br><?php echo $scoreResult['score_ts_high_from']."-".$scoreResult['score_ts_critic_from'].">"; ?></td>
                    <td class="table-leyend-point"><span class="point-green"></span><br><?php echo $scoreResult['score_ts_low_from']."-".$scoreResult['score_ts_medium_to']; ?></td>
                </tr>
            </table>
            <span style="line-height: 0.1"><br></span>
            <table class="table-leyend" id="elm-opportunity">
                <th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-aw-color">&nbsp;&nbsp;</span>Opportunity</th>
                <tr>
                    <td class="table-leyend-point"><span class="point-opportunity-low"></span><br><?php echo $scoreResult['score_ts_low_from']."-".$scoreResult['score_ts_low_to']; ?></td>
                    <td class="table-leyend-point"><span class="point-opportunity-medium"></span><br><?php echo $scoreResult['score_ts_medium_from']."-".$scoreResult['score_ts_medium_to']; ?></td>
                </tr>
                <tr>
                    <td class="table-leyend-point"><span class="point-opportunity-high"></span><br><?php echo $scoreResult['score_ts_high_from']."-".$scoreResult['score_ts_high_to']; ?></td>
                    <td class="table-leyend-point"><span class="point-opportunity-critic"></span><br><?php echo $scoreResult['score_ts_critic_from'].">"; ?></td>
                </tr>
            </table>
            <span style="line-height: 0.1"><br></span>

            <!-- Insights -->

            <?php

            $client = Elasticsearch\ClientBuilder::create()->build();
            $configFile = parse_ini_file("../config.ini");
            $ESindex = $configFile['es_words_index'];
            $ESalerterIndex = $configFile['es_alerter_index'];
            $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

            /* Matches data */
            
            $matchesRationalizationCount = countAllFraudTriangleMatches($fraudTriangleTerms['r'], $configFile['es_alerter_index'], $session->domain, samplerStatus($session->domain));
            $matchesOpportunityCount = countAllFraudTriangleMatches($fraudTriangleTerms['o'], $configFile['es_alerter_index'], $session->domain, samplerStatus($session->domain));
            $matchesPressureCount = countAllFraudTriangleMatches($fraudTriangleTerms['p'], $configFile['es_alerter_index'], $session->domain, samplerStatus($session->domain));

            $countRationalizationTotal = $matchesRationalizationCount['count'];        
            $countOpportunityTotal = $matchesOpportunityCount['count'];
            $countPressureTotal = $matchesPressureCount['count'];

            echo '<table class="table-insights" id="elm-phrasecounts">';
            echo '<th colspan=2 class="table-insights-header"><span class="fa fa-align-justify font-aw-color">&nbsp;&nbsp;</span>Phrase counts</th>';
            echo '<tr>';
            echo '<td class="table-insights-triangle">Pressure</td>';
            echo '<td class="table-insights-score">'.$countPressureTotal.'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-insights-triangle">Opportunity</td>';
            echo '<td class="table-insights-score">'.$countOpportunityTotal.'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-insights-triangle">Rationalization</td>';
            echo '<td class="table-insights-score">'.$countRationalizationTotal.'</td>';
            echo '</tr>';
            echo '</table>';
            echo '<span style="line-height: 0.1"><br></span>';

            $fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');
            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
            $dictionaryCount = array('pressure'=>'0', 'opportunity'=>'0', 'rationalization'=>'0');

            foreach ($jsonFT['dictionary'] as $ruleset => $value)
            {
                foreach($fraudTriangleTerms as $term)
                {
                    foreach ($jsonFT['dictionary'][$ruleset][$term] as $field => $termPhrase)
                    {
                        $dictionaryCount[$term]++;
                    }
                }
            }

            echo '<table class="table-dictionary" id="elm-dictionarysize">';
            echo '<th colspan=2 class="table-dictionary-header"><span class="fa fa-align-justify font-aw-color">&nbsp;&nbsp;</span>Dictionary DB</th>';
            echo ' <tr>';
            echo '<td class="table-dictionary-triangle">Pressure</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['pressure'].'</td>';
            echo ' </tr>';
            echo ' <tr>';
            echo '<td class="table-dictionary-triangle">Opportunity</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['opportunity'].'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-dictionary-triangle">Rationalization</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['rationalization'].'</td>';
            echo '</tr>';
            echo '</table>';
            echo '<br>';
            echo '</div>';
            
            /* Axis calculation */
        
            if($session->domain == "all")
            {
                if ($_SESSION['rulesetScope'] == "ALL") $result_axis = mysql_query($queryEndpointsGraphSQL);
                else $result_axis = mysql_query($queryEndpointsGraphSQLRuleset);

            }
            else
            {
                if ($_SESSION['rulesetScope'] == "ALL") $result_axis = mysql_query($queryEndpointsGraphSQLDomain);
                else $result_axis = mysql_query($queryEndpointsGraphSQLRulesetDomain);
            }

            $axisCounter = 0;
            $row_axis = mysql_fetch_array($result_axis);
            
            do
            {    
                $axisRationalization[$axisCounter] = $row_axis['rationalization'];
                $axisPressure[$axisCounter] = $row_axis['pressure'];
                $axisCounter++;
            }
            while ($row_axis = mysql_fetch_array($result_axis));
                
            $xAxisGraph = max($axisPressure);
            $yAxisGraph = max($axisRationalization);
            
            echo '<div></div>';
            echo '<div class="y-axis-leyend"><span class="fa fa-bar-chart font-aw-color">&nbsp;&nbsp;</span>Pressure to commit Fraud - scale '.$xAxisGraph.'</div>';
            echo '<div class="x-axis-leyend"><br><span class="fa fa-line-chart font-aw-color">&nbsp;&nbsp;</span>Unethical behavior, Rationalization - scale '.$yAxisGraph.'</div>';
            
            /* Data Table & Events */
            
            echo '<div class="data-table-icon" id="elm-analyticsaccess"><br>';
            echo '<span class="fa fa-cogs font-aw-color">&nbsp;&nbsp;</span><a href="mods/expertSystem" data-toggle="modal" data-target="#expertSystem" href="#">Expert deductions</a>&nbsp;&nbsp;&nbsp;';
            echo '<span class="fa fa-exclamation-triangle font-aw-color">&nbsp;&nbsp;</span><a href="eventData?endpoint='.base64_encode(base64_encode("all")).'">Access all events</a>&nbsp;&nbsp;&nbsp;';
            echo '<span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span><a href="mods/graphicData" data-toggle="modal" data-target="#graphicdata" href="#">Vertical analytics</a>';
            echo '</div>';
                    
            ?>
            
            <div class="fraudtriangle-bubble-container">
                <div class="tl"><br>High Pressures&emsp;</div>
                <div class="tr"><br>&emsp;Fraud Triangle Consolidation</div>
                <div class="bl"><br>Low Fraud Triangle Behaviors&emsp;</div>
                <div class="br"><br>&emsp;High Rationalizations</div>
                <canvas id="fraudtriangle-graph"></canvas>
            </div>
        </div>
    </div>
</center>

<!-- Scatterplot -->

<script type="text/javascript">
    
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'FFont'"
        }
    }
    
    var canvas = document.getElementById("fraudtriangle-graph");
    var ctx = canvas.getContext("2d");
    var BubbleChart = new Chart(ctx, {
    type: 'bubble',
    data: { datasets: [

    <?php
    
        /* Database querys */
        
        if($session->domain == "all")
        {
            if ($_SESSION['rulesetScope'] == "ALL")
            {
                $result_a = mysql_query($queryEndpointsGraphSQL);
                $result_b = mysql_query($queryEndpointsGraphSQL);
            }
            else
            {
                $result_a = mysql_query($queryEndpointsGraphSQLRuleset);
                $result_b = mysql_query($queryEndpointsGraphSQLRuleset);
            }
        }
        else
        {
            if ($_SESSION['rulesetScope'] == "ALL")
            {
                $result_a = mysql_query($queryEndpointsGraphSQLDomain);
                $result_b = mysql_query($queryEndpointsGraphSQLDomain);
            }
            else
            {
                $result_a = mysql_query($queryEndpointsGraphSQLRulesetDomain);
                $result_b = mysql_query($queryEndpointsGraphSQLRulesetDomain);
            }
        }
        
        /* Graph Logic */
        
        $counter = 1;
        $row_a = mysql_fetch_array($result_a);
    
        do
        {
            /* Endpoint data */
            
            $queryOpportunity = "SELECT opportunity FROM (SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates WHERE agent='".$row_a['agent']."'";
            $result_opportunity = mysql_query($queryOpportunity);
            $opportunityValue = mysql_fetch_array($result_opportunity);
         
            $countRationalization = $row_a['rationalization'];
            $countOpportunity = $opportunityValue['opportunity'];
            $countPressure = $row_a['pressure'];
        
            /*  Draw axis units */
            
            if ($counter == 1)
            {
                $subCounter = 1;
            
                /* Get max count value for both axis */
                
                $row_aT = mysql_fetch_array($result_b);
                
                do
                {
                    /* Endpoint data */
                    
                    $countRationalizationT[$subCounter] = $row_aT['rationalization'];
                    $countPressureT[$subCounter] = $row_aT['pressure'];
                    $subCounter++;
                }
                while ($row_aT = mysql_fetch_array($result_b));
                
                $GLOBALS['maxYAxis'] = max($countPressureT);
                $GLOBALS['maxXAxis'] = max($countRationalizationT);
            }
                
            /* Scoring calculation */
            
            $score=($countPressure+$countOpportunity+$countRationalization)/3;
            
            if ($counter%2 == 0) $xAxis = $countRationalization + mt_rand(25,50)/100;
            else $xAxis = $countRationalization;
            
            $yAxis = $countPressure;
            
            /* Do not graph */
            
            if ($countRationalization == 0 && $countOpportunity == 0 && $countPressure == 0) continue;
                      
            /* Low criticality */
    
            if ($score >= $scoreResult['score_ts_low_from'] && $score <= $scoreResult['score_ts_low_to']+0.9)
            {
                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    $radiusPoint = 10;

                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(128,216,135,0.3)", borderWidth: 1.8, borderColor: "rgba(24,131,47,1)", hoverBackgroundColor: "rgba(128,216,135,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    $radiusPoint = 10;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    $radiusPoint = 10;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    $radiusPoint = 10;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
            }
            
            /* Medium criticality */
            
            else if ($score >= $scoreResult['score_ts_medium_from'] && $score <= $scoreResult['score_ts_medium_to']+0.9)
            {
                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    $radiusPoint = 12;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(85,195,89,0.6)", borderWidth: 1.8, borderColor: "rgba(24,131,47,1)", hoverBackgroundColor: "rgba(128,216,135,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    $radiusPoint = 12;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    $radiusPoint = 12;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    $radiusPoint = 12;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
            }
            
            /* High criticality */
            
            else if ($score >= $scoreResult['score_ts_high_from'] && $score <= $scoreResult['score_ts_high_to']+0.9)
            {
                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    $radiusPoint = 13;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(85,195,89,0.6)", borderWidth: 1.8, borderColor: "rgba(24,131,47,1)", hoverBackgroundColor: "rgba(128,216,135,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    $radiusPoint = 13;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    $radiusPoint = 13;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    $radiusPoint = 13;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
            }
            
            /* Critical criticality */
            
            else if ($score >= $scoreResult['score_ts_critic_from'] && $score <= $scoreResult['score_ts_critic_to']+0.9)
            {
                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to']))
                {
                    $radiusPoint = 15;
                    
                    echo '{ label: "Endpoints matching", backgroundColor: "rgba(85,195,89,0.6)", borderWidth: 1.8, borderColor: "rgba(24,131,47,1)", hoverBackgroundColor: "rgba(128,216,135,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    $radiusPoint = 15;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    $radiusPoint = 15;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'circle').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    $radiusPoint = 15;
                    
                    echo '{ label: \''.$row_a["agent"].'\', backgroundColor: "rgba(253,140,139,0.3)", borderWidth: 1.8, borderColor: "rgba(249,62,77,1)", hoverBackgroundColor: "rgba(253,140,139,0.7)", hoverBorderWidth: 1, pointStyle: \''.($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2) ? 'triangle' : 'star').'\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                }
            }
            $counter++;
        }
        while ($row_a = mysql_fetch_array($result_a));
        
    ?>
     
    ]},
    options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
            display: false
        },
        layout: {
            padding: {
                left: 80,
                right: 80,
                top: 80,
                bottom: 60
            }
        },
        tooltips: {
            callbacks: {
                title: function(tooltipItems, data) {
                    return "Endpoints Behavior";
                },
                label: function(tooltipItems, data) {
                    return "Pressure " + parseInt(tooltipItems.yLabel);
                },
                footer: function(tooltipItems, data){                                    
                    return "Rationalization " + parseInt(tooltipItems[0].xLabel);
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
                display: false,
                ticks: {
                    suggestedMin: 0,
                    display: false,
                    
                    <?php echo "suggestedMax: ".$GLOBALS['maxXAxis']; ?>
                },
                gridLines: {
                    drawTicks: false
                }
            }],
            yAxes: [{
                display: false,
                ticks: {
                    suggestedMin: 0,
                    display: false,
                    
                    <?php echo "suggestedMax: ".$GLOBALS['maxYAxis']; ?>
                },
                gridLines: {
                    drawTicks: false
                }
            }]
        },
        onClick: function(e) {
            var element = this.getElementAtEvent(e);

            if (element.length > 0) {
                var data = JSON.stringify(this.config.data.datasets[element[0]._datasetIndex].data[element[0]._index]);
                var url = "mods/analyticsGraphPoints.php?coordinates=" + data;
                
                $('.modal-body').load(url);
                $('#bubble-clicking').modal('show');
            }
        },
        hover: {
            onHover: function(e) {
                var point = this.getElementAtEvent(e);
                if (point.length) e.target.style.cursor = 'pointer';
                else e.target.style.cursor = 'default';
            }
        }
    }
});
 
</script>

<!-- Modal for Artificial Intelligence -->

<div class="modal" id="expertSystem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for GraphicData -->

<div class="modal" id="graphicdata" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Bubble clicking -->

<div class="modal" id="bubble-clicking" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title window-title" id="myModalLabel">Coordinate zoom</h4>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>
</div>