<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for refresh XML file under dashBoard
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
		<center><span class="fa fa-cogs font-icon-color-gray">&nbsp;&nbsp;</span>COMMAND</center>
	</div>
	<div class="uniqueidh">
                <center><span class="fa fa-tags font-icon-color-gray">&nbsp;&nbsp;</span>ID</center>
        </div>
	<div class="agenth">
                <center><span class="fa fa-user font-icon-color-gray">&nbsp;&nbsp;</span>AGENT</center>
        </div>
	<div class="eventh">
                <center><span class="fa fa-hashtag font-icon-color-gray">&nbsp;&nbsp;</span>NUM</center>
        </div>
	<div class="paramh">
                <center><span class="fa fa-cube font-icon-color-gray">&nbsp;&nbsp;</span>PARAMETERS AND ARGUMENTS</center>
        </div>
</div>
	
<?php

	if ($type != "")
	{
		echo '<div class="tablerow">';
		echo '<div class="commandd">';
 		echo '<center>'.decRijndaelWOSC($type).'</center>';
		echo '</div>';
		echo '<div class="uniqueidd">';
                echo '<center>'.$id.'</center>';
                echo '</div>';
		echo '<div class="agentd">';
                echo '<center>'.decRijndaelWOSC($agt).'</center>';
                echo '</div>';
		echo '<div class="eventd">';
                echo '<center>'.$version.'</center>';
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
                echo '<div class="uniqueidd">';
                echo '<center>No ID</center>';
                echo '</div>';
                echo '<div class="agentd">';
                echo '<center>No agent command</center>';
                echo '</div>';
                echo '<div class="eventd">';
                echo '<center>No number</center>';
                echo '</div>';
                echo '<div class="paramd">';
                echo '<center>blank or none at the moment</center>';
                echo '</div>';
	}

?>
</div>
