<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for update machine status
 */

include "lbs/global-vars.php";
include $documentRoot."lbs/open-db-connection.php";
include $documentRoot."lbs/cryptography.php";

function filter($variable)
{
 	return mysql_real_escape_string($variable);
}

function queryOrDie($query)
{
 	$query = mysql_query($query);
 	if (! $query) exit(mysql_error());
 	return $query;
}

function getAgentIP() 
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

$macAgent = strtolower(decRijndael(filter($_GET['token'])));
$os = decRijndael(filter($_GET['s']));
$version = "v" . decRijndael(filter($_GET['v']));
$key = decRijndael(filter($_GET['k']));
$domain = strtolower(decRijndael(filter($_GET['d'])));
$agent=$macAgent;
$configFile = parse_ini_file("config.ini");
$ipAddress = getAgentIP();

$keyquery = mysql_query("SELECT password FROM t_crypt");
$keypass = mysql_fetch_array($keyquery);

/* If agent has the correct key (password), then connect */

if ($key == $keypass[0])
{
 	$result=mysql_query("SELECT count(*) FROM t_agents WHERE agent='".$agent."'");
 	if ($row_a = mysql_fetch_array($result)) { $count = $row_a[0]; }
 	$date=date('Y-M-d H:i:s');

 	if($count[0]>0)
 	{
  		date_default_timezone_set($configFile['php_timezone']);
  		$datecalendar=date('Y-m-d');
  		$result=mysql_query("Update t_agents set heartbeat=now(), system='" . $os . "', version='" . $version . "', domain='" . $domain . "', ipaddress='" . $ipAddress . "' where agent='".$agent."'");
 	}
 	else
 	{
  		if(strlen($macAgent)<60)
  		{
   			/* Send message alert for first agent connection */

   			include $documentRoot."lbs/mail-event.php";
   			mail($to, $subject, $message, $headers);

   			/* Heartbeat data */

   			$query="INSERT INTO t_agents (agent, heartbeat, system, version, ruleset, domain, ipaddress) VALUES ('" . $agent . "', now() ,'" . $os . "','" . $version . "','GENERIC','" . $domain ."','" . $ipAddress ."')";
   			queryOrDie($query);

   			/* Primary agent table */

   			$query="CREATE TABLE t_".$macAgent."(command varchar(50), response varchar(65000), finished boolean, date DATETIME, id_uniq_command int, showed boolean, PRIMARY KEY (date))";
   			queryOrDie($query);
  		}
 	}
}

include $documentRoot."lbs/close-db-connection.php";

?>
