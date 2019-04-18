
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
 * Description: Code for setup endpoint
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
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

</style>

<?php

$regid=filter($_GET['regid']);
$endpoint=filter($_GET['endpoint']);
$index=filter($_GET['index']);
$type=filter($_GET['type']);
$urlrefer=filter($_GET['urlrefer']);

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Event marking</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action=<?php echo '"mods/toggleEventMark?regid='.$regid.'&endpoint='.$endpoint.'&index='.$index.'&type='.$type.'&urlrefer='.$urlrefer.'"'; ?>>
        
        <div class="modal-body window-body" style="margin: 0px 10px 15px 10px;">
            <p style="text-align:justify; font-size: 12px;">You are about to mark this fraud triangle event as inactive, active, false positive or viceversa, this procedure disable or enable this event in the overall fraud triangle calculation process for this endpoint only. You can revert this decision at any time later. Do you want to proceed ?</p>
            <p class="debug-url window-debug"></p>
        </div>

        <br>
        <div class="modal-footer window-footer">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <input type="submit" name="delete-event" class="btn btn-danger setup" value="Delete event instead" style="outline: 0 !important;">
            <input type="submit" name="toggle-event" class="btn btn-success setup" value="Toggle mark" style="outline: 0 !important;">
        </div>
    </form>
</div>
