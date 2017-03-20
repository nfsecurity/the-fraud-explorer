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
 * Description: Code for general setup
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

function filter($variable)
{
	return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

function notempty($var) 
{
	return ($var==="0"||$var);
}

if (isset($_POST['key'])) 
{
	$keyPass=filter($_POST['key']);
	if (!empty($keyPass)) mysql_query(sprintf("UPDATE t_crypt SET password='%s'", $keyPass));
}

if (isset($_POST['changepassword']))
{
        $username="admin";
        $password=sha1(filter($_POST['password']));
        if (!empty($password)) mysql_query(sprintf("UPDATE t_users SET password='%s' WHERE user='%s'", $password, $username));
}

if (isset($_POST['encryption']))
{
        $encryption=filter($_POST['encryption']);
        if (!empty($encryption)) mysql_query(sprintf("UPDATE t_crypt SET `key`='%s', `iv`='%s'", $encryption, $encryption));
}

if (isset($_POST['lowfrom']) && isset($_POST['lowto']) && isset($_POST['mediumfrom']) && isset($_POST['mediumto']) && isset($_POST['highfrom']) && isset($_POST['highto']) && isset($_POST['criticfrom']) && isset($_POST['criticto']))
{
        $lowFrom=filter($_POST['lowfrom']);
	$lowTo=filter($_POST['lowto']);
	$mediumFrom=filter($_POST['mediumfrom']);
	$mediumTo=filter($_POST['mediumto']);
	$highFrom=filter($_POST['highfrom']);
	$highTo=filter($_POST['highto']);
	$criticFrom=filter($_POST['criticfrom']);
	$criticTo=filter($_POST['criticto']);
	
        if (notempty($lowFrom) && notempty($lowTo) && notempty($mediumFrom) && notempty($mediumTo) && notempty($highFrom) && notempty($highTo) && notempty($criticFrom) && notempty($criticTo)) 
	{
		mysql_query(sprintf("UPDATE t_config SET score_ts_low_from='%s', score_ts_low_to='%s', score_ts_medium_from='%s', score_ts_medium_to='%s', 
		score_ts_high_from='%s', score_ts_high_to='%s', score_ts_critic_from='%s', score_ts_critic_to='%s'", $lowFrom, $lowTo, $mediumFrom, $mediumTo, $highFrom, $highTo, $criticFrom, $criticTo));
	}
}

header ("location: dashBoard");
include "lbs/close-db-connection.php";

?>
</body>
</html>
