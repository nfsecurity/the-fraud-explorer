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
 * Date: 2020-01
 * Revision: v1.4.1-ai
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

include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

if (isset($_POST['reviewPhrasesTextArea']))
{
    $textArea = encRijndael(filter_var($_POST['reviewPhrasesTextArea'], FILTER_SANITIZE_STRING));

    if (isset($_GET['id'])) $documentId = filter($_GET['id']);
    if (isset($_GET['idx'])) $indexId = filter($_GET['idx']);

    $urlReview = "http://localhost:9200/".$indexId."/AlertEvent/".$documentId."/_update?pretty&pretty";
    $params = '{ "doc" : { "stringHistory" : "'.$textArea.'" } }';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $urlReview);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultReview = curl_exec($ch);
    curl_close($ch);
}   
    
header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>