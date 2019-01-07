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
 * Description: Mail events
*/

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");

$to      = $configFile['mail_to'];
$finalDomain = substr($domain, 0, strpos($domain, "."));
$subject = $configFile['mail_subject'].rtrim($endPoint, "*")."@".$finalDomain;

/* Translate reason */

switch($matchReason)
{
    case 'POR' :  
        $reasonTranslated = "Pressure, Opportunity and Rationalization";
        break;
    case 'PO' :  
        $reasonTranslated = "Pressure and Opportunity";
        break;
    case 'PR' :  
        $reasonTranslated = "Pressure and Rationalization";
        break;
    case 'OR' :  
        $reasonTranslated = "Opportunity and Rationalization";
        break;
}

$message = '<html>' .
    '<body>Greetings from The Fraud Explorer,<br><br>The artificial intelligence expert system has triggered the following alert:<br><br>' .
    '<table border="1" style="background-color:#FFFFFF;border-collapse:collapse;border:1px solid #33CC00;color:#000000;width:100%" ' .
    'cellpadding="8" cellspacing="3"> ' .
    '<tr>' .
    '<td style="background-color:#18832F; border:1px solid #18832F; color: white;"><b>Date</b></td>' .
    '<td style="background-color:#18832F; border:1px solid #18832F; color: white;"><b>Endpoint</b></td>' .
    '<td style="background-color:#18832F; border:1px solid #18832F; color: white;"><b>Deduction Reason</b></td>' .
    '<td style="background-color:#18832F; border:1px solid #18832F; color: white;"><b>Ruleset</b></td>' .
    '<td style="background-color:#18832F; border:1px solid #18832F; color: white;"><b>Probability</b></td>' .
    '</tr>' .
    '<tr>' .
    '<td style="background-color:#FFFFFF; border:1px solid #18832F;">' . $timeStamp . '</td>' .
    '<td style="background-color:#FFFFFF; border:1px solid #18832F;">' . rtrim($endPoint, "*").'@'.$finalDomain.'</td>' .
    '<td style="background-color:#FFFFFF; border:1px solid #18832F;">' . $reasonTranslated .'</td>' .
    '<td style="background-color:#FFFFFF; border:1px solid #18832F;">' . $ruleset .'</td>' .
    '<td style="background-color:#FFFFFF; border:1px solid #18832F;">' . $fraudProbDeduction . ' %</td>' .
    '</tr>' .
    '</table>' .
    '</body><br>You can review this alert in the main Dashboard, best regards.<br><br><b>The Fraud Explorer Team</b><br><a href="https://www.thefraudexplorer.com">thefraudexplorer.com</a><br>support@thefraudexplorer.com</html>';

$headers = $configFile['mail_from'] . "\r\n" .
    $configFile['mail_reply_to'] . "\r\n" .
    'MIME-Version: 1.0' . "\r\n" .
    'Content-Type: text/html; charset=ISO-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

?>