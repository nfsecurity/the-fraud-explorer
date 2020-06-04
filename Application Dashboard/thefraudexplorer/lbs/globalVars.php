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
 * Date: 2020-06
 * Revision: v1.4.5-aim
 *
 * Description: Code for global vars
 */

$globalINI = "/var/www/html/thefraudexplorer/config.ini";
$configFile = parse_ini_file($globalINI);
$serverURL = $configFile['php_server_url'];
$documentRoot = $configFile['php_document_root'];

/* Unwanted words */

$notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot", "cv");

/* Set TimeZone */

date_default_timezone_set('America/Bogota');

/* String sanitization */

function phraseSanitization($sanitizedPhrases, $notwantedWords)
{
    foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);

    $sanitizedPhrases = strtolower($sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/\.+/', '.', $sanitizedPhrases);
    $sanitizedPhrases = str_replace('.', '. ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' .', '.', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' ,', ',', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(',', ', ', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/\s+/', ' ', $sanitizedPhrases);
    $sanitizedPhrases = ucfirst($sanitizedPhrases);

    preg_match_all("/\.\s*\w/", $sanitizedPhrases, $matches);

    foreach($matches[0] as $match) $sanitizedPhrases = str_replace($match, strtoupper($match), $sanitizedPhrases);

    return $sanitizedPhrases;
}

?>
