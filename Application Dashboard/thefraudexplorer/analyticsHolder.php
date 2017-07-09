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
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for Chart
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

require 'vendor/autoload.php';
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";
include "lbs/elasticsearch.php";

/* Discover Online Endpoints */

discoverOnline();

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

            <form name="scope" method="post">
                <select class="select-scope-styled" name="ruleset" id="ruleset">
                    <option selected="selected"> <?php echo $_SESSION['rulesetScope']; ?></option>

                    <?php

                    $configFile = parse_ini_file("config.ini");
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
                    $queryAgentsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryAgentsGraphSQL = "SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent";
                    $queryAgentsGraphSQLRuleset = "SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent";
                }
                else
                {
                    $queryAgentsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryAgentsGraphSQL = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent";
                    $queryAgentsGraphSQLRuleset = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent";
                }
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    $queryAgentsGraphSQLLeyend = "SELECT * FROM t_config";
                    $queryAgentsGraphSQLDomain = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."' GROUP BY agent";
                    $queryAgentsGraphSQLRulesetDomain = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."' AND ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent";
                    $queryAgentsGraphSQL = "SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent";
                    $queryAgentsGraphSQLRuleset = "SELECT agent, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent";
                }
                else
                {
                    $queryAgentsGraphSQLLeyend = "SELECT * FROM t_config_".str_replace(".", "_", $session->domain);
                    $queryAgentsGraphSQLDomain = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='".$session->domain."' GROUP BY agent";
                    $queryAgentsGraphSQLRulesetDomain = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain='".$session->domain."' AND ruleset='".$_SESSION['rulesetScope']."' GROUP BY agent";
                    $queryAgentsGraphSQL = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent";
                    $queryAgentsGraphSQLRuleset = "SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$_SESSION['rulesetScope']."' AND domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent";
                }
            }
                   
            ?>

            <!-- Leyend -->

            <?php

            $scoreQuery = mysql_query($queryAgentsGraphSQLLeyend);
            $scoreResult = mysql_fetch_array($scoreQuery);

            ?>

            <span style="line-height: 0.3"><br></span>
            <table class="table-leyend">
                <th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-aw-color">&nbsp;&nbsp;</span>Score legend</th>
                <tr>
                    <td class="table-leyend-point"><span class="point-red"></span><br><?php echo $scoreResult['score_ts_critic_from'].">"; ?></td>
                    <td class="table-leyend-point"><span class="point-green"></span><br><?php echo $scoreResult['score_ts_high_from']."-".$scoreResult['score_ts_high_to']; ?></td>
                </tr>
                <tr>
                    <td class="table-leyend-point"><span class="point-blue"></span><br><?php echo $scoreResult['score_ts_medium_from']."-".$scoreResult['score_ts_medium_to']; ?></td>
                    <td class="table-leyend-point"><span class="point-yellow"></span><br><?php echo $scoreResult['score_ts_low_from']."-".$scoreResult['score_ts_low_to']; ?></td>
                </tr>
            </table>
            <span style="line-height: 0.1"><br></span>
            <table class="table-leyend">
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
            $configFile = parse_ini_file("config.ini");
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

            echo '<table class="table-insights">';
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

            echo '<table class="table-dictionary">';
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

            echo '<div class="y-axis-line"></div>';
            echo '<div class="y-axis-leyend"><span class="fa fa-bar-chart font-aw-color">&nbsp;&nbsp;</span>Pressure to commit Fraud - scale '.$countPressureTotal.'</div>';
            echo '<div class="x-axis-line-leyend"><br><span class="fa fa-line-chart font-aw-color">&nbsp;&nbsp;</span>Unethical behavior, Rationalization - scale '.$countRationalizationTotal.'</div>';
            
            /* Data Table */
            
            echo '<div class="data-table-icon"><br><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span><a href="graphicData" data-toggle="modal" data-target="#graphicdata" href="#">Access graphic data</a></div>';
                    
            ?>
            
            <div id="scatterplot">

                <?php

                function paintScatter($counter, $opportunityPoint, $agent, $score, $countPressure, $countOpportunity, $countRationalization)
                {
                    $agentEncoded=base64_encode(base64_encode($agent));
                    echo '<span id="point'.$counter.'" class="'.$opportunityPoint.' tooltip-custom pseudolink" title="<div class=tooltip-inside><b>'.$agent.'</b><table class=tooltip-table><tbody><tr><td>Total Fraud Score</td><td>'.$score.'</td></tr><tr><td>Pressure count</td><td>'.$countPressure.'</td></tr><tr><td>Opportunity count</td><td>'.$countOpportunity.'</td></tr><tr><td>Rationalization count</td><td>'.$countRationalization.'</td></tr></tbody></table></div>" onclick="javascript:location.href=\'alertData?agent='.$agentEncoded.'\'"></span>'."\n";
                }

                /* Elasticsearch querys for fraud triangle counts and score */

                $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

                /* Database querys */

                if($session->domain == "all")
                {
                    if ($_SESSION['rulesetScope'] == "ALL") $result_a = mysql_query($queryAgentsGraphSQL);
                    else $result_a = mysql_query($queryAgentsGraphSQLRuleset);
                }
                else
                {
                    if ($_SESSION['rulesetScope'] == "ALL") $result_a = mysql_query($queryAgentsGraphSQLDomain);
                    else $result_a = mysql_query($queryAgentsGraphSQLRulesetDomain);
                }

                /* Graph Logic */

                $counter = 1;

                if ($row_a = mysql_fetch_array($result_a))
                {
                    do
                    {
                        /* Agent data */

                        $countRationalization = $row_a['rationalization'];
                        $countOpportunity = $row_a['opportunity'];
                        $countPressure = $row_a['pressure'];
                        $score=($countPressure+$countOpportunity+$countRationalization)/3;

                        $score = round($score, 1);
                        unset($GLOBALS['numberOfRMatches']);
                        unset($GLOBALS['numberOfOMatches']);
                        unset($GLOBALS['numberOfPMatches']);
                        unset($GLOBALS['numberOfCMatches']);

                        if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= $scoreResult['score_ts_low_to'])
                        {
                            if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-low-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-low-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-low-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-low-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                        }

                        if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= $scoreResult['score_ts_medium_to'])
                        {
                            if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-medium-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-medium-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-medium-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-medium-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                        }

                        if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= $scoreResult['score_ts_high_to'])
                        {
                            if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-high-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-high-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-high-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-high-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                        }

                        if ($countOpportunity >= $scoreResult['score_ts_critic_from'])
                        {
                            if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-critic-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-critic-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-critic-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                            if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-critic-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                        }
                        $counter++;
                    }
                    while ($row_a = mysql_fetch_array($result_a));
                }
                ?>
            </div>
        </div>
    </div>
</center>

<!-- Scatterplot -->

<script type="text/javascript">
    $(document).ready(function () {
        $('#scatterplot').scatter({
            color: '#ededed',

            <?php

            /* Database querys */

            if($session->domain == "all")
            {
                if ($_SESSION['rulesetScope'] == "ALL")
                {
                    $result_a = mysql_query($queryAgentsGraphSQL);
                    $result_b = mysql_query($queryAgentsGraphSQL);
                }
                else
                {
                    $result_a = mysql_query($queryAgentsGraphSQLRuleset);
                    $result_b = mysql_query($queryAgentsGraphSQLRuleset);
                }
            }
            else
            {
                if ($_SESSION['rulesetScope'] == "ALL")
                {
                    $result_a = mysql_query($queryAgentsGraphSQLDomain);
                    $result_b = mysql_query($queryAgentsGraphSQLDomain);
                }
                else
                {
                    $result_a = mysql_query($queryAgentsGraphSQLRulesetDomain);
                    $result_b = mysql_query($queryAgentsGraphSQLRulesetDomain);
                }
            }

            /* Graph Logic */

            $counter = 1;
            $row_a = mysql_fetch_array($result_a);

            do
            {
                /* Agent data */

                $countRationalization = $row_a['rationalization'];
                $countOpportunity = $row_a['opportunity'];
                $countPressure = $row_a['pressure'];

                /*  Draw axis units */

                if ($counter == 1)
                {
                    $subCounter = 1;

                    /* Get max count value for both axis */

                    $row_aT = mysql_fetch_array($result_b);

                    do
                    {
                        /* Agent data */

                        $countRationalizationT[$subCounter] = $row_aT['rationalization'];
                        $countPressureT[$subCounter] = $row_aT['pressure'];

                        $subCounter++;
                    }
                    while ($row_aT = mysql_fetch_array($result_b));

                    $GLOBALS['maxYAxis'] = max($countPressureT);
                    $GLOBALS['maxXAxis'] = max($countRationalizationT);

                    echo 'rows: 2,';
                    echo 'columns: 2,';
                    echo 'subsections: 0,';
                    echo 'responsive: true';
                    echo '});';
                }

                /* Scoring calculation */

                $score=($countPressure+$countOpportunity+$countRationalization)/3;

                if($GLOBALS['maxYAxis'] == 0) $yAxis = ($countPressure*100)/1;
                else $yAxis = ($countPressure*100)/$GLOBALS['maxYAxis'];

                if($GLOBALS['maxXAxis'] == 0) $xAxis = ($countRationalization*100)/1;
                else $xAxis = ($countRationalization*100)/$GLOBALS['maxXAxis'];

                /* Fix corners */

                if ($xAxis == 100) $xAxis = $xAxis - 2;
                if ($yAxis == 100) $yAxis = $yAxis - 4.5;
                if ($xAxis == 0) $xAxis = $xAxis + 1.5;
                if ($yAxis == 0) $yAxis = $yAxis + 3;

                if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= $scoreResult['score_ts_low_to'])
                {
                    if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                }

                if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= $scoreResult['score_ts_medium_to'])
                {
                    if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                }

                if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= $scoreResult['score_ts_high_to'])
                {
                    if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                }

                if ($countOpportunity >= $scoreResult['score_ts_critic_from'] && $countOpportunity <= $scoreResult['score_ts_critic_to'])
                {
                    if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                    if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                }

                $counter++;
            }
            while ($row_a = mysql_fetch_array($result_a));

            ?>
        });
</script>

<!-- Tooltipster -->

<script>
    $(document).ready(function(){
        $('.tooltip-custom').tooltipster({
            theme: 'tooltipster-light',
            contentAsHTML: true
        });
    });
</script>

<!-- Modal for Ruleset -->

<div class="modal fade-scale" id="graphicdata" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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