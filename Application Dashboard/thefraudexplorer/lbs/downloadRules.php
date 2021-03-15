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
 * Description: Download multiple files
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

auditTrail("library", "downloaded phrase library to user local computer");

$pathdir = $documentRoot."/core/rules/";  
$zipcreated = $documentRoot."/core/ziprules/thefraudexplorer-rules.zip";

$zip = new ZipArchive; 
   
if($zip -> open($zipcreated, ZipArchive::CREATE ) === TRUE) 
{ 
    $dir = opendir($pathdir); 
       
    while($file = readdir($dir)) 
    { 
        if(is_file($pathdir.$file)) 
        { 
            $zip -> addFile($pathdir.$file, $file); 
        } 
    } 

    closedir($dir);
    $zip -> close(); 
}

echo "helpers/authAccess?file=".$zipcreated;

?> 
