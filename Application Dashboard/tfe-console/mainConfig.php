<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for main config
 */

session_start();

include "inc/check_perm.php";
include "inc/global-vars.php";

if(empty($_SESSION['connected']))
{
 	header ("Location: ".$serverURL);
 	exit;
}

error_reporting(0);
include "inc/open-db-connection.php";

?>

<style>

.title-config
{
    font-family: 'FFont', sans-serif; font-size:12px;
    float: left;
    padding-bottom: 10px;
    padding-top: 10px;
}

.input-value-text-config
{
    width:100%; 
    height: 30px; 
    padding: 5px; 
    border: solid 1px #c9c9c9; 
    outline: none;
    font-family: 'FFont', sans-serif; font-size:12px;
}

.window-footer-config
{
    padding: 0px 0px 0px 0px;
}

.div-container
{
    margin: 20px;
}

</style>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title window-title" id="myModalLabel">Main Configuration</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action="<?php echo 'configParameters?key=yes&changepassword=yes&iv=yes&encryption=yes' ?>">

	<p class="title-config">Specify/change the key/password for agents connection</p><br>
	<input class="input-value-text-config" type="text" name="key" id="key" autocomplete="off" placeholder=":key/password here <?php 
	$keyquery = mysql_query("SELECT password FROM t_crypt"); $keypass = mysql_fetch_array($keyquery); 
	echo '(current value:'.$keypass[0].')'; ?>" padding: 5px; border: solid 2px #c9c9c9;">

	<br><p class="title-config">Change 16Bit Encryption key</p><br>
        <input class="input-value-text-config" type="text" name="encryption" id="encryption" autocomplete="off" placeholder=":encryption key here <?php
        $keyquery = mysql_query("SELECT `key` FROM t_crypt"); $keypass = mysql_fetch_array($keyquery);
        echo '(current value:'.$keypass[0].')'; ?>" padding: 5px; border: solid 2px #c9c9c9;">

	<br><p class="title-config">Change 16Bit Encryption key Initialization Vector</p><br>
        <input class="input-value-text-config" type="text" name="iv" id="iv" autocomplete="off" placeholder=":initialization vector here <?php
        $keyquery = mysql_query("SELECT iv FROM t_crypt"); $keypass = mysql_fetch_array($keyquery);
        echo '(current value:'.$keypass[0].')'; ?>" padding: 5px; border: solid 2px #c9c9c9;">

	<br><p class="title-config">Admin password modification (mandatory)</p><br>
        <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="off" 
	placeholder=":new password here" padding: 5px; border: solid 2px #c9c9c9;">

	<br><br>
        <div class="modal-footer window-footer-config">
                <br>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <input type="submit" class="btn btn-danger setup" value="Set values">
        </div>
    </form>
</div> 
	
<?php
	include "inc/close-db-connection.php";
?>

