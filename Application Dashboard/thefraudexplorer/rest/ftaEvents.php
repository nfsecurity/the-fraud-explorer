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
 * Description: REST Webservice for FTA Events
 */

include "authValidation.php";
include "functions.php";
include "/var/www/html/thefraudexplorer/lbs/cryptography.php";
include "/var/www/html/thefraudexplorer/lbs/elasticsearch.php";
require 'vendor/autoload.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method)
{
    case 'GET':
        if (isset($_GET['endpoint']))
        {
            $username = $headers['username'];

            ftaEventsGETQuery($username, filter($_GET['endpoint']));
        }
        else if (isset($_GET['ai']))
        {
            $username = $headers['username'];

            aiAlertsGETQuery($username);
        }
        else echo json_encode("You must specify the endpoint or ai parameter");
        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: GET');
        break;
}

?>
