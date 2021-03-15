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

$notwantedWords = array("rwin", "lwin", "decimal", "snapshot", "cv", "zwin", "oemquestio", "oemquestion", "medianexttrack", "oemplus");

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

    /* Remove multiple dots and spaces and left one */

    $sanitizedPhrases = preg_replace('/\.+/', '.', $sanitizedPhrases);

    /* Adjust spaces near commas and dots */

    $sanitizedPhrases = str_replace(' ,', ',', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(',', ', ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace('.', ', ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' .', ',', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/^,\s?/', '', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/^\.\s?/', '', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' , ', ', ', $sanitizedPhrases);
    $sanitizedPhrases = str_replace(' . ', ', ', $sanitizedPhrases);

    /* Remove multiple spaces */

    $sanitizedPhrases = preg_replace('/\s+/', ' ', $sanitizedPhrases);

    /* Remove space between commas */

    $sanitizedPhrases = preg_replace('/(,\s){1,}/', ', ', $sanitizedPhrases);

    /* Remove last point or comma character */

    $sanitizedPhrases = preg_replace('/(, )$/', '', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(\. )$/', '', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(,)$/', '', $sanitizedPhrases);
    $sanitizedPhrases = preg_replace('/(\.)$/', '', $sanitizedPhrases);

    /* Ajust upper cases */

    return ucfirst(trim($sanitizedPhrases));
}

/* Audit Trail */

function auditTrail($eventModule, $eventAction)
{
    global $session, $configFile;

    $now = DateTime::createFromFormat('U.u', microtime(true));
    $time = $now->format("Y-m-d\TH:i:s.u");
    $time = substr($time, 0, -3);
    
    $eventDate = (string)$time."Z";
    $eventUser = $session->username;
    $eventIP = $_SERVER["REMOTE_ADDR"];
    $eventBrowser = $_SERVER['HTTP_USER_AGENT'];
    
    $msgData = $eventDate." - AuditEvent ".$eventUser." i: ".$eventIP." b: ".$eventBrowser." m: ".$eventModule." a: ".$eventAction;
    $sockAudit = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

    $lenData = strlen($msgData);
    socket_sendto($sockAudit, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_audit_port']);
    socket_close($sockAudit);
}

?>