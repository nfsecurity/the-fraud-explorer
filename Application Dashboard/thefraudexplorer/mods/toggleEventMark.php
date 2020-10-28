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
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
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

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

$regid = filter(decRijndael($_GET['id']));
$endpoint = filter(decRijndael($_GET['nt']));
$index = filter(decRijndael($_GET['ex']));
$type = filter(decRijndael($_GET['pe']));
$urlrefer = filter($_GET['er']);
$msg = "";

if (!empty($_POST['toggle-event']))
{
    /* Query actual falsePositive value */

    $urlEventValue="http://localhost:9200/".$index."/".$type."/".$regid;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $urlEventValue);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultValues = curl_exec($ch);
    curl_close($ch);

    $jsonResultValue = json_decode($resultValues);
    $falsePositiveValue = $jsonResultValue->_source->falsePositive;
    $mark = 0;

    if ($falsePositiveValue == "0") $mark = 1;

    /* Toggle falsePositive value */

    $urlEvents="http://localhost:9200/".$index."/".$type."/".$regid."/_update?pretty&pretty";
    $params = '{ "doc" : { "falsePositive" : "'.$mark.'" } } }';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$urlEvents);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultEvents = curl_exec($ch);
    curl_close($ch);

    $msg = "Successfully toggled event";
}
else if (!empty($_POST['delete-event']))
{
    /* Delete alert from t_inferences table */

    $queryDeleteAIAlert = "DELETE FROM t_inferences WHERE alertid='".$regid."'";        
    $resultQuery = mysqli_query($connection, $queryDeleteAIAlert);

    /* Delete alert from t_wtriggers table */

    $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$regid."%'";        
    $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);

    /* Delete endpoint elasticsearch documents */

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9200/".$index."/".$type."/".$regid); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch); 
    curl_close($ch); 

    $msg = "Successfully removed event";
}

$_SESSION['wm'] = encRijndael($msg);

/* Return to refering url */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>