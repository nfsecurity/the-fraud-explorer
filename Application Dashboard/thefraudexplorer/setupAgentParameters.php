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
 * Description: Code for agent setup
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

if (isset($_POST['alias']) && $_POST['alias'] != "") 
{
	$alias=filter($_POST['alias']);
	if (!empty($alias)) mysql_query(sprintf("UPDATE t_agents SET name='%s' WHERE agent='%s'",$alias,$agent_dec));
}

if (isset($_POST['ruleset']) && strpos($_POST['ruleset'], 'Choose the ruleset') === false) 
{
        $ruleset=filter($_POST['ruleset']);
        if (!empty($ruleset)) mysql_query(sprintf("UPDATE t_agents SET ruleset='%s' WHERE agent='%s'",$ruleset,$agent_dec));
}

if (isset($_POST['gender']) && strpos($_POST['gender'], 'Choose the gender') === false)
{
        $gender=filter($_POST['gender']);
        if (!empty($gender)) mysql_query(sprintf("UPDATE t_agents SET gender='%s' WHERE agent='%s'",$gender,$agent_dec));
}

header ("location:  dashBoard");

include "lbs/close-db-connection.php";

?>

</body>
</html>
