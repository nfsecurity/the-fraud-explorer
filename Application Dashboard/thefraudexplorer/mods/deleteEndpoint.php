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
 * Date: 2019-03
 * Revision: v1.3.2-ai
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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

$endpointEnc=filter($_GET['endpoint']);
$endpointDec=base64_decode(base64_decode($endpointEnc));
$endpointID=str_replace(array("."), array("_"), $endpointDec);

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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $urlDelete);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$resultDelete=curl_exec($ch);
curl_close($ch);

/* Return to home */

header ("location: endPoints");

include "../lbs/closeDBconn.php";

?>

</body>
</html>
