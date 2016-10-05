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
 * Description: Code for login page
 */

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Login &raquo; Analytics</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="icon" type="image/x-icon" href="images/favicon.png?v=2" sizes="32x32">
		<link rel="stylesheet" type="text/css" href="css/index.css" media="screen" />
	</head>
	<body>
	
	<div align="center">
		<table>
 			<th>Please enter the following data<br></th>
  			<tbody>
   			<tr>
    				<td class="login-container">
     					<form id="formLogin" name="formLogin" method="post" action="login">
    					<center><br>
     					<table class="sub-container">
     						<tr>
      						<td>
       							Login
      						</td>
      						<td>
       							<input type="text" name="user" id="user" autocomplete="off" placeholder=":enter your username" class="input-login">
      						</td>
      						<td rowspan="3" style="border-top:0px solid #e0e0e0; border-right:0px solid #e0e0e0;">
       							<center><img src="captcha"/></center><br>
       							&nbsp;&nbsp;<input type="submit" name="loginok" value="Sign In Now" class="sign-in-button">
      						</td>
    					 	</tr>
     						<tr>
      						<td>
       							Password&nbsp;&nbsp;
      						</td>
      						<td>
       							<input type="password" name="pass" id="pass" placeholder=":enter your password" class="input-login">
      						</td>
     						</tr>
    						<tr>
     						<td>
      							Captcha&nbsp;&nbsp;
     						</td>
     						<td>
      							<input type="captcha" name="captcha" id="captcha" placeholder=":enter captcha value" class="input-login"> 
     						</td>
    						</tr>
    					</table><br>
    					</center>
    					</form> 
    				</td>
   			</tr>
  			</tbody>
		</table>
	<br>
	</div>
	</body>
</html>
