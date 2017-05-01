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
 * Description: Code for false positive marking
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";

$regid=filter($_GET['regid']);
$agent=filter($_GET['agent']);
$index=filter($_GET['index']);
$type=filter($_GET['type']);

/* Query actual falsePositive value */

$urlAlertValue="http://localhost:9200/".$index."/".$type."/".$regid;
    
$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $urlAlertValue);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
$resultValues=curl_exec($ch);
curl_close($ch);

$jsonResultValue = json_decode($resultValues);
$falsePositiveValue = $jsonResultValue->_source->falsePositive;
$mark = 0;

if ($falsePositiveValue == "0") $mark = 1;

/* Toggle falsePositive value */

$urlAlerts="http://localhost:9200/".$index."/".$type."/".$regid."/_update?pretty&pretty";
$params = '{ "doc" : { "falsePositive" : "'.$mark.'" } } }';

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$urlAlerts);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
$resultAlerts=curl_exec($ch);
curl_close($ch);

/* Return to home */

header ("location: alertData?agent=".$agent);

?>

</body>
</html>