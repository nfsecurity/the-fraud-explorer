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
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for setup endpoint
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

?>

<style>

    .window-footer
    {
        padding: 0px 0px 0px 0px;
    }
    
    .window-body
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
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

<?php

$regid = filter($_GET['id']);
$endpoint = filter($_GET['nt']);
$index = filter($_GET['ex']);
$type = filter($_GET['pe']);
$urlrefer = filter($_GET['er']);

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Event marking</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action=<?php echo '"mods/toggleEventMark?id='.rawurlencode($regid).'&nt='.rawurlencode($endpoint).'&ex='.rawurlencode($index).'&pe='.rawurlencode($type).'&er='.$urlrefer.'"'; ?>>
        
        <div class="modal-body window-body" style="margin: 0px 10px 15px 10px;">
            <p style="text-align:justify; font-size: 12px;">You are about to mark this fraud triangle event as inactive, active, false positive or viceversa, this procedure disable or enable this event in the overall fraud triangle calculation process for this endpoint only. You can revert this decision at any time later. Do you want to proceed ?</p>
            <p class="debug-url window-debug"></p>
        </div>

        <br>
        <div class="modal-footer window-footer">
            <br><input type="submit" name="delete-event" class="btn btn-danger setup" value="Delete event instead" style="outline: 0 !important;">
            <input type="submit" name="toggle-event" class="btn btn-success setup" value="Toggle mark" style="outline: 0 !important;">
        </div>
    </form>
</div>
