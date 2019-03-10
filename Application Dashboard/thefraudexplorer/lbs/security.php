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
 * Date: 2019-03
 * Revision: v1.3.2-ai
 *
 * Description: Security methods
 */

function filter($variable)
{
    include "/var/www/html/thefraudexplorer/lbs/openDBconn.php";
    return mysqli_real_escape_string($connection, $variable);
}

function checkEndpoint($endPoint, $domain)
{
    include "lbs/openDBconn.php";
    
    if ($domain == "all" && $endPoint == "all") return true;
    
    $result = mysqli_query($connection, sprintf("SELECT * FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s' AND domain='%s'", $endPoint, $domain));
    include "lbs/closeDBconn.php";
    
    if(mysql_fetch_array($result) !== false) return true;
    return false;
}

function checkEvent($endPoint)
{
    include "lbs/openDBconn.php";    
    $result = mysqli_query($connection, sprintf("SELECT * FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s'", $endPoint));
    include "lbs/closeDBconn.php";
    
    if(mysqli_fetch_array($result) !== false) return true;
    else if ($endPoint == "all") return true;
    return false;
}

?>
