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
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: REST Webservice for Enpoints
 */

include "authValidation.php";
include "functions.php";
include "/var/www/html/thefraudexplorer/lbs/cryptography.php";

$method = $_SERVER['REQUEST_METHOD'];

switch($method)
{
    case 'PUT':
        if (isset($_GET['query']) && isset($_GET['token']) && isset($_GET['os']) && isset($_GET['v']) && isset($_GET['domain']) && isset($_GET['id']) && isset($_GET['ip']))
        {
            $params = $_GET['query'];
            $username = $headers['username'];

            if ($params == "create" || $params == "update")
            {
                endPointsPUTQuery($username, $_GET['token'], $_GET['os'], $_GET['v'], $_GET['domain'], $_GET['id'], $_GET['ip']);
            }
            else echo json_encode("Invalid query, invoke create method");
        }
        else echo json_encode("Insufficient parameters");
        break;
    case 'DELETE':
        if (isset($_GET['query']) && isset($_GET['endpoint']))
        {
            $params = $_GET['query'];
            $username = $headers['username'];

            if ($params == "delete")
            {
                endPointsDELETEQuery($username, $_GET['endpoint']);
            }
            else echo json_encode("Invalid query, invoke delete method");
        }
        else echo json_encode("Insufficient parameters");
        break;
    case 'GET':
        if (isset($_GET['query']))
        {
            $params = $_GET['query'];
            $username = $headers['username'];

            endPointsGETQuery($params, $username);
        }
        else echo json_encode("You must specify the query parameter");
        break;
    case 'POST':
        if (isset($_GET['id']))
        {
            $endpoint = $_GET['id'];
            $receivedJSON = file_get_contents("php://input",  TRUE);

            endPointsPOSTQuery($endpoint, $receivedJSON);
        }
        else echo json_encode("You must specify the endpoint id");
        break;
    default:
        header('HTTP/1.1 405 Method not allowed');
        header('Allow: GET, PUT, POST, DELETE');
        break;
}

?>
