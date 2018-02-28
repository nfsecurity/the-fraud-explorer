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
        <link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipster-sideTip-light.min.css">
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

            <?php
            
            $_SESSION['id_uniq_command']=null;

            /* Code for paint the table of agents via AJAX */

            echo '<div id="tableHolder" class="table-holder"></div>';

            ?>
        </div>

        <!-- Modal for deletion -->

        <div class="modal fade-scale" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="vertical-alignment-helper">
                <div class="modal-dialog vertical-align-center">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title window-title" id="myModalLabel">Confirm Delete</h4>
                        </div>

                        <div class="modal-body">
                            <p style="text-align:left; font-size: 12px;"><br>You are about to delete the agent, this procedure is irreversible and delete database entries and files without recovery opportunity. Do you want to proceed ?</p>
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

        <!-- Modal for agent setup -->

        <div class="modal fade-scale" id="confirm-setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

        <!-- ConsoleJS functions -->

        <script type="text/javascript" src="js/console.js"></script>

        <!-- Ajax for capture ENTER key in command line -->

        <script language="JavaScript" type="text/javascript" src="js/ajax.js"></script>

        <!-- TableXMLHolder AJAX funtions -->

        <script type="text/javascript" src="js/xmlTableHolder.js"></script>

        <div class="command-console-container">
            <div class="command-console" id="elm-commandconsole">
                
                <?php
                
                if(isset($_SESSION['id_command'])) unset($_SESSION['id_command']);
                if(isset($_SESSION['seconds_waiting'])) unset($_SESSION['seconds_waiting']);
                if(isset($_SESSION['NRF'])) unset($_SESSION['NRF']);
                if(isset($_SESSION['waiting_command'])) unset($_SESSION['waiting_command']);
                if(isset($_SESSION['NRF_CMD'])) unset($_SESSION['NRF_CMD']);
                if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);

                $command_console_enabled = "no";

                if (!isset($_GET['agent']) && !isset($_GET['domain']))
                {
                    echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute</strong><br><br>';
                    $command_console_enabled = "no";
                    if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);
                }
                else if(isset($_GET['agent']) && isset($_GET['domain']))
                {
                    $agent_dec = base64_decode(base64_decode(filter($_GET['agent'])));
                    $agent = $agent_dec;
                    $domain_dec = base64_decode(base64_decode(filter($_GET['domain'])));
                    $domain = $domain_dec;
                    
                    if (checkEndpoint($agent, $domain))
                    {
                        $command_console_enabled = "yes";
                        $_SESSION['agent']=$agent;
                        $_SESSION['agentchecked']=$agent_dec;
                        echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute on '.$agent.'</strong><br><br>';
                    }
                    else
                    {
                        echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute</strong><br><br>';
                        $command_console_enabled = "no";
                        if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);
                    }
                }
                else
                {
                    echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute</strong><br><br>';
                    $command_console_enabled = "no";
                    if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);
                }
                
                ?>
                
                <div id="result"></div>
                
                <?php
                
                if ($command_console_enabled == "yes")
                {
                    echo '<form id="fo3" name="fo3" method="post" action="saveCommands?agent='.$agent.'&domain='.$domain.'">';
                    echo '</strong><input class="intext command-cli" type="text" autocomplete="off" placeholder=":type instruction here" name="commands" id="commands" onkeypress="iSubmitEnter(event, document.form1)" >';
                    echo '<br><br><div class="window-command-status" id="commandStatus"></div>';
                    echo '</form>';
                }
                else
                {
                    echo '<form id="fo3" name="fo3" method="post" action="#">';
                    echo '<input class="intext command-cli" type="text" disabled autocomplete="off" placeholder=":type instruction here" name="commands" id="commands" onkeypress="iSubmitEnter(event, document.form1)" >';
                    echo '<br><br><div class="window-command-status" id="commandStatus"></div>';
                    echo '</form>';
                }
                
                ?>
                
            </div>

            <div id="tableHolderXML"></div>
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
        </div>
    </body>
</html>

<!-- Take tour -->

<script type="text/javascript">
    
var tour = new Tour({
    smartPlacement: false,
    backdrop: false,
    steps: [{
        element: "#elm-details-dashboard",
        placement: 'right',
        title: "Endpoint details",
        content: "You can hover the mouse pointer over this icon to see some endpoint data, like identification, corporate domain, operating system, IP address, sessions and connection status."
    }, {
        element: "#elm-endpoints-dashboard",
        placement: 'bottom',
        title: "Endpoints",
        content: "You can hover the mouse under the endpoint name and you will see some fraud triangle insights, like records stored, alerts by pressure, oportunity, rationalization, score and data representation."
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
        content: "You will see here the endpoint status, connected or disconnected. If the agent is disconnected means that the PC is offline or is working without an internet connection to send data."
    }, {
        element: "#elm-last-dashboard",
        placement: 'bottom',
        title: "Last connection",
        content: "Is important to see when was the last connection on every endpoint in date format, because you can troubleshoot some problems in endpoint deployment and connectivity issues."
    }, {
        element: "#elm-triangle-dashboard",
        placement: 'bottom',
        title: "Fraud Triangle data",
        content: "You can see here a consolidation of alerts for each endpoint in relation to the fraud triangle vertices, pressure, opportunity and rationalization. It's for rapid view of fraud triangle data."
    }, {
        element: "#elm-level-dashboard",
        placement: 'bottom',
        title: "Criticality level",
        content: "This field show the level of criticity for each endpoint based in the three fraud triangle vertices, pressure, opportunity and rationalization. This level can be adjusted in configuration."
    }, {
        element: "#elm-score-dashboard",
        placement: 'bottom',
        title: "Score",
        content: "The score is the average value from the amount of alerts in the fraud triangle vertices, pressure, opportunity and rationalization. You can clic the number to enter directly in alert data."
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
        element: "#elm-commandconsole",
        placement: 'top',
        title: "Command console",
        content: "This is and advanced function of the software. The advanced user can send commands to the endpoints to change individual or global configurations. See the documentation for more information."
    }, {
        element: "#tableHolderXML",
        placement: 'top',
        title: "Command response console",
        content: "This is command consolidation console. When the advanced user sends a command to an endpoint, the console show the entire command. This is useful to see the last command sent to the endpoints."
    }, {
        element: "#elm-pager",
        placement: 'top',
        title: "Data statistics and pager",
        content: "You can see some data statistics about the amount of data collected. Also, you can do paging between endpoints with the ability of download the entire endpoint list in a XLS format."
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