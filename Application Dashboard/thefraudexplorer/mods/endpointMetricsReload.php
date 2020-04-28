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
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for endpoint metrics reload
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

$_SESSION['endpointMetrics']['endpoint'] = filter($_GET['nt']);
$_SESSION['endpointMetrics']['pressure'] = filter($_GET['re']);
$_SESSION['endpointMetrics']['opportunity'] = filter($_GET['ty']);
$_SESSION['endpointMetrics']['rationalization'] = filter($_GET['on']);
@$_SESSION['endpointMetrics']['launch'] = $_SESSION['endpointMetrics']['launch'] + 1;

?>