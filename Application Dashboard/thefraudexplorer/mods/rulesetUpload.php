<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for ruleset file upload
 */

include "../lbs/login/session.php";
include "../lbs/security.php";
include "../lbs/cryptography.php";

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

auditTrail("library", "uploaded phrase library file, replacing current one");

$target_dir = "../core/rules/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$fileType = pathinfo($target_file, PATHINFO_EXTENSION);

if($fileType != "json")
{
    $_SESSION['wm'] = encRijndael("Invalid ruleset file format structure");

    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
else
{
    $_SESSION['wm'] = encRijndael("Successfully uploaded ruleset file");

    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}

?>