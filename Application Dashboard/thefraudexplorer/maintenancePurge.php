<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.0
 *
 * Description: Code for maintenance
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .score-text
    {
        font-family: 'FFont', sans-serif; font-size:11.5px;
    }

    .title-score
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 0px;
        padding-top: 10px;
        display: block;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
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
    }
    
    .select-option-styled
    {
        position: relative;
        border: 1px solid #ccc;
        width: 100%;
        font-family: 'FFont', sans-serif; font-size: 12px;
        color: #757575;
        height: 30px;
        overflow: scroll;
        background-color: #fff;
        outline: 0 !important;
    }

    .select-option-styled:before
    {
        content: '';
        position: absolute;
        right: 5px;
        top: 7px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 7px 5px 0 5px;
        border-color: #000000 transparent transparent transparent;
        z-index: 5;
        pointer-events: none;
    }

    .select-option-styled select
    {
        padding: 5px 8px;
        width: 130%;
        border: none;
        box-shadow: none;
        background-color: transparent;
        background-image: none;
        appearance: none;
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

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Maintenance</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action="maintenanceParameters">

        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Delete endpoint phrases (purge old records)</p><br>
                <select class="select-option-styled" name="deletephrases" id="deletephrases">
                    <option value="1month">Preserve last month</option>
                    <option value="2month">Preserve last 2 months</option>
                    <option value="3month">Preserve last 3 months</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">Delete endpoint alerts (purge old records)</p><br>
                <select class="select-option-styled" name="deletealerts" id="deletealerts">
                    <option value="1month">Preserve last month</option>
                    <option value="2month">Preserve last 2 months</option>
                    <option value="3month">Preserve last 3 months</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php
                
                $urlSize="http://localhost:9200/logstash-thefraudexplorer-text-*/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);
                $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024/1024;
                $dataCount = $resultSize['_all']['total']['docs']['count'];
                
                echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." GB";
                
                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                
                $urlSize="http://localhost:9200/logstash-alerter-*/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);
                $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                $dataCount = $resultSize['_all']['total']['docs']['count'];
                
                echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                
                ?>
                
            </div>
        </div>
        
        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Delete old endpoint sessions</p><br>
                <select class="select-option-styled" name="deadsessions" id="deadsessions">
                    <option value="1month">Purge dead sessions (30 days long)</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">Delete old alerts status records</p><br>
                <select class="select-option-styled" name="alertstatus" id="alertstatus">
                    <option value="1month">Preserve last month</option>
                    <option value="preserveall" selected="selected">Preserve all</option>
                </select>            
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php
                
                $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY)";
                $countDead = mysql_fetch_assoc(mysql_query($queryDeadEndpoints));
                
                echo $countDead['total']." sessions in dead status (30 days)";
                
                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                
                $urlSize="http://localhost:9200/tfe-alerter-status/_stats";
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_URL, $urlSize);
                $resultSize=curl_exec($ch);
                curl_close($ch);

                $resultSize = json_decode($resultSize, true);
                $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                $dataCount = $resultSize['_all']['total']['docs']['count'];
                
                echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                
                ?>
                
            </div>
        </div>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            
            <?php    
            
            if ($session->username != "admin") echo '<input type="submit" class="btn btn-danger setup disabled" value="Purge now" style="outline: 0 !important;">';
            else echo '<input type="submit" class="btn btn-danger setup" value="Purge now" style="outline: 0 !important;">';

            ?>
        
        </div>
    </form>
</div> 