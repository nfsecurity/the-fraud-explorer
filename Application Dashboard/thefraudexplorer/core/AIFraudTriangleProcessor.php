<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.1
 *
 * Description: Main Application, Fraud Triangle Analytics Alerting
 */

/* External includes */

include "/var/www/html/thefraudexplorer/lbs/cryptography.php";

/* Current time */

$now = DateTime::createFromFormat('U.u', microtime(true));
$time = $now->format("Y-m-d\TH:i:s.u");
$time = substr($time, 0, -3);
$GLOBALS['currentTime'] = (string)$time."Z";
$time_start = microtime(true); 

/* Load parameters, methods, functions and procedures from external files */

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
require 'vendor/autoload.php';
include 'include/functions.php';

/* Global variables */

$sockLT = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
$client = Elasticsearch\ClientBuilder::create()->build();
$GLOBALS['matchesGlobalCount'] = 0;
$startTime = microtime(true);
$ESindex = $configFile['es_words_index'];
$ESAlerterIndex = $configFile['es_alerter_index'];
$fistTimeIndex = true;
$fta_lang = $configFile['fta_lang_selection'];
$fraudTriangleTerms = array('rationalization'=>'0 1 0','opportunity'=>'0 0 1','pressure'=>'1 0 0');
$jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);

/* Unique agentID List */

$queryAgentList = "SELECT agent FROM t_agents";    
$resultQueryAgentList = mysql_query($queryAgentList);

/* Start the loop for each agent */

$singleEndpoint = false;
$endpointSelected = "all";

echo "\n[INFO] Starting Artificial Intelligence Fraud Triangle Analytics phrase matching processor ...\n";

/* Start from Scratch */

if (isset($argv[1]))
{
    if ($argv[1] == "fromScratch") 
    {
        echo "[INFO] Starting from scratch, deleting previous alert data ...\n";
        
        deleteAlertIndex();
        clearWords();
        repopulateSampler();
    }
    else if ($argv[1] == "fromEndpoint")
    {
        if (isset($argv[2]))
        {
            $singleEndpoint = true;
            $endpointSelected = $argv[2];
        }
    }
    else if ($argv[1] == "checkRule")
    {
        if (isset($argv[2]))
        {
            $ruleToCheck = $argv[2];
            
            checkRegexp($fraudTriangleTerms, $jsonFT, $ruleToCheck);
                   
            echo "[INFO] Exiting Artificial Intelligence Fraud Triangle Analytics phrase matching processor ...\n\n";
            exit;
        }
    }
    else if ($argv[1] == "onlyAI")
    {
        startAI($ESAlerterIndex, $fraudTriangleTerms, $jsonFT, $configFile);

        echo "[INFO] Exiting Artificial Intelligence Fraud Triangle Analytics phrase matching processor ...\n\n";
        exit;
    }
}

if (indexExist($configFile['es_alerter_status_index'], $configFile))
{
    echo "[INFO] Index ".$configFile['es_alerter_status_index']." already exist, continue ...\n";

    $firstTimeIndex = false;
    logToFileAndSyslog("LOG_INFO", $configFile['log_file'], "[INFO] - The alerter index already exist, continue with data range matching ...");
    $endDate = extractEndDateFromAlerter($configFile['es_alerter_status_index'], "AlertStatus");
    $GLOBALS['arrayPosition'] = 0;
    getArrayData($endDate, "endTime", 'lastAlertDate');
    
    echo "[INFO] Syncing new endpoints sessions with their existing rulesets ...\n";
    
    syncRuleset();

    echo "[INFO] Checking events from latest alert date: ".$GLOBALS['lastAlertDate'][0]." ...\n";

    logToFileAndSyslog("LOG_INFO", $configFile['log_file'], "[INFO] - Checking events from last date: ".$GLOBALS['lastAlertDate'][0]."  ...");

    echo "[INFO] Searching for typedwords by agent ...\n";

    include "../lbs/openDBconn.php";
    
    $arrayCounter = 0;
    $effectiveEndpointCounter = 1;
    $lastArrayElement = false;
    $arrayLenght = mysql_num_rows($resultQueryAgentList);
       
    if ($endpointSelected == "all" && $singleEndpoint == false)
    {
        $openProcesses = 0; 
        $procs = array();
        $maxProcesses = cpuCores();
    
        pcntl_signal(SIGCHLD, "childFinished");   
        mysql_close();
        
        while($row = mysql_fetch_array($resultQueryAgentList))
        {
            $pid = pcntl_fork();

            if (!$pid) 
            {
                include "../lbs/openDBconn.php";
                
                $agentID = $row['agent'];
                $typedWords = extractTypedWordsFromAgentIDWithDate($agentID, $ESindex, $GLOBALS['lastAlertDate'][0], $GLOBALS['currentTime']);

                if ($typedWords['hits']['total'] == 0)
                {      
                    if ($arrayCounter == $arrayLenght - 1) $lastArrayElement = true;
            
                    $arrayCounter++;
                }
                else
                {
                    $ruleset = getRuleset($agentID);
            
                    echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
            
                    if ($arrayCounter == $arrayLenght - 1) $lastArrayElement = true;
               
                    startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement);
               
                    $arrayCounter++;
                }
                exit();
            }
            else
            {
                $effectiveEndpointCounter++;
                ++$openProcesses;
            
                if ($openProcesses >= $maxProcesses) 
                {
                    pcntl_wait($status);
                }    
            }
        }
        while (pcntl_waitpid(0, $status) != -1) $status = pcntl_wexitstatus($status);
        
        echo "[INFO] Number of endpoints processed: ".$effectiveEndpointCounter."\n";       
    }
    else
    {
        $agentID = $endpointSelected;
        $typedWords = extractTypedWordsFromAgentIDWithDate($agentID, $ESindex, $GLOBALS['lastAlertDate'][0], $GLOBALS['currentTime']);

        if (!$typedWords['hits']['total'] == 0)
        {
            $ruleset = getRuleset($agentID);
            
            echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
            
            $lastArrayElement = true;
            
            startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement);
        }           
    }

    populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);
}
else
{
    echo "[INFO] Index ".$configFile['es_alerter_status_index']." doesn't exist, continue ...\n";
    logToFileAndSyslog("LOG_INFO", $configFile['log_file'], "[INFO] - Alerter index not found, continue with all data matching ...");
    
    echo "[INFO] Syncing new endpoints sessions with their existing rulesets ...\n";
    
    syncRuleset();
    
    echo "[INFO] Checking events from now ...\n";

    include "../lbs/openDBconn.php";
    
    $arrayCounter = 0;
    $effectiveEndpointCounter = 1;
    $lastArrayElement = false;
    $arrayLenght = mysql_num_rows($resultQueryAgentList);
    
    if ($endpointSelected == "all" && $singleEndpoint == false)
    {
        $openProcesses = 0; 
        $procs = array();
        $maxProcesses = cpuCores();
    
        pcntl_signal(SIGCHLD, "childFinished");   
        mysql_close();
        
        while($row = mysql_fetch_array($resultQueryAgentList))
        {
            $pid = pcntl_fork();

            if (!$pid) 
            {
                include "../lbs/openDBconn.php";
                
                $agentID = $row['agent'];
                $typedWords = extractTypedWordsFromAgentID($agentID, $ESindex);

                if ($typedWords['hits']['total'] == 0)
                {   
                    if ($arrayCounter == $arrayLenght - 1) $lastArrayElement = true;
            
                    $arrayCounter++;
                }
                else 
                {
                    $ruleset = getRuleset($agentID);
            
                    echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
            
                    if ($arrayCounter == $arrayLenght - 1) $lastArrayElement = true;
            
                    startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement);
                
                    $arrayCounter++;
                }
                exit();
            }
            else
            {
                ++$openProcesses;
                $effectiveEndpointCounter++;
            
                if ($openProcesses >= $maxProcesses) 
                {
                    pcntl_wait($status);
                }    
            }
        }
        while (pcntl_waitpid(0, $status) != -1) $status = pcntl_wexitstatus($status);
        
        echo "[INFO] Number of endpoints processed: ".$effectiveEndpointCounter."\n";
    }
    else
    {
        $agentID = $endpointSelected;
        $typedWords = extractTypedWordsFromAgentID($agentID, $ESindex);

        if (!$typedWords['hits']['total'] == 0)
        {
            $ruleset = getRuleset($agentID);
            
            echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
            
            $lastArrayElement = true;
            
            startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement);      
        }     
    }

    populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);
}

/* Artificial Intelligence deductions */

startAI($ESAlerterIndex, $fraudTriangleTerms, $jsonFT, $configFile);

/* Close Alerter UDP socket */

socket_close($sockLT);

/* Alerter status */

$endTime = $GLOBALS['currentTime'];
$sockAlerter = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
$timeTaken = microtime(true) - $startTime;
$timeTaken = floor($timeTaken * 100) / 100;

if ($firstTimeIndex = true) $GLOBALS['lastAlertDate'][0] = $endTime;
$msgData = $endTime." - ".$GLOBALS['lastAlertDate'][0]." TextEvent ".$timeTaken." ".$GLOBALS['matchesGlobalCount'];

$lenData = strlen($msgData);
socket_sendto($sockAlerter, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_alerter_status_port']);
socket_close($sockAlerter);

echo "[INFO] Sending this alert status to log file ...\n";

logToFileAndSyslog("LOG_INFO", $configFile['log_file'], "[INFO] - Sending alert-status to index, StartTime[".$GLOBALS['lastAlertDate'][0]."], EndTime[".$endTime."] TimeTaken[".$timeTaken."] Triggered[".$GLOBALS['matchesGlobalCount']."]");
include "/var/www/html/thefraudexplorer/lbs/closeDBconn.php";

$time_end = microtime(true);
$execution_time = ($time_end - $time_start)/60;

echo "[INFO] Total execution time in minutes: ".round($execution_time, 2)."\n";
echo "[INFO] Exiting Artificial Intelligence Fraud Triangle Analytics phrase matching processor ...\n\n";

?>