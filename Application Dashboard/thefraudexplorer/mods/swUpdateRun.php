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
 * Date: 2020-02
 * Revision: v1.4.2-aim
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

include "../lbs/globalVars.php";

$_SESSION['processingStatus'] = "pending";
$repository = $_POST['urlrepo'];

if (isset($_POST['urlrepo']))
{
    $runUpdate = '/usr/bin/sudo /usr/bin/sh '.$documentRoot.'update/script/runUpdate.sh '.$repository;
    exec($runUpdate, $output, $return);

    if ($return == 0) 
    {
        /* Change the updated version inf config.ini file */

        $URLConfigFile = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/config.ini";
        $repoConfigFile = file_get_contents($URLConfigFile);
        preg_match('/sw_version = "(.*)"/', $repoConfigFile, $repoVersion);

        $configFile = parse_ini_file("../config.ini");
        $swVersion_configFile = $configFile['sw_version'];

        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$swVersion_configFile.'/'.$repoVersion.'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = shell_exec($replaceParams);
    }
}

$_SESSION['processingStatus'] = "finished";

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>