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
 * Description: Code for phrase review
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

include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

$documentId = filter($_GET['id']);
$indexId = filter(decRijndael($_GET['ex']));
$type = "AlertEvent";
$msg = "";

if (!empty($_POST['review-save']))
{
    if (isset($_POST['reviewPhrasesTextArea']))
    {
        $textArea = encRijndael(filter_var($_POST['reviewPhrasesTextArea'], FILTER_SANITIZE_STRING));

        $urlReview = "http://localhost:9200/".$indexId."/AlertEvent/".$documentId."/_update?pretty&pretty";
        $params = '{ "doc" : { "stringHistory" : "'.$textArea.'" } }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlReview);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultReview = curl_exec($ch);
        curl_close($ch);

        $msg = "Successfully saved redflag event";
    }
}
else if (!empty($_POST['delete-event']))
{
    /* Delete alert from t_inferences table */

    $queryDeleteAIAlert = "DELETE FROM t_inferences WHERE alertid='".$documentId."'";        
    $resultQuery = mysqli_query($connection, $queryDeleteAIAlert);

    /* Delete alert from t_wtriggers table */

    $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$documentId."%'";        
    $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);

    /* Delete endpoint elasticsearch documents */

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:9200/".$indexId."/".$type."/".$documentId); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($ch); 
    curl_close($ch);

    $msg = "Successfully removed redflag event";
}

else if (!empty($_POST['relevancy']))
{
    $urlEventValue="http://localhost:9200/".$indexId."/".$type."/".$documentId;
    
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

    $urlEvents="http://localhost:9200/".$indexId."/".$type."/".$documentId."/_update?pretty&pretty";
    $params = '{ "doc" : { "falsePositive" : "'.$mark.'" } } }';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL,$urlEvents);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultEvents = curl_exec($ch);
    curl_close($ch);

    $msg = "Successfully changed event relevancy";
}

$_SESSION['wm'] = encRijndael($msg);
    
header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>