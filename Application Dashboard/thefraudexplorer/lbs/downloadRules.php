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
 * Date: 2018-12
 * Revision: v1.2.0
 *
 * Description: Download multiple files
 */

include "global-vars.php";

$zip = new ZipArchive();
$filename = $documentRoot."/core/ziprules/thefraudexplorer-rules.zip";

if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) 
{
    exit("cannot open <$filename>\n");
}

$dir = '../core/rules/';

if (is_dir($dir))
{
    if ($dh = opendir($dir))
    {
        while (($file = readdir($dh)) !== false)
        {
            if (is_file($dir.$file)) 
            {
                if($file != '' && $file != '.' && $file != '..')
                {
                    $zip->addFile($dir.$file);
                }
            }
 
        }
        closedir($dh);
    }
}

$zip->close();

echo "authAccess?file=".$filename;
