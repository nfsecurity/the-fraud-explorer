<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
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
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";
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

        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipster-sideTip-light.min.css">
        <script type="text/javascript" src="js/tooltipster.bundle.js"></script>

        <!-- Load ScatterPlotChart -->

        <link href="css/scatterplot.css" rel="stylesheet" type="text/css" />
        <script src="js/scatterplot.js"></script>

        <!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
    </head>
    <body>
        <div align="center">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <!-- Code for paint chart -->

            <?php
            if (isset($_POST["ruleset"])) $_SESSION['rulesetScope'] = $_POST["ruleset"];
            else $_SESSION['rulesetScope'] = "ALL";
            ?>

            <div id="chartHolder" class="chart-holder"></div>
        </div>

        <!-- Footer -->

        <div id="includedGenericFooterContent"></div>
    </body>
</html>

<!-- Take tour -->

<script type="text/javascript">
    
var tour = new Tour({
    smartPlacement: false,
    backdrop: false,
    steps: [{
        element: "#scatterplot",
        placement: 'top',
        title: "Scatter graph",
        content: "This is the module used for doing horizontal and vertical analytics. The circles represents alerts and are plotted using the pressure, opportunity and rationlaization axis with a score variable."
    }, {
        element: "#elm-analyticsaccess",
        placement: 'bottom',
        title: "Analytics data access",
        content: "You can access all alert data and also see the data source that produces this scatter graph. Some times there are endpoints with the same number of alerts, please use that data for clarification."
    }, {
        element: "#elm-scope",
        placement: 'right',
        title: "Analytics scope",
        content: "You can limit the data on graph based on departments in your company. All the software methodology is based on fraud triangle expressions associated with depertments with one global scope."
    }, {
        element: "#elm-legend",
        placement: 'right',
        title: "Graph leyend",
        content: "Depends of the data, the alert can be represented in various forms. A red point indicates that the endpoint has a high score and a yellow point indicates that endpoint has a lower score."
    }, {
        element: "#elm-opportunity",
        placement: 'right',
        title: "Opportunity variable",
        content: "The scatter graph has 4 variables, 2 of them represented in axes. Another variable is represented by the size of the point and the last variable is represented by score. Here is the opportunity variable values."
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
    }]
});

function startTour() {
    tour.init();
    tour.restart();
    tour.start(true);
}

</script>