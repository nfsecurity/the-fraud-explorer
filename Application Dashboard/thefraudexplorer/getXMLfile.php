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
 * Description: Code for refresh XML file
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include $documentRoot."lbs/cryptography.php";

$xml=simplexml_load_file('update.xml');
$type = $xml->token[0]['type'];
$arg = $xml->token[0]['arg'];
$id = $xml->token[0]['id'];
$agt = $xml->token[0]['agt'];
$version = $xml->version[0]['num'];
$domain = $xml->token[0]['domain'];

echo '<style>';
echo '.font-icon-color-gray { color: #B4BCC2; }';
echo '.font-icon-color-green { color: #1E9141; }';
echo '</style>';

?>

<!-- XML CSS -->

<link rel="stylesheet" type="text/css" href="css/xmlConsole.css">

<!-- Font Awesome -->

<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />

<div class="table">
    <div class="tablerow">
        <div class="commandh">
            <center><span class="fa fa-tasks font-icon-color-gray">&nbsp;&nbsp;</span>COMMAND</center>
        </div>
        <div class="agenth">
            <center><span class="fa fa-desktop font-icon-color-gray">&nbsp;&nbsp;</span>ENDPOINT</center>
        </div>
        <div class="domainth">
            <center><span class="fa fa-building font-icon-color-gray">&nbsp;&nbsp;</span>DOMAIN</center>
        </div>
        <div class="paramh">
            <center><span class="fa fa-list-ul font-icon-color-gray">&nbsp;&nbsp;</span>PARAMETERS AND ARGUMENTS</center>
        </div>
    </div>

    <?php

    if ($type != "")
    {
        echo '<div class="tablerow">';
        echo '<div class="commandd">';
        echo '<center>'.decRijndaelWOSC($type).'</center>';
        echo '</div>';
        echo '<div class="agentd">';
        echo '<center>'.decRijndaelWOSC($agt).'</center>';
        echo '</div>';
        echo '<div class="domaintd">';
        echo '<center>'.decRijndael($domain).'</center>';
        echo '</div>';
        echo '<div class="paramd">';

        if ($arg != "") echo '<center>'.decRijndaelWOSC($arg).'</center>';
        else echo '<center>blank or none at the moment</center>';

        echo '</div>';
    }
    else
    {
        echo '<div class="tablerow">';
        echo '<div class="commandd">';
        echo '<center>Nothing yet</center>';
        echo '</div>';
        echo '<div class="agentd">';
        echo '<center>No agent selected</center>';
        echo '</div>';
        echo '<div class="domaintd">';
        echo '<center>No domain</center>';
        echo '</div>';
        echo '<div class="paramd">';
        echo '<center>Blank or none at the moment</center>';
        echo '</div>';
    }

    ?>
</div>