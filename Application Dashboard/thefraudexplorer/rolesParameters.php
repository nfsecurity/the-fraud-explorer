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
 * Description: Code for roles parameters
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

if (!empty($_POST['createmodify']))
{
	if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['domain'])) 
	{
		$userName=filter($_POST['username']);
		$userPassword=sha1(filter($_POST['password']));
		$userDomain=filter($_POST['domain']);	

		$userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

		if ($row = mysql_fetch_array($userExists)) $count = $row[0];

        	if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET password='%s', domain='%s' WHERE user='%s'", $userPassword, $userDomain, $userName));
		else mysql_query(sprintf("INSERT INTO t_users (user, password, domain) VALUES ('%s', '%s', '%s')", $userName, $userPassword, $userDomain));
	}
	else if (!empty($_POST['username']) && !empty($_POST['password']))
	{
		$userName=filter($_POST['username']);
 	  	$userPassword=sha1(filter($_POST['password'])); 
		$userDomain="all";

		$userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        	if ($row = mysql_fetch_array($userExists)) $count = $row[0]; 

        	if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET password='%s' WHERE user='%s'", $userPassword, $userName));
        	else mysql_query(sprintf("INSERT INTO t_users (user, password, domain) VALUES ('%s', '%s', '%s')", $userName, $userPassword, $userDomain));
        }
	else if (!empty($_POST['username']) && !empty($_POST['domain']))
	{
        	$userName=filter($_POST['username']);
        	$userDomain=filter($_POST['domain']);

        	$userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        	if ($row = mysql_fetch_array($userExists)) $count = $row[0]; 

        	if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET domain='%s' WHERE user='%s'", $userDomain, $userName));
	}
}
else if (!empty($_POST['delete']))
{
	if (!empty($_POST['username']))
	{
		$userName=filter($_POST['username']);
		$userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));
		
		if ($row = mysql_fetch_array($userExists)) $count = $row[0];
		
		if(!empty($count)) mysql_query(sprintf("DELETE FROM t_users WHERE user='%s'", $userName));
	}
}

header ("location: dashBoard");
include "lbs/close-db-connection.php";

?>
</body>
</html>
