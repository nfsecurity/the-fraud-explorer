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
 * Description: Code for dashboard
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="css/footer.css">
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

<style>
    .font-icon-color { color: #FFFFFF; }
</style>

<div id="footer">
    <p class="main-text">&nbsp;</p>
    <div class="logo-container">
        &nbsp;&nbsp;&nbsp;<span class="fa fa-cube fa-lg font-icon-color">&nbsp;&nbsp;</span>The Fraud Explorer</b> &reg; NF Cybersecurity & Antifraud Firm
    </div>
    <div class="helpers-container">
        <span class="fa fa-bug fa-lg font-icon-color">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank">Bug Report</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="fa fa-file-text fa-lg font-icon-color">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/wiki" target="_blank">Documentation</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="fa fa-globe fa-lg font-icon-color">&nbsp;&nbsp;</span>Language&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="fa fa-medkit fa-lg font-icon-color">&nbsp;&nbsp;</span><a style="color: white;" href="https://www.thefraudexplorer.com/#contact" target="_blank">Support</a>&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="fa fa-building-o fa-lg font-icon-color">&nbsp;&nbsp;</span>Application context [<?php echo $session->username ." - ".$session->domain; ?>]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </div>
</div>