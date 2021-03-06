<?php
/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for roles parameters
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/cryptography.php";
include "../lbs/openDBconn.php";

$msg = "";

if (!empty($_POST['createmodify']))
{
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['domain']))
    {
        $userName = filter($_POST['username']);
        $userPassword = sha1(filter($_POST['password']));
        $userDomain = filter($_POST['domain']);

        $userExists = mysqli_query($connection, sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysqli_fetch_array($userExists)) $count = $row[0];

        if(!empty($count))
        {
            mysqli_query($connection, sprintf("UPDATE t_users SET password='%s', domain='%s' WHERE user='%s'", $userPassword, $userDomain, $userName));

            auditTrail("roles", "successfully modified role entry for the user ".$userName);
            $msg = "modification";
        }
        else 
        {
            mysqli_query($connection, sprintf("INSERT INTO t_users (user, password, domain) VALUES ('%s', '%s', '%s')", $userName, $userPassword, $userDomain));

            auditTrail("roles", "successfully created role for the user named ".$userName);
            $msg = "creation";
        }
  
        /* Domain config table */
        
        $domainConfigTable = "t_config_".str_replace(".", "_", $userDomain);
        $queryCreateDomainTable = "CREATE TABLE IF NOT EXISTS ".$domainConfigTable." (
        score_ts_low_from int DEFAULT NULL, 
        score_ts_low_to int DEFAULT NULL, 
        score_ts_medium_from int DEFAULT NULL, 
        score_ts_medium_to int DEFAULT NULL, 
        score_ts_high_from int DEFAULT NULL, 
        score_ts_high_to int DEFAULT NULL, 
        score_ts_critic_from int DEFAULT NULL, 
        score_ts_critic_to int DEFAULT NULL, 
        sample_data_calculation varchar(15) DEFAULT NULL)";
        
        $queryTable = mysqli_query($connection, $queryCreateDomainTable);
        
        if(mysqli_num_rows(mysqli_query($connection, "SHOW TABLES LIKE '".$domainConfigTable."'")) == 1) 
        {
            mysqli_query($connection, sprintf("INSERT INTO %s (score_ts_low_from, score_ts_low_to, score_ts_medium_from, score_ts_medium_to, score_ts_high_from, score_ts_high_to, score_ts_critic_from, score_ts_critic_to, sample_data_calculation) VALUES ('0', '10', '11', '20', '21', '30', '31', '100', 'enabled')", $domainConfigTable));
        }
    }
}
else if (!empty($_POST['delete']))
{
    if (!empty($_POST['username']))
    {
        $userName = filter($_POST['username']);
        $userExists = mysqli_query($connection, sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysqli_fetch_array($userExists)) $count = $row[0];
        if(!empty($count)) mysqli_query($connection, sprintf("DELETE FROM t_users WHERE user='%s'", $userName));
        
        $domainTable = "t_config_".str_replace(".", "_", $row[2]);
        
        if(mysqli_num_rows(mysqli_query($connection, "SHOW TABLES LIKE '".$domainTable."'")) == 1) 
        {
            mysqli_query($connection, sprintf("DROP TABLE %s", $domainTable));

            auditTrail("roles", "successfully deleted role for the user named ".$userName);
            $msg = "deletion";
        }
    }
}

if ($msg == "") 
{
    $msg = "none";
    $_SESSION['wm'] = encRijndael($msg);
}
else
{
    $msg = trim($msg, ",");
    $msg = ltrim($msg, " ");

    $_SESSION['wm'] = encRijndael("Successfully profile ".$msg);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>