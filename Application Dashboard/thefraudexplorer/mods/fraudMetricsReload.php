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
 * Description: Code for fraud metrics reload
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";

$_SESSION['endpointFraudMetrics']['endpoint'] = filter($_GET['endpoint']);
$_SESSION['endpointFraudMetrics']['ruleset'] = filter($_GET['ruleset']);
$_SESSION['endpointFraudMetrics']['allbusiness'] = filter($_GET['allbusiness']);
$_SESSION['endpointFraudMetrics']['allendpoints'] = filter($_GET['allendpoints']);
$_SESSION['endpointFraudMetrics']['launch'] = $_SESSION['endpointFraudMetrics']['launch'] + 1;

?>