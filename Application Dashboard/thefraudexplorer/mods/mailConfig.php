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
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for mail config
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

    .window-footer-config-mail
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-mail
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

    .master-container-mail
    {
        width: 100%; 
    }
    
    .left-container-mail
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: left;
    }
    
    .right-container-mail
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: right;
    }

    .status-align-left-mail
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

    .status-align-right-mail
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

    .container-status-mail
    {
        display: block;
    }

    .container-status-mail::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    @keyframes blink 
    { 
        50% 
        { 
            border: 1px solid white;
        } 
    }

    .blink-check
    {
        -webkit-animation: blink .1s step-end 6 alternate;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Mail configuration</h4>
</div>

<div class="div-container-mail">

    <form id="formMail" name="formMail" method="post" action="mods/buildMailAlerts">

    <div class="master-container-mail">
            <div class="left-container-mail">              
                
                <p class="title-config">SMTP server address</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="smtpserver" id="smtpserver" autocomplete="off" placeholder="<?php echo $mailSmtp; ?>" class="input-value-text" style="text-indent:5px;">
            
            </div>
            <div class="right-container-mail">
                   
                <p class="title-config">SMTP TLS port</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="port" id="port" autocomplete="off" placeholder="587" class="input-value-text" style="text-indent:5px;">
         
            </div>
    </div>

    <div class="container-status-mail">
            
            <div class="status-align-left-mail">      
                <p>Please write the server address</p>      
            </div>

            <div class="status-align-right-mail">
               <p>Known ports are 587, 25 and 465</p>
            </div>
    </div>

    <div class="master-container-mail">
            <div class="left-container-mail">              
                
                <p class="title-config">SMTP user and password</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="smtpuserpass" id="smtpuserpass" autocomplete="off" placeholder="mail@mydomain.com:password" class="input-value-text" style="text-indent:5px;">
            
            </div>
            <div class="right-container-mail">
                   
                <p class="title-config">Email address</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="mailaddress" id="mailaddress" autocomplete="off" placeholder="<?php echo $mailAlert; ?>" class="input-value-text" style="text-indent:5px;">
         
            </div>
    </div>

    <div class="container-status-mail">
            
            <div class="status-align-left-mail">
                <p>Please write user@mydomain.com:password</p>           
            </div>

            <div class="status-align-right-mail">
               <p>Mail used to send alert messages</p>
            </div>
    </div>

    <br>
    <div class="modal-footer window-footer-config-mail">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
        
        <?php    
            
            if ($session->username != "admin") echo '<input type="button" id="button-set-preferences" class="btn btn-success setup disabled" value="Set mail preferences" style="outline: 0 !important;">';
            else echo '<input type="button" id="button-set-preferences" class="btn btn-success setup" value="Set mail preferences" style="outline: 0 !important;">';

        ?>

    </div>

    </form>
</div>

<!-- Button set preferences -->

<script>

var $btn;

$("#button-set-preferences").click(function(e) {

    var smtpserver = $('#smtpserver').val();
    var port = $('#port').val();
    var smtpuserpass = $('#smtpuserpass').val();
    var mailaddress = $('#mailaddress').val();
    var allvalues = new Array(smtpserver, port, smtpuserpass, mailaddress);

    if (!smtpserver || !port || !smtpuserpass || !mailaddress)
    {
        var smtpserverfield = "#smtpserver,";
        var portfield = "#port,";
        var smtpuserpassfield = "#smtpuserpass,";
        var mailaddressfield = "#mailaddress,";
        var finalfield = "";

        if (allvalues[0] == "") finalfield = smtpserverfield;
        if (allvalues[1] == "") finalfield = finalfield + portfield;
        if (allvalues[2] == "") finalfield = finalfield + smtpuserpassfield;
        if (allvalues[3] == "") finalfield = finalfield + mailaddressfield;
 
        finalfield = finalfield.replace(/(,$)/g, "");

        setTimeout("$('"+finalfield+"').addClass('blink-check');", 100);
        setTimeout("$('"+finalfield+"').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        $('#formMail').submit();
    }

});

</script>
