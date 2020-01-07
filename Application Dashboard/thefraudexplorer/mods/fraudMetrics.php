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
 * Date: 2020-01
 * Revision: v1.4.1-ai
 *
 * Description: Code for fraud metrics
 */

sleep (2);

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
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
        $rulesetFilter = true;
    }
}

unset($_SESSION['endpointFraudMetrics']['allendpoints']);
unset($_SESSION['endpointFraudMetrics']['allbusiness']);
unset($_SESSION['endpointFraudMetrics']['endpoint']);
unset($_SESSION['endpointFraudMetrics']['ruleset']);

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];

/* Global data variables */

if ($session->domain == "all")
{
    for ($i = 1; $i <= 12; $i++) 
    {
        $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
        $daterangefrom = $months[$i-1] . "-01";
        $daterangeto = $months[$i-1] . "-18||/M";
        $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
        if ($endpointFilter == true) $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentID($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID);
        else $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTerm($ESAlerterIndex, $daterangefrom, $daterangeto);
        
        $countAlerts[] = json_decode(json_encode($resultAlerts), true);
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
    <?php if ($endpointID != NULL) echo '<p>Metrics for ' . $endpointIdentification . '</p>'; ?>
</div>

<div class="div-container">

    <div class="fraud-metrics-graph-container">
        
        <?php

            if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo 'impar: '.$_SESSION['endpointFraudMetrics']['launch'].'<canvas id="fraud-metrics-graph"></canvas>'; 
            else echo 'par: '.$_SESSION['endpointFraudMetrics']['launch'].'<canvas id="fraud-metrics-graph-reloaded"></canvas>';

        ?>
        
    </div>

    <div class="master-container-metrics">
            <div class="left-container-metrics">              
                
                <p class="title-config">Filter by business unit</p><br><br>

                <select class="select-ruleset-metrics wide" name="ruleset" id="ruleset-business">
                    
                    <?php

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
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="allbusiness" value="allbusiness" id="allbusiness" autocomplete="off" checked>I want all business units
                    </label>
                </div>          
              
            </div>
            <div class="right-container-metrics">
                   
                <p class="title-config">Filter by endpoint</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="endpoint" id="endpoint" autocomplete="off" placeholder="eleanor@mydomain" class="input-value-text" style="text-indent:5px;">
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="allendpoints" value="allendpoints" id="allendpoints" autocomplete="off" checked>I want all endpoints
                    </label>
                </div>           
                    
            </div>
    </div>

    <br>
    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>

        <?php

            if ($_SESSION['endpointFraudMetrics']['launch'] % 2 != 0) echo '<a href="../mods/fraudMetrics" onclick="getFilters()" class="btn btn-success fraud-metrics-reload-button" data-toggle="modal" data-target="#fraud-metrics-reload" data-dismiss="modal" data-dismiss="modal" style="outline: 0 !important;">Apply filters</a>';
            else echo '<a href="../mods/fraudMetrics" onclick="getFilters()" class="btn btn-success fraud-metrics-noreload-button" data-toggle="modal" data-target="#fraud-metrics" data-dismiss="modal" data-dismiss="modal" style="outline: 0 !important;">Apply filters</a>';
        ?>

    </div>

</div>

<!-- Modal for Fraud Metrics -->

<script>
    $('#fraud-metrics-reload').on('hidden.bs.modal', function () {
    $(this).removeData('bs.modal');
    });

    $('#fraud-metrics-reload').on('show.bs.modal', function(e){
        $(this).find('.fraud-metrics-reload-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Javascript for filters -->

<script>
    function getFilters()
    {
        var endpointData = document.getElementById("endpoint").value;
        var businessData = document.getElementById("ruleset-business").value;
        var allEndpoints, allBusiness;

        if (document.getElementById('allbusiness').checked) allBusiness = true;
        else allBusiness = false;

        if (document.getElementById('allendpoints').checked) allEndpoints = true;
        else allEndpoints = false;
        if (!endpointData) endpointData = "null";

        $.get({
            url: 'mods/fraudMetricsReload.php?endpoint=' + endpointData + '&allbusiness=' + allBusiness + '&allendpoints=' + allEndpoints + '&ruleset=' + businessData, 
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
                    data: [ <?php echo '"'. $countAlerts[11][11]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[10][10]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[9][9]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[8][8]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[7][7]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[6][6]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[5][5]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[4][4]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[3][3]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[2][2]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[1][1]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[0][0]['count']/3 . '"'; ?> ],
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

    var ctx = document.getElementById("fraud-metrics-graph-reloaded");
    var myChart = new Chart(ctx, {
        type: 'line',
        defaults: defaultOptions,
        data: {
            labels: [ <?php echo '"'. $monthName[11] . '"'; ?>, <?php echo '"'. $monthName[10] . '"'; ?>, <?php echo '"'. $monthName[9] . '"'; ?>, <?php echo '"'. $monthName[8] . '"'; ?>, <?php echo '"'. $monthName[7] . '"'; ?>, <?php echo '"'. $monthName[6] . '"'; ?>, <?php echo '"'. $monthName[5] . '"'; ?>, <?php echo '"'. $monthName[4] . '"'; ?>, <?php echo '"'. $monthName[3] . '"'; ?>, <?php echo '"'. $monthName[2] . '"'; ?>, <?php echo '"'. $monthName[1] . '"'; ?>, <?php echo '"'. $monthName[0] . '"'; ?> ],
            datasets: [
                {
                    label: "Fraud Metrics",
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
                    data: [ <?php echo '"'. $countAlerts[11][11]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[10][10]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[9][9]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[8][8]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[7][7]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[6][6]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[5][5]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[4][4]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[3][3]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[2][2]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[1][1]['count']/3 . '"'; ?>, <?php echo '"'. $countAlerts[0][0]['count']/3 . '"'; ?> ],
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

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>