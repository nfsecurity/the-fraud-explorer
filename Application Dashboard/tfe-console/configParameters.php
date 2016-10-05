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
 * Description: Code for general setup
*/

session_start();
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

if (isset($_GET['key'])) 
{
	$password=filter($_POST['key']);
	mysql_query(sprintf("UPDATE t_crypt SET password='%s'", $password));
}

if (isset($_GET['changepassword']))
{
        $username="admin";
        $password=sha1(filter($_POST['password']));
        mysql_query(sprintf("UPDATE t_users SET password='%s' WHERE user='%s'", $password, $username));
}

if (isset($_GET['encryption']))
{
        $encryption=filter($_POST['encryption']);
        mysql_query(sprintf("UPDATE t_crypt SET `key`='%s'", $encryption));
}

if (isset($_GET['iv']))
{
        $iv=filter($_POST['iv']);
        mysql_query(sprintf("UPDATE t_crypt SET iv='%s'", $iv));
}


header ("location: dashBoard");
include "inc/close-db-connection.php";

?>
</body>
</html>
