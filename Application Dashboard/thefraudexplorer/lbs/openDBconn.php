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
 * Date: 2019-02
 * Revision: v1.3.1-ai
 *
 * Description: Code for setup DB connection
 */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$dbhost = $configFile['db_dbhost'];
$dbuser = $configFile['db_user'];
$dbpassword = $configFile['db_password'];
$db = $configFile['db_db'];
$connection = mysqli_connect($dbhost, $dbuser, $dbpassword, $db);

?>