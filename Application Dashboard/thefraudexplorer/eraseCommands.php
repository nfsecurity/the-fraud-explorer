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
 * Description: Code for erase commands
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";

function filter($variable)
{
    return addcslashes(mysql_real_escape_string($variable),',<>');
}

$xml = simplexml_load_file('update.xml');

foreach ($xml->version as $version)
{
    $numVersion = (int) $version['num'];
}

$numVersion++;
$xmlContent="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
$xmlContent = $xmlContent . "</update>";
$fp = fopen('update.xml',"w+");
fputs($fp, $xmlContent); 
fclose($fp);

/* Clear session variables */

unset($_SESSION['id_command']);
unset($_SESSION['seconds_waiting']);
unset($_SESSION['NRF']);
unset($_SESSION['waiting_command']);
unset($_SESSION['NRF_CMD']);
unset($_SESSION['agentchecked']);

header("Location: ".$serverURL."/endPoints"); 

?>