<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.0
 *
 * Description: Code for setup DB connection
 */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$dbhost = $configFile['db_dbhost'];
$dbuser = $configFile['db_user'];
$dbpassword = $configFile['db_password'];
$db = $configFile['db_db'];
$connection = mysql_connect($dbhost, $dbuser, $dbpassword, true);
mysql_select_db($db, $connection);

?>