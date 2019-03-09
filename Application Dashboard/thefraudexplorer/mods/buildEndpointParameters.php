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
 * Date: 2019-02
 * Revision: v1.3.1-ai
 *
 * Description: Code for endpoint generation
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

/* Configuration variables */

$finalPlatformForBuild = null;
$finalServerHTTPSAddress = null;
$finalPCEnabled = null;
$finalIPAddress = null;
$finalCryptKey = null;
$finalSrvPwd = null;

/* Platform */

if (isset($_POST['platform'])) $finalPlatformForBuild = filter($_POST['platform']);

/* HTTPS Address */

if (isset($_POST['address'])) $finalServerHTTPSAddress = filter($_POST['address']);

$finalServerHTTPSAddress = str_replace('/', '\/', $finalServerHTTPSAddress);

/* Phrase collection enabled or disabled */

if (isset($_POST['pcenabled']))
{
    $phraseValue = filter($_POST['pcenabled']);

    if ($phraseValue == "enable") $finalPCEnabled = "1";
    else $finalPCEnabled = "0";
}

/* IP ADDRESS */

if (isset($_POST['ip'])) $finalIPAddress = filter($_POST['ip']);

/*  CRYPT KEY RIJNDAEL */

$cryptKeyQuery = mysqli_query($connection, sprintf("SELECT iv FROM t_crypt"));

if ($row = mysqli_fetch_array($cryptKeyQuery)) $finalCryptKey = $row[0];

/*  Server password */

$srvpwdQuery = mysqli_query($connection, sprintf("SELECT password FROM t_crypt"));

if ($row = mysqli_fetch_array($srvpwdQuery)) $finalSrvPwd = $row[0]; 

/* Replace data in the MSI XML template */

$replaceParams = '/usr/bin/sudo /usr/bin/sed "s/1337/'.$finalPCEnabled.'/g;s/1uBu8ycVugDIJz61/'.$finalCryptKey.'/g;s/KGBz77/'.$finalSrvPwd.'/g;s/https:\/\/cloud.thefraudexplorer.com\/update.xml/'.$finalServerHTTPSAddress.'/g;s/10.1.1.253/'.$finalIPAddress.'/g" '.$documentRoot.'msi/endpointInstaller.xml > '.$documentRoot.'msi/endpointInstallerForDownload.xml';
$commandReplacements = shell_exec($replaceParams);

/* Generate the final MSI for Download */

$buildMSI = 'cd '.$documentRoot.'msi ; /usr/bin/sudo /usr/bin/wine '.$documentRoot.'msi/bin/xml2msi.exe endpointInstallerForDownload.xml';
$commandMSI = shell_exec($buildMSI);

/* Close DB Connections */

include "../lbs/closeDBconn.php";

/* Auto download */

$msiFile = $documentRoot.'msi/endpointInstallerForDownload.MSI';

if (file_exists($msiFile)) 
{
    $original_filename = $documentRoot.'msi/endpointInstallerForDownload.MSI';
    $new_filename = 'endpointInstaller.msi';

    header("Content-Type: application/octet-stream");
    header('Content-Transfer-Encoding: binary');
    header("Content-Length: " . filesize($original_filename));
    header('Content-Disposition: attachment; filename="' . $new_filename . '"');

    readfile($original_filename);
    exit;
} 
?>

</body>
</html>