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
 * Description: Code for login control
 */

session_start();

include "inc/global-vars.php";
include "inc/open-db-connection.php";

function filter($variable)
{
 	return addcslashes(mysql_real_escape_string($variable),',<>');
}

$user = filter($_POST["user"]);
$pass = sha1(filter($_POST["pass"]));
$captcha = filter($_POST["captcha"]);

$sql = "SELECT * FROM t_users WHERE user='".($user)."' AND password='".($pass)."'";
$result_a = mysql_query($sql);
$rowUser = mysql_fetch_row($result_a);

$sql2 = "SELECT count(*) FROM t_captcha WHERE captcha='".($captcha)."'";
$result_b = mysql_query($sql2);

if ($row = mysql_fetch_array($result_b))
{
 	if($row[0]>0)
 	{
  		if($rowUser != FALSE)
  		{
    			$_SESSION['connected']=1;
    			$_SESSION['user_con']=$user;
    			Header("Location: dashBoard");
  		}
  		else
  		{
   			header ("Location: index");
   			exit;
  		}
 	}
 	else
 	{
  		header ("Location: index");
  		exit;
 	}
}

include "inc/close-db-connection.php";

?>
