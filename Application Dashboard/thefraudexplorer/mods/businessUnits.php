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
 * Description: Code for business units
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
include "../lbs/endpointMethods.php";

$configFile = parse_ini_file("../config.ini");
$mailAlert = $configFile['mail_address'];
$mailSmtp = $configFile['mail_smtp'];

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
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

    .window-footer-config-business
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-business
    {
        margin: 20px;
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

    .master-container-business
    {
        width: 100%; 
    }

    .csv-container
    {
        height: 130px;
        width: 100%;
        border: 0px solid gray;
        border-radius: 3px;
        background: #F7F7F7;
        font-family: Courier; font-size: 12px;
        display: inline-table;
        margin-bottom: 13px;
    }

    .csv-button-upload
    {
        width: 100%;
        outline: 0 !important;
    }

    .csv-text
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .business-header
    {
        width: calc(100%/5);
        min-width: calc(100%/5);
        height: 25px;
        line-height: 25px;
        display: inline-block;
        background-color: #F0F0F0;
        font-weight: bold;
    }

    .business-content
    {
        width: calc(100%/5);
        min-width: calc(100%/5);
        height: 25px;
        line-height: 25px;
        display: inline-block;
        text-align: left;
        
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Business units segmentation</h4>
</div>

<div class="div-container-business">

    <div class="master-container-business">
          
        <p class="csv-text">
        Please format your CSV file prior the upload using this tool. The file needs to be formatted with the following structure to meet the requirements for business unit segmentation:<br><br>
        </p>

        <div class="csv-container">

            <div class="business-header">User login</div> 
            <div class="business-header">Domain</div>
            <div class="business-header">Full name</div>
            <div class="business-header">Unit</div>
            <div class="business-header">Gender</div>
            <br><br>

            <div class="business-content" style="padding-left: 10px">eleanormaggy,</div> 
            <div class="business-content">mydomain.local,</div>
            <div class="business-content">Eleanora Maggy,</div>
            <div class="business-content">HumanResources,</div>
            <div class="business-content">Female or Male</div>

            <div class="business-content" style="padding-left: 10px">robertdeniro,</div> 
            <div class="business-content">mydomain.local,</div>
            <div class="business-content">Robert De Niro,</div>
            <div class="business-content">FinancialUnits,</div>
            <div class="business-content">Female or Male</div>

            <div class="business-content" style="padding-left: 10px">gwensteffany,</div> 
            <div class="business-content">mydomain.local,</div>
            <div class="business-content">Gwens Steffany,</div>
            <div class="business-content">SecurityandTIC,</div>
            <div class="business-content">Female or Male</div>

        </div>

        <form action="mods/departmentsUpload" id="departmentsUpload" method="post" enctype="multipart/form-data" accept-charset="utf-8">  
            <div class="departmentsUploadStyle" style="outline: 0 !important;">  
                <button type="button" class="btn btn-default csv-button-upload" id="departmentsToUpload-button" onclick="document.getElementById('departmentsToUpload').click();">Upload and process CSV file from my computer</button>
                <input type="file" name="departmentsToUpload" id="departmentsToUpload" class="upload" />
            </div>
        </form>

    </div>

    <br>

    <div class="modal-footer window-footer-config-business">
        <br>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
    </div>

</div>

<!-- Upload file script -->

<script>
    document.getElementById("departmentsToUpload").onchange = function() 
    {
        document.getElementById("departmentsUpload").submit();
    }
</script>
