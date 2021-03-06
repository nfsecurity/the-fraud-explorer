<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for horizontal analytics data
 */

include "lbs/login/session.php";
include "lbs/security.php";
include "lbs/cryptography.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "eventData";
if (isset($_GET['nt'])) $_SESSION['endpointIDh'] = filter($_GET['nt']);
else header ("location: eventData?nt=".encRijndael("all")."");

if (!checkEvent(decRijndael(filter($_SESSION['endpointIDh'])))) header ("location: eventData?nt=".encRijndael("all")."");

?>

<html>
    <head>
        <title>Endpoint Data &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/nftop.svg" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tourist.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tourist.js"></script>

        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipsterCustom.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- CSS -->

        <link rel="stylesheet" type="text/css" href="css/eventData.css?<?php echo filemtime('css/eventData.css') ?>" media="screen" />

        <!-- ChartJS -->

        <script type="text/javascript" src="js/Chart.js"></script>

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

        <!-- JQuery Table -->

        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
        <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.widgets.js"></script>
        <script type="text/javascript" src="js/widgets/widget-output.js"></script>

        <!-- Footer CSS -->

        <link rel="stylesheet" type="text/css" href="css/footer.css?<?php echo filemtime('css/footer.css') ?>">

        <!-- JQuery nice select -->

        <script src="js/jquery.nice-select.js"></script>
        <link rel="stylesheet" href="css/nice-select.css">

        <!-- Rangy -->

        <script type="text/javascript" src="js/rangy-core.js"></script>
        <script type="text/javascript" src="js/rangy-textrange.js"></script>

        <!-- jGrowl -->

        <script type="text/javascript" src="js/jquery.jgrowl.js"></script>
        <link rel="stylesheet" href="css/jquery.jgrowl.css">

        <!-- JS functions -->

        <script type="text/javascript" src="js/eventData.js"></script>
    </head>
    <body>
        <div align="center" style="height:100%;">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <!-- Footer inclusion -->

            <div id="includedFooterContent"></div>

            <?php
            
            include "lbs/openDBconn.php";
            echo '<div id="tableHolder" class="table-holder"></div>';
            include "lbs/closeDBconn.php";
            
            ?>
        </div>
        
        <!-- Modal for Event Marking -->

        <center>
            <div class="modal" id="eventMarking" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>

        <!-- Modal for Advanced Reports -->

        <center>
            <div class="modal" id="advanced-reports" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>
        
        <!-- Modal for Phrase Viewer -->

        <center>
            <div class="modal" id="event-phrases" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>

        <!-- Modal for Endpoint Card -->

        <center>
            <div class="modal" id="endpoint-card" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>

        <!-- Modal for Endpoint Metrics -->

        <center>
            <div class="modal" id="endpoint-metrics" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>

        <!-- Modal for Endpoint Metrics Reloaded -->

        <center>
            <div class="modal" id="endpoint-metrics-reload" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        </center>
    </body>
</html>

<!-- Take tour -->

<script type="text/javascript">
    
var tour = new Tour({
    smartPlacement: false,
    getProgressBarHTML: function(percent)
    {
        return '<div class="progress"><div class="progress-bar progress-bar-striped" role="progressbar" style="width: ' + percent + '%; background-color: #89C1A3;"></div></div>';
    },
    backdrop: false,
    steps: [{
        element: "#elm-date-event",
        placement: 'bottom',
        title: "Event date",
        content: "This column show the date when the event was triggered from the specified endpoint. This ocurr when the endpoint types a phrase that match with one of the fraud triangle vertices."
    }, {
        element: "#elm-type-event",
        placement: 'bottom',
        title: "Event type",
        content: "The sofware classify the events under the three vertices of the fraud triangle. Pressure, opportunity and rationalization are the three types of events you can see in this column."
    }, {
        element: "#elm-endpoint-event",
        placement: 'bottom',
        title: "Endpoint",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, events by pressure, oportunity, rationalization and score."
    }, {
        element: "#elm-windowtitle-event",
        placement: 'bottom',
        title: "Window title",
        content: "We map the endpoint phrases with Application contexts. For every event, you will see the phrase matched and the Window or Application context that was used by the employee while typing."
    }, {
        element: "#elm-endpoint-metrics",
        placement: 'bottom',
        title: "History metrics",
        content: "Here you can see in a graph the events count from fraud triangle vertices in the past 12 months. You can filter by one, two or three vertices to see only specific behavior for the employee."
    }, {
        element: "#elm-phrasetyped-event",
        placement: 'bottom',
        title: "Phrase typed",
        content: "You can click over the phrase typed and you will see a new window showing the entire conversation history. If you are the admin, you also can review it and correct the phrases to fix typos."
    }, {
        element: "#elm-mark-event",
        placement: 'left',
        title: "Event marking",
        content: "The software provides the ability to mark an event and classify it as a false positive. This is useful when you consider that the event is not relevant and need to disable it from futher calculations."
    }, {
        element: "#elm-advanced-reports",
        placement: 'top',
        title: "Advanced Reports",
        content: "In this module you can generate an advance report filtering by endpoint, by fraud vertice, by phrase, by department, by applications and by date and finally get a formatted Excel file."
    }, {
        element: "#elm-pager-events",
        placement: 'top',
        title: "Statistics and pager",
        content: "You can see here some statistics like the total number of events and their classification. In the right side, you can use the pager option to navigate the entire events using paging."
    }, {
        element: "#elm-search",
        placement: 'bottom',
        title: "Search",
        content: "You can use this search box to find one or more fraud triangle events in the entire list. This is useful when you have a lot of events under the methodology and needs to focus in one of them."
    }, {
        element: "#elm-bugreport",
        placement: 'auto',
        title: "Bug reporting",
        content: "Please use this link to access to the bug reporting platform at GitHub. If you have troubles using this application due to a malfunctioning feature, please feel free to report it."
    }, {
        element: "#elm-fraud-simulator",
        placement: 'auto',
        title: "Fraud simulator",
        content: "This is a special feature that can be used to test the Fraud Triangle Theory in one shoot, emulating the presense of an endpoint typing phrases under a set of predefined applications."
    }, {
        element: "#elm-logging",
        placement: 'auto',
        title: "Audit logging",
        content: "Every time the fraud triangle processor with artificial intelligence runs, leaves a trail of information you can review, like the launch date, time taken and number of event matches."
    }, {
        element: "#elm-software-update",
        placement: 'auto',
        title: "Software update",
        content: "The Fraud Explorer, like other software, is being developed actively, therefore you should expect many upgrades, fixes, changes and improvements in the core, server and client architecture."
    }]
});

function startTour() {
    tour.restart();
    tour.start(true);
}

</script>