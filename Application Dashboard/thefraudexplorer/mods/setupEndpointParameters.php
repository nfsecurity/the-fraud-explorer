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
 * Date: 2019-02
 * Revision: v1.3.1-ai
 *
 * Description: Code for endpoint setup
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

if (isset($_POST['alias']) && $_POST['alias'] != "") 
{
    $alias=filter($_POST['alias']);
    if (!empty($alias)) mysqli_query($connection, sprintf("UPDATE t_agents SET name='%s' WHERE agent LIKE '%s%%'", $alias, $endpointDec));
}

if (isset($_POST['ruleset']) && strpos($_POST['ruleset'], 'Choose the ruleset') === false) 
{
    $ruleset=filter($_POST['ruleset']);
    if (!empty($ruleset)) mysqli_query($connection, sprintf("UPDATE t_agents SET ruleset='%s' WHERE agent LIKE '%s%%'", $ruleset, $endpointDec));
}

if (isset($_POST['gender']) && strpos($_POST['gender'], 'Choose the gender') === false)
{
    $gender=filter($_POST['gender']);
    if (!empty($gender)) mysqli_query($connection, sprintf("UPDATE t_agents SET gender='%s' WHERE agent LIKE '%s%%'", $gender, $endpointDec));
}

header ("location: ../endPoints");
include "../lbs/closeDBconn.php";

?>