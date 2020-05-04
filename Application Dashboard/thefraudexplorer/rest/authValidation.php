<?php

/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: Code for REST login database
 */

header('Content-Type: application/json');
$headers = apache_request_headers();
$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$dbhost = $configFile['db_dbhost'];

define("DB_SERVER", $configFile['db_dbhost']);
define("DB_USER", $configFile['db_user']);
define("DB_PASS", $configFile['db_password']);
define("DB_NAME", $configFile['db_db']);
define("TBL_USERS", "t_users");

$dbConnection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());

/* Check context */

function getUserContext($username)
{
    global $dbConnection;

    if (!get_magic_quotes_gpc()) $username = addslashes($username);

    $q = "SELECT domain FROM ".TBL_USERS." WHERE user = '$username'";
    $result = mysqli_query($dbConnection, $q);
    $dbarray = mysqli_fetch_array($result);

    return  $dbarray['domain'];
}

/* Check login method */

function confirmUserPass($username, $password)
{
    global $dbConnection;

    if (!get_magic_quotes_gpc()) $username = addslashes($username);
        
    $q = "SELECT password FROM ".TBL_USERS." WHERE user = '$username'";
    $result = mysqli_query($dbConnection, $q);

    if(!$result || (mysqli_num_rows($result) < 1)) return 1; 
        
    $dbarray = mysqli_fetch_array($result);
    $dbarray['password'] = stripslashes($dbarray['password']);
    $password = stripslashes($password);

    if(sha1($password) == $dbarray['password']) return 0;
    else return 1; 	
}

/* Check login procedure */

if(isset($headers['username']) && isset($headers['password']))
{
    $restAuthUser = $headers['username'];
    $restAuthPassword = $headers['password'];

    $loginCheck = confirmUserPass($restAuthUser, $restAuthPassword);

    if ($loginCheck != 0)
    {
        echo json_encode("Authentication error: invalid username or password");
        exit;
    }
}
else 
{
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

?>
