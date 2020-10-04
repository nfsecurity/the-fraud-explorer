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
 * Description: Upgrade phrase library
 */

include "globalVars.php";
include $documentRoot."lbs/login/session.php";
include $documentRoot."lbs/security.php";

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

include $documentRoot."lbs/cryptography.php";

$phraseNameSpanish = explode("/", $configFile['fta_text_rule_spanish']);
$phraseNameSelectionSpanish = $phraseNameSpanish[7];
$phraseNameEnglish = explode("/", $configFile['fta_text_rule_english']);
$phraseNameSelectionEnglish = $phraseNameEnglish[7];

$localLibraryPathSpanish = $configFile['fta_text_rule_spanish'];
$localLibraryPathEnglish = $configFile['fta_text_rule_english'];

$remotePhraseLibraryURLSpanish = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/core/rules/".$phraseNameSelectionSpanish;
$remotePhraseLibraryURLEnglish = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/core/rules/".$phraseNameSelectionEnglish;

$onlineLibrarySpanish = file_get_contents($remotePhraseLibraryURLSpanish);
file_put_contents($localLibraryPathSpanish, $onlineLibrarySpanish);

$onlineLibraryEnglish = file_get_contents($remotePhraseLibraryURLEnglish);
file_put_contents($localLibraryPathEnglish, $onlineLibraryEnglish);

$_SESSION['wm'] = encRijndael("Successfully updated phrase libraries");

?>