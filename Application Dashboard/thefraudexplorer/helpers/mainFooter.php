<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for Footer
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

$configFile = parse_ini_file("../config.ini");
$currentversion = $configFile['sw_version'];

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="../css/footer.css?<?php echo filemtime('../css/footer.css') ?>">
<link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css" />

<style>

    .font-icon-color-footer 
    { 
        color: #FFFFFF; 
    }

    .software-version
    {
        display: inline-block;
        color: white;
    }

    .software-version a, software-version a:link, software-version a:hover, software-version a:visited
    {
        color: white;
    }

</style>

<div id="footer">
    <div class="footer-components">
        <p class="main-text">&nbsp;</p>
        <div class="logo-container">
            &nbsp;&nbsp;&nbsp;<span class="fa fa-cube fa-lg font-icon-color-footer">&nbsp;&nbsp;</span>The Fraud Explorer</b> &reg; Opensource Fraud Triangle Analytics
        </div>
        <div class="helpers-container">
            <span class="fa fa-bug fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/issues" target="_blank" rel="noopener noreferrer">Bug Report</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-file-text fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><a style="color: white;" href="https://github.com/nfsecurity/the-fraud-explorer/wiki" target="_blank" rel="noopener noreferrer">Documentation</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-globe fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><a href="#" onclick="startTour()" style="color: white;">Take tour</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-medkit fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><a style="color: white;" href="https://www.thefraudexplorer.com/#contact" target="_blank" rel="noopener noreferrer">Support</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-bank fa-lg font-icon-color-footer">&nbsp;&nbsp;</span>Business [<?php echo $session->username ." - ".$session->domain; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-codepen fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><div class="software-version"><a href="../mods/swUpdate" data-toggle="modal" class="software-update-button" data-target="#software-update" href="#" id="elm-software-update"><?php echo "Version v".$currentversion; ?></a></div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
    </div>  
</div>

<!-- Modal for Software Update-->

<div class="modal" id="software-update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!-- Script for Software Update -->

<script>
    $('#software-update').on('show.bs.modal', function(e){
        $(this).find('.software-update-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>