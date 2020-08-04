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
 * Description: Code for process Worlflows
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

/* Flows */

if (isset($_POST['rulesetFlow'])) $rulesetFlow = $_POST['rulesetFlow'];
if (isset($_POST['fraudverticeFlow'])) $fraudverticeFlow = $_POST['fraudverticeFlow'];
if (isset($_POST['endpointsFlow'])) $endpointsFlow = $_POST['endpointsFlow'];
if (isset($_POST['applicationsFlow'])) $applicationsFlow = $_POST['applicationsFlow'];
if (isset($_POST['phrasesFlow'])) $phrasesFlow = $_POST['phrasesFlow'];
if (isset($_POST['fraudOperator'])) $fraudOperator = $_POST['fraudOperator'];
if (isset($_POST['workflowSelection'])) $workflowSelection = $_POST['workflowSelection'];

/* Workflow deletion */

if (isset($_POST['delete'])) 
{
    foreach($workflowSelection as $workflow) 
    {
        mysqli_query($connection, sprintf("DELETE FROM t_workflows WHERE name='%s'", $workflow));
        mysqli_query($connection, sprintf("DELETE FROM t_wtriggers WHERE name='%s'", $workflow));
    }
}
else if (isset($_POST['add']))
{
    /* Flows configuration */

    if (isset($_POST['workflowName'])) $workflowName = filter($_POST['workflowName']);
    else header('Location: ' . $_SERVER['HTTP_REFERER']);

    if (isset($_POST['workflowInterval']) && $_POST['workflowInterval'] != "") $workflowInterval = filter($_POST['workflowInterval']);
    if (isset($_POST['workflowTone']) && $_POST['workflowTone'] != "") $workflowTone = filter($_POST['workflowTone']);
    if (isset($_POST['workflowDomain'])) $workflowDomain = filter($_POST['workflowDomain']);

    if (isset($_POST['custodianEmail']) && $_POST['custodianEmail'] != "") $custodianEmail = filter($_POST['custodianEmail']);
    else header('Location: ' . $_SERVER['HTTP_REFERER']);

    $finalWorkflow = "";

    foreach($rulesetFlow as $key => $n)
    {
        $finalWorkflow = $finalWorkflow . "[D]=" .$n. ", [V]=" .$fraudverticeFlow[$key]. ", [D]=" . ($workflowDomain == "" ? "ALLD" : $workflowDomain) . ", [E]=" . ($endpointsFlow[$key] == "" ? "ALLE" : $endpointsFlow[$key]) . ", [A]=" . ($applicationsFlow[$key] == "" ? "ALLA" : $applicationsFlow[$key]). ", [P]=" .($phrasesFlow[$key] == "" ? "ALLP" : $phrasesFlow[$key]). ", [O]=" .$fraudOperator[$key] . ", ";  
    }

    $finalWorkflow = substr($finalWorkflow, 0, -2);

    /* Flows storage */

    mysqli_query($connection, sprintf("INSERT INTO t_workflows values('%s', '%s', '%d', '%s', '%d', '0')", $workflowName, $finalWorkflow, $workflowInterval, $custodianEmail, $workflowTone));
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>