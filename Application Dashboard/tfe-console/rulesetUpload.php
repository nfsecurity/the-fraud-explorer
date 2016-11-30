<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for ruleset file upload
 */

session_start();

include "inc/check_perm.php";
include "inc/global-vars.php";

if(empty($_SESSION['connected']))
{
        header ("Location: ".$serverURL);
        exit;
}

$target_dir = "tfe-alerter/rules/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$fileType = pathinfo($target_file, PATHINFO_EXTENSION);

if($fileType != "json") exit;
else 
{
	move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file);
	header ("location:  dashBoard");
}

?>
