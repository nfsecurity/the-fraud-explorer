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
 * Description: Mail events
*/

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");

$to = $custodian;
$subject = "[The Fraud Explorer] Workflow Alert: " . $name;

$message = '<html>' .
    '<body>Greetings from The Fraud Explorer,<br><br>The workflow engine has triggered and alert related to the flow: '.$name.'<br>' .
    '</body><br>You can review this alert contacting the person in charge, you can\'t view it directly due to confidential and posible sensitive data, best regards.<br><br><b>The Fraud Explorer Team</b><br><a href="https://www.thefraudexplorer.com">thefraudexplorer.com</a><br>support@thefraudexplorer.com</html>';

$headers = "From: " . $configFile['mail_address'] . "\r\n" .
    "Reply-To: " . $configFile['mail_address'] . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

?>