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
 * Description: Code for Elasticsearch row deletions
 */

session_start();

include "inc/global-vars.php";

if(empty($_SESSION['connected']))
{
 	header ("Location: ".$serverURL);
 	exit;
}

function filter($variable)
{
	return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

$regid=$_GET['regid'];
$agent=$_GET['agent'];
$index=$_GET['index'];

/* Delete agent elasticsearch documents */

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, "http://localhost:9200/logstash-".$index."-*/_query?q=_id:".$regid); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_exec($ch); 
curl_close($ch);   

/* Return to home */

header ("location: alertData?agent=".$agent);

?>

</body>
</html>
