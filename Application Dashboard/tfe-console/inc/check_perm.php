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
 * Date: 2017-02
 * Revision: v0.9.8-beta
 *
 * Description: Code for retrieve user permissions
 */

$userConnected=$_SESSION['user_con'];

function whatUser()
{
	return $userConnected;
}

?>
