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
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
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
    
    if (!get_magic_quotes_gpc()) $endPoint = addslashes($endPoint);
    if (!get_magic_quotes_gpc()) $domain = addslashes($domain);

    if ($domain == "all" && $endPoint == "all") return true;
    
    $result = mysqli_query($connection, sprintf("SELECT * FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s' AND domain='%s'", $endPoint, $domain));
    include "lbs/closeDBconn.php";
    
    if(mysql_fetch_array($result) !== false) return true;
    return false;
}

function checkEvent($endPoint)
{
    include "lbs/openDBconn.php";    

    if (!get_magic_quotes_gpc()) $endPoint = addslashes($endPoint);

    $result = mysqli_query($connection, sprintf("SELECT * FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl group by agent) as agt WHERE agent='%s'", $endPoint));
    include "lbs/closeDBconn.php";
    
    if (mysqli_num_rows($result) != 0) return true;
    else if ($endPoint == "all") return true;
    return false;
}

function console_log($output, $with_script_tags = true) 
{
    $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) . ');';
    
    if ($with_script_tags) 
    {
        $js_code = '<script>' . $js_code . '</script>';
    }
    
    echo $js_code;
}

?>
