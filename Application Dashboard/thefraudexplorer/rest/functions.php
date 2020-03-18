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
    $resultArray[] = array();

    if (!get_magic_quotes_gpc()) $query = addslashes($query);
    if (!get_magic_quotes_gpc()) $username = addslashes($username);

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

        if($countRows > 0)
        {
            $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
            date_default_timezone_set($configFile['php_timezone']);
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

/* Insert Endpoint phrases */

function endPointsPOSTQuery($endpoint, $rawJSON)
{
    global $dbConnection;

    if (!get_magic_quotes_gpc()) $endpoint = addslashes($endpoint);
    $receivedJSON = json_decode($rawJSON, true);
    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $timeZone = $configFile['php_timezone'];
    $sockLT = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    $endpointQuery = mysqli_query($dbConnection, "SELECT * FROM t_agents WHERE agent='".$endpoint."'");
    $countRows = mysqli_num_rows($endpointQuery);

    if($countRows > 0)
    {
        if(!isJson($rawJSON))
        {
            echo json_encode("You have a JSON syntax error");
            exit;
        }
        else
        {
            if(isset($receivedJSON['hostPrivateIP']) && isset($receivedJSON['userDomain']) && isset($receivedJSON['appTitle']) && isset($receivedJSON['phrases']))
            {
                $textPhrases = $receivedJSON['phrases'];
                $words = explode(" ", $textPhrases);

                foreach ($words as $word)
                {
                    $now = DateTime::createFromFormat('U.u', microtime(true));
                    $now->setTimezone(new DateTimeZone($timeZone));
                    $wordTime = $now->format("Y-m-d H:i:s,v");
                    usleep(10 * 1000);

                    $msgData = $wordTime." a: ".$receivedJSON['hostPrivateIP']." b: ".$receivedJSON['userDomain']." c: ".$endpoint." d: TextEvent - e: ".encRijndael($receivedJSON['appTitle'])." f: ".encRijndael($word);
                    $lenData = strlen($msgData);
                    socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_webservice_text_port']);
                }

                echo json_encode("Seccesfully sent data with paragraph: ".$receivedJSON['phrases']);
            }
            else 
            {
                echo json_encode("Insufficient JSON keys");
                exit;
            }
        }
    }
    else echo json_encode("The endpoint doesn't exist");
}

/* Check if it's JSON */

function isJson($string) 
{
    json_decode($string, true);
    return (json_last_error() == JSON_ERROR_NONE);
}

/* FTA Events GET function */

function ftaEventsGETQuery($username, $endpoint)
{
    global $dbConnection;
    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $ESAlerterIndex = $configFile['es_alerter_index'];
    if ($endpoint == "all")
    {
        if (getUserContext($username) == "all")
        {
            $eventMatches = getAllFraudTriangleMatches($ESAlerterIndex, "all", "disabled", "allalerts");
            $eventData = json_decode(json_encode($eventMatches), true);

            foreach ($eventData['hits']['hits'] as $result)
            {
                if (isset($result['_source']['tags'])) continue;
                $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
                $wordTyped = decRijndael($result['_source']['wordTyped']);
                $stringHistory = decRijndael($result['_source']['stringHistory']);
                $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                $endpointID = $result['_source']['agentId'];
                $domain = $result['_source']['userDomain'];
                $endPoint = explode("_", $result['_source']['agentId']);
                $endpointDECSQL = $endPoint[0];
                $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
                $rulesetquery = mysqli_query($dbConnection, sprintf($queryRuleset, $endpointDECSQL));
                $ruleset = mysqli_fetch_array($rulesetquery);
                if (is_null($ruleset[0])) $ruleset[0] = "BASELINE";
                $rule = $ruleset[0];
                $regExpression = htmlentities($result['_source']['phraseMatch']);
                $eventsMatrix[$endpointID] = ["Alert date"=>$date, "Domain"=>$domain, "Phrase typed"=>$wordTyped, "Paragraph"=>$stringHistory, "Application Title"=>$windowTitle, "Ruleset"=>$rule, "Regular expression"=>$regExpression];
            }
            echo json_encode($eventsMatrix);
        }
        else
        {
            $eventMatches = getAllFraudTriangleMatches($ESAlerterIndex, getUserContext($username), "disabled", "allalerts");
            $eventData = json_decode(json_encode($eventMatches), true);

            foreach ($eventData['hits']['hits'] as $result)
            {
                if (isset($result['_source']['tags'])) continue;
                $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
                $wordTyped = decRijndael($result['_source']['wordTyped']);
                $stringHistory = decRijndael($result['_source']['stringHistory']);
                $domain = $result['_source']['userDomain'];
                $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                $endpointID = $result['_source']['agentId'];
                $endPoint = explode("_", $result['_source']['agentId']);
                $endpointDECSQL = $endPoint[0];
                $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
                $rulesetquery = mysqli_query($dbConnection, sprintf($queryRuleset, $endpointDECSQL));
                $ruleset = mysqli_fetch_array($rulesetquery);
                if (is_null($ruleset[0])) $ruleset[0] = "BASELINE";
                $rule = $ruleset[0];
                $regExpression = htmlentities($result['_source']['phraseMatch']);
                $eventsMatrix[$endpointID] = ["Alert date"=>$date, "Domain"=>$domain, "Phrase typed"=>$wordTyped, "Paragraph"=>$stringHistory, "Application Title"=>$windowTitle, "Ruleset"=>$rule, "Regular expression"=>$regExpression];
            }
            echo json_encode($eventsMatrix);
        }
    }
    else
    {
        if (getUserContext($username) == "all")
        {
            $endpointWildcard = $endpoint."*";
            $matchesDataEndpoint = getAgentIdData($endpointWildcard, $ESAlerterIndex, "AlertEvent");
            $eventData = json_decode(json_encode($matchesDataEndpoint), true);
            foreach ($eventData['hits']['hits'] as $result)
            {
                if (isset($result['_source']['tags'])) continue;
                $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
                $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                $endpointID = $result['_source']['agentId'];
                $domain = $result['_source']['userDomain'];
                $endPoint = explode("_", $result['_source']['agentId']);
                $endpointDECSQL = $endPoint[0];
                $wordTyped = decRijndael($result['_source']['wordTyped']);
                $stringHistory = decRijndael($result['_source']['stringHistory']);
                $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
                $rulesetquery = mysqli_query($dbConnection, sprintf($queryRuleset, $endpointDECSQL));
                $ruleset = mysqli_fetch_array($rulesetquery);
                if (is_null($ruleset[0])) $ruleset[0] = "BASELINE";
                $rule = $ruleset[0];
                $regExpression = htmlentities($result['_source']['phraseMatch']);
                $eventsMatrix[$endpointID] = ["Alert date"=>$date, "Domain"=>$domain, "Phrase typed"=>$wordTyped, "Paragraph"=>$stringHistory, "Application Title"=>$windowTitle, "Ruleset"=>$rule, "Regular expression"=>$regExpression];
            }
            echo json_encode($eventsMatrix); 
        }
        else
        {
            $endpointWildcard = $endpoint."*";
            $matchesDataEndpoint = getAgentIdData($endpointWildcard, $ESAlerterIndex, "AlertEvent");
            $eventData = json_decode(json_encode($matchesDataEndpoint), true);
            $endpointsMatched = false;
            foreach ($eventData['hits']['hits'] as $result)
            {
                if (isset($result['_source']['tags'])) continue;
                $domain = $result['_source']['userDomain'];
                if (!($domain == getUserContext($username))) continue;
                $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
                $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
                $endpointID = $result['_source']['agentId'];
                $endPoint = explode("_", $result['_source']['agentId']);
                $endpointDECSQL = $endPoint[0];
                $wordTyped = decRijndael($result['_source']['wordTyped']);
                $stringHistory = decRijndael($result['_source']['stringHistory']);
                $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
                $rulesetquery = mysqli_query($dbConnection, sprintf($queryRuleset, $endpointDECSQL));
                $ruleset = mysqli_fetch_array($rulesetquery);
                if (is_null($ruleset[0])) $ruleset[0] = "BASELINE";
                $rule = $ruleset[0];
                $regExpression = htmlentities($result['_source']['phraseMatch']);
                $endpointsMatched = true;
                $eventsMatrix[$endpointID] = ["Alert date"=>$date, "Domain"=>$domain, "Phrase typed"=>$wordTyped, "Paragraph"=>$stringHistory, "Application Title"=>$windowTitle, "Ruleset"=>$rule, "Regular expression"=>$regExpression];
            }
            if ($endpointsMatched == true) echo json_encode($eventsMatrix); 
            else echo json_encode("No events with your criteria");      
        }
    }
}

/* AI Alerts GET function */

function aiAlertsGETQuery($username)
{
    global $dbConnection;
    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $ESalerterIndex = $configFile['es_alerter_index'];

    if (getUserContext($username) == "all")
    {
        $queryDeductions = "SELECT * from t_inferences";
        $resultQuery = mysqli_query($dbConnection, $queryDeductions);

        if ($row = mysqli_fetch_array($resultQuery))
        {
            do
            {
                $application = $row['application'];
                $timeDate = $row['date'];
                $endpoint = $row['endpoint'];
                $domain = $row['domain'];
                $rule = $row['ruleset'];
                $alertid = $row['alertid'];
                $reason = $row['reason'];
                $deduction = $row['deduction'];

                $alertPhrase = getAlertIdData($alertid, $ESalerterIndex, "AlertEvent");
                $notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot");
                $sanitizedPhrases = decRijndael($alertPhrase['hits']['hits'][0]['_source']['stringHistory']);
                foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);
                    
                $alertsMatrix[] = ["Endpoint"=>$endpoint, "Alert date"=>$timeDate, "Domain"=>$domain, "Application Title"=>$application, "Phrase typed"=>"$sanitizedPhrases", "Ruleset"=>$rule, "Reason"=>$reason, "Deduction"=>$deduction];
            }
            while ($row = mysqli_fetch_array($resultQuery));

            echo json_encode($alertsMatrix);
        }
        else echo json_encode("No deductions at this time"); 
    }
    else
    {
        $queryDeductions = "SELECT * from t_inferences WHERE domain='".getUserContext($username)."'";
        $resultQuery = mysqli_query($dbConnection, $queryDeductions);

        if ($row = mysqli_fetch_array($resultQuery))
        {
            do
            {
                $application = $row['application'];
                $timeDate = $row['date'];
                $endpoint = $row['endpoint'];
                $domain = $row['domain'];
                $rule = $row['ruleset'];
                $alertid = $row['alertid'];
                $reason = $row['reason'];
                $deduction = $row['deduction'];

                $alertPhrase = getAlertIdData($alertid, $ESalerterIndex, "AlertEvent");
                $notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot");
                $sanitizedPhrases = decRijndael($alertPhrase['hits']['hits'][0]['_source']['stringHistory']);
                foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);
                    
                $alertsMatrix[] = ["Endpoint"=>$endpoint, "Alert date"=>$timeDate, "Domain"=>$domain, "Application Title"=>$application, "Phrase typed"=>"$sanitizedPhrases", "Ruleset"=>$rule, "Reason"=>$reason, "Deduction"=>$deduction];
            }
            while ($row = mysqli_fetch_array($resultQuery));

            echo json_encode($alertsMatrix);
        }
        else echo json_encode("No deductions at this time"); 
    }
}

?>