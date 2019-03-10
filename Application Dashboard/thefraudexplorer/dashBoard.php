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
 * Description: Code for dashboard
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "dashBoard";

?>

<html>
    <head>
        <title>Dashboard &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- JS functions -->

        <script type="text/javascript" src="js/dashBoard.js"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tour.min.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>
        
        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipsterCustom.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- ChartJS -->

        <script type="text/javascript" src="js/Chart.js"></script>

        <!-- CSS -->

        <link rel="stylesheet" type="text/css" href="css/dashBoard.css" media="screen" />

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

        <link rel="stylesheet" type="text/css" href="css/footer.css" />
    </head>
    <body>
        <div align="center" style="height:100%;">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <!-- Footer inclusion -->

            <div id="includedFooterContent"></div>

            <?php
            
            include "lbs/openDBconn.php";

            echo '<div id="mainDashHolder" class="table-holder"></div>';
            if (isset($_SESSION['welcome']) && $_SESSION['welcome'] == "enable") echo '<script type="text/javascript"> $(document).ready(function(){$(\'#welcomeScreen\').modal(\'show\');});</script>';

            $_SESSION['welcome'] = "disable";
            include "lbs/closeDBconn.php";
            
            ?>
        </div>

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
    backdrop: false,
    steps: [{
        element: "#elm-topmenu",
        placement: 'bottom',
        title: "Main menu",
        content: "This is the top menu you can use for navigate the entire solution."
    }, {
        element: "#elm-dashboard",
        placement: 'bottom',
        title: "Dashboard",
        content: "This is the main dashboard, here you can see all relevant data, including events, top endpoints, metrics and data volume graphs. You can use the dashboard for executive analysis of fraud triangle."
    }, {
        element: "#elm-eventmodule",
        placement: 'bottom',
        title: "Events",
        content: "Here you can see the events produced by the Fraud Triangle Analytics methodology based on the Fraud Triangle theory from Donald R. Cressey. By clicking here you will be redirected to the Events module."
    }, {
        element: "#elm-analytics",
        placement: 'bottom',
        title: "Analytics",
        content: "You can do horizontal, vertical and diagonal analytics with the main graph and source data. All events are placed in a scatter plot graph that represents pressure, opportunity and rationalization."
    }, {
        element: "#elm-endpoints",
        placement: 'bottom',
        title: "Endpoint administration",
        content: "This is the module for endpoint administration, you can use it for search, set department, view the amount of data collected, send commands to endpoints and view some statistics and scores."
    },  {
        element: "#elm-ruleset",
        placement: 'bottom',
        title: "Ruleset / Phrase library",
        content: "This software provides a base phrase library of pressure, opportunity and rationalization expressions. You can view and edit that library here and map them to departments in your company."
    }, {
        element: "#elm-roles",
        placement: 'bottom',
        title: "Roles and profiles",
        content: "Here you can create, delete or modify users and assign a domain as a context for administration segregation. Only the admin user can get into this option, other user can't access this role setting."
    }, {
        element: "#elm-configuration",
        placement: 'bottom',
        title: "Main configuration",
        content: "In the main configuration you can specify a password for endpoints connection, enable or disable sample data, set the admin password and stablish the fraud score criticity for fraud triangle events."
    }, {
        element: "#elm-maintenance",
        placement: 'bottom',
        title: "Maintenance",
        content: "You can purge many records in this module, for example, the phrase collected, the fraud triangle analytics events, the general status and the endpoint dead sessions (people leaving the company)."
    }, {
        element: "#elm-top50events",
        placement: 'top',
        title: "Latest events",
        content: "You can see here a list of top events by fraud triangle analytics ordered by date. You can clic on View events button to expand them in a new page. Hold the mouse in info icon to see more data."
    }, {
        element: "#elm-viewallevents",
        placement: 'top',
        title: "View all events",
        content: "This button open a new page in the current browser window and show all events in extended mode. You can review an event, order the result in pagination, analyze and mark it as a false positive."
    }, {
        element: "#elm-termstatistics",
        placement: 'top',
        title: "Term statistics",
        content: "This graph show an average of fraud triangle term events in count. You can use this graph to quick view the amount of events triggered by pressure, opportunity and rationalization in your company."
    }, {
        element: "#elm-top50endpoints",
        placement: 'bottom',
        title: "Top Endpoints",
        content: "You can see here a list of top endpoints ordered by number of events triggered in total, with score and ruleset (department). You can clic on Download as CSV to get this list in XLS format."
    }, {
        element: "#elm-generalstatistics",
        placement: 'bottom',
        title: "Statistics graph",
        content: "This graph show a global count of important endpoint data like the number of users covered, the number of sessions, events, dead endpoints (more than 30 days) and users that are typing phrases."
    }, {
        element: "#elm-counters",
        placement: 'bottom',
        title: "Endpoint counter",
        content: "You can see here the total number of endpoints, the number of offline and online in The Fraud Explorer. This data is useful to see the status in deployment and compare with company users."
    }, {
        element: "#footer",
        placement: 'auto',
        title: "Application context",
        content: "You can see some helper links and bug report, also, The Fraud Explorer is a multi company solution, you can see here the context of the company that can use with your actual login credentials."
    }]
});

function startTour() {
    tour.init();
    tour.restart();
    tour.start(true);
}

</script>
