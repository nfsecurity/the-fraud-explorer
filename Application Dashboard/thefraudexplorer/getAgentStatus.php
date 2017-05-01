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
 * Description: Code for refresh agents state
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/agent_methods.php";

$_SESSION['id_uniq_command']=null;
$agent = filter($_GET['agent']);
$agent_dec = base64_decode(base64_decode(filter($_GET['agent'])));

$query="SELECT agent, heartbeat, now() FROM t_agents WHERE agent = \"" .$agent_dec. "\"";
$result_a = mysql_query($query);

if ($row_a = mysql_fetch_array($result_a))
{
    if(isConnected($row_a["heartbeat"], $row_a[2])) echo '<img src="images/on.png" border="0">';
    else echo '<img src="images/off.png" border="0">';
}

?>