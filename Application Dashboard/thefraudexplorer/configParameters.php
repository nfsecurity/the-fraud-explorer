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
        if (!empty($encryption)) mysql_query(sprintf("UPDATE t_crypt SET `key`='%s'", $encryption));
}

if (isset($_POST['iv']))
{
        $iv=filter($_POST['iv']);
        if (!empty($iv)) mysql_query(sprintf("UPDATE t_crypt SET iv='%s'", $iv));
}


header ("location: dashBoard");
include "lbs/close-db-connection.php";

?>
</body>
</html>
