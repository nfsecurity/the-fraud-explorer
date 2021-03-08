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
 * Description: Code for setting words age
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

sleep(2);

$currentWordsAge = $configFile['store_words_days'];
$newerWordsAge = filter($_POST['wordsage']);

$replaceParams = '/usr/bin/sudo /usr/bin/sed "s/store_words_days = \"'.$currentWordsAge.'\"/store_words_days = \"'.$newerWordsAge.'\"/g" --in-place '.$documentRoot.'config.ini';
$commandReplacements = shell_exec($replaceParams);

?>