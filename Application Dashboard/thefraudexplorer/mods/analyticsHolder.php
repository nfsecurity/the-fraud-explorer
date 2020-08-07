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
 * Description: Code for Chart
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
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/cryptography.php";

?>

<!-- Styles -->

<style>

    .font-aw-color
    {
        color: #B4BCC2;
    }

    .btn-success, .btn-success:hover, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-success, .btn-success:active, .btn-success:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }
    
</style>

<!-- Chart -->

<center>
    <div class="content-graph">
        <div class="graph-insights">

            <!-- Graph scope -->

            <form name="scope" method="post" id="elm-scope">
                <select class="select-scope-styled" name="ruleset" id="ruleset">
                    <option selected="selected">&#xf07b;&nbsp;&nbsp;<?php echo $_SESSION['rulesetScope']; ?></option>

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                    $GLOBALS['listRuleset'] = null;

                    foreach ($jsonFT['dictionary'] as $ruleset => $value)
                    {
                        echo '<option value="'.$ruleset.'">&#xf07b;&nbsp;&nbsp;'.$ruleset.'</option>';
                    }

                    ?>

                </select>

                <span style="line-height: 0.7"><br><br></span>
                <input type="submit" name="submit" id="submit" value="Refresh graph" class="btn btn-default" style="width: 100%; outline:0 !important;"/>
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
                    $queryEndpointsGraphSQLDomain = "SELECT agent, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
                    $queryEndpointsGraphSQLRulesetDomain = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE (domain='thefraudexplorer.com' OR domain='".$session->domain."') AND ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
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

            $scoreQuery = mysqli_query($connection, $queryEndpointsGraphSQLLeyend);
            $scoreResult = mysqli_fetch_array($scoreQuery);

            ?>

            <table class="table-leyend" id="elm-legend" style="margin-top: 4px;">
                <th colspan=2 class="table-leyend-header"><span class="fa fa-folder-o fa-lg font-aw-color">&nbsp;&nbsp;</span>Plot Graphic legend</th>
                <tr>
                    <td class="table-leyend-point-left"><span class="fa fa-3x fa-asterisk asterisk-color-low"></span><br>low</td>
                    <td class="table-leyend-point-right"><span class="fa fa-3x fa-asterisk asterisk-color-high"></span><br>high</td>
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

            if($session->domain == "all")
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    if ($_SESSION['rulesetScope'] == "ALL") 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents");
                    }
                    else 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."'");
                    }
                }
                else
                {
                    if ($_SESSION['rulesetScope'] == "ALL") 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'");
                    }
                    else 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com'");
                    }
                }
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    if ($_SESSION['rulesetScope'] == "ALL") 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE domain = '".$session->domain."' OR domain = 'thefraudexplorer.com'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE domain = '".$session->domain."' OR domain = 'thefraudexplorer.com'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE domain = '".$session->domain."' OR domain = 'thefraudexplorer.com'");
                    }
                    else 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND (domain = '".$session->domain."' OR domain = 'thefraudexplorer.com')");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND (domain = '".$session->domain."' OR domain = 'thefraudexplorer.com')");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND (domain = '".$session->domain."' OR domain = 'thefraudexplorer.com')");
                    }
                }
                else
                {
                    if ($_SESSION['rulesetScope'] == "ALL") 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE domain = '".$session->domain."'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE domain = '".$session->domain."'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE domain = '".$session->domain."'");
                    }
                    else 
                    {
                        $resultCountPressure = mysqli_query($connection, "SELECT SUM(pressure) AS totalPressure FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain = '".$session->domain."'");
                        $resultCountOpportunity = mysqli_query($connection, "SELECT SUM(opportunity) AS totalOpportunity FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain = '".$session->domain."'");
                        $resultCountRationalization = mysqli_query($connection, "SELECT SUM(rationalization) AS totalRationalization FROM t_agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain = '".$session->domain."'");
                    }
                }
            }

            $resultCountPressure = mysqli_fetch_array($resultCountPressure);
            $resultCountOpportunity = mysqli_fetch_array($resultCountOpportunity);
            $resultCountRationalization = mysqli_fetch_array($resultCountRationalization);

            $countPressureTotal = ($resultCountPressure['totalPressure'] == NULL ? 0 : $resultCountPressure['totalPressure']);              
            $countOpportunityTotal = ($resultCountOpportunity['totalOpportunity'] == NULL ? 0 : $resultCountOpportunity['totalOpportunity']);
            $countRationalizationTotal = ($resultCountRationalization['totalRationalization'] == NULL ? 0 : $resultCountRationalization['totalRationalization']); 

            echo '<table class="table-insights" id="elm-phrasecounts">';
            echo '<th colspan=2 class="table-insights-header-phrase-counts"><span class="fa fa-folder-o fa-lg font-aw-color">&nbsp;&nbsp;</span>Fraud phrase count</th>';
            echo '<tr>';
            echo '<td class="table-insights-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Pressure</td>';
            echo '<td class="table-insights-score">'.$countPressureTotal.'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-insights-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Opportunity</td>';
            echo '<td class="table-insights-score">'.$countOpportunityTotal.'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-insights-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Rationalization</td>';
            echo '<td class="table-insights-score">'.$countRationalizationTotal.'</td>';
            echo '</tr>';
            echo '</table>';
            echo '<span style="line-height: 0.1"><br></span>';

            $fraudTriangleTerms = array('pressure','opportunity','rationalization');
            $fta_lang = $configFile['fta_lang_selection'];

            if ($fta_lang == "fta_text_rule_multilanguage") 
            {
                $numberOfLibraries = 2;
                $jsonFTA[1] = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                $jsonFTA[2] = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);
            }
            else 
            {
                $numberOfLibraries = 1;
                $jsonFTA[1] = json_decode(file_get_contents($configFile[$fta_lang]), true);
            }

            $dictionaryCount = array('pressure'=>'0', 'opportunity'=>'0', 'rationalization'=>'0');

            for ($lib = 1; $lib<=$numberOfLibraries; $lib++)
            {  
                foreach ($jsonFTA[$lib]['dictionary'] as $ruleset => $value)
                {
                    foreach($fraudTriangleTerms as $term)
                    {
                        foreach ($jsonFTA[$lib]['dictionary'][$ruleset][$term] as $field => $termPhrase)
                        {
                            $dictionaryCount[$term]++;
                        }
                    }
                }
            }

            echo '<table class="table-dictionary" id="elm-dictionarysize">';
            echo '<th colspan=2 class="table-dictionary-header"><span class="fa fa-folder-o fa-lg font-aw-color">&nbsp;&nbsp;</span>Phrases in database</th>';
            echo '<tr>';
            echo '<td class="table-dictionary-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Pressure</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['pressure'].'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-dictionary-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Opportunity</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['opportunity'].'</td>';
            echo '</tr>';
            echo '<tr>';
            echo '<td class="table-dictionary-triangle"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Rationalization</td>';
            echo '<td class="table-dictionary-score">'.$dictionaryCount['rationalization'].'</td>';
            echo '</tr>';
            echo '</table>';

            /* Add, delete or modify rules */

            echo '<span style="line-height: 0.5"><br></span>';
            echo '<a href="../mods/fraudTriangleRules" data-toggle="modal" class="fraud-triangle-rules-button btn btn-success" data-target="#fraudTriangleRules" href="#" id="elm-fraud-triangle-rules">Library workshop</a>';
            echo '</div>';
            
            /* Axis calculation */
        
            if($session->domain == "all")
            {
                if ($_SESSION['rulesetScope'] == "ALL") $result_axis = mysqli_query($connection, $queryEndpointsGraphSQL);
                else $result_axis = mysqli_query($connection, $queryEndpointsGraphSQLRuleset);

            }
            else
            {
                if ($_SESSION['rulesetScope'] == "ALL") $result_axis = mysqli_query($connection, $queryEndpointsGraphSQLDomain);
                else $result_axis = mysqli_query($connection, $queryEndpointsGraphSQLRulesetDomain);
            }

            $axisCounter = 0;
            $row_axis = mysqli_fetch_array($result_axis);
            
            do
            {    
                $axisRationalization[$axisCounter] = $row_axis['rationalization'];
                $axisPressure[$axisCounter] = $row_axis['pressure'];
                $axisCounter++;
            }
            while ($row_axis = mysqli_fetch_array($result_axis));
                
            $xAxisGraph = max($axisPressure);
            $yAxisGraph = max($axisRationalization);
            
            echo '<div></div>';
            
            /* Data Table & Events */
            
            echo '<div class="data-table-icon"><br>';
            echo '<span class="fa fa-th-large fa-lg font-aw-color">&nbsp;&nbsp;</span><a href="mods/expertSystem" data-toggle="modal" data-target="#expertSystem" href="#" id="elm-ai">Artificial intelligence expert deductions</a>&nbsp;&nbsp;&nbsp;';
            echo '<span class="fa fa-th-list fa-lg font-aw-color">&nbsp;&nbsp;</span><a href="eventData?nt='.encRijndael("all").'" id="elm-analyticsaccess">All fraud triangle events</a>&nbsp;&nbsp;&nbsp;';
            echo '<span class="fa fa-table fa-lg font-aw-color">&nbsp;&nbsp;</span><a href="mods/graphicData" data-toggle="modal" data-target="#graphicdata" href="#" id="elm-vertical">Vertical analytics</a>';
            echo '</div>';
                    
            ?>
            
            <div class="fraudtriangle-bubble-container">
                <div class="tl"><br>
                    <span class="fa fa-chevron-right font-aw-color">&nbsp;</span>High Pressures&emsp;
                    <div class="y-axis-button"><center><div class="axis-button-title">axy</div></center><center>P</center></div>
                    <div class="x-axis-button"><center><div class="axis-button-title">axx</div></center><center>R</center></div>
                </div>
                <div class="tr" id="elm-bubble"><br>&emsp;<span class="fa fa-chevron-right font-aw-color">&nbsp;</span>High Behaviors</div>
                <div class="bl"><br><div class="bl-leyend"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>Some Behaviors</div></div>
                <div class="br"><br><div class="br-leyend"><span class="fa fa-chevron-right font-aw-color">&nbsp;</span>High Rationals</div></div>
                <canvas id="fraudtriangle-graph"></canvas>
            </div>
        </div>
    </div>
</center>

<!-- Modal for Fraud Triangle Rulest -->

<script>
    $('#fraudTriangleRules').on('show.bs.modal', function(e){
        $(this).find('.fraud-triangle-rules-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

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
                $result_a = mysqli_query($connection, $queryEndpointsGraphSQL);
                $result_b = mysqli_query($connection, $queryEndpointsGraphSQL);
            }
            else
            {
                $result_a = mysqli_query($connection, $queryEndpointsGraphSQLRuleset);
                $result_b = mysqli_query($connection, $queryEndpointsGraphSQLRuleset);
            }
        }
        else
        {
            if ($_SESSION['rulesetScope'] == "ALL")
            {
                $result_a = mysqli_query($connection, $queryEndpointsGraphSQLDomain);
                $result_b = mysqli_query($connection, $queryEndpointsGraphSQLDomain);
            }
            else
            {
                $result_a = mysqli_query($connection, $queryEndpointsGraphSQLRulesetDomain);
                $result_b = mysqli_query($connection, $queryEndpointsGraphSQLRulesetDomain);
            }
        }
        
        /* Graph Logic */
        
        $counter = 1;
        $row_a = mysqli_fetch_array($result_a);
    
        do
        {
            /* Endpoint data */
            
            $queryOpportunity = "SELECT opportunity FROM (SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates WHERE agent='".$row_a['agent']."'";
            $result_opportunity = mysqli_query($connection, $queryOpportunity);
            $opportunityValue = mysqli_fetch_array($result_opportunity);
         
            $countRationalization = $row_a['rationalization'];
            $countOpportunity = $opportunityValue['opportunity'];
            $countPressure = $row_a['pressure'];
        
            /*  Draw axis units */
            
            if ($counter == 1)
            {
                $subCounter = 1;
            
                /* Get max count value for both axis */
                
                $row_aT = mysqli_fetch_array($result_b);
                
                do
                {
                    /* Endpoint data */
                    
                    $countRationalizationT[$subCounter] = $row_aT['rationalization'];
                    $countPressureT[$subCounter] = $row_aT['pressure'];
                    $subCounter++;
                }
                while ($row_aT = mysqli_fetch_array($result_b));
                
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
                      
            /* Low criticality Pressure */
    
            if ($countPressure >= $scoreResult['score_ts_low_from'] && $countPressure <= $scoreResult['score_ts_low_to']+0.9)
            {
                /* Low Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Medium Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    /* Rationalization*/

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* High Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Critical Opportunity */

                if ($countOpportunity  >= $scoreResult['score_ts_critic_from']) 
                {
                   /* Rationalization */

                   if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }
            }
            
            /* Medium criticality Pressure */
            
            else if ($countPressure >= $scoreResult['score_ts_medium_from'] && $countPressure <= $scoreResult['score_ts_medium_to']+0.9)
            {
                /* Low Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Medium Opportunity */ 

                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* High Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Critical Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }
            }
            
            /* High criticality Pressure */
            
            else if ($countPressure >= $scoreResult['score_ts_high_from'] && $countPressure <= $scoreResult['score_ts_high_to']+0.9)
            {
                /* Low Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Medium Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* High opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Critical Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }
            }
            
            /* Critical Pressure criticality */
            
            else if ($countPressure >= $scoreResult['score_ts_critic_from'] && $countPressure <= $scoreResult['score_ts_critic_to']+0.9)
            {
                /* Low Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= ($scoreResult['score_ts_low_to']))
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Medium Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= ($scoreResult['score_ts_medium_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* High Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= ($scoreResult['score_ts_high_to'])) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }

                /* Critical Opportunity */

                if ($countOpportunity >= $scoreResult['score_ts_critic_from']) 
                {
                    /* Rationalization */

                    if ($countRationalization >= $scoreResult['score_ts_low_from'] && $countRationalization <= ($scoreResult['score_ts_low_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_medium_from'] && $countRationalization <= ($scoreResult['score_ts_medium_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_high_from'] && $countRationalization <= ($scoreResult['score_ts_high_to'])) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                    if ($countRationalization >= $scoreResult['score_ts_critic_from']) 
                    {
                        if ($xAxis >= ($GLOBALS['maxXAxis']/2) || $yAxis >= ($GLOBALS['maxYAxis']/2)) 
                        {
                            $radiusPoint = 12;
                            echo '{ backgroundColor: "rgb(75, 144, 111, 0.9)", borderWidth: 4, borderColor: "rgb(75, 144, 111, 0.9)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                        else 
                        {
                            $radiusPoint = 10;
                            echo '{ backgroundColor: "rgba(75, 144, 111, 0.5)", borderWidth: 4, borderColor: "rgba(75, 144, 111, 0.5)", hoverBackgroundColor: "rgba(75, 144, 111, 1)", hoverBorderWidth: 5, pointStyle: \'star\', data: [{ x: '.$xAxis.', y: '.$yAxis.', r: '.$radiusPoint.' } ]},';
                        }
                    }
                }
            }
            $counter++;
        }
        while ($row_a = mysqli_fetch_array($result_a));
        
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
                var url = "mods/analyticsGraphPoints.php?oo=" + data;
                
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

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
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

<!-- Modal for Fraud Triangle Rules -->

<div class="modal" id="fraudTriangleRules" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center" style="width: 760px;">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>
