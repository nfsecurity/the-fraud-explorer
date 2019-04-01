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
 * Date: 2019-03
 * Revision: v1.3.2-ai
 *
 * Description: Code for build endpoint
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

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
        width:100%; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
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
    <h4 class="modal-title window-title" id="myModalLabel">Build endpoint</h4>
</div>

<div class="div-container">
    <form id="formBuild" name="formBuild" method="post" action="mods/buildEndpointParameters">

        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Select platform</p><br>
                <select class="select-option-styled" name="platform" id="platform">
                    <option value="windows" selected="selected">Windows 32 & 64 Bits</option>
                    <option value="linux" disabled>RedHat Linux based distributions</option>
                    <option value="macosx" disabled>MacOS X 64 Bits Intel</option>
                </select>            
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">Server HTTPS Address</p><br>
                <input type="text" name="address" id="address" autocomplete="off" placeholder="https://tfe.mycompany.com/update.xml" class="input-value-text">   
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php

                echo "Select your employees operating system";

                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                        
                echo "Please note the \"update.xml\" at the end";
                
                ?>
                
            </div>
        </div>
        
        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Enable or disable phrase collection</p><br>
                <select class="select-option-styled" name="pcenabled" id="pcenabled">
                    <option value="enable" selected="selected">Enable collection inside endpoint</option>
                    <option value="disable">I'd like to enable it another day</option>
                </select>            
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">IP Address</p><br>
                <input type="text" name="ip" id="ip" autocomplete="off" placeholder="10.1.1.253" class="input-value-text">             
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php
                
                echo "Enable or disable phrase collection";
                
                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                             
                echo "Enter the server IP Address";

                ?>
                
            </div>
        </div>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to endpoints</button>
            
            <?php    
            
            if ($session->username != "admin") echo '<input type="submit" class="btn btn-success setup disabled" value="Build & Download" style="outline: 0 !important;">';
            else echo '<input type="submit" class="btn btn-success setup" value="Build & Download" style="outline: 0 !important;">';

            ?>
        
        </div>
    </form>
</div> 