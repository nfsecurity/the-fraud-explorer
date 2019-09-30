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
 * Date: 2019-05
 * Revision: v1.3.3-ai
 *
 * Description: Code for false positive marking
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";

$regid=filter($_GET['regid']);
$endpoint=filter($_GET['endpoint']);
$index=filter($_GET['index']);
$type=filter($_GET['type']);
$urlrefer=filter($_GET['urlrefer']);

if (!empty($_POST['toggle-event']))
{
    /* Query actual falsePositive value */

    $urlEventValue="http://localhost:9200/".$index."/".$type."/".$regid;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $urlEventValue);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultValues=curl_exec($ch);
    curl_close($ch);

    $jsonResultValue = json_decode($resultValues);
    $falsePositiveValue = $jsonResultValue->_source->falsePositive;
    $mark = 0;

    if ($falsePositiveValue == "0") $mark = 1;

    /* Toggle falsePositive value */

    $urlEvents="http://localhost:9200/".$index."/".$type."/".$regid."/_update?pretty&pretty";
    $params = '{ "doc" : { "falsePositive" : "'.$mark.'" } } }';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$urlEvents);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultEvents=curl_exec($ch);
    curl_close($ch);
}
else if (!empty($_POST['delete-event']))
{
    /* Delete endpoint elasticsearch documents */

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9200/".$index."/".$type."/".$regid); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch); 
    curl_close($ch); 
}

/* Return to refering url */

if ($urlrefer == "allevents") header ("location: ../eventData?endpoint=".base64_encode(base64_encode("all")));
else header ("location: ../eventData?endpoint=".base64_encode(base64_encode($endpoint)));

?>

</body>
</html>