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
 * Description: Code for run update procedure
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
include "../lbs/cryptography.php";

$_SESSION['processingStatus'] = "pending";
$repository = filter($_POST['urlrepo']);
$msg = "";

if (isset($_POST['urlrepo']))
{
    $runUpdate = '/usr/bin/sudo /usr/bin/sh '.$documentRoot.'update/script/runUpdate.sh '.$repository;
    exec($runUpdate, $output, $return);

    if ($return == 0) 
    {
        /* Change the updated version in config.ini file */

        $URLConfigFile = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/config.ini";
        $repoConfigFile = file_get_contents($URLConfigFile);
        preg_match('/sw_version = "(.*)"/', $repoConfigFile, $repoVersion);

        $configFile = parse_ini_file("../config.ini");
        $swVersion_configFile = $configFile['sw_version'];

        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$swVersion_configFile.'/'.$repoVersion[1].'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = shell_exec($replaceParams);

        $msg = $repoVersion[1];
        auditTrail("update", "successfully updated thefraudexplorer platform to ".$msg);
    }
}

$_SESSION['processingStatus'] = "finished";
$_SESSION['wm'] = encRijndael("Successfully updated to version ".$msg);

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>