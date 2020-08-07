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
 * Date: 2020-08
 * Revision: v1.4.7-aim
 *
 * Description: Code for maintenance
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
include "../lbs/openDBconn.php";

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

    .window-footer-maintenance
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container-maintenance
    {
        margin: 20px;
    }

    .container-status
    {
        display: block;
    }

    .container-status::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .status-align-left
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:left;
        margin: 10px 0px 0px 0px;
        font-family: Verdana, sans-serif; font-size: 11px;
    }

    .status-align-right
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:right;
        margin: 10px 0px 0px 0px;
        font-family: Verdana, sans-serif; font-size: 11px;
    }
    
    .select-option-styled
    {
        max-height: 30px !important;
        min-height: 30px !important;
        border: 1px solid #ccc !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        line-height: 11.6px !important;
        padding: 8px 0px 0px 10px !important;
        position: relative;
    }

    .select-option-styled .list
    {
        margin-left: 5px;
        overflow-y: scroll !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) !important;
        background: #f9f9f9 !important;
    }

    .master-container
    {
        width: 100%; 
        height: 70px;
    }
    
    .left-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .right-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Maintenance</h4>
</div>

<div class="div-container-maintenance">
    <form id="formPurge" name="formPurge" method="post" action="mods/maintenanceParameters">

        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Purge old endpoint phrases</p><br>
                <select class="select-option-styled wide" name="deletephrases" id="deletephrases">
                    <option value="1month">Preserve last month</option>
                    <option value="3month">Preserve last 3 months</option>
                    <option value="6month">Preserve last 6 months</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                
            </div>

            <div class="right-container">
                   
                <p class="title-config">Purge old endpoint events</p><br>
                <select class="select-option-styled wide" name="deletealerts" id="deletealerts">
                    <option value="1month">Preserve last month</option>
                    <option value="3month">Preserve last 3 months</option>
                    <option value="6month">Preserve last 6 months</option>
                    <option value="12month">Preserve last year</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php
                
                $urlSize="http://localhost:9200/logstash-thefraudexplorer-text-*/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);

                if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                {
                    $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024/1024;
                    $dataCount = $resultSize['_all']['total']['docs']['count'];

                    echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." GB";
                }
                else
                {
                    echo "You don't have any data yet";
                }

                ?>
                
            </div>

            <div class="status-align-right">
               
                <?php
                
                $urlSize="http://localhost:9200/logstash-alerter-*/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);

                if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                {
                    $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                    $dataCount = $resultSize['_all']['total']['docs']['count'];
                
                    echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                }
                else
                {
                    echo "You don't have any data yet";
                }
                
                ?>
                
            </div>
        </div>
        
        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Delete old endpoint sessions</p><br>
                <select class="select-option-styled wide" name="deadsessions" id="deadsessions">
                    <option value="1month">Purge dead sessions (30 days long)</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                
            </div>

            <div class="right-container">
                   
                <p class="title-config">Delete old events status records</p><br>
                <select class="select-option-styled wide" name="alertstatus" id="alertstatus">
                    <option value="1month">Preserve last month</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php
                
                $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY)";
                $countDead = mysqli_fetch_assoc(mysqli_query($connection, $queryDeadEndpoints));
                
                echo $countDead['total']." sessions in dead status (30 days)";
                
                ?>
                
            </div>

            <div class="status-align-right">
               
                <?php
                
                $urlSize="http://localhost:9200/tfe-alerter-status/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);

                if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                {
                    $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                    $dataCount = $resultSize['_all']['total']['docs']['count'];
                
                    echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                }
                else
                {
                    echo "You don't have any data yet";
                }
                
                ?>
                
            </div>
        </div>

        <div class="modal-footer window-footer-maintenance">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            
            <?php    
            
            if ($session->username != "admin") echo '<button type="submit" class="btn btn-danger setup disabled" value="Purge data now" style="outline: 0 !important;">';
            else echo '<button type="submit" id="btn-purge" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Purging, please wait" style="outline: 0 !important;">Purge data now</button>';

            ?>
        
        </div>
    </form>
</div>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Button Purge -->

<script>

var $btn;

$("#btn-purge").click(function() {
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
