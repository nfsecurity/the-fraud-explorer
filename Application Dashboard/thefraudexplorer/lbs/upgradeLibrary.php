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
 * Description: Upgrade phrase library
 */

include "globalVars.php";

$rulesetLanguage = $configFile['fta_lang_selection'];
$localLibraryPath = $configFile[$rulesetLanguage];
$phraseName = explode("/", $configFile[$rulesetLanguage]);
$phraseNameSelection = $phraseName[7];
$remotePhraseLibraryURL = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/core/rules/".$phraseNameSelection;

$onlineLibrary = file_get_contents($remotePhraseLibraryURL);
file_put_contents($localLibraryPath, $onlineLibrary);