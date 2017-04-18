<?php

/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
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
$type=$_GET['type'];

/* Delete agent elasticsearch documents */

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, "http://localhost:9200/".$index."/".$type."/".$regid); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_exec($ch); 
curl_close($ch);   

/* Return to home */

header ("location: alertData?agent=".$agent);

?>

</body>
</html>