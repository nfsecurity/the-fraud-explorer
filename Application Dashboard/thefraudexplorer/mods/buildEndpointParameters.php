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
$finalExcludedApps = null;
$finalPCEnabled = null;
$finalCryptKey = null;
$finalSrvPwd = null;
$finalRESTcredentials = null;
$finalCompanyDomain = null;
$finalRESTusername = null;
$finalRESTpassword = null;

/* Platform */

if (isset($_POST['platform'])) $finalPlatformForBuild = filter($_POST['platform']);

/* HTTPS Address */

if (isset($_POST['address'])) $finalServerHTTPSAddress = filter($_POST['address']);
if ($finalPlatformForBuild == "windows") $finalServerHTTPSAddress = $finalServerHTTPSAddress . "/update.xml";

$finalServerHTTPSAddress = str_replace('/', '\/', $finalServerHTTPSAddress);

/* Phrase collection enabled or disabled */

if (isset($_POST['pcenabled']))
{
    $phraseValue = filter($_POST['pcenabled']);

    if ($phraseValue == "enable") $finalPCEnabled = "1";
    else $finalPCEnabled = "0";
}

/*  CRYPT KEY RIJNDAEL */

$cryptKeyQuery = mysqli_query($connection, sprintf("SELECT iv FROM t_crypt"));

if ($row = mysqli_fetch_array($cryptKeyQuery)) $finalCryptKey = $row[0];

/*  Server password */

$srvpwdQuery = mysqli_query($connection, sprintf("SELECT password FROM t_crypt"));

if ($row = mysqli_fetch_array($srvpwdQuery)) $finalSrvPwd = $row[0]; 

/* Excluded Apps */

if (isset($_POST['excluded']) && $_POST['excluded'] != '' && $_POST['excluded'] != " ") $finalExcludedApps = filter($_POST['excluded']);
else $finalExcludedApps = "NoExcludedApps";

/* COMPANY DOMAIN */

if (isset($_POST['companydomain'])) $finalCompanyDomain = filter($_POST['companydomain']);

/* REST CREDENTIALS */

if (isset($_POST['restcredentials'])) 
{
    $finalRESTcredentials = filter($_POST['restcredentials']);
    $credentialsArray = explode(':', $finalRESTcredentials);
    $finalRESTusername = $credentialsArray[0];
    $finalRESTpassword = $credentialsArray[1];
}

/* Start build logic */

if ($finalPlatformForBuild == "windows")
{
    /* Replace data in the MSI XML template */

    $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/1337/'.$finalPCEnabled.'/g;s/1uBu8ycVugDIJz61/'.$finalCryptKey.'/g;s/KGBz77/'.$finalSrvPwd.'/g;s/https:\/\/cloud.thefraudexplorer.com\/update.xml/'.$finalServerHTTPSAddress.'/g;s/NoExcludedApps/'.$finalExcludedApps.'/g" '.$documentRoot.'endpoints/msi/endpointInstaller.xml > '.$documentRoot.'endpoints/msi/endpointInstallerForDownload.xml';
    $commandReplacements = shell_exec($replaceParams);

    /* Generate the final MSI for Download */

    $buildMSI = 'cd '.$documentRoot.'endpoints/msi ; /usr/bin/sudo /usr/bin/wine '.$documentRoot.'endpoints/msi/bin/xml2msi.exe endpointInstallerForDownload.xml';
    $commandMSI = shell_exec($buildMSI);

    /* Auto download */

    $msiFile = $documentRoot.'endpoints/msi/endpointInstallerForDownload.MSI';

    if (file_exists($msiFile)) 
    {
        $original_filename = $documentRoot.'endpoints/msi/endpointInstallerForDownload.MSI';
        $new_filename = 'endpointInstaller.msi';

        header("Content-Type: application/octet-stream");
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: " . filesize($original_filename));
        header('Content-Disposition: attachment; filename="' . $new_filename . '"');

        readfile($original_filename);
        exit;
    }
}
else if ($finalPlatformForBuild == "android")
{
    /* Replace data in the AndroidManifest XML template */

    $analyticsEnabled = ($finalPCEnabled == 1 ? "enabled" : "disabled");
    $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/androidcipherKey/'.$finalCryptKey.'/g; s/enabled/'.$analyticsEnabled.'/g;s/androidserverPassword/'.$finalSrvPwd.'/g;s/androidserverAddress/'.$finalServerHTTPSAddress.'/g;s/androidRESTusername/'.$finalRESTusername.'/g;s/androidRESTpassword/'.$finalRESTpassword.'/g;s/androidcompanyDomain/'.$finalCompanyDomain.'/g" '.$documentRoot.'endpoints/apk/AndroidManifestTemplate.xml > '.$documentRoot.'endpoints/apk/androidEndpointTemplate/AndroidManifest.xml';
    $commandReplacements = shell_exec($replaceParams);

    /* Generate the final APK for Download */

    $buildAPK = 'cd '.$documentRoot.'endpoints/apk ; /usr/bin/sudo /usr/bin/chmod 777 androidEndpointTemplate/AndroidManifest.xml ; /usr/bin/sudo /usr/bin/chown apache:apache androidEndpointTemplate/AndroidManifest.xml ; /usr/bin/sudo /usr/local/bin/apktool b androidEndpointTemplate ; /usr/bin/sudo /usr/bin/jarsigner -sigalg SHA1withRSA -digestalg SHA1 -keystore keyStore.keystore androidEndpointTemplate/dist/androidEndpointTemplate.apk app --storepass XecmcD56Z4BjFEQC --keypass XecmcD56Z4BjFEQC ; /usr/bin/sudo /usr/bin/chmod 777 androidEndpointTemplate/AndroidManifest.xml ; /usr/bin/sudo /usr/bin/chown apache:apache androidEndpointTemplate/AndroidManifest.xml';
    $commandAPK = shell_exec($buildAPK);

    /* Auto download */

    $apkFile = $documentRoot.'endpoints/apk/androidEndpointTemplate/dist/androidEndpointTemplate.apk';

    if (file_exists($apkFile)) 
    {
        $original_filename = $documentRoot.'endpoints/apk/androidEndpointTemplate/dist/androidEndpointTemplate.apk';
        $new_filename = 'androidEndpoint.apk';

        header("Content-Type: application/octet-stream");
        header('Content-Transfer-Encoding: binary');
        header("Content-Length: " . filesize($original_filename));
        header('Content-Disposition: attachment; filename="' . $new_filename . '"');

        readfile($original_filename);
        exit;
    }
}

/* Close DB Connections */

include "../lbs/closeDBconn.php";

?>

</body>
</html>