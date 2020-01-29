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
 * Description: Code for endpoint metrics
 */

sleep(1);

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

$firstTime = false;

if (isset($_SESSION['endpointMetrics']['endpoint'])) $metricForEndpoint = $_SESSION['endpointMetrics']['endpoint'];
else 
{
    $metricForEndpoint = filter($_GET['id']);
    $metricForEndpoint = base64_decode($metricForEndpoint);
    $firstTime = true;
}

$endpointLogin = explode("@", $metricForEndpoint);
$endpointID = $endpointLogin[0] . "*";
$endpointIdentification = $metricForEndpoint;

unset($_SESSION['endpointMetrics']['endpoint']);

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];

/* Global data variables */

for ($i = 1; $i <= 12; $i++) 
{
    $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
    $daterangefrom = $months[$i-1] . "-01";
    $daterangeto = $months[$i-1] . "-18||/M";
    $monthName[] = substr(date("F", strtotime($months[$i-1])), 0, 3);
        
    if ($firstTime == true)
    {
        $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentID($ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID);
    }
    else
    {    
        $pressureCheck = $_SESSION['endpointMetrics']['pressure'];
        $opportunityCheck = $_SESSION['endpointMetrics']['opportunity'];
        $rationalizationCheck = $_SESSION['endpointMetrics']['rationalization'];
        $fraudTerms = $pressureCheck . " " . $opportunityCheck . " " . $rationalizationCheck;
        $fraudTerms = str_replace(array("true", "false"), array("1", "0"), $fraudTerms);
        $resultAlerts[] = countFraudTriangleMatchesWithDateRangeWithTermWithAgentID($fraudTerms, $ESAlerterIndex, $daterangefrom, $daterangeto, $endpointID);
    }

    $countAlerts[] = json_decode(json_encode($resultAlerts), true);
}

?>

<style>

    @font-face 
    {
        font-family: 'FFont';
        src: url('../fonts/Open_Sans/OpenSans-Regular.ttf');
    }

    @font-face
    {
        font-family: 'FFont-Bold';
        src: url('../fonts/Open_Sans/OpenSans-Bold.ttf');
    }

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

    .endpoint-metrics-graph-container
    {
        height: 250px;
        padding: 20px 15px 15px 15px;
        border: 0px solid gray;
        border-radius: 3px;
        background: #FAFAFA;
    }

    .master-container-endpoint-metrics
    {
        width: 100%; 
        height: 85px;
    }
    
    .left-container-endpoint-metrics
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .right-container-endpoint-metrics
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .endpoint-metrics-reload-button, .endpoint-metrics-noreload-button
    {
        color: white !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">&nbsp;Endpoint history events</h4>

    <?php 
    
        echo '<p>Metrics for ' . $endpointIdentification . '&nbsp;&nbsp;&nbsp;</p>'; 
        
    ?>
</div>

<div class="div-container">

    <div class="endpoint-metrics-graph-container">
        
        <?php

            if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) 
            {
                echo '<canvas id="endpoint-metrics-graph"></canvas>'; 
            }
            else 
            {
                echo '<canvas id="endpoint-metrics-graph-reloaded"></canvas>';
            }

        ?>
        
    </div>

    <div class="master-container-endpoint-metrics">
            <div class="left-container-endpoint-metrics">              
                
                <p class="title-config">Filter by fraud triangle vertice</p>

                <div style="line-height:35px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="<?php if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) echo 'checkboxPressurePar'; else echo 'checkboxPressureImpar'; ?>" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">

                    <?php

                        if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) 
                        {
                            echo '<input type="checkbox" onchange="checkboxPressurePar()" name="pressurepar" value="pressure" id="pressurepar" autocomplete="off" checked>Pressure</input>';
                        }
                        else 
                        {
                            echo '<input type="checkbox" onchange="checkboxPressureImpar()" name="pressureimpar" value="pressure" id="pressureimpar" autocomplete="off" checked>Pressure</input>';
                        }
                        
                    ?>

                    </label>
                </div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 95px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="<?php if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) echo 'checkboxOpportunityPar'; else echo 'checkboxOpportunityImpar'; ?>" style="width: 95px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">

                    <?php

                        if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) 
                        {
                            echo '<input type="checkbox" onchange="checkboxOpportunityPar()" name="opportunitypar" value="opportunity" id="opportunitypar" autocomplete="off" checked>Opportunity</input>';
                        }
                        else 
                        {
                            echo '<input type="checkbox" onchange="checkboxOpportunityImpar()" name="opportunityimpar" value="opportunity" id="opportunityimpar" autocomplete="off" checked>Opportunity</input>';
                        }
                        
                    ?>

                    </label>
                </div>          

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="<?php if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) echo 'checkboxRationalizationPar'; else echo 'checkboxRationalizationImpar'; ?>" style="width: 85px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">

                    <?php

                        if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) 
                        {
                            echo '<input type="checkbox" onchange="checkboxRationalizationPar()" name="rationalizationpar" value="rationalization" id="rationalizationpar" autocomplete="off" checked>Rational</input>';
                        }
                        else 
                        {
                            echo '<input type="checkbox" onchange="checkboxRationalizationImpar()" name="rationalizationimpar" value="rationalization" id="rationalizationimpar" autocomplete="off" checked>Rational</input>';
                        }
                        
                    ?>

                    </label>
                </div>          
              
            </div>
            <div class="right-container-endpoint-metrics">
                   
                <p class="title-config">Endpoint history events for</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>

                <?php

                    if ($_SESSION['endpointMetrics']['launch'] % 2 != 0)  
                    {
                        echo '<input type="text" disabled name="endpointpar" id="endpointpar" autocomplete="off" placeholder="'.$metricForEndpoint.'" value="'.$metricForEndpoint.'" class="input-value-text" style="text-indent:5px;">';
                    }
                    else 
                    {
                        echo '<input type="text" disabled name="endpointimpar" id="endpointimpar" autocomplete="off" placeholder="'.$metricForEndpoint.'" value="'.$metricForEndpoint.'" class="input-value-text" style="text-indent:5px;">';
                    }
                
                ?>
                    
            </div>
    </div>

    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>

        <?php

            if ($_SESSION['endpointMetrics']['launch'] % 2 != 0) 
            {
                echo '<a href="../mods/endpointMetrics" onclick="getFiltersPar()" class="btn btn-success endpoint-metrics-reload-button" id="btn-metrics-par" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Filtering, please wait" data-toggle="modal" data-dismiss="modal" data-target="#endpoint-metrics-reload" style="outline: 0 !important;">Apply filters</a>';
            }
            else 
            {
                echo '<a href="../mods/endpointMetrics" onclick="getFiltersImpar()" class="btn btn-success endpoint-metrics-noreload-button" id="btn-metrics-impar" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Filtering, please wait" data-toggle="modal" data-dismiss="modal" data-target="#endpoint-metrics" style="outline: 0 !important;">Apply filters</a>';
            }
        
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

<!-- Modal for Endpoint Metrics -->

<script>
    $(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
    });

    $('#endpoint-metrics-reload').on('show.bs.modal', function(e){
        $(this).find('.endpoint-metrics-reload-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Javascript for filters Par -->

<script>
    function getFiltersPar()
    {
        var endpointData = document.getElementById("endpointpar").value;

        if (document.getElementById('pressurepar').checked) pressure = true;
        else pressure = false;

        if (document.getElementById('opportunitypar').checked) opportunity = true;
        else opportunity = false;

        if (document.getElementById('rationalizationpar').checked) rationalization = true;
        else rationalization = false;

        $.get({
            url: 'mods/endpointMetricsReload.php?endpoint=' + endpointData + '&pressure=' + pressure + '&opportunity=' + opportunity + '&rationalization=' + rationalization, 
            success: function(data) { return true; }
        });
    }
</script>

<!-- Javascript for filters Impar-->

<script>
    function getFiltersImpar()
    {
        var endpointData = document.getElementById("endpointimpar").value;

        if (document.getElementById('pressureimpar').checked) pressure = true;
        else pressure = false;

        if (document.getElementById('opportunityimpar').checked) opportunity = true;
        else opportunity = false;

        if (document.getElementById('rationalizationimpar').checked) rationalization = true;
        else rationalization = false;

        $.get({
            url: 'mods/endpointMetricsReload.php?endpoint=' + endpointData + '&pressure=' + pressure + '&opportunity=' + opportunity + '&rationalization=' + rationalization, 
            success: function(data) { return true; }
        });
    }
</script>

<script>
    var defaultOptions = {
        global: {
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'FFont'"
        }
    }

    var ctx = document.getElementById("endpoint-metrics-graph");
    var myChart = new Chart(ctx, {
        type: 'line',
        defaults: defaultOptions,
        data: {
            labels: [ <?php echo '"'. $monthName[11] . '"'; ?>, <?php echo '"'. $monthName[10] . '"'; ?>, <?php echo '"'. $monthName[9] . '"'; ?>, <?php echo '"'. $monthName[8] . '"'; ?>, <?php echo '"'. $monthName[7] . '"'; ?>, <?php echo '"'. $monthName[6] . '"'; ?>, <?php echo '"'. $monthName[5] . '"'; ?>, <?php echo '"'. $monthName[4] . '"'; ?>, <?php echo '"'. $monthName[3] . '"'; ?>, <?php echo '"'. $monthName[2] . '"'; ?>, <?php echo '"'. $monthName[1] . '"'; ?>, <?php echo '"'. $monthName[0] . '"'; ?> ],
            datasets: [
                {
                    label: "Endpoint Metrics",
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

                        echo 'data: [ "'. $countAlerts[11][11]['count'] . '","' . $countAlerts[10][10]['count'] . '","' . $countAlerts[9][9]['count'] . '","' . $countAlerts[8][8]['count'] . '","' . $countAlerts[7][7]['count'] . '","' . $countAlerts[6][6]['count'] . '","' . $countAlerts[5][5]['count'] . '","' . $countAlerts[4][4]['count'] . '","' . $countAlerts[3][3]['count'] . '","' . $countAlerts[2][2]['count'] . '","' . $countAlerts[1][1]['count'] . '","' . $countAlerts[0][0]['count'] . '" ],';

                    ?>

                    spanGaps: false,
                },
                {
                    label: "Endpoint Metrics",
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

                        echo 'data: [ "'. $countAlerts[11][11]['count'] . '","' . $countAlerts[10][10]['count'] . '","' . $countAlerts[9][9]['count'] . '","' . $countAlerts[8][8]['count'] . '","' . $countAlerts[7][7]['count'] . '","' . $countAlerts[6][6]['count'] . '","' . $countAlerts[5][5]['count'] . '","' . $countAlerts[4][4]['count'] . '","' . $countAlerts[3][3]['count'] . '","' . $countAlerts[2][2]['count'] . '","' . $countAlerts[1][1]['count'] . '","' . $countAlerts[0][0]['count'] . '" ],';

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
            defaultFontFamily: Chart.defaults.global.defaultFontFamily = "'FFont'"
        }
    }

    var ctx = document.getElementById("endpoint-metrics-graph-reloaded");
    var myChart = new Chart(ctx, {
        type: 'line',
        defaults: defaultOptions,
        data: {
            labels: [ <?php echo '"'. $monthName[11] . '"'; ?>, <?php echo '"'. $monthName[10] . '"'; ?>, <?php echo '"'. $monthName[9] . '"'; ?>, <?php echo '"'. $monthName[8] . '"'; ?>, <?php echo '"'. $monthName[7] . '"'; ?>, <?php echo '"'. $monthName[6] . '"'; ?>, <?php echo '"'. $monthName[5] . '"'; ?>, <?php echo '"'. $monthName[4] . '"'; ?>, <?php echo '"'. $monthName[3] . '"'; ?>, <?php echo '"'. $monthName[2] . '"'; ?>, <?php echo '"'. $monthName[1] . '"'; ?>, <?php echo '"'. $monthName[0] . '"'; ?> ],
            datasets: [
                {
                    label: "Endpoint Metrics",
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

                        echo 'data: [ "'. $countAlerts[11][11]['count'] . '","' . $countAlerts[10][10]['count'] . '","' . $countAlerts[9][9]['count'] . '","' . $countAlerts[8][8]['count'] . '","' . $countAlerts[7][7]['count'] . '","' . $countAlerts[6][6]['count'] . '","' . $countAlerts[5][5]['count'] . '","' . $countAlerts[4][4]['count'] . '","' . $countAlerts[3][3]['count'] . '","' . $countAlerts[2][2]['count'] . '","' . $countAlerts[1][1]['count'] . '","' . $countAlerts[0][0]['count'] . '" ],';

                    ?>

                    spanGaps: false,
                },
                {
                    label: "Endpoint Metrics",
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

                        echo 'data: [ "'. $countAlerts[11][11]['count'] . '","' . $countAlerts[10][10]['count'] . '","' . $countAlerts[9][9]['count'] . '","' . $countAlerts[8][8]['count'] . '","' . $countAlerts[7][7]['count'] . '","' . $countAlerts[6][6]['count'] . '","' . $countAlerts[5][5]['count'] . '","' . $countAlerts[4][4]['count'] . '","' . $countAlerts[3][3]['count'] . '","' . $countAlerts[2][2]['count'] . '","' . $countAlerts[1][1]['count'] . '","' . $countAlerts[0][0]['count'] . '" ],';

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