<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for dashboard
 */

session_start();

if(empty($_SESSION['connected']))
{
 header ("Location: index");
 exit;
}

include "inc/check_perm.php";
error_reporting(0);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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

	<!-- CSS -->

	<link rel="stylesheet" type="text/css" href="css/dashBoard.css" media="screen" />

	<!-- Font Awesome -->

        <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

	<!-- Table sorting -->

	<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
	<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script>
</head>
<body>
	<div align="center" style="height:100%;">

		<!-- Top main menu -->

		<div id="includedTopMenu"></div>

		<?php
			include "inc/open-db-connection.php";
			$_SESSION['id_uniq_command']=null;
			$result_a = mysql_query("SELECT agent,heartbeat, now(), system, version FROM t_agents ORDER BY heartbeat DESC");

			if ($row_a = mysql_fetch_array($result_a))
			{
				/* Code for paint the table of agents via AJAX */

				echo '<div id="tableHolder" class="table-holder"></div>';
			}
		?>
	</div>

	<?php
		include "inc/close-db-connection.php";
	?>

	<!-- Modal for deletion -->

        <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
                                        	<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <a class="btn btn-danger delete">Delete</a>
                                        </div>
                        	</div>
                	</div>
        	</div>  
        </div>

	<!-- Modal for agent setup -->

        <div class="modal fade" id="confirm-setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
       	 	<div class="command-console">
	                <?php
        	                if(isset($_SESSION['id_command'])) unset($_SESSION['id_command']);
                 	        if(isset($_SESSION['seconds_waiting'])) unset($_SESSION['seconds_waiting']);
                        	if(isset($_SESSION['NRF'])) unset($_SESSION['NRF']);
                        	if(isset($_SESSION['waiting_command'])) unset($_SESSION['waiting_command']);
                        	if(isset($_SESSION['NRF_CMD'])) unset($_SESSION['NRF_CMD']);
                        	if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);

                        	$command_console_enabled = "no";

             	            	if (!isset($_GET['agent']))
                        	{
                                	echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute</strong><br><br>';
                                	$command_console_enabled = "no";
                                	if(isset($_SESSION['agentchecked'])) unset($_SESSION['agentchecked']);
                        	}
                        	else
                        	{
                            		$command_console_enabled = "yes";
                                	$agent_dec=base64_decode(base64_decode($_GET['agent']));
                                	$agent = $agent_dec;
                                	$_SESSION['agent']=$agent;
                                	$_SESSION['agentchecked']=$agent_dec;
                                	echo '<strong class="console-title"><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>Please give an instruction to execute on '.$agent.'</strong><br><br>';
                        	}
                	?>
        		<div id="result"></div>
                		<?php
                        		if ($command_console_enabled == "yes")
                      	  		{
                                		echo '<form id="fo3" name="fo3" method="post" action="saveCommands?agent='.$agent.'">';
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
	        	<p class="footer-container">&nbsp;</p>
        	        	<div class="footer-logo-container">
                        		&nbsp;&nbsp;&nbsp;<img src="images/pre-logo.svg" class="footer-logo"/><b>The Fraud Explorer</b> &reg; NF Cybersecurity & Antifraud Firm
        			</div>
            			<div class="footer-helpers">
                        		<img src="images/report-bug.svg" class="footer-link"/><a href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank" class="footer-link-a">Bug Report</a>&nbsp;&nbsp;&nbsp;&nbsp;
                        		<img src="images/documentation.svg" class="footer-link"/><a href="https://github.com/nfsecurity/the-fraud-explorer/wiki" target="_blank" class="footer-link-a">Documentation</a>&nbsp;&nbsp;&nbsp;&nbsp;
                        		<img src="images/language.svg" class="footer-link"/>Language&nbsp;&nbsp;&nbsp;&nbsp;
                        		<img src="images/support.svg" class="footer-link"/><a href="https://www.thefraudexplorer.com/#contact" target="_blank" class="footer-link-a">Support</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                		</div>
        	</div>
	</div>
</body>
</html>
