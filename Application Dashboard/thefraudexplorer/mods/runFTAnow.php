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
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: Code for run FTA
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

$process = shell_exec('/usr/bin/sudo /usr/bin/ps auxf | grep \"AIFraudTriangleProcessor\" | grep \"php\"');
$process = trim(preg_replace('/\s+/', '', $process));

if ($process == null || $process == "" || $process == " ")
{
    $output = shell_exec('/bin/php /var/www/html/thefraudexplorer/core/AIFraudTriangleProcessor.php > /dev/null 2>/dev/null &');
}

sleep(5);

?>