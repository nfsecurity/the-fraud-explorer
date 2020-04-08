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
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for Build Mail Alerts
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
include "../lbs/openDBconn.php";

/* POST Variables */

if (isset($_POST['smtpserver'])) $smtpServer = filter($_POST['smtpserver']);
if (isset($_POST['smtpuserpass'])) $smtpUserPass = filter($_POST['smtpuserpass']);
if (isset($_POST['mailaddress'])) $mailAddress = filter($_POST['mailaddress']);

if (isset($_POST['smtpserver']) && isset($_POST['smtpuserpass']) && isset($_POST['mailaddress']))
{
    $configFile = parse_ini_file("../config.ini");
    $mailAdress_configFile = $configFile['mail_address'];
    $smtpServer_configFile = $configFile['mail_smtp'];
    $smtpUserPass_configFile = $configFile['mail_userpass'];

    $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$mailAdress_configFile.'/'.$mailAddress.'/g;s/'.$smtpServer_configFile.'/'.$smtpServer.'/g;s/'.$smtpUserPass_configFile.'/'.$smtpUserPass.'/g" --in-place '.$documentRoot.'config.ini /etc/postfix/private/canonical /etc/postfix/private/sasl_passwd /etc/postfix/private/sender_relay';
    $commandReplacements = shell_exec($replaceParams);

    $postmapCommand = 'cd /etc/postfix/private ; /usr/bin/sudo /usr/sbin/postmap /etc/postfix/private/canonical ; /usr/bin/sudo /usr/sbin/postmap /etc/postfix/private/sender_relay ; /usr/bin/sudo /usr/sbin/postmap /etc/postfix/private/sasl_passwd';
    $commandPostmap = shell_exec($postmapCommand);

     /* Send message test */

     $to = $mailAddress;
     $subject = "[The Fraud Explorer] Test email";
     $message = '<html>' .
    '<body>Greetings from The Fraud Explorer,<br><br>This is an email test message to verify the server configuration. It is workig so good!<br>' .
    '</body><br>You could receive general messages and Artificial Intelligence alerts, best regards.<br><br><b>The Fraud Explorer Team</b><br><a href="https://www.thefraudexplorer.com">thefraudexplorer.com</a><br>support@thefraudexplorer.com</html>';
    $headers = "From: " . $configFile['mail_address'] . "\r\n" .
    "Reply-To: " . $configFile['mail_address'] . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

     mail($to, $subject, $message, $headers);
}

header ("location: ../dashBoard");

?>
