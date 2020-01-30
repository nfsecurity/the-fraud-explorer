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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for update machine status
 */

include "lbs/globalVars.php";
include $documentRoot."lbs/openDBconn.php";
include $documentRoot."lbs/cryptography.php";
include "lbs/security.php";

function queryOrDie($query)
{
    global $connection;

    $query = mysqli_query($connection, $query);
    if (! $query) exit(mysqli_error($connection));
    return $query;
}

function getEndpointIP() 
{
    $ipaddress = '';

    if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED'])) $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR'])) $ipaddress = $_SERVER['REMOTE_ADDR'];
    else $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$endpointIdentification = strtolower(decRijndael(filter($_GET['token'])));
$os = decRijndael(filter($_GET['s']));
$version = "v" . decRijndael(filter($_GET['v']));
$key = decRijndael(filter($_GET['k']));
$domain = strtolower(decRijndael(filter($_GET['d'])));
$endpoint=$endpointIdentification;
$configFile = parse_ini_file("config.ini");
$ipAddress = getEndpointIP();
$keyquery = mysqli_query($connection, "SELECT password FROM t_crypt");
$keypass = mysqli_fetch_array($keyquery);

if (empty($domain)) $domain = "mydomain.loc";

/* If endpoint has the correct key (password), then connect */

if ($key == $keypass[0])
{
    $result = mysqli_query($connection, "SELECT count(*) FROM t_agents WHERE agent='".$endpoint."'");
    if ($row_a = mysqli_fetch_array($result)) { $count = $row_a[0]; }
    $date = date('Y-M-d H:i:s');

    if($count[0]>0)
    {
        date_default_timezone_set($configFile['php_timezone']);
        $datecalendar=date('Y-m-d');
        $result=mysqli_query($connection, "Update t_agents set heartbeat=now(), system='" . $os . "', version='" . $version . "', domain='" . $domain . "', ipaddress='" . $ipAddress . "' where agent='".$endpoint."'");
    }
    else
    {
        if(strlen($endpointIdentification)<60)
        {
            /* Heartbeat data */

            $query="INSERT INTO t_agents (agent, heartbeat, system, version, ruleset, domain, ipaddress) VALUES ('" . $endpoint . "', now() ,'" . $os . "','" . $version . "','BASELINE','" . $domain ."','" . $ipAddress ."')";
            queryOrDie($query);

            /* Primary endpoint table */

            $query="CREATE TABLE t_".$endpointIdentification."(command varchar(50), response varchar(65000), finished boolean, date DATETIME, id_uniq_command int, showed boolean, PRIMARY KEY (date)) ENGINE = MyISAM";
            queryOrDie($query);
        }
    }
}

include $documentRoot."lbs/closeDBconn.php";

?>