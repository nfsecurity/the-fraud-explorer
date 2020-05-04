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
 * Description: Upgrade phrase library
 */

include "globalVars.php";

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