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
 * Date: 2019-05
 * Revision: v1.3.3-ai
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
    
    .select-option-build-styled
    {
        margin-right: 0px;
        min-height: 30px !important;
        max-height: 30px !important;
        padding: 8px 0px 8px 10px;
        line-height: 11.6px;
        border: 1px solid #ccc;
        color: #757575;
    }

    .select-option-build-styled .list
    {
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        max-height: 200px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
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
                <select class="select-option-build-styled wide" name="platform" id="platform" onChange="changeBehavior();">
                    <option value="windows" selected="selected">Microsoft Windows</option>
                    <option value="android">Android Devices</option>
                    <option value="macosx" disabled>Apple macOSX</option>
                </select>            
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">Server HTTPS Address</p><br>
                <input type="text" name="address" id="address" autocomplete="off" placeholder="https://tfe.mycompany.com" class="input-value-text">   
                    
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
                        
                echo "Please note the \"https\" at the address";
                
                ?>
                
            </div>
        </div>
        
        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Enable or disable phrase collection</p><br>
                <select class="select-option-build-styled wide" name="pcenabled" id="pcenabled">
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

        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Encryption key</p><br>
                <input type="text" name="encryption" id="encryption" autocomplete="off" disabled placeholder="<?php $cryptKeyQuery = mysqli_query($connection, sprintf("SELECT iv FROM t_crypt")); if ($row = mysqli_fetch_array($cryptKeyQuery)) echo $row[0]; ?>" class="input-value-text">         
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">Do not capture phrases on</p><br>
                <input type="text" name="excluded" id="excluded" autocomplete="off" placeholder="Whatsapp, Skype, Gmail" class="input-value-text">   
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php

                echo "Rijndael-128 Key & IV ciphering";

                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                        
                echo "Enter app-names by comma separated";
                
                ?>
                
            </div>
        </div>

        <!-- Android endpoint -->

        <div class="master-container">
            <div class="left-container">              
                
                <p class="title-config">Company domain name</p><br>
                <input type="text" name="companydomain" id="companydomain" disabled autocomplete="off" placeholder="domain.local" class="input-value-text">      
                
            </div>
            <div class="right-container">
                   
                <p class="title-config">REST API Credentials</p><br>
                <input type="text" name="restcredentials" id="restcredentials" disabled autocomplete="off" placeholder="restusername:restpassword" class="input-value-text">             
                    
            </div>
        </div>

        <div class="container-status">
            <div class="status-align-left">
                
                <?php

                echo "Only needed for mobile devices";

                ?>
                
            </div>
            <div class="status-align-right">
               
                <?php
                        
                echo "Mobile devices require this parameter";
                
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

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Disable or enable items -->

<script type="text/javascript">
    function changeBehavior()
    {
        if (document.getElementById("platform").value == "windows") 
        {
            document.getElementById("companydomain").disabled=true;
            document.getElementById("restcredentials").disabled=true;
            document.getElementById("pcenabled").disabled=false;
            document.getElementById("excluded").disabled=false;
            document.getElementById("ip").disabled=false;
        } 
        else if (document.getElementById("platform").value == "android") 
        {
            document.getElementById("companydomain").disabled=false;
            document.getElementById("restcredentials").disabled=false;
            document.getElementById("pcenabled").disabled=false;
            document.getElementById("excluded").disabled=true;
            document.getElementById("ip").disabled=true;
        }
    }
</script> 