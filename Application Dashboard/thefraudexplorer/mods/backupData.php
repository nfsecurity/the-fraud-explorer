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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for data backup
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

    .input-value-text-cron
    {
        width: 51px; 
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
    }

    .div-container-backup
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

    .master-container-backup
    {
        width: 100%; 
    }
    
    .left-container-backup
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: left;
    }
    
    .right-container-backup
    {
        width: calc(50% - 5px); 
        display: inline; 
        float: right;
    }

    .status-align-left-backup
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

    .status-align-right-backup
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

    .container-status-backup
    {
        display: block;
    }

    .container-status-backup::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .latest-backup
    {
        font-family: 'FFont', sans-serif; font-size: 10px;
    }

    .downloadfile
    {
        outline: 0 !important;
    }

</style>

<?php

$cron_manager = new CronManager();
$latestBackup = shell_exec("sudo find /backup/*.zip -printf '%T++%s+%p\n' | sort -r | head -n 1");
$latestBackup = explode('+', trim($latestBackup));

$datetime = DateTime::createFromFormat('Y-m-d', $latestBackup[0]);
$noBackup = false;

$backupHour = explode(':', trim($latestBackup[1]));
$size = $latestBackup[2]/1024/1024;

if (!isset($latestBackup[1])) $noBackup = true;

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Backup & Restore</h4>
</div>

<div class="div-container-backup">

    <form id="formBackup" name="formBackup" method="post" action="mods/setBackup">

    <div class="master-container-backup">
            <div class="left-container-backup">              
                
                <p class="title-config">Backup schedule</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="min" id="min" autocomplete="off" placeholder="<?php echo $cron_manager->cron_get_portion("fta-backup", "minutes"); ?>" class="input-value-text-cron">
                <input type="text" name="hours" id="hours" autocomplete="off" placeholder="<?php echo $cron_manager->cron_get_portion("fta-backup", "hours"); ?>" class="input-value-text-cron">
                <input type="text" name="day" id="day" autocomplete="off" placeholder="<?php echo $cron_manager->cron_get_portion("fta-backup", "day"); ?>" class="input-value-text-cron">
                <input type="text" name="month" id="month" autocomplete="off" placeholder="<?php echo $cron_manager->cron_get_portion("fta-backup", "month"); ?>" class="input-value-text-cron">
                <input type="text" name="weekday" id="weekday" autocomplete="off" placeholder="<?php echo $cron_manager->cron_get_portion("fta-backup", "weekday"); ?>" class="input-value-text-cron">          
            </div>
            <div class="right-container-backup">
                   
                <p class="title-config">Password protection</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="password" id="password" autocomplete="off" placeholder="mypassword" class="input-value-text" style="text-indent:5px;">
         
            </div>
    </div>

    <div class="container-status-backup">
            
            <div class="status-align-left-backup">      
                <p>Minute, Hour, Day, Month, Week day</p>      
            </div>

            <div class="status-align-right-backup">
               <p>Please set a backup password</p>
            </div>
            
    </div>

    <br>
    <a class="downloadfile" href="mods/downloadBackup?le=<?php if ($noBackup == true) echo "nobackupfile"; else echo encRijndael($latestBackup[3]); ?>">
    <button type="button" class="btn btn-default" style="width: 100%; outline: 0 !important;">
        Download latest backup to my computer<br>
        <p class="latest-backup">

            <?php 

                if ($noBackup == true) echo "No backup files found at the moment, please try again later";
                else echo $datetime->format('F d, Y') . ", at " . $backupHour[0] . ":" . $backupHour[1] . " with " . number_format(round($size)) . " Mb of size"; 
            ?>

        </p>
    </button>
    </a>
    <br>

    <br>
    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
        
        <?php    
            
            if ($session->username != "admin") echo '<input type="submit" class="btn btn-success setup disabled" value="Set schedule" style="outline: 0 !important;">';
            else echo '<input type="submit" class="btn btn-success setup" value="Set schedule" style="outline: 0 !important;">';

        ?>

    </div>

    </form>
</div>