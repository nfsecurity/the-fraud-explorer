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
 * Description: Code for update machine status
 */

include "lbs/globalVars.php";
include $documentRoot."lbs/openDBconn.php";
include $documentRoot."lbs/cryptography.php";
include "lbs/security.php";

/* Validate SQL connection */

function queryOrDie($query)
{
    global $connection;

    $query = mysqli_query($connection, $query);
    if (! $query) exit(mysqli_error($connection));
    return $query;
}

/* Get endpoint IP address */

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

/* Get default entry ruleset to be assinged to the endpoint at singup */

function singupRuleset()
{
    $configFile = parse_ini_file("config.ini");
    return $configFile['singup_ruleset'];
}

$endpointIdentification = strtolower(decRijndaelRemote(filter($_GET['token'])));
$os = decRijndaelRemote(filter($_GET['s']));
$version = "v" . decRijndaelRemote(filter($_GET['v']));
$key = decRijndaelRemote(filter($_GET['k']));
$domain = strtolower(decRijndaelRemote(filter($_GET['d'])));
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

    if ($count[0] > 0)
    {
        date_default_timezone_set($configFile['php_timezone']);
        $datecalendar = date('Y-m-d');
        $result = mysqli_query($connection, "UPDATE t_agents SET heartbeat=NOW(), system='" . $os . "', version='" . $version . "', domain='" . $domain . "', ipaddress='" . $ipAddress . "' WHERE agent='".$endpoint."'");
    }
    else
    {
        if (strlen($endpointIdentification) < 60)
        {
            /* Heartbeat data */

            $query = "INSERT INTO t_agents (agent, heartbeat, system, version, ruleset, domain, ipaddress) VALUES ('" . $endpoint . "', NOW() ,'" . $os . "','" . $version . "','" . singupRuleset() . "','" . $domain ."','" . $ipAddress ."')";
            queryOrDie($query);

            /* Primary endpoint table */

            $query = "CREATE TABLE t_".$endpointIdentification."(command varchar(50), response varchar(65000), finished boolean, date DATETIME, id_uniq_command int, showed boolean, PRIMARY KEY (date)) ENGINE = MyISAM";
            queryOrDie($query);
        }
    }
}

include $documentRoot."lbs/closeDBconn.php";

?>