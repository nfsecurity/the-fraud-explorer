<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for dashboard
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
	header ("Location: index");
	exit;
}

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

			$result_a = mysql_query("SELECT agent FROM t_agents", $connection);

			if ($row_a = mysql_fetch_array($result_a))
			{
				/* Code for paint the table of agents via AJAX */

				echo '<div id="tableHolder" class="table-holder"></div>';
			}
			else
			{	
				echo '<script type="text/javascript"> $(document).ready(function(){$(\'#noAgentsYet\').modal(\'show\');});</script>';
			}

			include "lbs/close-db-connection.php";
		?>
	</div>

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
                                        	<button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
                                                <a class="btn btn-danger delete" style="outline: 0 !important;">Delete</a>
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

	<!-- Modal for no-agents-yet message -->

        <div class="modal fade" id="noAgentsYet" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="vertical-alignment-helper">
                        <div class="modal-dialog vertical-align-center">
                                <div class="modal-content">
                                        <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                                <h4 class="modal-title window-title" id="myModalLabel">Welcome to The Fraud Explorer</h4>
                                        </div>

                                        <div class="modal-body">
                                                <p style="text-align:left; font-size: 12px;"><br>At this time there is no agents connected to The Fraud Explorer, maybe because it's the first time you are using the software or you are near to deploy the agents.
						If you need help, please refer to the Wiki at www.thefraudexplorer.com/es/wiki.</p>
                                                <p class="debug-url window-debug"></p>
                                        </div>

                                        <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
						<button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Accept</button>
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
