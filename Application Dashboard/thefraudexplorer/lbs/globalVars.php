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
 * Description: Code for global vars
 */

$globalINI = "/var/www/html/thefraudexplorer/config.ini";
$configFile = parse_ini_file($globalINI);
$serverURL = $configFile['php_server_url'];
$documentRoot = $configFile['php_document_root'];

/* Unwanted words */

$notwantedWords = array("rwin", "lwin", "decimal", "snapshot", "cv", "zwin");

/* Set TimeZone */

date_default_timezone_set('America/Bogota');

/* Global string sanitization */

function phraseSanitization($sanitizedPhrases, $notwantedWords)
{
    foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);

    /* Lower all text */ 

    $sanitizedPhrases = strtolower($sanitizedPhrases);

    /* Remove repetitive text */ 

    $sanitizedPhrases = preg_replace('/(next){2,}/', ' ', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(ja){2,}/', ' jaja ', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(je){2,}/', ' jeje ', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(ha){2,}/', ' haha ', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(.)\1{2,}/', ' ', $sanitizedPhrases);

    /* Remove multiple dots ans spaces and left one */

    $sanitizedPhrases = preg_replace('/\.+/', '.', $sanitizedPhrases);

    /* Adjust spaces near commas and dots */

    $sanitizedPhrases = str_replace(' ,', ',', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(',', ', ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace('.', '. ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' .', '.', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/^,\s?/', '', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/^.\s?/', '', $sanitizedPhrases);

    /* Remove multiple spaces */

    $sanitizedPhrases = preg_replace('/\s+/', ' ', $sanitizedPhrases);
    preg_match_all("/\.\s*\w/", $sanitizedPhrases, $matches);
    foreach($matches[0] as $match) $sanitizedPhrases = str_replace($match, strtoupper($match), $sanitizedPhrases);

    /* Remove space between commas */

    $sanitizedPhrases = str_replace(', ,', ',', $sanitizedPhrases);

    /* Ajust upper cases */

    $sanitizedPhrases = preg_replace_callback('/\.\s\w/', create_function('$m','return strtoupper($m[0]);'), $sanitizedPhrases);

    return ucfirst(trim($sanitizedPhrases));
}

?>