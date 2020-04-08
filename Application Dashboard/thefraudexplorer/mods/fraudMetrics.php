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
 * Description: Code for fraud metrics
 */

sleep(1);

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

$endpointFilter = false;
$rulesetFilter = false;
$endpointID = NULL;

if (isset($_SESSION['endpointFraudMetrics']['allendpoints']) || isset($_SESSION['endpointFraudMetrics']['allbusiness']))
{
    if ($_SESSION['endpointFraudMetrics']['allendpoints'] == "false" || $_SESSION['endpointFraudMetrics']['allbusiness'] == "false") 
    { 
        if ($_SESSION['endpointFraudMetrics']['endpoint'] != "null")
        {
            $endpointLogin = explode("@", $_SESSION['endpointFraudMetrics']['endpoint']);
            $endpointID = $endpointLogin[0] . "*";
            $endpointIdentification = $_SESSION['endpointFraudMetrics']['endpoint'];
            $endpointFilter = true;
        }
        else if ($_SESSION['endpointFraudMetrics']['ruleset'] != "BASELINE")
        {
            $rulesetSelected = filter($_SESSION['endpointFraudMetrics']['ruleset']);
            $rulesetFilter = true;
        }
    }
}

if (isset($_SESSION['endpointFraudMetrics']['allendpoints'])) unset($_SESSION['endpointFraudMetrics']['allendpoints']);
if (isset($_SESSION['endpointFraudMetrics']['allbusiness'])) unset($_SESSION['endpointFraudMetrics']['allbusiness']);
if (isset($_SESSION['endpointFraudMetrics']['endpoint'])) unset($_SESSION['endpointFraudMetrics']['endpoint']);
if (isset($_SESSION['endpointFraudMetrics']['ruleset'])) unset($_SESSION['endpointFraudMetrics']['ruleset']);

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];

/* Global data variables */

if ($session->domain == "all")
{
    if ($endpointFilter == true)
    {
        for ($i = 1; $i <= 12; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $daterangefrom = $months[$i-1] . "-01";
            $daterangeto = $months[$i-1] . "-18||/M";
            $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
            $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentID($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID);
            $countAlerts[] = json_decode(json_encode($resultAlerts), true);
        }
    }
    else if ($rulesetFilter == true)
    {
        $queryEndpointsSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$rulesetSelected."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
        $resultSQLRuleset = mysqli_query($connection, $queryEndpointsSQLRuleset);
        $endCounter = 0;

        while ($row = mysqli_fetch_array($resultSQLRuleset))
        { 
            $endpointID = $row['agent'] . "*";  

            for ($i = 1; $i <= 12; $i++) 
            {
                $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
                $daterangefrom = $months[$i-1] . "-01";
                $daterangeto = $months[$i-1] . "-18||/M";
                $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
                $resultAlerts[$endCounter][] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentID($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID);
                $countAlerts[$endCounter][] = json_decode(json_encode($resultAlerts), true);
            }

            $endCounter++;
        }     

        /* Array correlation by month */

        foreach($resultAlerts as $endpoint => $month) 
        { 
            $arrCount = 0;

            foreach($month as $k) 
            {         
                $finalArray[$arrCount][] = $k['count'];
                $arrCount++;
            }            
        } 

        /* Array sumation */

        foreach($finalArray as $month => $endpoint) 
        { 
            foreach($endpoint as $value)
            {
                @$sumArray[$month] += $value;
            }
        }
    }
    else 
    {
        for ($i = 1; $i <= 12; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $daterangefrom = $months[$i-1] . "-01";
            $daterangeto = $months[$i-1] . "-18||/M";
            $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
            $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTerm($ESAlerterIndex, $daterangefrom, $daterangeto);
            $countAlerts[] = json_decode(json_encode($resultAlerts), true);
        }
    }
}
else
{
    if ($endpointFilter == true)
    {
        for ($i = 1; $i <= 12; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $daterangefrom = $months[$i-1] . "-01";
            $daterangeto = $months[$i-1] . "-18||/M";
            $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
            $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentIDWithDomain($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID, $session->domain);
            $countAlerts[] = json_decode(json_encode($resultAlerts), true);
        }
    }
    else if ($rulesetFilter == true)
    {
        $queryEndpointsSQLRuleset = "SELECT agent, domain, ruleset, pressure, rationalization FROM (SELECT agent, domain, ruleset, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents WHERE ruleset='".$rulesetSelected."' AND domain='".$session->domain."' GROUP BY agent) AS duplicates GROUP BY pressure, rationalization";
        $resultSQLRuleset = mysqli_query($connection, $queryEndpointsSQLRuleset);
        $endCounter = 0;

        while ($row = mysqli_fetch_array($resultSQLRuleset))
        { 
            $endpointID = $row['agent'] . "*";  

            for ($i = 1; $i <= 12; $i++) 
            {
                $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
                $daterangefrom = $months[$i-1] . "-01";
                $daterangeto = $months[$i-1] . "-18||/M";
                $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
                $resultAlerts[$endCounter][] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentIDWithDomain($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID, $session->domain);
                $countAlerts[$endCounter][] = json_decode(json_encode($resultAlerts), true);
            }

            $endCounter++;
        }     

        /* Array correlation by month */

        foreach($resultAlerts as $endpoint => $month) 
        { 
            $arrCount = 0;

            foreach($month as $k) 
            {         
                $finalArray[$arrCount][] = $k['count'];
                $arrCount++;
            }            
        } 

        /* Array sumation */

        foreach($finalArray as $month => $endpoint) 
        { 
            foreach($endpoint as $value)
            {
                @$sumArray[$month] += $value;
            }
        }
    }
    else 
    {
        for ($i = 1; $i <= 12; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $daterangefrom = $months[$i-1] . "-01";
            $daterangeto = $months[$i-1] . "-18||/M";
            $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
            $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTermWithDomain($ESAlerterIndex, $daterangefrom, $daterangeto, $session->domain);
            $countAlerts[] = json_decode(json_encode($resultAlerts), true);
        }
    }
}

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .input-value-text
    {
        width: 100%; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-config
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
        height: 105px;
    }
    
    .left-container-metrics
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .right-container-metrics
    {
        width: calc(50% - 5px); 
        height: 100%; 
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
    
        if ($rulesetFilter == true) echo '<p>Metrics for ' . $rulesetSelected . '</p>'; 
        else if ($endpointID != NULL) echo '<p>Metrics for ' . $endpointIdentification . '</p>'; 
        
    ?>
</div>

<div class="div-container">

    <div class="fraud-metrics-graph-container">
        
        <?php

            if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) 
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
                
                <p class="title-config">Filter by business unit</p><br><br>

                <?php

                    if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo '<select class="select-ruleset-metrics wide" name="ruleset" id="ruleset-businesspar">';
                    else echo '<select class="select-ruleset-metrics wide" name="ruleset" id="ruleset-businessimpar">';

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                    $GLOBALS['listRuleset'] = null;

                    foreach ($jsonFT['dictionary'] as $ruleset => $value)
                    {
                        if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected>ALL BUSINESS UNITS</option>';
                        else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                    }

                ?>

                </select>

                <div style="line-height:47px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="<?php if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo 'checkboxAllBusinessPar'; else echo 'checkboxAllBusinessImpar'; ?>" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">

                    <?php

                        if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) 
                        {
                            echo '<input type="checkbox" onchange="checkboxAllBusinessPar()" id="allbusinesspar" name="allbusinesspar" value="allbusiness" id="allbusinesspar" autocomplete="off" checked>I want all business units';
                        }
                        else 
                        {
                            echo '<input type="checkbox" onchange="checkboxAllBusinessImpar()" id="allbusinessimpar" name="allbusinessimpar" value="allbusiness" id="allbusinessimpar" autocomplete="off" checked>I want all business units';
                        }
                        
                    ?>

                    </label>
                </div>          
              
            </div>
            <div class="right-container-metrics">
                   
                <p class="title-config">Filter by endpoint</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>

                <?php

                    if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) 
                    {
                        echo '<input type="text" name="endpointpar" id="endpointpar" autocomplete="off" placeholder="eleanor@mydomain" class="input-value-text" style="text-indent:5px;">';
                    }
                    else 
                    {
                        echo '<input type="text" name="endpointimpar" id="endpointimpar" autocomplete="off" placeholder="eleanor@mydomain" class="input-value-text" style="text-indent:5px;">';
                    }
                
                ?>

                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="<?php if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo 'checkboxAllEndpointsPar'; else echo 'checkboxAllEndpointsImpar'; ?>" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">

                    <?php

                        if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) 
                        {
                            echo '<input type="checkbox" onchange="checkboxAllEndpointsPar()" id="allendpointspar" name="allendpointspar" value="allendpoints" id="allendpointspar" autocomplete="off" checked>I want all endpoints';
                        }
                        else 
                        {
                            echo '<input type="checkbox" onchange="checkboxAllEndpointsImpar()" id="allendpointsimpar" name="allendpointsimpar" value="allendpoints" id="allendpointsimpar" autocomplete="off" checked>I want all endpoints';
                        }

                    ?>

                    </label>
                </div>           
                    
            </div>
    </div>

    <br>
    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>

        <?php

            if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo '<a href="../mods/fraudMetrics" onclick="getFiltersPar()" class="btn btn-success fraud-metrics-reload-button" id="btn-metrics-par" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Filtering, please wait" data-toggle="modal" data-dismiss="modal" data-target="#fraud-metrics-reload" style="outline: 0 !important;">Apply filters</a>';
            else echo '<a href="../mods/fraudMetrics" onclick="getFiltersImpar()" class="btn btn-success fraud-metrics-noreload-button" id="btn-metrics-impar" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Filtering, please wait" data-toggle="modal" data-dismiss="modal" data-target="#fraud-metrics" style="outline: 0 !important;">Apply filters</a>';
        
        ?>

    </div>

</div>

<!-- Button loading Par -->

<script>

var $btn;

$("#btn-metrics-par").click(function() {
    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

function getstatus()
{
    $.ajax({
        url: "../helpers/processingStatus.php",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#statusmessage').html(data.message);
            if(data.status=="pending")
              setTimeout('getstatus()', 1000);
            else
                $btn.button('reset');
        }
    });
}

</script>

<!-- Button loading Impar -->

<script>

var $btn;

$("#btn-metrics-impar").click(function() {
    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

function getstatus()
{
    $.ajax({
        url: "../helpers/processingStatus.php",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#statusmessage').html(data.message);
            if(data.status=="pending")
              setTimeout('getstatus()', 1000);
            else
                $btn.button('reset');
        }
    });
}

</script>

<!-- Modal for Fraud Metrics -->

<script>
    $(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
    });

    $('#fraud-metrics-reload').on('show.bs.modal', function(e){
        $(this).find('.fraud-metrics-reload-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Javascript for filters Par -->

<script>
    function getFiltersPar()
    {
        var endpointData = document.getElementById("endpointpar").value;
        var businessData = document.getElementById("ruleset-businesspar").value;
        var allEndpoints, allBusiness;

        if (document.getElementById('allbusinesspar').checked) allBusiness = true;
        else allBusiness = false;

        if (document.getElementById('allendpointspar').checked) allEndpoints = true;
        else allEndpoints = false;
        if (!endpointData) endpointData = "null";

        $.get({
            url: 'mods/fraudMetricsReload.php?nt=' + endpointData + '&ss=' + allBusiness + '&ts=' + allEndpoints + '&et=' + businessData, 
            success: function(data) { return true; }
        });
    }
</script>

<!-- Javascript for filters Impar-->

<script>
    function getFiltersImpar()
    {
        var endpointData = document.getElementById("endpointimpar").value;
        var businessData = document.getElementById("ruleset-businessimpar").value;
        var allEndpoints, allBusiness;

        if (document.getElementById('allbusinessimpar').checked) allBusiness = true;
        else allBusiness = false;

        if (document.getElementById('allendpointsimpar').checked) allEndpoints = true;
        else allEndpoints = false;
        if (!endpointData) endpointData = "null";

        $.get({
            url: 'mods/fraudMetricsReload.php?nt=' + endpointData + '&ss=' + allBusiness + '&ts=' + allEndpoints + '&et=' + businessData, 
            success: function(data) { return true; }
        });
    }
</script>

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'CFont'"
        }
    }

    var ctx = document.getElementById("fraud-metrics-graph");
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

                        if ($rulesetFilter == true)
                        {
                            echo 'data: [ "'. $sumArray[11]/3 . '","' . $sumArray[10]/3 . '","' . $sumArray[9]/3 . '","' . $sumArray[8]/3 . '","' . $sumArray[7]/3 . '","' . $sumArray[6]/3 . '","' . $sumArray[5]/3 . '","' . $sumArray[4]/3 . '","' . $sumArray[3]/3 . '","' . $sumArray[2]/3 . '","' . $sumArray[1]/3 . '","' . $sumArray[0]/3 . '" ],';
                        }
                        else
                        {
                            echo 'data: [ "'. $countAlerts[11][11]['count']/3 . '","' . $countAlerts[10][10]['count']/3 . '","' . $countAlerts[9][9]['count']/3 . '","' . $countAlerts[8][8]['count']/3 . '","' . $countAlerts[7][7]['count']/3 . '","' . $countAlerts[6][6]['count']/3 . '","' . $countAlerts[5][5]['count']/3 . '","' . $countAlerts[4][4]['count']/3 . '","' . $countAlerts[3][3]['count']/3 . '","' . $countAlerts[2][2]['count']/3 . '","' . $countAlerts[1][1]['count']/3 . '","' . $countAlerts[0][0]['count']/3 . '" ],';

                        }

                    ?>

                    spanGaps: false,
                },
                {
                    label: "Fraud Metrics",
                    type: 'line',
                    fill: false,
                    fillColor: "#13923D",
                    lineTension: 0.0,
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

                        if ($rulesetFilter == true)
                        {
                            echo 'data: [ "'. $sumArray[11]/3 . '","' . $sumArray[10]/3 . '","' . $sumArray[9]/3 . '","' . $sumArray[8]/3 . '","' . $sumArray[7]/3 . '","' . $sumArray[6]/3 . '","' . $sumArray[5]/3 . '","' . $sumArray[4]/3 . '","' . $sumArray[3]/3 . '","' . $sumArray[2]/3 . '","' . $sumArray[1]/3 . '","' . $sumArray[0]/3 . '" ],';
                        }
                        else
                        {
                            echo 'data: [ "'. $countAlerts[11][11]['count']/3 . '","' . $countAlerts[10][10]['count']/3 . '","' . $countAlerts[9][9]['count']/3 . '","' . $countAlerts[8][8]['count']/3 . '","' . $countAlerts[7][7]['count']/3 . '","' . $countAlerts[6][6]['count']/3 . '","' . $countAlerts[5][5]['count']/3 . '","' . $countAlerts[4][4]['count']/3 . '","' . $countAlerts[3][3]['count']/3 . '","' . $countAlerts[2][2]['count']/3 . '","' . $countAlerts[1][1]['count']/3 . '","' . $countAlerts[0][0]['count']/3 . '" ],';

                        }

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
                    }},
                    {
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

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'CFont'"
        }
    }

    var ctx = document.getElementById("fraud-metrics-graph-reloaded");
    var myChart = new Chart(ctx, {
        type: 'line',
        defaults: defaultOptions,
        data: {
            labels: [ <?php echo '"'. $monthName[11] . '"'; ?>, <?php echo '"'. $monthName[10] . '"'; ?>, <?php echo '"'. $monthName[9] . '"'; ?>, <?php echo '"'. $monthName[8] . '"'; ?>, <?php echo '"'. $monthName[7] . '"'; ?>, <?php echo '"'. $monthName[6] . '"'; ?>, <?php echo '"'. $monthName[5] . '"'; ?>, <?php echo '"'. $monthName[4] . '"'; ?>, <?php echo '"'. $monthName[3] . '"'; ?>, <?php echo '"'. $monthName[2] . '"'; ?>, <?php echo '"'. $monthName[1] . '"'; ?>, <?php echo '"'. $monthName[0] . '"'; ?> ],
            datasets: [
                {
                    label: "Fraud Metrics",
                    yAxisID: "y-axis-right-normal",
                    type: 'line',
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

                        if ($rulesetFilter == true)
                        {
                            echo 'data: [ "'. $sumArray[11]/3 . '","' . $sumArray[10]/3 . '","' . $sumArray[9]/3 . '","' . $sumArray[8]/3 . '","' . $sumArray[7]/3 . '","' . $sumArray[6]/3 . '","' . $sumArray[5]/3 . '","' . $sumArray[4]/3 . '","' . $sumArray[3]/3 . '","' . $sumArray[2]/3 . '","' . $sumArray[1]/3 . '","' . $sumArray[0]/3 . '" ],';
                        }
                        else
                        {
                            echo 'data: [ "'. $countAlerts[11][11]['count']/3 . '","' . $countAlerts[10][10]['count']/3 . '","' . $countAlerts[9][9]['count']/3 . '","' . $countAlerts[8][8]['count']/3 . '","' . $countAlerts[7][7]['count']/3 . '","' . $countAlerts[6][6]['count']/3 . '","' . $countAlerts[5][5]['count']/3 . '","' . $countAlerts[4][4]['count']/3 . '","' . $countAlerts[3][3]['count']/3 . '","' . $countAlerts[2][2]['count']/3 . '","' . $countAlerts[1][1]['count']/3 . '","' . $countAlerts[0][0]['count']/3 . '" ],';

                        }

                    ?>

                    spanGaps: false,
                },
                {
                    label: "Fraud Metrics",
                    type: 'line',
                    fill: false,
                    fillColor: "#13923D",
                    lineTension: 0.0,
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

                        if ($rulesetFilter == true)
                        {
                            echo 'data: [ "'. $sumArray[11]/3 . '","' . $sumArray[10]/3 . '","' . $sumArray[9]/3 . '","' . $sumArray[8]/3 . '","' . $sumArray[7]/3 . '","' . $sumArray[6]/3 . '","' . $sumArray[5]/3 . '","' . $sumArray[4]/3 . '","' . $sumArray[3]/3 . '","' . $sumArray[2]/3 . '","' . $sumArray[1]/3 . '","' . $sumArray[0]/3 . '" ],';
                        }
                        else
                        {
                            echo 'data: [ "'. $countAlerts[11][11]['count']/3 . '","' . $countAlerts[10][10]['count']/3 . '","' . $countAlerts[9][9]['count']/3 . '","' . $countAlerts[8][8]['count']/3 . '","' . $countAlerts[7][7]['count']/3 . '","' . $countAlerts[6][6]['count']/3 . '","' . $countAlerts[5][5]['count']/3 . '","' . $countAlerts[4][4]['count']/3 . '","' . $countAlerts[3][3]['count']/3 . '","' . $countAlerts[2][2]['count']/3 . '","' . $countAlerts[1][1]['count']/3 . '","' . $countAlerts[0][0]['count']/3 . '" ],';

                        }

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
                    }},
                    {
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

    function checkboxAllBusinessPar()
    {
        var checkbox = document.getElementById('allbusinesspar');
        var checkboxAllBusiness = document.getElementById('checkboxAllBusinessPar');

        if(checkbox.checked === true)
        {
            checkboxAllBusiness.style.background = "#E0E0E0";
        }
        else
        {
            checkboxAllBusiness.style.background = "white";
        }
    }

    function checkboxAllBusinessImpar()
    {
        var checkbox = document.getElementById('allbusinessimpar');
        var checkboxAllBusiness = document.getElementById('checkboxAllBusinessImpar');

        if(checkbox.checked === true)
        {
            checkboxAllBusiness.style.background = "#E0E0E0";
        }
        else
        {
            checkboxAllBusiness.style.background = "white";
        }
    }

    function checkboxAllEndpointsPar()
    {
        var checkbox = document.getElementById('allendpointspar');
        var checkboxAllEndpoints = document.getElementById('checkboxAllEndpointsPar');

        if(checkbox.checked === true)
        {
            checkboxAllEndpoints.style.background = "#E0E0E0";
        }
        else
        {
            checkboxAllEndpoints.style.background = "white";
        }
    }

    function checkboxAllEndpointsImpar()
    {
        var checkbox = document.getElementById('allendpointsimpar');
        var checkboxAllEndpoints = document.getElementById('checkboxAllEndpointsImpar');

        if(checkbox.checked === true)
        {
            checkboxAllEndpoints.style.background = "#E0E0E0";
        }
        else
        {
            checkboxAllEndpoints.style.background = "white";
        }
    }

</script>