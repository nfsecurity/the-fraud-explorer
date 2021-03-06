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
 * Description: REST Webservice for Workflow Events
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
        if (isset($_GET['list']))
        {
            $username = $headers['username'];

            workflowsList($username);
        }
        else if (isset($_GET['name']))
        {
            $username = $headers['username'];

            workflowsGet($username, filter($_GET['name']));
        }
        else echo json_encode("You must specify the list or name parameter");
        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: GET');
        break;
}

?>
