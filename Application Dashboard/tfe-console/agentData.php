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
 * Description: Code for agent data
 */

session_start();

if(empty($_SESSION['connected']))
{
 header ("Location: index");
 exit;
}

include "inc/check_perm.php";
error_reporting(0);

function filter($variable)
{
        return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

$_SESSION['agentID']=filter($_GET['agent']);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TFE - Agent Data</title>
	<meta http-equiv="X-Frame-Options" content="deny">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<!-- JQuery 11 inclusion -->

	<script type="text/javascript" src="js/jquery.min.js"></script>

	<!-- JS functions -->

	<script type="text/javascript" src="js/agentData.js"></script>
	
	<!-- Styles and JS for modal dialogs -->

	<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
	<script src="js/bootstrap.js"></script>

	<!-- Finish code for modal dialogs -->

	<link rel="stylesheet" type="text/css" href="css/agentData.css" media="screen" />

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
			echo '<div id="tableHolder" class="table-holder"></div>';
			include "inc/close-db-connection.php";
		?>
	</div>

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
