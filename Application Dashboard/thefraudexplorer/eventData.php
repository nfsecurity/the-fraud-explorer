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
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- JS functions -->

        <script type="text/javascript" src="js/eventData.js"></script>

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
        content: "This column show the date when the event was triggered from the specified endpoint. This ocurr when the endpoint types some phrases that match with the fraud triangle phrase database."
    }, {
        element: "#elm-type-event",
        placement: 'bottom',
        title: "Event type",
        content: "The sofware classify the events under the three vertices of the fraud triangle. Pressure, opportunity and rationalitazion are the three types of events you can see in this module."
    }, {
        element: "#elm-endpoint-event",
        placement: 'bottom',
        title: "Endpoint",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, events by pressure, oportunity, rationalization, score and data representation."
    }, {
        element: "#elm-windowtitle-event",
        placement: 'bottom',
        title: "Window title",
        content: "This software maps the endpoint writing with windows titles. For every event, you will see the phrase matched and the window or application context that was used for type the phrase."
    }, {
        element: "#elm-endpoint-metrics",
        placement: 'bottom',
        title: "History metrics",
        content: "Here you can see in a graph the events count from fraud triangle vertices in the past 12 months. You can filter by one, two or three vertices to see only specific behavior for the employee."
    }, {
        element: "#elm-phrasetyped-event",
        placement: 'bottom',
        title: "Phrase typed",
        content: "You can clic over the phrase typed and you will see a new window showing the entire text history. If you are the admin user, you also can review and correct the phrases fixing typos."
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
        content: "You can see here some statistics like the total number of events and your classification. In the right side, you can use the pager option to navigate in the entire events using paging."
    }, {
        element: "#elm-search",
        placement: 'bottom',
        title: "Search",
        content: "You can use this search box to find one or more events in the entire list. This is useful when you have a lot of events under the methodology and needs to focus in one of them."
    }]
});

function startTour() {
    tour.restart();
    tour.start(true);
}

</script>