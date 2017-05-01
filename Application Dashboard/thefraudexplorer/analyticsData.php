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
 * Date: 2017-04
 * Revision: v1.0.0-beta
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
        <script src="js/bootstrap.js"></script>

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