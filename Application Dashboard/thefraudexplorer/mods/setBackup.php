<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for set backup schedule
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
include "../lbs/cryptography.php";
include "../lbs/openDBconn.php";

if (isset($_POST['min'])) $min = filter($_POST['min']);
if (isset($_POST['hours'])) $hours = filter($_POST['hours']);
if (isset($_POST['day'])) $day = filter($_POST['day']);
if (isset($_POST['month'])) $month = filter($_POST['month']);
if (isset($_POST['weekday'])) $weekday = filter($_POST['weekday']);
if (isset($_POST['password'])) $pwd = filter($_POST['password']);

if ($min != "" && $hours != "" && $day != "" && $month != "" && $weekday != "" && $pwd != "")
{
    $cronJob = new CronManager();
    $remove_cron_result = $cronJob->remove_cronjob('fta-backup');
    
    sleep(1);

    $addCron = trim($min) . ' ' . trim($hours) . ' ' . trim($day) . ' ' . trim($month) . ' ' . trim($weekday) . ' /usr/bin/sh /backup/bin/backup.sh';
    $cron_add_result = $cronJob->add_cronjob($addCron, 'fta-backup');

    $configFile = parse_ini_file("../config.ini");
    $backupPassword = $configFile['backup_password'];

    $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$backupPassword.'/'.$pwd.'/g" --in-place '.$documentRoot.'config.ini /backup/bin/backup.sh';
    $commandReplacements = shell_exec($replaceParams);

    auditTrail("backup", "successfully scheduled the data backup procedure");

    $_SESSION['wm'] = encRijndael("Successfully scheduled backup procedure");
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>