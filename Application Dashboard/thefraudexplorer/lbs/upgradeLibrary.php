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

/* Local libraries */

$phraseNameSpanish = explode("/", $configFile['fta_text_rule_spanish']);
$phraseNameSelectionSpanish = $phraseNameSpanish[7];
$phraseNameEnglish = explode("/", $configFile['fta_text_rule_english']);
$phraseNameSelectionEnglish = $phraseNameEnglish[7];

$localLibraryPathSpanish = $configFile['fta_text_rule_spanish'];
$localLibraryPathEnglish = $configFile['fta_text_rule_english'];

/* Remote license */

$configFile = parse_ini_file("../config.ini");
$serialNumber = $configFile['pl_serial'];
$serverAddress = "https://licensing.thefraudexplorer.com/validateSerial.php";

$postRequest = array(
    'serial' => $serialNumber,
    'capabilities' => "false",
    'retrieve' => "true"
);

$payload = json_encode($postRequest);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $serverAddress);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

$headers = [
    'Content-Type: application/json',
];

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$server_output = curl_exec($ch);
curl_close($ch);
$replyJSON = json_decode($server_output, true);
$remoteSpanishLibrary = json_decode($replyJSON['spanishLibrary'], true);
$remoteEnglishLibrary = json_decode($replyJSON['englishLibrary'], true);

/* Recover custom phrase expressions */

$fraudTriangleTerms = array('0'=>'pressure','1'=>'opportunity','2'=>'rationalization');
$numberOfLibraries = 2;
$jsonFT[1] = json_decode(file_get_contents($localLibraryPathSpanish), true);
$jsonFT[2] = json_decode(file_get_contents($localLibraryPathEnglish), true);
$pressureCustomExpressions = array();
$opportunityCustomExpressions = array();
$rationalizationCustomExpressions = array();

foreach ($jsonFT[1]['dictionary'] as $ruleset => $value)
{
    foreach($fraudTriangleTerms as $term)
    {
        for ($lib = 1; $lib<=$numberOfLibraries; $lib++)
        {  
            foreach ($jsonFT[$lib]['dictionary'][$ruleset][$term] as $field => $termPhrase)
            {
                if (strpos($field, 'c:') !== false) 
                {
                    if ($lib == 1) $remoteSpanishLibrary['dictionary'][$ruleset][$term][$field] = $termPhrase;
                    else $remoteEnglishLibrary['dictionary'][$ruleset][$term][$field] = $termPhrase;
                }      
            }
        }
    }
}

/* Write to filesystem */ 

$jsonDataSpanish = json_encode($remoteSpanishLibrary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($localLibraryPathSpanish, $jsonDataSpanish);

$jsonDataEnglish = json_encode($remoteEnglishLibrary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($localLibraryPathEnglish, $jsonDataEnglish);

auditTrail("library", "successfully updated phrase libraries from the core");

$_SESSION['wm'] = encRijndael("Successfully updated phrase libraries");

?>