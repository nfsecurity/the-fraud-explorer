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
 * Description: Code for dashboard
 */

include "lbs/login/session.php";
include "lbs/security.php";
include "lbs/cryptography.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "dashBoard";
$_SESSION['endpointIDh'] = encRijndael("all");
$_SESSION['rulesetScope'] = "ALL";

?>

<html>
    <head>
        <title>Dashboard &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/nftop.svg" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script src="js/jquery.cookie.js" type="text/javascript"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tourist.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tourist.js"></script>
        
        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipsterCustom.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- ChartJS -->

        <script type="text/javascript" src="js/Chart.js"></script>

        <!-- CSS -->

        <link rel="stylesheet" type="text/css" href="css/dashBoard.css?<?php echo filemtime('css/dashBoard.css') ?>" media="screen" />

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
        
        <!-- JQuery Table -->

        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.widgets.js"></script>
        <script type="text/javascript" src="js/widgets/widget-output.js"></script>
        
        <!-- JQuery DotDotDot -->
        
        <script src="js/jquery.dotdotdot.js" type="text/javascript"></script>

        <!-- Footer -->

        <link rel="stylesheet" type="text/css" href="css/footer.css?<?php echo filemtime('css/footer.css') ?>" />

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

        <script type="text/javascript" src="js/dashBoard.js"></script>

        <style>

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
            
            include "lbs/openDBconn.php";

            echo '<div id="mainDashHolder"></div>';
            if (isset($_SESSION['welcome']) && $_SESSION['welcome'] == "enable") echo '<script type="text/javascript"> $(document).ready(function(){$(\'#welcomeScreen\').modal(\'show\');});</script>';

            $_SESSION['welcome'] = "disable";
            include "lbs/closeDBconn.php";
            
            ?>
        </div>

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

        <!-- Modal for Fraud Metrics -->

        <center>
            <div class="modal" id="fraud-metrics" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Fraud Metrics Reloaded -->

        <center>
            <div class="modal" id="fraud-metrics-reload" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Mail Config -->

        <center>
            <div class="modal" id="mail-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Fraud Triangle Flows Building -->

        <center>
            <div class="modal" id="fraudFlows" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="vertical-alignment-helper">
                    <div class="modal-dialog vertical-align-center" style="width: 980px;">
                        <div class="modal-content">
                            <div class="modal-body">
                                <p class="debug-url window-debug"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </center>

        <!-- Modal for Workflow View -->

        <div class="modal" id="viewWorkflow" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Fraud Triangle Rules -->

        <div class="modal" id="fraudTriangleRules" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog vertical-align-center" style="width: 760px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="debug-url window-debug"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for words universe -->

        <div class="modal" id="wordsUniverse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for GraphicData -->

        <div class="modal" id="graphicdata" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog vertical-align-center" style="width: 833px;">
                    <div class="modal-content">
                        <div class="modal-body">
                            <p class="debug-url window-debug"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Artificial Intelligence -->

        <div class="modal" id="expertSystem" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Data Backup -->

        <div class="modal" id="backupData" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- Modal for Fraud Tree -->

        <div class="modal" id="fraudTree" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

                                <?php

                                    include "lbs/openDBconn.php";
    
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

        <!-- Modal for Welcome screen -->

        <div class="modal" id="welcomeScreen" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title window-title" id="myModalLabel">Welcome to The Fraud Explorer</h4>
                        </div>

                        <div class="modal-body" style="margin: 0px 10px 15px 10px;">
                            <p style="text-align:justify; font-size: 12px;"><br>Welcome to the realtime implementation of Fraud Triangle Analytics methodology with Artificial Intelligence. With this software your company will address the fraud from a new preventive and prescriptive perspective, identifying human behaviors that may conduct to a dishonest actions mapping them into a three important aspects: social or company pressures, opportunity and justification attitudes in order to commit a fraud.<br><br> Read the documentation located in the <a href="https://www.thefraudexplorer.com/es/wiki" class="welcome">Wiki</a> and feel free to submit requests for software improvement, methodology application or bug reports at <a href="https://github.com/nfsecurity/the-fraud-explorer/issues" class="welcome">Github Issues</a>. In the name of The Fraud Explorer team, we wish you a good fraud fight.</p>
                            <p class="debug-url window-debug"></p>
                        </div>

                        <div class="modal-footer">
                            <button type="button" onclick="startTour()" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Take the tour</button>
                            <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Let's begin</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
        element: "#elm-topmenu",
        placement: 'bottom',
        title: "Main menu",
        content: "This is the main menu you can use to browse the entire solution. Dashboard, Events, Analytics and Endpoints are the principal software modules that will help you in the task of fraud detection."
    }, {
        element: "#elm-left-menu",
        placement: 'right',
        title: "Discovery menu",
        content: "Access here to the most valuable features of the software: workflows, advanced reports, endpoint agent generation, phrase library, regionalisms, business units, artificial intelligence, backup and email."
    }, { 
        element: "#elm-dashboard",
        placement: 'bottom',
        title: "Dashboard",
        content: "Here you can see the consolidated data, including top events, endpoints, metrics and data volume graphs. Here you can do a quick and daily analysis to get the 360 view of fraud triangle data."
    }, {
        element: "#elm-eventmodule",
        placement: 'bottom',
        title: "Events",
        content: "Here you can see the events produced by the Fraud Triangle Analytics methodology based on the Fraud Triangle theory from Donald R. Cressey. By clicking here you will be redirected to the Events module."
    }, {
        element: "#elm-analytics",
        placement: 'bottom',
        title: "Analytics",
        content: "You can do horizontal, vertical and diagonal fraud triangle analytics using a scatterplot graph and data tables that you can setup later to show a specific business unit from your organization."
    }, {
        element: "#elm-endpoints",
        placement: 'bottom',
        title: "Endpoint administration",
        content: "This is the endpoint administration module. Here you can get the inventory of available employees with the agent deployed and do some configurations related to their characterization."
    },  {
        element: "#elm-ruleset",
        placement: 'bottom',
        title: "Ruleset / Phrase library",
        content: "This module provides a phrase library of pressure, opportunity and rationalization expressions that you can personalize in order to detect, prevent and prescribes corporate fraud using semantics."
    }, {
        element: "#elm-roles",
        placement: 'bottom',
        title: "Roles and profiles",
        content: "Here you can create, delete or modify users and assign a domain as a context for administration segregation. Only the admin user can get into this option, other users can't access this role setting."
    }, {
        element: "#elm-configuration",
        placement: 'bottom',
        title: "Main configuration",
        content: "In the main configuration you can specify the phrase library language, the time interval for the Fraud Triangle Processor, enable or disable the data sampler, set fraud score criticality and more."
    }, {
        element: "#elm-maintenance",
        placement: 'bottom',
        title: "Maintenance",
        content: "This module provides procedures to purge databases for old records. It's recommended to do some purges every 6 months to maintain only the necessary records for fraud triangle processing rules."
    }, {
        element: "#elm-top50events",
        placement: 'top',
        title: "Latest events",
        content: "You can see here a list of top employee events occurred from fraud triangle analytics process ordered by date. Hover the mouse over the matched phrases to open the expression analysis dialog."
    }, {
        element: "#elm-viewallevents",
        placement: 'left',
        title: "View all events",
        content: "This button opens a new page in the current browser window and show all events module in extended mode. Here you can browse every event in deep and mark them as a false positive if you like."
    }, {
        element: "#elm-termstatistics",
        placement: 'top',
        title: "Term statistics",
        content: "This dashlet shows a base metric for fraud triangle analytics events in order to maintain a registry for expressions matched by pressure, opportunity and rationalziation vertices."
    }, {
        element: "#elm-fraud-metrics",
        placement: 'bottom',
        title: "Fraud metrics",
        content: "With this module you can generate very interesting metrics during the 12 months of the year about the fraud triangle behaviors covering all the business units or a particular employee or department."
    }, {
        element: "#elm-top50endpoints",
        placement: 'bottom',
        title: "Top Endpoints",
        content: "You can see here a list of top endpoints ordered by total number of events triggered, with score, ruleset and business units. This is a short view you can use to get a rapid events analysis."
    }, {
        element: "#elm-generalstatistics",
        placement: 'bottom',
        title: "Statistics graph",
        content: "This graph shows a global count of important endpoint data like the number of users covered, the number of sessions, events, dead endpoints (more than 30 days) and users that are typing phrases."
    }, {
        element: "#elm-fraud-tree",
        placement: 'bottom',
        title: "Fraud we detect",
        content: "What kind of corporate fraud we detect?, good question. Click here to see the ACFE Fraud Tree to see what are the capabilities of this software."
    }, {
        element: "#elm-counters",
        placement: 'bottom',
        title: "Endpoint counter",
        content: "You can see here the total number of endpoints deployed in your company. Usually, an agent is deployed to an employee device through Active Directory or a Mobile Device Managment solution."
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

<!-- Cookies for metrics -->

<script>
    $.cookie('endpointFraudMetrics_launch', "0");
</script>