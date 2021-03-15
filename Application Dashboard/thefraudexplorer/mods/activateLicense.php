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
 * Description: Code for license activation
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

$msg = "";

if (isset($_POST['serial']))
{
    $configFile = parse_ini_file("../config.ini");
    $currentSerial = $configFile['pl_serial'];
    $newSerial = $_POST['serial'];

    /* Query license capabilities */

    $serverAddress = "https://licensing.thefraudexplorer.com/validateSerial.php";

    $postRequest = array(
        'serial' => $newSerial,
        'capabilities' => "false",
        'retrieve' => "false"
    );

    $payload = json_encode($postRequest);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serverAddress);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

    $headers = [
        'Content-Type: application/json',
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $server_output = curl_exec($ch);
    curl_close($ch);
    $replyJSON = json_decode($server_output, true);
    $validOrNot = $replyJSON['Valid'];
    $validUntil = strtotime($replyJSON['Until']);
    $today = date("Y-m-d H:i:s");

    if ($validOrNot == "true")
    {
        if ($today < $validUntil)
        {
            $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$currentSerial.'/'.$newSerial.'/g" --in-place '.$documentRoot.'config.ini';
            $commandReplacements = shell_exec($replaceParams);

            $msg = "Serial number successfully activated";
            auditTrail("license", "serial number successfully activated");
        }
        else
        {
            $msg = "Your license has expired, contact support";
            auditTrail("license", "serial number license has expired");
        }
    }
    else 
    {
        $msg = "Invalid phrase library serial number";
        auditTrail("license", "invalid phrase library serial number");
    }
    
}

$_SESSION['wm'] = encRijndael($msg);

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>