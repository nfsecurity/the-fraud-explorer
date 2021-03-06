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
 * Description: Code for switch between phrase collection
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
include $documentRoot."lbs/cryptography.php";

$xml = simplexml_load_file('../update.xml');
$enableCommand = "module textAnalytics 1";
$disableCommand = "module textAnalytics 0";
$endpointScope = "all";
$domainScope = "all";
$enabledPhraseCollection = false;
$phraseCollectionStatus = decRijndaelRemote($xml->token[0]['arg']);
$msg = "";

if ($phraseCollectionStatus == "textAnalytics 1") 
{
    $enabledPhraseCollection = true;
    $com = strip_tags($disableCommand);

    auditTrail("collection", "the endpoint phrase collection capability was disabled");
    $msg = "Endpoint phrase collection disabled";
}
else 
{
    $enabledPhraseCollection = false;
    $com = strip_tags($enableCommand);

    auditTrail("collection", "the endpoint phrase collection capability was enabled");
    $msg = "Endpoint phrase collection enabled";
}

$com = str_replace(array('"'),array('\''), $com);

foreach ($xml->version as $version) $numVersion = (int) $version['num'];

$numVersion++;
$xmlContent = "<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
$id = mt_rand(1,32000);	
$endpoint = encRijndaelRemote($endpointScope);
$domain = encRijndaelRemote($domainScope);

if (stristr($com, ' ') === FALSE) $xmlContent=$xmlContent . "<token type=\"" . encRijndaelRemote($com) . "\" arg=\"\" id=\"".$id."\" agt=\"".$endpoint."\" domain=\"".$domain."\"/>\r\n";
else $xmlContent=$xmlContent . "<token type=\"" . encRijndaelRemote(substr($com, 0, strpos($com, " "))) . "\" arg=\"" . encRijndaelRemote(substr(strstr($com, ' '),1)) . "\" id=\"".$id."\" agt=\"".$endpoint."\" domain=\"".$domain."\"/>\r\n";

$xmlContent = $xmlContent . "</update>";
$fp = fopen('../update.xml',"w+");
fputs($fp, $xmlContent); 
fclose($fp);

echo $com;

$_SESSION['wm'] = encRijndael($msg);

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>