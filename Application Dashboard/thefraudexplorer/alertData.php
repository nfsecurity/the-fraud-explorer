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
 * Date: 2018-12
 * Revision: v1.2.1
 *
 * Description: Code for horizontal analytics data
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "alertData";
$_SESSION['agentIDh']=filter($_GET['agent']);

if (!checkAlert(base64_decode(base64_decode(filter($_SESSION['agentIDh']))))) header ("location: endPoints");

?>

<html>
    <head>
        <title>Agent Data &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- JS functions -->

        <script type="text/javascript" src="js/alertData.js"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tour.min.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>

        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipster-sideTip-light.min.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- CSS -->

        <link rel="stylesheet" type="text/css" href="css/alertData.css" media="screen" />

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

        <!-- JQuery Table -->

        <script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
        <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.widgets.js"></script>
        <script type="text/javascript" src="js/widgets/widget-output.js"></script>

        <!-- Footes CSS -->

        <link rel="stylesheet" type="text/css" href="css/footer.css">
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
        
        <!-- Modal for Alert Marking -->

        <center>
            <div class="modal" id="alertMarking" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
            <div class="modal" id="alert-phrases" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
    backdrop: false,
    steps: [{
        element: "#elm-details-alert",
        placement: 'right',
        title: "Alert details",
        content: "Hover mouse pointer over this icon, you will see the alert consolidation data. This icon show all the alert data you can see in the main table plus the regular expression matched by the phrase."
    }, {
        element: "#elm-date-alert",
        placement: 'bottom',
        title: "Alert date",
        content: "This column show the date when the alert was triggered from the specified endpoint. This ocurr when the endpoint types some phrases that match with the fraud triangle phrase database."
    }, {
        element: "#elm-type-alert",
        placement: 'bottom',
        title: "Alert type",
        content: "The sofware classify the alerts under the three vertices of the fraud triangle. Pressure, opportunity and rationalitazion are the three types of alerts you can see in this module."
    }, {
        element: "#elm-endpoint-alert",
        placement: 'bottom',
        title: "Endpoint",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, alerts by pressure, oportunity, rationalization, score and data representation."
    }, {
        element: "#elm-windowtitle-alert",
        placement: 'bottom',
        title: "Window title",
        content: "This software maps the endpoint writing with windows titles. For every alert, you will see the phrase matched and the window or application context that was used for type the phrase."
    }, {
        element: "#elm-phrasetyped-alert",
        placement: 'bottom',
        title: "Phrase typed",
        content: "You can clic over the phrase typed and you will see a new window showing the entire text history. If you are the admin user, you also can review and correct the phrases fixing typos."
    }, {
        element: "#elm-mark-alert",
        placement: 'left',
        title: "Alert marking",
        content: "The software provides the ability to mark an alert and classify it as a false positive. This is useful when you consider that the alert is not relevant and need to disable it from futher calculations."
    }, {
        element: "#elm-pager-alerts",
        placement: 'top',
        title: "Statistics and pager",
        content: "You can see here some statistics like the total number of alerts and your classification. In the right side, you can use the pager option to navigate in the entire alerts using paging."
    }, {
        element: "#elm-search",
        placement: 'bottom',
        title: "Search",
        content: "You can use this search box to find one or more alerts in the entire list. This is useful when you have a lot of alerts under the methodology and needs to focus in one of them."
    }]
});

function startTour() {
    tour.init();
    tour.restart();
    tour.start(true);
}

</script>