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
 * Date: 2019-05
 * Revision: v1.3.3-ai
 *
 * Description: Code for login lockout
 */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$dbhost = $configFile['db_dbhost'];

define("DB_SERVER", $configFile['db_dbhost']);
define("DB_USER", $configFile['db_user']);
define("DB_PASS", $configFile['db_password']);
define("DB_NAME", $configFile['db_db']);
define("TBL_USERS", "t_users");
define("TBL_ATTEMPTS", "t_login_attempts");
define("ATTEMPTS_NUMBER", "3");
define("TIME_PERIOD", "5");
define("COOKIE_EXPIRE", 60*60*24*100); 
define("COOKIE_PATH", "/");            

?>
