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
 * Description: Code for download and view authorization
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";

$file=filter($_GET['file']);
$ext = substr($file, strrpos($file, '.')+1);

if(isset($_GET['ctype']) $contentType=filter($_GET['ctype']); 
else $contentType="aplication/octet-stream"; 

if(empty($_SESSION['connected']))
{
    header ("Location: ".$serverURL);
    exit;
}
else
{
    /* Grant access to this type of file depending of the session status */

    if($ext=="txt")
    {
        header('Content-Type: text/'.$contentType);
        flush();
        readfile($_REQUEST['file']);
        exit();
    }
    else if($ext=="html" || $ext=="htm")
    {
        header('Content-Type: text/'.$contentType);
        flush();
        readfile($_REQUEST['file']);
        exit();
    }
    else if($ext=="png")
    {
        header('Content-Type: image/'.$contentType);
        flush();
        readfile($_REQUEST['file']);
        exit();
    }
    else
    {
        if (file_exists($file))
        {
            header("Expires: 0");
            header("Content-Description: File Transfer");
            header("Content-type: ".$contentType );
            header("Content-Disposition: attachment; filename=$file");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
    }
}

?>