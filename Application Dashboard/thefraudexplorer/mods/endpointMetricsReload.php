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
 * Date: 2020-02
 * Revision: v1.4.2-aim
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

include "../lbs/globalVars.php";

$_SESSION['endpointMetrics']['endpoint'] = filter($_GET['endpoint']);
$_SESSION['endpointMetrics']['pressure'] = filter($_GET['pressure']);
$_SESSION['endpointMetrics']['opportunity'] = filter($_GET['opportunity']);
$_SESSION['endpointMetrics']['rationalization'] = filter($_GET['rationalization']);
@$_SESSION['endpointMetrics']['launch'] = $_SESSION['endpointMetrics']['launch'] + 1;

?>