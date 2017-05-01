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
 * Description: Code for ruleset file upload
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";

$target_dir = "core/rules/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$fileType = pathinfo($target_file, PATHINFO_EXTENSION);

if($fileType != "json") exit;
else
{
    move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
    header ("location:  endPoints");
}

?>