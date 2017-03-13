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
 * Description: Code for Elasticsearch row deletions
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

include "lbs/global-vars.php";

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
