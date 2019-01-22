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
 * Description: REST functions
 */

/* Input validation */

function filter($variable)
{
    global $dbConnection;

    return mysqli_real_escape_string($dbConnection, $variable);
}

/* MySQL Query or Die */

function queryOrDie($query)
{
    global $dbConnection;

    $query = mysqli_query($dbConnection, $query);
    if (! $query) exit(mysqli_error($dbConnection));
    return $query;
}

/* Endpoints GET function */

function endPointsGETQuery($query, $username)
{
    global $dbConnection;
    $counterRows = 0;
    $resultArray[] = array();

    if ($query == "all")
    {
        if (getUserContext($username) == "all")
        {
            $sqlQuery = mysqli_query($dbConnection, "SELECT * FROM t_agents");

            if (mysqli_num_rows($sqlQuery) == 0) echo json_encode("No endpoint matches with your criteria");
            else 
            {
                while($row = mysqli_fetch_assoc($sqlQuery)) 
                {
                    $resultArray[] = $row;
    
                    array_walk_recursive($resultArray, function (&$item, $key) 
                    {
                        $item = null === $item ? '' : $item;
                    });
                }
    
                echo json_encode($resultArray);
            }
        }
        else
        {
            $sqlQuery = mysqli_query($dbConnection, "SELECT * FROM t_agents WHERE domain = '".getUserContext($username)."'");

            if (mysqli_num_rows($sqlQuery) == 0) echo json_encode("No endpoint matches with your criteria");
            else 
            {
                while($row = mysqli_fetch_assoc($sqlQuery)) 
                {
                    $resultArray[] = $row;
    
                    array_walk_recursive($resultArray, function (&$item, $key) 
                    {
                        $item = null === $item ? '' : $item;
                    });
                }
    
                echo json_encode($resultArray);
            }
        }
    }
    else
    {
        if (getUserContext($username) == "all")
        {
            $sqlQuery = mysqli_query($dbConnection, "SELECT * FROM t_agents WHERE agent LIKE '".$query."\_%'");

            if (mysqli_num_rows($sqlQuery) == 0) echo json_encode("No endpoint matches with your criteria");
            else 
            {
                while($row = mysqli_fetch_assoc($sqlQuery)) 
                {
                    $resultArray[] = $row;

                    array_walk_recursive($resultArray, function (&$item, $key) 
                    {
                        $item = null === $item ? '' : $item;
                    });
                }

                echo json_encode($resultArray);
            } 
        }
        else
        {
            $sqlQuery = mysqli_query($dbConnection, "SELECT * FROM t_agents WHERE domain = '".getUserContext($username)."' AND agent LIKE '".$query."\_%'");

            if (mysqli_num_rows($sqlQuery) == 0) echo json_encode("No endpoint matches with your criteria");
            else 
            {
                while($row = mysqli_fetch_assoc($sqlQuery)) 
                {
                    $resultArray[] = $row;

                    array_walk_recursive($resultArray, function (&$item, $key) 
                    {
                        $item = null === $item ? '' : $item;
                    });
                }

                echo json_encode($resultArray);
            } 
        }
    }
}

/* Create endpoint */

function endPointsPUTQuery($username, $token, $operatingSystem, $version, $domainContext, $endpointID, $endpointIP)
{
    if ($username != "admin") 
    {
        echo json_encode("You don't have sufficient permissions to create endpoints");
        exit;
    }

    global $dbConnection;

    $os = filter($operatingSystem);
    $version = "v" . filter($version);
    $key = filter($token);
    $domain = filter($domainContext);
    $endpoint = filter($endpointID);
    $ipAddress = filter($endpointIP);
    $keyquery = mysqli_query($dbConnection, "SELECT password FROM t_crypt");
    $keypass = mysqli_fetch_array($keyquery);
    $sucessEndpoint = false;

    if ($key == $keypass[0])
    {
        $result = mysqli_query($dbConnection, "SELECT * FROM t_agents WHERE agent='".$endpoint."'");
        $countRows = mysqli_num_rows($result);
        $date = date('Y-M-d H:i:s');

        if($countRows > 0)
        {
            $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
            date_default_timezone_set($configFile['php_timezone']);
            $datecalendar = date('Y-m-d');
            $result = mysqli_query($dbConnection, "UPDATE t_agents SET heartbeat=now(), system='" . $os . "', version='" . $version . "', domain='" . $domain . "', ipaddress='" . $ipAddress . "' WHERE agent='".$endpoint."'");

            $sucessEndpoint = true;
        }
        else
        {
            if(strlen($endpoint) < 60)
            {
                /* Heartbeat data */

                $query = "INSERT INTO t_agents (agent, heartbeat, system, version, ruleset, domain, ipaddress) VALUES ('" . $endpoint . "', now() ,'" . $os . "','" . $version . "','BASELINE','" . $domain ."','" . $ipAddress ."')";
                queryOrDie($query);

                /* Primary endpoint table */

                $query = "CREATE TABLE t_".$endpoint."(command varchar(50), response varchar(65000), finished boolean, date DATETIME, id_uniq_command int, showed boolean, PRIMARY KEY (date)) ENGINE = MyISAM";
                queryOrDie($query);

                $sucessEndpoint = true;
            }
        }
    }
    else
    {
        echo json_encode("Invalid token");
    }

    /* Retrieve endpoint JSON */

    if ($sucessEndpoint == true) 
    {
        $endpointPieces = explode("_", $endpoint);
        endPointsGETQuery($endpointPieces[0], $username);
    }
}

/* Endpoint deletion */

function endPointsDELETEQuery($username, $endpoint)
{
    global $dbConnection;

    if (getUserContext($username) == "all")
    {
        $endpointID = $endpoint;
        
        /* Delete agent tables */
        
        $queryStatement = "SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(table_name), ';') AS statement FROM information_schema.tables WHERE table_schema = 'thefraudexplorer' AND table_name LIKE 't_%s\\_%%'";
        $statement = mysqli_query($dbConnection, sprintf($queryStatement, $endpoint));
        $rowStatement = mysqli_fetch_array($statement);
        
        mysqli_query($dbConnection, $rowStatement[0]);
        mysqli_query($dbConnection, sprintf("DELETE FROM t_agents WHERE agent like '%s\\_%%'", $endpointID));
        mysqli_query($dbConnection, sprintf("DELETE FROM t_inferences WHERE endpoint = '%s'", $endpointID));
        
        /* Delete agent elasticsearch documents */
        
        $urlDelete = "http://localhost:9200/_all/_delete_by_query?pretty";
        $params = '{ "query": { "wildcard" : { "agentId" : "'.$endpointID.'*" } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlDelete);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultDelete=curl_exec($ch);
        curl_close($ch);

        echo json_encode("Deleted endpoint ".$endpointID." with related data (if exist)");
    }
    else
    {
        $endpointID = $endpoint;
        
        /* Delete agent tables */

        $queryControl = mysqli_query($dbConnection, sprintf("SELECT * FROM t_agents WHERE domain = '%s' AND agent like '%s\\_%%'", getUserContext($username), $endpointID));
        $queryControlRows = mysqli_num_rows($queryControl);

        if ($queryControlRows == 0)
        {
            echo json_encode("The endpoint doesn't exist");
            exit;
        } 

        $queryStatement = "SELECT CONCAT('DROP TABLE ', GROUP_CONCAT(table_name), ';') AS statement FROM information_schema.tables WHERE table_schema = 'thefraudexplorer' AND table_name LIKE 't_%s\\_%%'";
        $statement = mysqli_query($dbConnection, sprintf($queryStatement, $endpoint));
        $rowStatement = mysqli_fetch_array($statement);
        
        mysqli_query($dbConnection, $rowStatement[0]);
        mysqli_query($dbConnection, sprintf("DELETE FROM t_agents WHERE agent like '%s\\_%%'", $endpointID));
        mysqli_query($dbConnection, sprintf("DELETE FROM t_inferences WHERE endpoint = '%s'", $endpointID));
        
        /* Delete agent elasticsearch documents */
        
        $urlDelete = "http://localhost:9200/_all/_delete_by_query?pretty";
        $params = '{ "query": { "wildcard" : { "agentId" : "'.$endpointID.'*" } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlDelete);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultDelete=curl_exec($ch);
        curl_close($ch);

        echo json_encode("Deleted endpoint ".$endpointID." with related data (if exist)");
    }
}

?>