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
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for agent deletion
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

$agent_enc=filter($_GET['agent']);
$agent_dec=base64_decode(base64_decode($agent_enc));
$agentID=str_replace(array("."), array("_"), $agent_dec);

/* Delete agent tables */

$queryStatement = "SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(table_name), ';') AS statement FROM information_schema.tables WHERE table_schema = 'thefraudexplorer' AND table_name LIKE 't_%s_%%'";
$statement = mysql_query(sprintf($queryStatement, $agentID));
$rowStatement = mysql_fetch_array($statement);

mysql_query($rowStatement[0]);
mysql_query(sprintf("DELETE FROM t_agents WHERE agent like '%s%%'", $agentID));

/* Delete agent elasticsearch documents */

$urlDelete = "http://localhost:9200/_all/_delete_by_query?pretty";
$params = '{ "query": { "wildcard" : { "agentId.raw" : "'.$agentID.'*" } } }';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $urlDelete);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
$resultDelete=curl_exec($ch);
curl_close($ch);

/* Return to home */

header ("location: endPoints");

include "lbs/close-db-connection.php";

?>

</body>
</html>