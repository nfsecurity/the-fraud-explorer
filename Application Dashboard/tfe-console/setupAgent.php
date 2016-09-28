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
 * Description: Code for setup agent
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

function filter($variable)
{
 	return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

$agent_enc=filter($_GET['agent']);
$agent_dec=base64_decode(base64_decode($agent_enc));

?>

<style>

.title
{
    font-family: 'FFont', sans-serif; font-size:12px;
}

.input-value-text
{
    width:100%; 
    height: 30px; 
    padding: 5px; 
    border: solid 1px #c9c9c9; 
    outline: none;
    font-family: 'FFont', sans-serif; font-size:12px;
}

.window-footer
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
        <h4 class="modal-title window-title" id="myModalLabel">Agent setup</h4>
</div>

<div class="div-container">
    <form id="formSetup" name="formSetup" method="post" action="<?php echo 'setupAgentParameters?agent='.$agent_enc.'&alias=yes&owner=yes&gender=yes' ?>">
	<p class="title">Agent alias</p><br>
        <input type="text" name="alias" id="alias" autocomplete="off" placeholder=":alias here <?php
        $aliasquery = mysql_query(sprintf("SELECT name FROM t_agents WHERE agent='%s'",$agent_dec)); $alias = mysql_fetch_array($aliasquery);
        if ($alias[0] == NULL) echo '(current value: Not alias yet)';
        else echo '(current value: '.$alias[0].')'; ?>" class="input-value-text">

        <br><br><p class="title">Company or group name</p><br>
        <input type="text" name="owner" id="owner" autocomplete="off" placeholder=":owner/company name here <?php
        $ownerquery = mysql_query(sprintf("SELECT owner FROM t_agents WHERE agent='%s'",$agent_dec)); $owner = mysql_fetch_array($ownerquery);
        if ($owner[0] == NULL) echo '(current value: Not company yet)';
        else echo '(current value: '.$owner[0].')'; ?>" class="input-value-text">

        <br><br><p class="title">Agent gender</p><br>
        <input type="text" name="gender" id="gender" autocomplete="off" placeholder=":gender here (male/female) <?php
        $genderquery = mysql_query(sprintf("SELECT gender FROM t_agents WHERE agent='%s'",$agent_dec)); $gender = mysql_fetch_array($genderquery);
        if ($gender[0] == NULL) echo '(current value: Not gender yet)';
        else echo '(current value: '.$gender[0].')'; ?>" class="input-value-text">

	<br><br>

	<div class="modal-footer window-footer">
		<br>
	        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <input type="submit" class="btn btn-danger setup" value="Set values">
        </div>
    </form>
</div>

<?php
	include "inc/close-db-connection.php";
?>

