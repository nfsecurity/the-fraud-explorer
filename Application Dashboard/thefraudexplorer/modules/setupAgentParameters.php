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
 * Date: 2018-12
 * Revision: v1.2.1
 *
 * Description: Code for agent setup
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

$agent_enc=filter($_GET['agent']);
$agent_dec=base64_decode(base64_decode($agent_enc));

if (isset($_POST['alias']) && $_POST['alias'] != "") 
{
    $alias=filter($_POST['alias']);
    if (!empty($alias)) mysql_query(sprintf("UPDATE t_agents SET name='%s' WHERE agent LIKE '%s%%'", $alias, $agent_dec));
}

if (isset($_POST['ruleset']) && strpos($_POST['ruleset'], 'Choose the ruleset') === false) 
{
    $ruleset=filter($_POST['ruleset']);
    if (!empty($ruleset)) mysql_query(sprintf("UPDATE t_agents SET ruleset='%s' WHERE agent LIKE '%s%%'", $ruleset, $agent_dec));
}

if (isset($_POST['gender']) && strpos($_POST['gender'], 'Choose the gender') === false)
{
    $gender=filter($_POST['gender']);
    if (!empty($gender)) mysql_query(sprintf("UPDATE t_agents SET gender='%s' WHERE agent LIKE '%s%%'", $gender, $agent_dec));
}

header ("location: ../endPoints");
include "../lbs/closeDBconn.php";

?>