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
 * Date: 2020-06
 * Revision: v1.4.5-aim
 *
 * Description: Code for software update
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

include "../lbs/globalVars.php";
include "../lbs/endpointMethods.php";

$configFile = parse_ini_file("../config.ini");
$mailAlert = $configFile['mail_address'];
$mailSmtp = $configFile['mail_smtp'];
$_SESSION['processingStatus'] = "notstarted";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .update-text
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .input-value-text
    {
        width: 100%; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-config-update
    {
        padding: 15px 0px 0px 0px;
    }
    
    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .master-container-update
    {
        width: 100%; 
    }

    .div-container-update
    {
        margin: 20px;
    }

    .container-status-update
    {
        width: 100%; 
        border-radius: 5px;
        background: #f2f2f2;
        margin: 15px 0px 15px 0px;
        padding: 0px 10px 15px 10px;
        height: 30px;
    }

    .warning
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        line-height: 30px;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Software update</h4>
</div>

<div class="div-container-update">

    <form id="formUpdate" name="formUpdate" method="post" action="mods/swUpdateRun">

        <div class="master-container-update">
            
            <p class="update-text">
            Please specify the software repository to load the lastest software version. Please note that is recommended doing a backup copy first of all data, in order to do that, you can use the Backup module. Once you have the latest backup, you can use the official URL repository at GITHUB below as a source for synchronization:<br><br>
            </p>

            <input type="text" name="urlrepo" id="urlrepo" autocomplete="off" placeholder="https://github.com/nfsecurity/the-fraud-explorer/archive/master.zip" value="https://github.com/nfsecurity/the-fraud-explorer/archive/master.zip" class="input-value-text" style="text-indent:5px;">

        </div>

        <div class="container-status-update">

                <?php

                    /* Online upgrade process */

                    $configFile = parse_ini_file("../config.ini");
                    $currentversion = $configFile['sw_version'];
                    $URLConfigFile = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/config.ini";
                    $repoConfigFile = file_get_contents($URLConfigFile);
                    preg_match('/sw_version = "(.*)"/', $repoConfigFile, $repoVersion);

                    echo '<p class="warning"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;&nbsp;';

                    if (isset($repoVersion))
                    {
                        if ($repoVersion[1] != $currentversion)
                        {
                            echo 'There is new version at the official repo (v'.$repoVersion[1].'). Your current version is (v'.$currentversion.') ';
                        }
                        else echo 'The Fraud Explorer is up to date, you don\'t need to update your software now, check later';
                    }
                    else echo 'At this time the server cannot check if is there a newer version, try again later';

                    echo '</p>';

                ?>

        </div>

        <div class="modal-footer window-footer-config-update">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>

            <?php

                echo '<button type="submit" id="btn-update" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Updating, please wait" style="outline: 0 !important;">';
                echo 'Update now';
                echo '</button>';

            ?>

        </div>

    </form>

</div>

<!-- Button updating -->

<script>

var $btn;

$("#btn-update").click(function() {
    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

function getstatus()
{
    $.ajax({
        url: "../helpers/processingStatus.php",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#statusmessage').html(data.message);
            if(data.status=="pending")
              setTimeout('getstatus()', 1000);
            else
                $btn.button('reset');
        }
    });
}

</script>
