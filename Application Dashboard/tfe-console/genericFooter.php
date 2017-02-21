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
 * Date: 2017-02
 * Revision: v0.9.8-beta
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

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="css/footer.css">

<div id="footer">
	<p class="main-text">&nbsp;</p>
		<div class="logo-container">
			&nbsp;&nbsp;&nbsp;<img src="images/pre-logo.svg" class="logo"/><b>The Fraud Explorer</b> &reg; NF Cybersecurity & Antifraud Firm
		</div>
		<div class="helpers-container">
                        <img src="images/report-bug.svg" class="svg-link"/><a href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank">Bug Report</a>&nbsp;&nbsp;&nbsp;&nbsp;
			<img src="images/documentation.svg" class="svg-link"/><a href="https://github.com/nfsecurity/the-fraud-explorer/wiki" target="_blank">Documentation</a>&nbsp;&nbsp;&nbsp;&nbsp;
                	<img src="images/language.svg" class="svg-link"/>Language&nbsp;&nbsp;&nbsp;&nbsp;
			<img src="images/support.svg" class="svg-link"/><a href="https://www.thefraudexplorer.com/#contact" target="_blank">Support</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		</div>
	</div>
</div>
