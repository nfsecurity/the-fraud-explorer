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
 * Description: Security methods
 */

function filter($variable)
{
    return mysql_real_escape_string($variable);
}

function checkEndpoint($endPoint, $domain)
{
    include "lbs/open-db-connection.php";
    
    if ($domain == "all" && $endPoint == "all") return true;
    
    $result = mysql_query(sprintf("SELECT * FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s' AND domain='%s'", $endPoint, $domain));
    include "lbs/close-db-connection.php";
    
    if(mysql_fetch_array($result) !== false) return true;
    return false;
}

function checkAlert($endPoint)
{
    include "lbs/open-db-connection.php";    
    $result = mysql_query(sprintf("SELECT * FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s'", $endPoint));
    include "lbs/close-db-connection.php";
    
    if(mysql_fetch_array($result) !== false) return true;
    else if ($endPoint == "all") return true;
    return false;
}

?>