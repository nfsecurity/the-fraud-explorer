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
        <script src="js/bootstrap.js"></script>
        
        <!-- JS/CSS for Tooltip -->

        <link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipster-sideTip-light.min.css">
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

        <style>
            .font-icon-color-white { color: #FFFFFF; }
        </style>
    </head>
    <body>
        <div align="center" style="height:100%;">

            <!-- Top main menu -->

            <div id="includedTopMenu"></div>

            <?php
            
            include "lbs/open-db-connection.php";
            $_SESSION['id_uniq_command']=null;

            echo '<div id="mainDashHolder" class="table-holder"></div>';
            if (isset($_SESSION['welcome']) && $_SESSION['welcome'] == "enable") echo '<script type="text/javascript"> $(document).ready(function(){$(\'#welcomeScreen\').modal(\'show\');});</script>';

            $_SESSION['welcome'] = "disable";
            include "lbs/close-db-connection.php";
            
            ?>
        </div>

        <div id="footer">
            <p class="main-text">&nbsp;</p>
            <div class="logo-container">
                &nbsp;&nbsp;&nbsp;<span class="fa fa-cube fa-lg font-icon-color-white">&nbsp;&nbsp;</span>The Fraud Explorer</b> &reg; NF Cybersecurity & Antifraud Firm
        </div>
        <div class="helpers-container">
            <span class="fa fa-bug fa-lg font-icon-color-white">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank">Bug Report</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-file-text fa-lg font-icon-color-white">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/wiki" target="_blank">Documentation</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-globe fa-lg font-icon-color-white">&nbsp;&nbsp;</span>Language&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-medkit fa-lg font-icon-color-white">&nbsp;&nbsp;</span><a style="color: white;" href="https://www.thefraudexplorer.com/#contact" target="_blank">Support</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-building-o fa-lg font-icon-color-white">&nbsp;&nbsp;</span>Application context [<?php echo $session->username ." - ".$session->domain; ?>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
        </div>

    <!-- Modal for Welcome Screen -->

    <div class="modal fade-scale" id="welcomeScreen" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="vertical-alignment-helper">
            <div class="modal-dialog vertical-align-center">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title window-title" id="myModalLabel">Welcome to The Fraud Explorer</h4>
                    </div>

                    <div class="modal-body">
                        <p style="text-align:justify; font-size: 12px;"><br>Welcome to the realtime implementation of Fraud Triangle Analytics methodology. With this software your company will address the fraud from a new detective and preventive perspective, identifying human behaviors that conduct to a dishonest actions mapping them into a three important aspects: social or company pressures, opportunity and justification attitudes in order to commit a fraud.<br><br> Read the documentation located in the <a href="https://www.thefraudexplorer.com/es/wiki">Wiki</a> and feel free to submit requests for software improvement, methodology application or bug reports at <a href="https://github.com/nfsecurity/the-fraud-explorer/issues">Github Issues</a>. In the name of The Fraud Explorer team, we wish you a good fraud fight.</p>
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