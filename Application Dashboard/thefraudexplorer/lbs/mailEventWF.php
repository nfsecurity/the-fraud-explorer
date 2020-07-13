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
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Mail events
*/

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$messageComplement = null;

$to = $custodian;
$subject = "[The Fraud Explorer] Flow: " . $alert_workflowName . ", from: " . $alert_eventEndpoint[0] ."@".$alert_eventCompany[0];

for ($i=0; $i<$alert_amount; $i++)
{
    $messageComplement = $messageComplement . '<tr>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . $alert_eventDate[$i] . '</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . $alert_eventEndpoint[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . $alert_eventCompany[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . $alert_eventType[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . substr($alert_eventApplication[$i], 0, 30) . ' ...' . '</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F;">' . substr($alert_eventPhrase[$i], 0, 30) . ' ...' . '</td>';
    $messageComplement = $messageComplement . '</tr>';
}

$message = '<html>' .
    '<body>Greetings from The Fraud Explorer,<br><br>The workflow <b>'.$alert_workflowName.'</b> has triggered the following alert:<br><br>' .
    '<table border="1" style="background-color:#FFFFFF;border-collapse:collapse;border:1px solid #33CC00;color:#000000;width:100%" ' .
    'cellpadding="8" cellspacing="3"> ' .
    '<tr>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Date</b></td>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Employee</b></td>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Company</b></td>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Vertice</b></td>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Application</b></td>' .
    '<td style="background-color:#4B906F; border:1px solid #4B906F; color: white;"><b>Phrase</b></td>' .
    '</tr>' .
    $messageComplement .
    '</table>' .
    '</body><br>You can review this alert in the Workflows module, best regards.<br><br><b>The Fraud Explorer Team</b><br><a href="https://www.thefraudexplorer.com">thefraudexplorer.com</a><br>support@thefraudexplorer.com</html>';

$headers = "From: " . $configFile['mail_address'] . "\r\n" .
    "Reply-To: " . $configFile['mail_address'] . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

?>