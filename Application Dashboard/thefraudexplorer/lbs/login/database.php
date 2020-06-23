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
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Code for login
 */

include "constants.php";
include "/var/www/html/thefraudexplorer/lbs/openDBconn.php";     

class MySQLDB
{
    var $connection;

    function __construct()
    {
        $this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysqli_error());
    }

    function confirmIPAddress($value) 
    {
        $q = "SELECT attempts, (CASE when lastlogin is not NULL and DATE_ADD(LastLogin, INTERVAL ".TIME_PERIOD." MINUTE)>NOW() then 1 else 0 end) as Denied FROM ".TBL_ATTEMPTS." WHERE ip = '$value'";
        $result = mysqli_query($this->connection, $q);
        $data = mysqli_fetch_array($result);   

        if (!$data) 
        {
            return 0;
        } 

        if ($data["attempts"] >= ATTEMPTS_NUMBER)
        {
            if($data["Denied"] == 1)
            {
                return 1;
            }
            else
            {
                $this->clearLoginAttempts($value);
                return 0;
            }
        }

        return 0;  
    }

    function addLoginAttempt($value) 
    {
        $q = "SELECT * FROM ".TBL_ATTEMPTS." WHERE ip = '$value'"; 
        $result = mysqli_query($this->connection, $q);
        $data = mysqli_fetch_array($result);

        if($data)
        {
            $attempts = $data["attempts"]+1;

            if($attempts==3) 
            {
                $q = "UPDATE ".TBL_ATTEMPTS." SET attempts=".$attempts.", lastlogin=NOW() WHERE ip = '$value'";
                $result = mysqli_query($this->connection, $q);
            }
            else 
            {
                $q = "UPDATE ".TBL_ATTEMPTS." SET attempts=".$attempts." WHERE ip = '$value'";
                $result = mysqli_query($this->connection, $q);
            }
        }
        else 
        {
            $q = "INSERT INTO ".TBL_ATTEMPTS." (attempts,IP,lastlogin) values (1, '$value', NOW())";
            $result = mysqli_query($this->connection, $q);
        }
    }

    function confirmUserPass($username, $password)
    {
        if(!get_magic_quotes_gpc()) 
        {
            $username = addslashes($username);
        }

        $q = "SELECT password FROM ".TBL_USERS." WHERE user = '$username'";
        $result = mysqli_query($this->connection, $q);

        if(!$result || (mysqli_num_rows($result) < 1))
        {
            return 1; 
        }

        $dbarray = mysqli_fetch_array($result);
        $dbarray['password'] = stripslashes($dbarray['password']);
        $password = stripslashes($password);

        /* Password validation */

        if(sha1($password) == $dbarray['password'])
        {
            return 0;
        }
        else
        {
            return 1; 
        }
    }	

    function confirmUserName($username)
    {
        if(!get_magic_quotes_gpc()) 
        {
            $username = addslashes($username);
        }

        $q = "SELECT * FROM ".TBL_USERS." WHERE user = '$username'";
        $result = mysqli_query($this->connection, $q);

        if(!$result || (mysqli_num_rows($result) < 1))
        {
            return 1;
        } 

        return 0;
    }

    function clearLoginAttempts($value) 
    {
        $q = "UPDATE ".TBL_ATTEMPTS." SET attempts = 0 WHERE ip = '$value'"; 
        return mysqli_query($this->connection, $q);
    }

    function getUserInfo($username)
    {
        $q = "SELECT * FROM ".TBL_USERS." WHERE user = '$username'";
        $result = mysqli_query($this->connection, $q);

        if(!$result || (mysqli_num_rows($result) < 1))
        {
            return NULL;
        }

        $dbarray = mysqli_fetch_array($result);
        return $dbarray;
    }

    function getUserDomain($username)
    {
        $q = "SELECT domain FROM ".TBL_USERS." WHERE user = '$username'";
        $result = mysqli_query($this->connection, $q);

        if(!$result || (mysqli_num_rows($result) < 1))
        {
            return NULL;
        }

        $dbarray = mysqli_fetch_array($result);
        return $dbarray;
    }

    function displayAttempts($value)
    {
        $q = "SELECT ip, attempts,lastlogin FROM ".TBL_ATTEMPTS." WHERE ip = '$value' ORDER BY lastlogin";
        $result = mysqli_query($this->connection, $q);
        $num_rows = mysqli_num_rows($result);

        if($num_rows == 0)
        {
            echo "You have 3 available login attempts before locking your account";
            return;
        }

        for($i=0; $i<$num_rows; $i++)
        {
            $uip  = mysqli_result($result,$i,"ip");
            $uattempt = mysqli_result($result,$i,"attempts");
            $ulogin = mysqli_result($result,$i,"lastlogin");

            echo "Your failed attempts: $uattempt from $uip at $ulogin";
        }
    }
}

function mysqli_result($res, $row=0, $col=0)
{
    $numrows = mysqli_num_rows($res);

    if ($numrows && $row <= ($numrows-1) && $row >=0)
    {
        mysqli_data_seek($res,$row);
        $resrow = (is_numeric($col)) ? mysqli_fetch_row($res) : mysqli_fetch_assoc($res);

        if (isset($resrow[$col])) return $resrow[$col];
    }
    return false;
}

$database = new MySQLDB;
include "/var/www/html/thefraudexplorer/lbs/closeDBconn.php";

?>
