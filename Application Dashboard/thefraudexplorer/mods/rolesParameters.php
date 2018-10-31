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
 * Revision: v1.2.1
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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

if (!empty($_POST['createmodify']))
{
    if (!empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['domain']))
    {
        $userName=filter($_POST['username']);
        $userPassword=sha1(filter($_POST['password']));
        $userDomain=filter($_POST['domain']);

        $userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysql_fetch_array($userExists)) $count = $row[0];

        if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET password='%s', domain='%s' WHERE user='%s'", $userPassword, $userDomain, $userName));
        else mysql_query(sprintf("INSERT INTO t_users (user, password, domain) VALUES ('%s', '%s', '%s')", $userName, $userPassword, $userDomain));
  
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
        
        $queryTable = mysql_query($queryCreateDomainTable);
        
        if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$domainConfigTable."'")) == 1) 
        {
            mysql_query(sprintf("INSERT INTO %s (score_ts_low_from, score_ts_low_to, score_ts_medium_from, score_ts_medium_to, score_ts_high_from, score_ts_high_to, score_ts_critic_from, score_ts_critic_to, sample_data_calculation) VALUES ('0', '10', '11', '20', '21', '30', '31', '100', 'enabled')", $domainConfigTable));
        }
    }
    else if (!empty($_POST['username']) && !empty($_POST['password']))
    {
        $userName=filter($_POST['username']);
        $userPassword=sha1(filter($_POST['password']));
        $userDomain="all";

        $userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysql_fetch_array($userExists)) $count = $row[0]; 

        if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET password='%s' WHERE user='%s'", $userPassword, $userName));
        else mysql_query(sprintf("INSERT INTO t_users (user, password, domain) VALUES ('%s', '%s', '%s')", $userName, $userPassword, $userDomain));
    }
    else if (!empty($_POST['username']) && !empty($_POST['domain']))
    {
        $userName=filter($_POST['username']);
        $userDomain=filter($_POST['domain']);
        $userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysql_fetch_array($userExists)) $count = $row[0];
        if(!empty($count)) mysql_query(sprintf("UPDATE t_users SET domain='%s' WHERE user='%s'", $userDomain, $userName));
    }
}
else if (!empty($_POST['delete']))
{
    if (!empty($_POST['username']))
    {
        $userName=filter($_POST['username']);
        $userExists = mysql_query(sprintf("SELECT * FROM t_users WHERE user='%s'", $userName));

        if ($row = mysql_fetch_array($userExists)) $count = $row[0];
        if(!empty($count)) mysql_query(sprintf("DELETE FROM t_users WHERE user='%s'", $userName));
        
        $domainTable = "t_config_".str_replace(".", "_", $row[2]);
        
        if(mysql_num_rows(mysql_query("SHOW TABLES LIKE '".$domainTable."'")) == 1) mysql_query(sprintf("DROP TABLE %s", $domainTable));
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>