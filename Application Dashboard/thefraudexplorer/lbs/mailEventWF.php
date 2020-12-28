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
 * Description: Mail events
*/

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$messageComplement = null;

$to = $custodian;
$subject = "[The Fraud Explorer] Flow: " . $alert_workflowName . ", from: " . $alert_eventEndpoint[0] ."@".$alert_eventCompany[0];

for ($i=0; $i<$alert_amount; $i++)
{
    if ($alert_eventTone[$i] == 0) $eventTone = "neutral";
    else $eventTone = "negative";

    if ($alert_eventFlag[$i] == 0) $eventFlag = "no";
    else $eventFlag = "yes";

    $applicationXmail = substr($alert_eventApplication[$i], 0, 30) . ' ...';
    $expressionXmail = substr($alert_eventPhrase[$i], 0, 30) . ' ...';

    $messageComplement = $messageComplement . '<tr>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $alert_eventDate[$i] . '</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $alert_eventEndpoint[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $alert_eventCompany[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $alert_eventType[$i] .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $applicationXmail . '</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $expressionXmail . '</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $eventTone .'</td>';
    $messageComplement = $messageComplement . '<td style="background-color:#FFFFFF; border:1px solid #4B906F; font-size: 12px; padding: 4px 2px 4px 2px;">' . $eventFlag .'</td>';
    $messageComplement = $messageComplement . '</tr>';
}

$message = '<html>' .
    '<body>Greetings from The Fraud Explorer,<br><br>The workflow <b>'.$alert_workflowName.'</b> has triggered the following alert:<br><br>' .
    '<table style="border-collapse: collapse; border: 1px solid #4B906F; width: 100%;">' .
    '<tr>' .
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Date</b></th>' .
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Employee</b></th>' . 
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Company</b></th>' . 
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Vertice</b></th>' . 
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Application</b></th>' .
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Expression</b></th>' . 
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Tone</b></th>' . 
    '<th style="background-color: #4B906F; color: white; padding: 9px 2px 9px 2px; text-align: left;"><b>Flag</b></th>' . 
    '</tr>' .
    $messageComplement .
    '</table>' .
    '</body><br>You can review this alert in the Workflows module, best regards.<br><br><b>The Fraud Explorer Team</b><br><a href="https://www.thefraudexplorer.com">thefraudexplorer.com</a><br>support@thefraudexplorer.com</html>';

$headers = "From: " . $configFile['mail_address'] . "\r\n" .
    "Reply-To: " . $configFile['mail_address'] . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/html; charset=UTF8' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

?>