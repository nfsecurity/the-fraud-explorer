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
 * Date: 2019-05
 * Revision: v1.3.3-ai
 *
 * Description: Code for Chart
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

$_SESSION['instance'] = "analyticsData";

require 'vendor/autoload.php';
include "lbs/openDBconn.php";
include "lbs/endpointMethods.php";
include "lbs/elasticsearch.php";

?>

<html>
    <head>
        <title>Analytics &raquo; The Fraud Explorer</title>
        <link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <!-- JQuery 11 inclusion -->

        <script type="text/javascript" src="js/jquery.min.js"></script>

        <!-- JS functions -->

        <script type="text/javascript" src="js/analyticsData.js"></script>

        <!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <link href="css/bootstrap-tour.min.css" rel="stylesheet">
        <script src="js/bootstrap.js"></script>
        <script src="js/bootstrap-tour.min.js"></script>

        <!-- Charts CSS -->

        <link rel="stylesheet" type="text/css" href="css/analyticsData.css"/>
        <link rel="stylesheet" type="text/css" href="css/chartAnalytics.css" media="screen" />

        <!-- Load Chart.js -->

        <script src="js/Chart.js"></script>

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

        <!-- JQuery nice select -->

        <script src="js/jquery.nice-select.js"></script>
        <link rel="stylesheet" href="css/nice-select.css">

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
        <div align="center">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <!-- Footer inclusion -->

            <div id="includedFooterContent"></div>

            <!-- Code for paint chart -->

            <?php

            if (isset($_POST["ruleset"])) $_SESSION['rulesetScope'] = $_POST["ruleset"];
            else $_SESSION['rulesetScope'] = "ALL";

            ?>

            <div id="chartHolder" class="chart-holder"></div>
        </div>
    </body>
</html>

<!-- Take tour -->

<script type="text/javascript">
    
var tour = new Tour({
    smartPlacement: false,
    backdrop: false,
    steps: [{
        element: "#elm-bubble",
        placement: 'left',
        title: "Scatter graph",
        content: "This is the module used for doing horizontal and vertical analytics. The points represents events and are plotted using the pressure, opportunity and rationlaization axis with a score variable."
    }, {
        element: "#elm-vertical",
        placement: 'bottom',
        title: "Vertical analytics",
        content: "This is the module where you can do vertical analytics, examining data flow and viewing source tables. With vertical analytics you see all events and data hints usefull for deep behavior analysis."
    }, {
        element: "#elm-analyticsaccess",
        placement: 'bottom',
        title: "Access all events",
        content: "With this link you can access the main events database that originates this graph. In that module you will see all behaviors regarding fraud triangle theory from Donald Ray Cressey."
    }, {
        element: "#elm-ai",
        placement: 'bottom',
        title: "Analytics data access",
        content: "this is the Artificial Intelligence module where you can see the deductions and inferences that the expert system produces based on the knowledge and facts database."
    }, {
        element: "#elm-scope",
        placement: 'right',
        title: "Analytics scope",
        content: "You can limit the data on graph based on departments in your company. All the software methodology is based on fraud triangle expressions associated with depertments with one global scope."
    }, {
        element: "#elm-legend",
        placement: 'right',
        title: "Graph leyend",
        content: "Depends of the data, the event can be represented in various forms. A dark star indicates that the endpoint has a high score and a light star indicates that endpoint has a lower score."
    }, {
        element: "#elm-phrasecounts",
        placement: 'right',
        title: "Phrase counts",
        content: "This is the amount of phrases matched by the phrase library database. This represents the total phrases in pressure, opportunity and rationalization that the company has."
    }, {
        element: "#elm-dictionarysize",
        placement: 'right',
        title: "Dictionary size",
        content: "This data represents the size of the expression or phrase library size. Each phrase under pressure, opportunity and rationalization can has many expansions due to the use of regular expressions."
    }, {
        element: "#elm-fraud-triangle-rules",
        placement: 'right',
        title: "Library workshop",
        content: "You can use this option to add, modify or delete rules in the phrase library database based on JSON representation. Please note that is neccesary that you have knowledge in regular expressions."
    }]
});

function startTour() {
    tour.init();
    tour.restart();
    tour.start(true);
}

</script>