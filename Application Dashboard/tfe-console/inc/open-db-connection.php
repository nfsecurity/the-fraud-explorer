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
 * Description: Code for setup DB connection
 */

$configFile = parse_ini_file("/var/www/html/tfe-console/config.ini");
$dbhost = $configFile['db_dbhost'];
$dbuser = $configFile['db_user'];
$dbpassword = $configFile['db_password'];
$db = $configFile['db_db'];
$connection = mysql_connect($dbhost, $dbuser, $dbpassword);
mysql_select_db($db, $connection);

?>
