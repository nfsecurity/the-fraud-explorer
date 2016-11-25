<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for update machine status
 */

include "inc/global-vars.php";
include $documentRoot."inc/open-db-connection.php";
include $documentRoot."inc/cryptography.php";

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

$macAgent = decRijndael(filter($_GET['token']));
$os = decRijndael(filter($_GET['s']));
$version = "v" . decRijndael(filter($_GET['v']));
$key = decRijndael(filter($_GET['k']));
$agent=$macAgent;
$configFile = parse_ini_file("config.ini");

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
  		$result=mysql_query("Update t_agents set heartbeat=now(), system='" . $os . "', version='" . $version . "' where agent='".$agent."'");
 	}
 	else
 	{
  		if(strlen($macAgent)<60)
  		{
   			/* Send message alert for first agent connection */

   			include $documentRoot."inc/mail-event.php";
   			mail($to, $subject, $message, $headers);

   			/* Heartbeat data */

   			$query="INSERT INTO t_agents (agent, heartbeat, system, version, ruleset) VALUES ('" . $agent . "', now() ,'" . $os . "','" . $version . "','GENERIC')";
   			queryOrDie($query);

   			/* Primary agent table */

   			$query="CREATE TABLE t_".$macAgent."(command varchar(50),response varchar(65000),finished boolean,date DATETIME,id_uniq_command int,showed boolean,PRIMARY KEY (date))";
   			queryOrDie($query);
  		}
 	}
}

include $documentRoot."inc/close-db-connection.php";

?>
