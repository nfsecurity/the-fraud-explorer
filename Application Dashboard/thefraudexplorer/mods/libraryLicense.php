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
 * Description: Code for library license
 */

include "../lbs/login/session.php";
include "../lbs/security.php";
include "../lbs/cronManager.php";

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
include "../lbs/cryptography.php";

/* Procedure to check internet connection to license server */

function is_internet()
{
    $connected = @fsockopen("licensing.thefraudexplorer.com", 443); 

    if ($connected)
    {
        $is_conn = true;
        fclose($connected);
    }
    else $is_conn = false;

    return $is_conn;
}

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

    .input-value-text-capabilities
    {
        width: 51px; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-config-license
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-license
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

    .master-container-license
    {
        width: 100%; 
    }
    
    .left-container-license
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: left;
    }
    
    .right-container-license
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: right;
    }

    .status-align-left-license
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

    .status-align-right-license
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

    .container-status-license
    {
        display: block;
    }

    .container-status-license::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .latest-license
    {
        font-family: 'FFont', sans-serif; font-size: 10px;
    }

    .downloadfile
    {
        outline: 0 !important;
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

    .input-value-text-capabilities::placeholder 
    {  
        text-align: center; 
    } 

</style>

<?php

    $configFile = parse_ini_file("../config.ini");
    $serialNumber = $configFile['pl_serial'];

    if (is_internet() == true)
    {
        /* Query license capabilities */

        $serverAddress = "https://licensing.thefraudexplorer.com/validateSerial.php";

        $postRequest = array(
            'serial' => $serialNumber,
            'capabilities' => "true",
            'retrieve' => "false"
        );

        $payload = json_encode($postRequest);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $serverAddress);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $headers = [
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $replyJSON = json_decode($server_output, true);
        $capabilitiesContent = explode(",", $replyJSON['Capabilities']);
        $pressurePhrases = $capabilitiesContent[0];
        $opportunityPhrases = $capabilitiesContent[1];
        $rationalizationPhrases = $capabilitiesContent[2];
        $departments = $capabilitiesContent[3];
        $flags = $capabilitiesContent[4];
        $validUntil = strtotime($replyJSON['Until']);
        $licenseClass = $replyJSON['Type'];
    }
    else
    {
        $pressurePhrases = "N/A";
        $opportunityPhrases = "N/A";
        $rationalizationPhrases = "N/A";
        $departments = "N/A";
        $flags = "N/A";
        $validUntil = strtotime("1990-12-31 12:59:29");   
    }

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Phrase library license</h4>
</div>

<div class="div-container-license">

    <form id="formLicense" name="formLicense" method="post" action="mods/activateLicense">

    <div class="master-container-license">

            <div class="left-container-license">
                   
                <p class="title-config">Phrase library serial number</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="serial" id="serial" autocomplete="off" placeholder="<?php echo $serialNumber; ?>" class="input-value-text" style="text-indent:5px;">
            
            </div>

            <div class="right-container-license">              
                
                <p class="title-config">License capabilities: CLASS-<?php echo $licenseClass; ?></p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" disabled="disabled" name="pressure" id="pressure" autocomplete="off" placeholder="<?php echo $pressurePhrases; ?>" class="input-value-text-capabilities">
                <input type="text" disabled="disabled" name="opportunity" id="opportunity" autocomplete="off" placeholder="<?php echo $opportunityPhrases; ?>" class="input-value-text-capabilities">
                <input type="text" disabled="disabled" name="rationalization" id="rationalization" autocomplete="off" placeholder="<?php echo $rationalizationPhrases; ?>" class="input-value-text-capabilities">
                <input type="text" disabled="disabled" name="departments" id="departments" autocomplete="off" placeholder="<?php echo $departments; ?>" class="input-value-text-capabilities">
                <input type="text" disabled="disabled" name="flags" id="flags" autocomplete="off" placeholder="<?php echo $flags; ?>" class="input-value-text-capabilities">          
           
            </div>
            
    </div>

    <div class="container-status-license">
            
            <div class="status-align-left-license">      
                <p>Please specify your purchased serial number</p>      
            </div>

            <div class="status-align-right-license">
               <p>Pressure, Opportunity, Rational, Units, Flags</p>
            </div>
            
    </div>

    <br>
    <a class="downloadfile" href="mods/downloadLicense?le=<?php if ($noBackup == true) echo "nobackupfile"; else echo encRijndael($latestBackup[3]); ?>">
    <button type="button" id="button-download-license" class="btn btn-default" style="width: 100%; outline: 0 !important;">
        Download current phrase library to my computer<br>
        <p class="latest-license">

            <?php 

                echo "This license is valid until " . date('F d, Y', $validUntil) . " at 12:59 pm";
            
            ?>

        </p>
    </button>
    </a>
    <br>

    <br>
    <div class="modal-footer window-footer-config-license">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
        
        <?php    
            
            if ($session->username != "admin") echo '<input type="button" id="button-activate-license" class="btn btn-success setup disabled" value="Activate new license" style="outline: 0 !important;">';
            else echo '<input type="button" id="button-activate-license" class="btn btn-success setup" value="Activate new license" style="outline: 0 !important;">';

        ?>

    </div>

    </form>
</div>

<!-- Download multiple rule files -->

<script>
    $(document).ready(function(){
    $('#button-download-license').click(function(e){

    e.preventDefault();    

    $.ajax({
        url: '../lbs/downloadRules.php',
        type: 'post',
        success: function(response){
            window.location = response;
        }
    });
    });
    });
</script>

<!-- Button activate license -->

<script>

var $btn;

$("#button-activate-license").click(function(e) {

    var serial = $('#serial').val();

    if (!serial)
    {
        setTimeout("$('#serial').addClass('blink-check');", 100);
        setTimeout("$('#serial').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        $('#formLicense').submit();
    }

});

</script>