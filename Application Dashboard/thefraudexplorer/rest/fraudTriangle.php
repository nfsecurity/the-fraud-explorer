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
 * Description: REST Webservice for Fraud Triangle Processing
 */

include "authValidation.php";
include "functions.php";
include "/var/www/html/thefraudexplorer/lbs/cryptography.php";

$method = $_SERVER['REQUEST_METHOD'];

switch($method)
{
    case 'POST':
        $receivedJSON = file_get_contents("php://input",  TRUE);

        fraudTrianglePOSTQuery($receivedJSON);

        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: POST');
        break;
}

?>
