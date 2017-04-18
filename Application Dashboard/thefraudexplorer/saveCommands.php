<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for save commands
 */

header("Cache-Control: no-store, no-cache, must-revalidate");

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include $documentRoot."lbs/cryptography.php";

function filter($variable)
{
    return mysql_real_escape_string(strip_tags($variable));
}

$com = strip_tags($_POST['commands']);
$com = str_replace(array('"'),array('\''),$com);
$xml = simplexml_load_file('update.xml');

foreach ($xml->version as $version) $numVersion = (int) $version['num'];

$numVersion++;
$xmlContent="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
$id = mt_rand(1,32000);	
$agent = filter($_GET['agent']);
$_SESSION['agent'] = $agent;
$domain = filter($_GET['domain']);

/* Encrypt variables */

$agent = encRijndael($agent);
$domain = encRijndael($domain);

if (stristr($com, ' ') === FALSE) $xmlContent=$xmlContent . "<token type=\"" . encRijndael($com) . "\" arg=\"\" id=\"".$id."\" agt=\"".$agent."\" domain=\"".$domain."\"/>\r\n";
else $xmlContent=$xmlContent . "<token type=\"" . encRijndael(substr($com, 0, strpos($com, " "))) . "\" arg=\"" . encRijndael(substr(strstr($com, ' '),1)) . "\" id=\"".$id."\" agt=\"".$agent."\" domain=\"".$domain."\"/>\r\n";

$xmlContent = $xmlContent . "</update>";
$fp = fopen('update.xml',"w+");
fputs($fp, $xmlContent); 
fclose($fp);

echo "<font face=\"Courier New\" size=2px><br/>: ". $com. "</font>";

$_SESSION['id_command'] = $id;
$_SESSION['NRF']=0;
$_SESSION['waiting_command']=0;

?>