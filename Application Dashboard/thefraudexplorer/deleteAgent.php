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
 * Description: Code for agent deletion
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

function filter($variable)
{
	return addcslashes(mysql_real_escape_string($variable),',-<>"');
}

$agent_enc=filter($_GET['agent']);
$agent_dec=base64_decode(base64_decode($agent_enc));
$agentID=str_replace(array("."),array("_"),$agent_dec);

/* Delete agent tables */
 
mysql_query(sprintf("DROP TABLE t_%s",$agentID));
mysql_query(sprintf("DELETE FROM t_agents WHERE agent='%s'",$agentID));

/* Delete agent elasticsearch documents */

$urlDelete = "http://localhost:9200/_all/_delete_by_query?pretty";
$params = '{ "query": { "match" : { "agentId" : "'.$agentID.'" } } }';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $urlDelete);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
$resultDelete=curl_exec($ch);
curl_close($ch);

var_dump($resultDelete);

/* Return to home */

header ("location:  dashBoard");

include "lbs/close-db-connection.php";

?>

</body>
</html>
