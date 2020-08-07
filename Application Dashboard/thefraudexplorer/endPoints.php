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

    <link rel="stylesheet" type="text/css" href="css/endPoints.css?<?php echo filemtime('css/endPoints.css') ?>" media="screen" />

    <!-- Font Awesome -->

    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

    <!-- JQuery Table -->

    <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
    <script type="text/javascript" src="js/jquery.tablesorter.widgets.js"></script>
    <script type="text/javascript" src="js/widgets/widget-output.js"></script>

    <!-- Footer -->

    <link rel="stylesheet" type="text/css" href="css/footer.css?<?php echo filemtime('css/footer.css') ?>" />

    <!-- JQuery nice select -->
      
    <script src="js/jquery.nice-select.js"></script>
    <link rel="stylesheet" href="css/nice-select.css">

    <!-- Rangy -->

    <script type="text/javascript" src="js/rangy-core.js"></script>
    <script type="text/javascript" src="js/rangy-textrange.js"></script>  

    <!-- JS functions -->

    <script type="text/javascript" src="js/endPoints.js"></script>

    <style>

        .font-icon-color-white 
        { 
            color: #FFFFFF; 
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

    <!-- Modal for Build Endpoint -->

    <div class="modal" id="build-endpoint" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

    <!-- Modal for Business Units Segmentation -->

    <center>
        <div class="modal" id="business-units" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

                            <?php
    
                                include "lbs/cryptography.php";

                                $xml = simplexml_load_file('update.xml');
                                $phraseCollectionStatus = decRijndael($xml->token[0]['arg']);
        
                                if ($phraseCollectionStatus == "textAnalytics 1") echo '<a class="btn btn-success switch-phrase-collection-button" style="outline: 0 !important;">Disable collection</a>';
                                else echo '<a class="btn btn-success switch-phrase-collection-button" style="outline: 0 !important;">Enable collection</a>';
                                
                            ?>

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
        element: "#elm-endpoints-dashboard",
        placement: 'bottom',
        title: "Endpoints",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, events by pressure, oportunity, rationalization and score."
    }, {
        element: "#elm-ruleset-dashboard",
        placement: 'bottom',
        title: "Ruleset",
        content: "The sofware classify the events under the three vertices of the fraud triangle. Pressure, opportunity and rationalization are the three types of events you can see in this column."
    }, {
        element: "#elm-version-dashboard",
        placement: 'bottom',
        title: "Endpoint version",
        content: "You can see here the endpoint version of the software. This is useful when you are doing updates and you can see how many endpoints are going to work with the new agent versions."
    }, {
        element: "#elm-status-dashboard",
        placement: 'bottom',
        title: "Status",
        content: "You will see here the endpoint status, connected or disconnected. If the endpoint is disconnected means that the PC or mobile is offline or is working without an internet connection to send data."
    }, {
        element: "#elm-last-dashboard",
        placement: 'bottom',
        title: "Last connection",
        content: "Is important to see when was the last connection on every endpoint in date format, because you can troubleshoot some problems in endpoint deployment and connectivity issues."
    }, {
        element: "#elm-triangle-dashboard",
        placement: 'bottom',
        title: "Fraud Triangle data",
        content: "You can see here the events consolidation for each endpoint in relation to the fraud triangle vertices, pressure, opportunity and rationalization. Useful for quick view of fraud triangle data."
    }, {
        element: "#elm-level-dashboard",
        placement: 'bottom',
        title: "Criticality level",
        content: "This field shows the level of criticity for each endpoint based in the three fraud triangle vertices, pressure, opportunity and rationalization. This level can be adjusted in the setup module."
    }, {
        element: "#elm-score-dashboard",
        placement: 'bottom',
        title: "Score",
        content: "The score is the average value from the amount of events in the fraud triangle vertices, pressure, opportunity and rationalization. You can click the number to enter directly in events data."
    }, {
        element: "#elm-command-dashboard",
        placement: 'bottom',
        title: "Send command",
        content: "This is and advance feature of the software available only to experienced users. You can send some commands to an endpoint. Please read the documentation for more information about that."
    }, {
        element: "#elm-delete-dashboard",
        placement: 'bottom',
        title: "Endpoint deletion",
        content: "You can delete endpoints if you don't need them anymore. This process is irreversible and will delete all the data from databases. It's recommended to do a backup first."
    }, {
        element: "#elm-set-dashboard",
        placement: 'left',
        title: "Endpoint setup",
        content: "You can click on this icon to adjust the endpoint alias, the gender and their ruleset. Remember that assign a ruleset is similar to set the business unit of the employee."
    }, {
        element: "#elm-pager",
        placement: 'top',
        title: "Data statistics and pager",
        content: "You can see here some statistics about the amount of data collected. Also, you can do paging between endpoints with the ability to download the entire endpoint list in a CSV format."
    }, {
        element: "#elm-build-endpoint",
        placement: 'top',
        title: "Download endpoint",
        content: "You can build your own personalized endpoint in MSI or APK format. You must specify the FQDN server address, the platform and the option to disable or enable phrase collection."
    }, {
        element: "#elm-business-units",
        placement: 'top',
        title: "Departments",
        content: "You can click this button to configure the business units of your organization and map them to the endpoints. This will apply the specific rules for your endpoints for better results."
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
