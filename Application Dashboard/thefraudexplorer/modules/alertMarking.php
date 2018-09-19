
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
 * Revision: v1.2.1
 *
 * Description: Code for setup agent
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
$agent=filter($_GET['agent']);
$index=filter($_GET['index']);
$type=filter($_GET['type']);
$urlrefer=filter($_GET['urlrefer']);

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Alert marking</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action=<?php echo '"modules/toggleAlertMark?regid='.$regid.'&agent='.$agent.'&index='.$index.'&type='.$type.'&urlrefer='.$urlrefer.'"'; ?>>
        
        <div class="modal-body window-body">
            <p style="text-align:justify; font-size: 12px;"><br>You are about to mark this fraud triangle alert as inactive, active, false positive or viceversa, this procedure disable or enable this alert in the overall fraud triangle calculation process for this endpoint only. You can revert this decision at any time later. Do you want to proceed ?</p>
            <p class="debug-url window-debug"></p>
        </div>

        <br>
        <div class="modal-footer window-footer">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <input type="submit" name="delete-alert" class="btn btn-danger setup" value="Delete alert instead" style="outline: 0 !important;">
            <input type="submit" name="toggle-alert" class="btn btn-success setup" value="Toggle mark" style="outline: 0 !important;">
        </div>
    </form>
</div>