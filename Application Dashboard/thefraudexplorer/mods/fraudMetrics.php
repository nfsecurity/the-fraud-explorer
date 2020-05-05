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
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: Code for fraud metrics
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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
require "../vendor/autoload.php";
include "../lbs/elasticsearch.php";

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];

$firstTime = false;

if ($_COOKIE['endpointFraudMetrics_launch'] == 0) 
{
    $rulesetSelected = "BASELINE";
    $firstTime = true;
}
else
{
    $rulesetSelected = $_COOKIE['endpointFraudMetrics_ruleset'];
    if (isset($_COOKIE['endpointFraudMetrics_ruleset'])) setcookie("endpointFraudMetrics_ruleset", "", time() - 3600);
}

if (isset($_COOKIE['endpointFraudMetrics_pressure'])) 
{           
    $pressureCheck = $_COOKIE['endpointFraudMetrics_pressure'];
    setcookie("endpointFraudMetrics_pressure", "", time() - 3600);
}

if (isset($_COOKIE['endpointFraudMetrics_opportunity'])) 
{
    $opportunityCheck = $_COOKIE['endpointFraudMetrics_opportunity'];
    setcookie("endpointFraudMetrics_opportunity", "", time() - 3600);
}

if (isset($_COOKIE['endpointFraudMetrics_rationalization'])) 
{
    $rationalizationCheck = $_COOKIE['endpointFraudMetrics_rationalization'];
    setcookie("endpointFraudMetrics_rationalization", "", time() - 3600);
}

/* Metrics logic */

$zeroQuery = false;

if ($session->domain == "all")
{
    if ($firstTime == false)
    {
        $fraudTerms = $pressureCheck . " " . $opportunityCheck . " " . $rationalizationCheck;
        $fraudTerms = str_replace(array("true", "false"), array("1", "0"), $fraudTerms);

        for ($i = 0; $i <= 11; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $monthName[] = substr(date("F", strtotime($months[$i])), 0, 3);

            if ($rulesetSelected == "BASELINE")
            {
                if ($fraudTerms == "1 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics) AS QUERY", $i, $i, $i));
                if ($fraudTerms == "1 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO FROM t_metrics) AS QUERY", $i, $i));
                if ($fraudTerms == "1 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP) AS SUM FROM (SELECT SUM(%sP) AS SUMP FROM t_metrics) AS QUERY", $i));
                if ($fraudTerms == "1 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sR) AS SUMR FROM t_metrics) AS QUERY", $i, $i));
                if ($fraudTerms == "0 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMR) AS SUM FROM (SELECT SUM(%sR) AS SUMR FROM t_metrics) AS QUERY", $i));
                if ($fraudTerms == "0 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO+SUMR) AS SUM FROM (SELECT SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics) AS QUERY", $i, $i));
                if ($fraudTerms == "0 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO) AS SUM FROM (SELECT SUM(%sO) AS SUMO FROM t_metrics) AS QUERY", $i));
                if ($fraudTerms == "0 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT * FROM t_metrics WHERE 1 != 1"));
            }
            else
            {
                if ($fraudTerms == "1 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $i, $i, $rulesetSelected));
                if ($fraudTerms == "1 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $i, $rulesetSelected));
                if ($fraudTerms == "1 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP) AS SUM FROM (SELECT SUM(%sP) AS SUMP FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $rulesetSelected));
                if ($fraudTerms == "1 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $i, $rulesetSelected));
                if ($fraudTerms == "0 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMR) AS SUM FROM (SELECT SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $rulesetSelected));
                if ($fraudTerms == "0 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO+SUMR) AS SUM FROM (SELECT SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $i, $rulesetSelected));
                if ($fraudTerms == "0 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO) AS SUM FROM (SELECT SUM(%sO) AS SUMO FROM t_metrics WHERE ruleset='%s') AS QUERY", $i, $rulesetSelected));
                if ($fraudTerms == "0 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT * FROM t_metrics WHERE 1 != 1"));
            }
        
            $sumValue = mysqli_fetch_all($resultSQL, MYSQLI_ASSOC);

            if (mysqli_num_rows($resultSQL) == 0) 
            {
                $countAlerts[$i] = 0;
                $zeroQuery = true;
            }
            else $countAlerts[$i] = $sumValue[0]['SUM'];
        }
    }
    else 
    {
        /* First time modal load */

        for ($i = 0; $i <= 11; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $monthName[] = substr(date("F", strtotime($months[$i])), 0, 3);

            $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics) AS QUERY", $i, $i, $i));
            $sumValue = mysqli_fetch_all($resultSQL, MYSQLI_ASSOC);

            if (mysqli_num_rows($resultSQL) == 0) 
            {
                $countAlerts[$i] = 0;
                $zeroQuery = true;
            }
            else $countAlerts[$i] = $sumValue[0]['SUM'];
        }
    }
}
else
{
    if ($firstTime == false)
    {
        $queryEndpointsSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$rulesetSelected."' AND domain='".$session->domain."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";

        $fraudTerms = $pressureCheck . " " . $opportunityCheck . " " . $rationalizationCheck;
        $fraudTerms = str_replace(array("true", "false"), array("1", "0"), $fraudTerms);

        for ($i = 0; $i <= 11; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $monthName[] = substr(date("F", strtotime($months[$i])), 0, 3);

            if ($rulesetSelected == "BASELINE")
            {
                if ($fraudTerms == "1 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE domain='%s') AS QUERY", $i, $i, $i, $session->domain));
                if ($fraudTerms == "1 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO FROM t_metrics WHERE domain='%s') AS QUERY", $i, $i, $session->domain));
                if ($fraudTerms == "1 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP) AS SUM FROM (SELECT SUM(%sP) AS SUMP FROM t_metrics WHERE domain='%s') AS QUERY", $i, $session->domain));
                if ($fraudTerms == "1 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sR) AS SUMR FROM t_metrics WHERE domain='%s') AS QUERY", $i, $i, $session->domain));
                if ($fraudTerms == "0 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMR) AS SUM FROM (SELECT SUM(%sR) AS SUMR FROM t_metrics WHERE domain='%s') AS QUERY", $i, $session->domain));
                if ($fraudTerms == "0 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO+SUMR) AS SUM FROM (SELECT SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE domain='%s') AS QUERY", $i, $i, $session->domain));
                if ($fraudTerms == "0 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO) AS SUM FROM (SELECT SUM(%sO) AS SUMO FROM t_metrics WHERE domain='%s') AS QUERY", $i, $session->domain));
            }
            else
            {
                if ($fraudTerms == "1 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $i, $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "1 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "1 0 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP) AS SUM FROM (SELECT SUM(%sP) AS SUMP FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "1 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "0 0 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMR) AS SUM FROM (SELECT SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "0 1 1") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO+SUMR) AS SUM FROM (SELECT SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $i, $rulesetSelected, $session->domain));
                if ($fraudTerms == "0 1 0") $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMO) AS SUM FROM (SELECT SUM(%sO) AS SUMO FROM t_metrics WHERE ruleset='%s' AND domain='%s') AS QUERY", $i, $rulesetSelected, $session->domain));
            }
        
            $sumValue = mysqli_fetch_all($resultSQL, MYSQLI_ASSOC);

            if (mysqli_num_rows($resultSQL) == 0) 
            {
                $countAlerts[$i] = 0;
                $zeroQuery = true;
            }
            else $countAlerts[$i] = $sumValue[0]['SUM'];
        }
    }
    else 
    {
         /* First time modal load */

         for ($i = 0; $i <= 11; $i++) 
         {
             $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
             $monthName[] = substr(date("F", strtotime($months[$i])), 0, 3);
 
             $resultSQL = mysqli_query($connection, sprintf("SELECT SUM(SUMP+SUMO+SUMR) AS SUM FROM (SELECT SUM(%sP) AS SUMP, SUM(%sO) AS SUMO, SUM(%sR) AS SUMR FROM t_metrics WHERE domain='%s') AS QUERY", $i, $i, $i, $session->domain));
             $sumValue = mysqli_fetch_all($resultSQL, MYSQLI_ASSOC);
 
             if (mysqli_num_rows($resultSQL) == 0) 
             {
                 $countAlerts[$i] = 0;
                 $zeroQuery = true;
             }
             else $countAlerts[$i] = $sumValue[0]['SUM'];
         }
    }
}

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config-fraudmetrics
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }
    
    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .fraud-metrics-graph-container
    {
        height: 250px;
        padding: 20px 15px 15px 15px;
        border: 0px solid gray;
        border-radius: 3px;
        background: #FAFAFA;
    }

    .master-container-metrics
    {
        width: 100%; 
        height: 70px !important;
    }
    
    .left-container-metrics
    {
        width: calc(50% - 5px); 
        display: inline;
        float: left;
    }
    
    .right-container-metrics
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: right;
    }

    .select-ruleset-metrics
    {
        margin-right: 0px;
        min-height: 30px !important;
        max-height: 30px !important;
        padding: 8px 0px 8px 10px;
        line-height: 11.6px;
        border: 1px solid #ccc;
        color: #757575;
    }

    .select-ruleset-metrics .list
    {
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        max-height: 200px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .fraud-metrics-reload-button, .fraud-metrics-noreload-button
    {
        color: white !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Fraud Triangle Metrics</h4>

    <?php 
    
        if ($firstTime == false) 
        {
            $underVerticePressure = ($pressureCheck == "true" ? "P" : "");
            $underVerticeOpportunity = ($opportunityCheck == "true" ? "O" : "");
            $underVerticeRationalization = ($rationalizationCheck == "true" ? "R" : "");

            if ($zeroQuery == true) echo "<p>Please select at leat one vertice&emsp;&emsp;</p>";
            else if ($rulesetSelected == "BASELINE") echo '<p>Metrics filtered for all business units under ['.$underVerticePressure.$underVerticeOpportunity.$underVerticeRationalization.']&emsp;&emsp;</p>';
            else echo '<p>Metrics filtered for ' . $rulesetSelected . ' under ['.$underVerticePressure.$underVerticeOpportunity.$underVerticeRationalization.']&emsp;&emsp;</p>';
        }
        else echo '<p>Metrics for all business units under [POR]&emsp;&emsp;</p>';
        
    ?>
</div>

<div class="div-container">

    <div class="fraud-metrics-graph-container">
        
        <?php

            if ($_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) 
            {
                echo '<canvas id="fraud-metrics-graph"></canvas>'; 
            }
            else 
            {
                echo '<canvas id="fraud-metrics-graph-reloaded"></canvas>';
            }

        ?>
        
    </div>

    <div class="master-container-metrics">
            <div class="left-container-metrics">              
                
                <p class="title-config">Filter by fraud triangle vertice</p><br><br>

                <div style="line-height:9px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">                                  
                    <label class="btn btn-default btn-sm <?php if ($firstTime == false) { if ($pressureCheck == "true") echo "active"; else echo ""; } else echo "active"; ?>" id="<?php if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo 'checkboxPressurePar'; else echo 'checkboxPressureImpar'; ?>" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-size: 12px !important;">

                    <?php

                        if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) 
                        {
                            if ($firstTime == false)
                            {
                                if ($pressureCheck == "true") echo '<input type="checkbox" onchange="checkboxPressurePar()" name="pressurepar" value="pressure" id="pressurepar" autocomplete="off" checked>Pressure</input>';
                                else echo '<input type="checkbox" onchange="checkboxPressurePar()" name="pressurepar" value="pressure" id="pressurepar" autocomplete="off">Pressure</input>';
                            }
                            else echo '<input type="checkbox" onchange="checkboxPressurePar()" name="pressurepar" value="pressure" id="pressurepar" autocomplete="off" checked>Pressure</input>';
                        }
                        else 
                        {
                            if ($firstTime == false)
                            {
                                if ($pressureCheck == "true") echo '<input type="checkbox" onchange="checkboxPressureImpar()" name="pressureimpar" value="pressure" id="pressureimpar" autocomplete="off" checked>Pressure</input>';
                                else echo '<input type="checkbox" onchange="checkboxPressureImpar()" name="pressureimpar" value="pressure" id="pressureimpar" autocomplete="off">Pressure</input>';

                            }
                            else echo '<input type="checkbox" onchange="checkboxPressureImpar()" name="pressureimpar" value="pressure" id="pressureimpar" autocomplete="off" checked>Pressure</input>';
                        }
                        
                    ?>

                    </label>
                </div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 95px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm <?php if ($firstTime == false) { if ($opportunityCheck == "true") echo "active"; else echo ""; } else echo "active"; ?>" id="<?php if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo 'checkboxOpportunityPar'; else echo 'checkboxOpportunityImpar'; ?>" style="width: 95px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-size: 12px !important;">

                    <?php

                        if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) 
                        {
                            if ($firstTime == false)
                            {
                                if ($opportunityCheck == "true") echo '<input type="checkbox" onchange="checkboxOpportunityPar()" name="opportunitypar" value="opportunity" id="opportunitypar" autocomplete="off" checked>Opportunity</input>';
                                else echo '<input type="checkbox" onchange="checkboxOpportunityPar()" name="opportunitypar" value="opportunity" id="opportunitypar" autocomplete="off">Opportunity</input>';
                            }
                            else echo '<input type="checkbox" onchange="checkboxOpportunityPar()" name="opportunitypar" value="opportunity" id="opportunitypar" autocomplete="off" checked>Opportunity</input>';
                        }
                        else 
                        {
                            if ($firstTime == false)
                            {
                                if ($opportunityCheck == "true") echo '<input type="checkbox" onchange="checkboxOpportunityImpar()" name="opportunityimpar" value="opportunity" id="opportunityimpar" autocomplete="off" checked>Opportunity</input>';
                                else echo '<input type="checkbox" onchange="checkboxOpportunityImpar()" name="opportunityimpar" value="opportunity" id="opportunityimpar" autocomplete="off">Opportunity</input>';
                            }
                            else echo '<input type="checkbox" onchange="checkboxOpportunityImpar()" name="opportunityimpar" value="opportunity" id="opportunityimpar" autocomplete="off" checked>Opportunity</input>';
                        }
                        
                    ?>

                    </label>
                </div>          

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm <?php if ($firstTime == false) { if ($rationalizationCheck == "true") echo "active"; else echo ""; } else echo "active"; ?>" id="<?php if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo 'checkboxRationalizationPar'; else echo 'checkboxRationalizationImpar'; ?>" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-size: 12px !important;">

                    <?php

                        if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) 
                        {
                            if ($firstTime == false)
                            {
                                if ($rationalizationCheck == "true") echo '<input type="checkbox" onchange="checkboxRationalizationPar()" name="rationalizationpar" value="rationalization" id="rationalizationpar" autocomplete="off" checked>Rational</input>';
                                else echo '<input type="checkbox" onchange="checkboxRationalizationPar()" name="rationalizationpar" value="rationalization" id="rationalizationpar" autocomplete="off">Rational</input>';
                            }
                            else echo '<input type="checkbox" onchange="checkboxRationalizationPar()" name="rationalizationpar" value="rationalization" id="rationalizationpar" autocomplete="off" checked>Rational</input>';
                        }
                        else 
                        {
                            if ($firstTime == false)
                            {
                                if ($rationalizationCheck == "true") echo '<input type="checkbox" onchange="checkboxRationalizationImpar()" name="rationalizationimpar" value="rationalization" id="rationalizationimpar" autocomplete="off" checked>Rational</input>';
                                else echo '<input type="checkbox" onchange="checkboxRationalizationImpar()" name="rationalizationimpar" value="rationalization" id="rationalizationimpar" autocomplete="off">Rational</input>';
                            }
                            else echo '<input type="checkbox" onchange="checkboxRationalizationImpar()" name="rationalizationimpar" value="rationalization" id="rationalizationimpar" autocomplete="off" checked>Rational</input>';
                        }
                        
                    ?>

                    </label>
                </div>
          
            </div>

            <div class="right-container-metrics">
                   
                <p class="title-config">Filter by business unit</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>

                <?php

                    if ($_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo '<select class="select-ruleset-metrics wide" name="ruleset" id="ruleset-businesspar">';
                    else echo '<select class="select-ruleset-metrics wide" name="ruleset" id="ruleset-businessimpar">';

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                    $GLOBALS['listRuleset'] = null;

                    if ($firstTime == true)
                    {
                        foreach ($jsonFT['dictionary'] as $ruleset => $value)
                        {
                            if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected>ALL BUSINESS UNITS</option>';
                            else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                        }
                    }
                    else
                    {
                        foreach ($jsonFT['dictionary'] as $ruleset => $value)
                        {
                            if ($ruleset == $rulesetSelected) 
                            {
                                if ($rulesetSelected == "BASELINE") echo '<option value="'.$ruleset.'" selected>ALL BUSINESS UNITS</option>';
                                else echo '<option value="'.$ruleset.'" selected>'.$ruleset.'</option>'; 
                            }
                            else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                        }
                    } 

                ?>

                </select>
                    
            </div>
    </div>

    <br>
    <div class="modal-footer window-footer-config-fraudmetrics">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>

        <?php

            if ($_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo '<a href="../mods/fraudMetrics" onclick="getFiltersPar()" class="btn btn-success fraud-metrics-reload-button" id="btn-metrics-par" data-toggle="modal" data-dismiss="modal" data-target="#fraud-metrics-reload" style="outline: 0 !important;">Apply filters</a>';
            else echo '<a href="../mods/fraudMetrics" onclick="getFiltersImpar()" class="btn btn-success fraud-metrics-noreload-button" id="btn-metrics-impar" data-toggle="modal" data-dismiss="modal" data-target="#fraud-metrics" style="outline: 0 !important;">Apply filters</a>';
        
        ?>

    </div>

</div>

<!-- Modal for Fraud Metrics -->

<script>
    $('#fraud-metrics-reload').on('show.bs.modal', function(e){
        $(this).find('.fraud-metrics-reload-button').attr('href', $(e.relatedTarget).data('href'));
    });

    $('#fraud-metrics-reload').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Javascript for filters Par -->

<script>
    function getFiltersPar()
    {
        var businessData = document.getElementById("ruleset-businesspar").value;

        if (document.getElementById('pressurepar').checked) pressure = true;
        else pressure = false;

        if (document.getElementById('opportunitypar').checked) opportunity = true;
        else opportunity = false;

        if (document.getElementById('rationalizationpar').checked) rationalization = true;
        else rationalization = false;

        var launchCounter = parseInt($.cookie('endpointFraudMetrics_launch')) + 1;

        $.cookie('endpointFraudMetrics_ruleset', businessData);
        $.cookie('endpointFraudMetrics_pressure', pressure);
        $.cookie('endpointFraudMetrics_opportunity', opportunity);
        $.cookie('endpointFraudMetrics_rationalization', rationalization);
        $.cookie('endpointFraudMetrics_launch', launchCounter);
    }
</script>

<!-- Javascript for filters Impar-->

<script>
    function getFiltersImpar()
    {
        var businessData = document.getElementById("ruleset-businessimpar").value;

        if (document.getElementById('pressureimpar').checked) pressure = true;
        else pressure = false;

        if (document.getElementById('opportunityimpar').checked) opportunity = true;
        else opportunity = false;

        if (document.getElementById('rationalizationimpar').checked) rationalization = true;
        else rationalization = false;

        var launchCounter = parseInt($.cookie('endpointFraudMetrics_launch')) + 1;

        $.cookie('endpointFraudMetrics_ruleset', businessData);
        $.cookie('endpointFraudMetrics_pressure', pressure);
        $.cookie('endpointFraudMetrics_opportunity', opportunity);
        $.cookie('endpointFraudMetrics_rationalization', rationalization);
        $.cookie('endpointFraudMetrics_launch', launchCounter);
    }
</script>

<!-- Graph --> 

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'CFont'"
        }
    }

    var ctx = document.getElementById("<?php if (@$_COOKIE['endpointFraudMetrics_launch'] % 2 != 0) echo 'fraud-metrics-graph'; else echo 'fraud-metrics-graph-reloaded'; ?>");
    var myChart = new Chart(ctx, {
        type: 'line',
        defaults: defaultOptions,
        data: {
            labels: [ <?php echo '"'. $monthName[11] . '"'; ?>, <?php echo '"'. $monthName[10] . '"'; ?>, <?php echo '"'. $monthName[9] . '"'; ?>, <?php echo '"'. $monthName[8] . '"'; ?>, <?php echo '"'. $monthName[7] . '"'; ?>, <?php echo '"'. $monthName[6] . '"'; ?>, <?php echo '"'. $monthName[5] . '"'; ?>, <?php echo '"'. $monthName[4] . '"'; ?>, <?php echo '"'. $monthName[3] . '"'; ?>, <?php echo '"'. $monthName[2] . '"'; ?>, <?php echo '"'. $monthName[1] . '"'; ?>, <?php echo '"'. $monthName[0] . '"'; ?> ],
            datasets: [
                {
                    label: "Fraud Metrics",
                    type: 'line',
                    yAxisID: "y-axis-right-normal",
                    fill: true,
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

                    <?php

                        echo 'data: [ "'. $countAlerts[11] . '","' . $countAlerts[10] . '","' . $countAlerts[9] . '","' . $countAlerts[8] . '","' . $countAlerts[7] . '","' . $countAlerts[6] . '","' . $countAlerts[5] . '","' . $countAlerts[4] . '","' . $countAlerts[3] . '","' . $countAlerts[2] . '","' . $countAlerts[1] . '","' . $countAlerts[0] . '" ],';

                    ?>

                    spanGaps: false,
                },
                {
                    label: "Fraud Metrics",
                    type: 'line',
                    yAxisID: "y-axis-left-normal",
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
                    pointBorderWidth: 0,
                    pointHoverRadius: 0,
                    pointHoverBackgroundColor: "rgb(75, 144, 111, 0.5)",
                    pointHoverBorderColor: "rgb(75, 144, 111, 0.25)",
                    pointHoverBorderWidth: 0,
                    pointRadius: 0,
                    pointHitRadius: 0,

                    <?php
 
                        echo 'data: [ "'. $countAlerts[11] . '","' . $countAlerts[10] . '","' . $countAlerts[9] . '","' . $countAlerts[8] . '","' . $countAlerts[7] . '","' . $countAlerts[6] . '","' . $countAlerts[5] . '","' . $countAlerts[4] . '","' . $countAlerts[3] . '","' . $countAlerts[2]. '","' . $countAlerts[1] . '","' . $countAlerts[0] . '" ],';
     
                    ?>

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
                        display: false
                    }}, {
                        display: true,
                        position: 'left',
                        id: 'y-axis-left-normal',
                        ticks: {
                            padding: 15,
                            beginAtZero: true,
                            min: 0
                        },
                        gridLines: {
                            display: false,
                            drawTicks: false
                        }
                    }, {
                        display: true,
                        position: 'right',
                        id: 'y-axis-right-normal',
                        ticks: {
                            padding: 15,
                            beginAtZero: true,
                            min: 0
                        },
                        gridLines: {
                            display: false,
                            drawTicks: false
                        }
                    }, {
                        position: 'right',
                        id: 'y-axis-right-hidden',
                        ticks: {
                            display: false
                        },
                        gridLines: {
                            display: false,
                            drawTicks: false
                        },
                    }]
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

<!-- Checkbox background changer -->

<script>

    function checkboxPressurePar()
    {
        var checkbox = document.getElementById('pressurepar');
        var checkboxPressure = document.getElementById('checkboxPressurePar');

        if(checkbox.checked === true)
        {
            checkboxPressure.style.background = "#E0E0E0";
        }
        else
        {
            checkboxPressure.style.background = "white";
        }
    }

    function checkboxPressureImpar()
    {
        var checkbox = document.getElementById('pressureimpar');
        var checkboxPressure = document.getElementById('checkboxPressureImpar');

        if(checkbox.checked === true)
        {
            checkboxPressure.style.background = "#E0E0E0";
        }
        else
        {
            checkboxPressure.style.background = "white";
        }
    }

    function checkboxOpportunityPar()
    {
        var checkbox = document.getElementById('opportunitypar');
        var checkboxOpportunity = document.getElementById('checkboxOpportunityPar');

        if(checkbox.checked === true)
        {
            checkboxOpportunity.style.background = "#E0E0E0";
        }
        else
        {
            checkboxOpportunity.style.background = "white";
        }
    }

    function checkboxOpportunityImpar()
    {
        var checkbox = document.getElementById('opportunityimpar');
        var checkboxOpportunity = document.getElementById('checkboxOpportunityImpar');

        if(checkbox.checked === true)
        {
            checkboxOpportunity.style.background = "#E0E0E0";
        }
        else
        {
            checkboxOpportunity.style.background = "white";
        }
    }

    function checkboxRationalizationPar()
    {
        var checkbox = document.getElementById('rationalizationpar');
        var checkboxRationalization = document.getElementById('checkboxRationalizationPar');

        if(checkbox.checked === true)
        {
            checkboxRationalization.style.background = "#E0E0E0";
        }
        else
        {
            checkboxRationalization.style.background = "white";
        }
    }

    function checkboxRationalizationImpar()
    {
        var checkbox = document.getElementById('rationalizationimpar');
        var checkboxRationalization = document.getElementById('checkboxRationalizationImpar');

        if(checkbox.checked === true)
        {
            checkboxRationalization.style.background = "#E0E0E0";
        }
        else
        {
            checkboxRationalization.style.background = "white";
        }
    }

</script>