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
 * Date: 2019-03
 * Revision: v1.3.2-ai
 *
 * Description: Code for endPoints
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "endPoints";

?>

<html>
    <head>
        <title>Endpoints &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- JS functions -->

        <script type="text/javascript" src="js/endPoints.js"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tour.min.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>

        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipsterCustom.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- CSS -->

        <link rel="stylesheet" type="text/css" href="css/endPoints.css" media="screen" />

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

        <!-- JQuery Table -->

        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.widgets.js"></script>
        <script type="text/javascript" src="js/widgets/widget-output.js"></script>

        <!-- Footer -->

        <link rel="stylesheet" type="text/css" href="css/footer.css" />

        <style>
            .font-icon-color-white { color: #FFFFFF; }
        </style>
    </head>
    <body>
        <div align="center" style="height:100%;">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <!-- Footer inclusion -->

            <div id="includedFooterContent"></div>

            <?php

            /* Code for paint endpoint table via AJAX */

            echo '<div id="tableHolder" class="table-holder"></div>';

            ?>
        </div>

        <!-- Modal for deletion -->

        <div class="modal" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title window-title" id="myModalLabel">Confirm Delete</h4>
                        </div>

                        <div class="modal-body" style="margin: 0px 10px 15px 10px;">
                            <p style="text-align:left; font-size: 12px;"><br>You are about to delete the endpoint, this procedure is irreversible and delete database entries and files without recovery opportunity. Do you want to proceed ?</p>
                            <p class="debug-url window-debug"></p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
                            <a class="btn btn-danger delete" style="outline: 0 !important;">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for endpoint setup -->

        <div class="modal" id="confirm-setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
        
        <!-- Modal for switch phrases collection -->

        <center>
            <div class="modal" id="switch-phrase-collection" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="vertical-alignment-helper">
                    <div class="modal-dialog vertical-align-center">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title window-title" id="myModalLabel">Phrase collection switching</h4>
                            </div>

                            <div class="modal-body" style="margin: 0px 10px 15px 10px;">
                                <p style="text-align:justify; font-size: 12px;"><br>You are about to switch between enable/disable phrase collection. This means that depends of your selection, the endpoints will not send data (phrases that are being typing in applications) to the server, and The Fraud Explorer can't do the work. You can switch the times you want. Do you want to proceed ?</p>
                                <p class="debug-url window-debug"></p>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">I'm not sure</button>
                                <a class="btn btn-success switch-phrase-collection-button" style="outline: 0 !important;">I'm sure, proceed</a>
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
        element: "#elm-endpoints-dashboard",
        placement: 'bottom',
        title: "Endpoints",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, events by pressure, oportunity, rationalization, score and data representation."
    }, {
        element: "#elm-ruleset-dashboard",
        placement: 'bottom',
        title: "Ruleset",
        content: "Each endpoint can has a category assigned. This category provides the ability to specify a portion of phrase dictionary to get matched using departments like human resources, purchases, etc. "
    }, {
        element: "#elm-version-dashboard",
        placement: 'bottom',
        title: "Endpoint version",
        content: "You can see here the endpoint version of the software. This is useful when you are doing updates and you can see how many endpoints are going to work with the new software versions."
    }, {
        element: "#elm-status-dashboard",
        placement: 'bottom',
        title: "Status",
        content: "You will see here the endpoint status, connected or disconnected. If the endpoint is disconnected means that the PC is offline or is working without an internet connection to send data."
    }, {
        element: "#elm-last-dashboard",
        placement: 'bottom',
        title: "Last connection",
        content: "Is important to see when was the last connection on every endpoint in date format, because you can troubleshoot some problems in endpoint deployment and connectivity issues."
    }, {
        element: "#elm-triangle-dashboard",
        placement: 'bottom',
        title: "Fraud Triangle data",
        content: "You can see here a consolidation of events for each endpoint in relation to the fraud triangle vertices, pressure, opportunity and rationalization. It's for rapid view of fraud triangle data."
    }, {
        element: "#elm-level-dashboard",
        placement: 'bottom',
        title: "Criticality level",
        content: "This field show the level of criticity for each endpoint based in the three fraud triangle vertices, pressure, opportunity and rationalization. This level can be adjusted in configuration."
    }, {
        element: "#elm-score-dashboard",
        placement: 'bottom',
        title: "Score",
        content: "The score is the average value from the amount of events in the fraud triangle vertices, pressure, opportunity and rationalization. You can clic the number to enter directly in events data."
    }, {
        element: "#elm-command-dashboard",
        placement: 'bottom',
        title: "Send command",
        content: "This is and advance feature of the software available only to experienced users. You can send some commands to an endpoint. Please read the documentation for more information about that."
    }, {
        element: "#elm-delete-dashboard",
        placement: 'bottom',
        title: "Endpoint deletion",
        content: "You can delete endpoints if you don't need them anymore. This process is irreversible and delete all data from databases. It's recommended to do a backup first before endpoint deletions."
    }, {
        element: "#elm-set-dashboard",
        placement: 'left',
        title: "Endpoint setup",
        content: "You can clic on this icon to adjust the endpoint alias, the gender and the ruleset. Remember that assign ruleset is similar to set the department of the endpoint in the company."
    }, {
        element: "#elm-pager",
        placement: 'top',
        title: "Data statistics and pager",
        content: "You can see some data statistics about the amount of data collected. Also, you can do paging between endpoints with the ability of download the entire endpoint list in a XLS format."
    }, {
        element: "#elm-csv",
        placement: 'top',
        title: "Export endpoints",
        content: "By pressing thus button you can export the endpoint list in a comma separated value (CSV) file. This file is useful when you need to filter and make some reports in an executive manner."
    }, {
        element: "#elm-msi",
        placement: 'top',
        title: "Download MSI Endpoint",
        content: "The MSI file you obtain here is useful for deploy the software in the Active Directory enviromnet of your organization. Please note that this MSI is only valid for your public IP address."
    }, {
        element: "#elm-departments",
        placement: 'top',
        title: "Departments",
        content: "You can clic this button to upload a CSV file with the content of departments for each user you can specify in the format login, domain, full name, department or ruleset, gender."
    }, {
        element: "#elm-switch-phrase-collection",
        placement: 'top',
        title: "Switch phrase collection",
        content: "You can switch between enable/disable phrase collection. This means that depends of your selection, the endpoints will not send data (phrases that are being typing in applications) to the server."
    }, {
        element: "#elm-search",
        placement: 'bottom',
        title: "Search",
        content: "You can use this search box to find one or more endpoints in the entire list. This is useful when you have a lot of endpoints under the methodology and needs to focus in one of them."
    }]
});

function startTour() {
    tour.init();
    tour.restart();
    tour.start(true);
}

</script>
