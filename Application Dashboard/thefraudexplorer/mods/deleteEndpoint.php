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
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Code for endpoint deletion
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

$endpointEnc = filter($_GET['nt']);
$endpointDec = decRijndael($endpointEnc);
$endpointID = str_replace(array("."), array("_"), $endpointDec);

/* Delete agent tables */

$queryStatement = "SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(table_name), ';') AS statement FROM information_schema.tables WHERE table_schema = 'thefraudexplorer' AND table_name LIKE 't_%s\\_%%'";
$statement = mysqli_query($connection, sprintf($queryStatement, $endpointID));
$rowStatement = mysqli_fetch_array($statement);

mysqli_query($connection, $rowStatement[0]);
mysqli_query($connection, sprintf("DELETE FROM t_agents WHERE agent like '%s\\_%%'", $endpointID));
mysqli_query($connection, sprintf("DELETE FROM t_inferences WHERE endpoint = '%s'", $endpointID));

/* Delete agent elasticsearch documents */

$urlDelete = "http://localhost:9200/_all/_delete_by_query?pretty";
$params = '{ "query": { "wildcard" : { "agentId" : "'.$endpointID.'*" } } }';

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $urlDelete);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$resultDelete=curl_exec($ch);
curl_close($ch);

/* Referer Return */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>
