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
 * Date: 2019-02
 * Revision: v1.3.1-ai
 *
 * Description: Functions extension file
 */

/* Get array data in form field => value */

function getArrayData($array, $field, $globalVar)
{
    foreach($array as $key => $value)
    {
        if (is_array($value)) getArrayData($value, $field, $globalVar);
        else
        {
            if ($key == $field && $key != "sort")
            {
                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']] = $value;
                $GLOBALS['arrayPosition']++;
            }
        }
    }
}

/* Get multi array data in form field1 => value, field2 => value */

function getMultiArrayData($array, $field1, $field2, $field3, $field4, $globalVar)
{
    $timeStartgetMultiArrayData = microtime(true);
    
    foreach($array as $key => $value)
    {
        if (is_array($value)) getMultiArrayData($value, $field1, $field2, $field3, $field4, $globalVar);
        else
        {
            if ($key == $field1 && $key != "sort")
            {
                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][0] = $value;
                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][1] = $array[$field2];
                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][2] = $array[$field3];
                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][3] = $array[$field4];
                $GLOBALS['arrayPosition']++;
            }
        }
    }
    
    $timeEndgetMultiArrayData = microtime(true);
    $executionTimegetMultiArrayData = ($timeEndgetMultiArrayData - $timeStartgetMultiArrayData);
    
    // echo "Time taken getMultiArrayData in seconds: ".$executionTimegetMultiArrayData."\n";
}

/* Extract all words typed by agent */

function extractTypedWordsFromAgentID($agentID, $index)
{
    $specificAgentTypedWordsParams = [
        'index' => $index,
        'type' => 'TextEvent',
        'body' => [
            'size' => 10000,
            'query' => [
                'term' => [ 'agentId' => $agentID ]
            ],
            'sort' => [
                '@timestamp' => [ 'order' => 'asc' ]
            ]
        ]
    ];
    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdTypedWords = $client->search($specificAgentTypedWordsParams);

    return $agentIdTypedWords;
}

/* Extract words typed by agent depending of the last date */

function extractTypedWordsFromAgentIDWithDate($agentID, $index, $from, $to)
{
    $specificAgentTypedWordsParams = [
        'index' => $index, 
        'type' => 'TextEvent',
        'body' =>[
            'size' => 10000,
            'query' => [
                'bool' => [
                    'must' => [
                        'term' => [ 'agentId' => $agentID ]
                    ],
                    'filter' => [
                        'range' => [
                            '@timestamp' => [ 'gte' => $from, 'lte' => $to ]
                        ]
                    ]
                ]
            ],
            'sort' => [
                '@timestamp' => [ 'order' => 'asc' ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdTypedWords = $client->search($specificAgentTypedWordsParams);

    return $agentIdTypedWords;
}

/* Check if Elasticsearch alerter index exists */

function indexExist($indexName, $configFile)
{
    $url = $configFile['es_host'].$indexName;
    $status = get_headers($url, 1);
    if (strpos($status[0], "OK") != false) return true;
}

/* Extract the last alert date */

function extractEndDateFromAlerter($indexName, $indexType)
{
    $endDateParams = [
        'index' => $indexName,
        'type' => $indexType,
        'body' =>[
            'size' => 1,
            'query' => [
                'term' => [ 'host' => '127.0.0.1' ]
            ],
            'sort' => [
                'endTime' => [ 'order' => 'desc' ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $lastAlertTime = $client->search($endDateParams);

    return $lastAlertTime;
}

/* Fork child management */

function childFinished($signo)
{
    global $openProcesses, $procs;

    foreach ($procs as $key => $pid) 
    {
        if (posix_getpgid($pid) === false) 
        {
            unset($procs[$key]);
            $openProcesses--;
        }
    }
}

/*  Syncronize Rulesets */

function syncRuleset()
{   
    global $connection;

    $queryEndpoints = "SELECT agent FROM t_agents";    
    $resultEndpoints = mysqli_query($connection, $queryEndpoints);

    $openProcesses = 0; 
    $procs = array();
    $maxProcesses = cpuCores();
    
    pcntl_signal(SIGCHLD, "childFinished");   
    mysqli_close($connection);

    while($row = mysqli_fetch_array($resultEndpoints))
    {
        $pid = pcntl_fork();

        if (!$pid) 
        {
            include "../lbs/openDBconn.php";
        
            $endPart = explode("_", $row['agent']);
            $endPoint = $endPart[0];    
        
            $queryRule = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, heartbeat FROM t_agents GROUP BY agent ORDER BY heartbeat ASC) AS tbl WHERE agent='%s' LIMIT 1";
            
            $rulesetQuery = mysqli_query($connection, sprintf($queryRule, $endPoint)); 
            $rulesetArray = mysqli_fetch_array($rulesetQuery);
               
            if ($rulesetArray[0] == NULL) $ruleset = "BASELINE";
            else $ruleset = $rulesetArray[0];
        
            mysqli_query($connection, sprintf("UPDATE t_agents SET ruleset='%s' WHERE agent LIKE '%s%%'", $ruleset, $endPoint));
            exit();
        }
        else
        {
            ++$openProcesses;
            
            if ($openProcesses >= $maxProcesses) 
            {
                pcntl_wait($status);
            }    
        }
    }
    while (pcntl_waitpid(0, $status) != -1) $status = pcntl_wexitstatus($status);
}

/*  Delete Alert Index */

function deleteAlertIndex()
{
    $urlAlertData="http://localhost:9200/logstash-alerter-*";
    $urlAlertStatus="http://localhost:9200/tfe-alerter-status";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $urlAlertData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultAlertData=curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $urlAlertStatus);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultAlertStatus=curl_exec($ch);
    curl_close($ch);
}

/* Start data procesing */ 

function startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement)
{   
    $GLOBALS['arrayPosition'] = 0;
    getMultiArrayData($typedWords, "typedWord", "applicationTitle", "sourceTimestamp", "userDomain", $agentID."_typedWords");
    $arrayOfWordsAndWindows = $GLOBALS[$agentID."_typedWords"];
    
    $arrayOfWordsAndWindows = $GLOBALS[$agentID."_typedWords"];
    $lastWindowTitle = null;
    $lastTimeStamp = null;
    $stringOfWords = null;
    $counter = 0;
    $countWindows = 0;
    $numberOfWindowsAndWords = count($arrayOfWordsAndWindows);
    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $dictLan = $configFile['wc_language'];
    $dictEna = $configFile['wc_enabled'];

    foreach($arrayOfWordsAndWindows as $key=>$value)
    {
        $windowTitle = decRijndael($value[1]);
        $timeStamp = $value[2];

        if ($counter == 0)
        {
            $stringOfWords = decRijndael($value[0]);
        }
        else if ($windowTitle == $lastWindowTitle)
        {
            $stringOfWords = $stringOfWords . " " .decRijndael($value[0]);
        }
        else
        {
            if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

            parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, "matchesGlobalCount", $configFile, $jsonFT, $ruleset, $lastArrayElement);
            $counter = 0;
            $stringOfWords = decRijndael($value[0]);
        }
        
        /* Process the last Window */
        
        $countWindows++;
        
        if ($countWindows === $numberOfWindowsAndWords)
        {            
            $lastWindowTitle = $windowTitle;
            $lastTimeStamp = $timeStamp; 

            if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

            parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, "matchesGlobalCount", $configFile, $jsonFT, $ruleset, $lastArrayElement);
        }

        $counter++;
        $lastWindowTitle = $windowTitle;
        $lastTimeStamp = $timeStamp;
    }
}

/* Parse Fraud Triangle phrases */

function parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $windowTitle, $timeStamp, $matchesGlobalCount, $configFile, $jsonFT, $ruleset, $lastArrayElement)
{
    $timeStartparseFraudTrianglePhrases = microtime(true); 
    
    $matched = FALSE;
    $countOutput = 1;

    foreach ($fraudTriangleTerms as $term => $value)
    {
        $rule = "BASELINE";

        if ($ruleset != "BASELINE") $steps = 2;
        else $steps = 1;

        for($i=1; $i<=$steps; $i++)
        {
            foreach ($jsonFT['dictionary'][$rule][$term] as $field => $termPhrase)
            {
                if (preg_match_all($termPhrase, $stringOfWords, $matches)) 
                {
                    $matched = TRUE;
                    $now = DateTime::createFromFormat('U.u', microtime(true));
                    $end = $now->format("Y-m-d\TH:i:s.u");
                    $end = substr($end, 0, -3);
                    $matchTime = (string)$end."Z";
                    $domain = getUserDomain($agentID);
                    $msgData = $matchTime." ".$agentID." ".$domain." TextEvent - ".$term." e: ".$timeStamp." w: ".str_replace('/', '', $termPhrase)." s: ".$value." m: ".count($matches[0])." p: ".encRijndael($matches[0][0])." t: ".encRijndael($windowTitle)." z: ".encRijndael($stringOfWords)." f: 0";
                    $lenData = strlen($msgData);
                    socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_alerter_port']);       
                    $GLOBALS[$matchesGlobalCount]++;

                    logToFileAndSyslog("LOG_ALERT", $configFile['log_file'], "[INFO] - MatchTime[".$matchTime."] - EventTime[".$timeStamp."] AgentID[".$agentID."] TextEvent - Term[".$term."] Window[".$windowTitle."] Word[".$matches[0][0]."] Phrase[".str_replace('/', '', $termPhrase)."] Score[".$value."] TotalMatches[".count($matches[0])."]");

                    $countOutput++;
                }
            }
            $rule = $ruleset;
        }
    }
    
    $timeEndparseFraudTrianglePhrases = microtime(true);
    $executionTimeparseFraudTrianglePhrases = ($timeEndparseFraudTrianglePhrases - $timeStartparseFraudTrianglePhrases);
    
    // echo "Time taken parseFraudTrianglePhrases in seconds: ".$executionTimeparseFraudTrianglePhrases."\n";
}

/* Check regular expressions */

function checkRegexp($fraudTriangleTerms, $jsonFT, $ruleset)
{
    $errors = false;
    $numberOfTerms = 0;
    
    if (!isset($jsonFT['dictionary'][$ruleset]))
    {
        echo "[ERROR] The specified rule doesn't exist, please check ... \n";
        echo "[INFO] Exiting Fraud Triangle Analytics phrase matching processor ...\n\n";
        exit;
    }
    
    echo "[INFO] Start checking regular expressions on fraud triangle phrases ... \n";
    
    foreach ($fraudTriangleTerms as $term => $value)
    {
        foreach ($jsonFT['dictionary'][$ruleset][$term] as $field => $termPhrase)
        {
            if (@preg_match_all($termPhrase, null) === false) 
            {
                $errors = true;
                echo "[ERROR] Invalid regular expression in rule [".$ruleset."] term [".$term."] and phrase [".$field."]\n";
            }
            
            $numberOfTerms++;
        }
    }
    
    echo "[INFO] Number of regular expressions checked [".$numberOfTerms."]\n";
    
    if ($errors == true) echo "[ERROR] There are one or more invalid regular expressions, please fix them ...\n";
    else echo "[INFO] All regular expressions are OK under the rule checked ...\n";
}

/* Get ruleset from agent */

function getRuleset($agentID)
{
    global $connection;

    $rulesetQuery = sprintf("SELECT ruleset FROM t_agents WHERE agent='%s'", $agentID);
    $rulesetExecution = mysqli_query($connection, $rulesetQuery);
    $rowRuleset = mysqli_fetch_assoc($rulesetExecution);
    $ruleset = $rowRuleset['ruleset'];

    return $ruleset;
}

/* Count Fraud Triangle matches by Agent */

function countFraudTriangleMatches($agentID, $fraudTerm, $index)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'term' => [ 'agentId' => $agentID ] ],
                        [ 'term' => [ 'alertType' => $fraudTerm ] ]
                    ],
                    'must_not' => [
                            [ 'match' => [ 'falsePositive' => '1'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdMatches = $client->count($matchesParams);

    return $agentIdMatches;
}

/* Count Words typed by agent */

function countWordsTypedByAgent($agentID, $alertType, $index)
{
    $matchesParams = [
        'index' => $index,
        'type' => 'TextEvent',
        'body' => [
            'query' => [
                'term' => [ 'agentId' => $agentID ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdMatches = $client->count($matchesParams);

    return $agentIdMatches;
}

/* Populate SQL Database with Fraud Triangle Data */

function populateTriangleByAgent($ESindex, $configFile_es_alerter_index)
{
    global $connection;

    echo "[INFO] Populating SQL-Database with Fraud Triangle Analytics Insights by agent ...\n";

    sleep(10);
    include "../lbs/openDBconn.php";
    
    $resultQuery = mysqli_query($connection, "SELECT agent FROM t_agents");
    
    $openProcesses = 0; 
    $procs = array();
    $maxProcesses = cpuCores();
    
    pcntl_signal(SIGCHLD, "childFinished");
    mysqli_close($connection);

    if ($row_a = mysqli_fetch_array($resultQuery))
    {
        do
        {
            $pid = pcntl_fork();
            
            if (!$pid) 
            {
                include "../lbs/openDBconn.php";
                               
                $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');
                $totalWordCount = countWordsTypedByAgent($row_a['agent'], "TextEvent", $ESindex);
                $matchesRationalization = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['r'], $configFile_es_alerter_index);
                $matchesOpportunity = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['o'], $configFile_es_alerter_index);
                $matchesPressure = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['p'], $configFile_es_alerter_index);
                $totalWords = $totalWordCount['count'];
                $totalPressure = $matchesPressure['count'];
                $totalOpportunity = $matchesOpportunity['count'];
                $totalRationalization = $matchesRationalization['count'];

                $result = mysqli_query($connection, "UPDATE t_agents SET totalwords='.$totalWords.', pressure='.$totalPressure.', opportunity='.$totalOpportunity.', rationalization='.$totalRationalization.' WHERE agent='".$row_a['agent']."'");

                exit();
            }
            else
            {
                ++$openProcesses;
            
                if ($openProcesses >= $maxProcesses) 
                {
                    pcntl_wait($status);
                }    
            }
            
        }
        while ($row_a = mysqli_fetch_array($resultQuery));
        
        while (pcntl_waitpid(0, $status) != -1) $status = pcntl_wexitstatus($status);
    }
}

function getUserDomain($agentID)
{
    global $connection;

    $result = mysqli_query($connection, "SELECT domain FROM t_agents WHERE agent='".$agentID."'");
    $row = mysqli_fetch_array($result);
    return $row['domain'];
}

/* Word Correction */

function checkPhrases($string, $language)
{
    $unwanted_chars = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');

    $config_dic = pspell_config_create($language);
    pspell_config_mode($config_dic, PSPELL_FAST);
    $dictionary = pspell_new_config($config_dic);
    $replacement_suggest = false;
    $string = explode(' ', trim(str_replace(',', ' ', $string)));

    foreach ($string as $key => $value)
    {
        if(!pspell_check($dictionary, $value))
        {
            $suggestion = pspell_suggest($dictionary, $value);
            
            if (array_key_exists('0', $suggestion)) 
            {
                if(strtolower($suggestion[0]) != strtolower($value))
                {
                    $string[$key] = $suggestion[0];
                    $replacement_suggest = true;
                }
            }
        }
    }

    return strtr(implode(' ', $string), $unwanted_chars);
}

/* Re-populate Sampler data */

function repopulateSampler()
{
    global $connection;

    $deleteQuery = "DELETE FROM t_agents WHERE agent in ('johndoe_90214c1_agt', 'nigel_abc14c1_agt', 'desmond_402vcc4_agt', 'spruce_s0214ck_agt', 'fletch_80j14g1_agt', 'ingredia_tq2v4c1_agt', 'archibald_b0314cm_agt', 'niles_1011jcl_agt', 'lurch_t021ycp_agt', 'eleanor_1114c3_agt', 'gordon_bbb94cc_agt', 'gustav_cht14f2_agt', 'jason_j8g12cg_agt', 'burgundy_18hg4cj_agt', 'benjamin_0001kc9_agt')";
    
    $resultQuery = mysqli_query($connection, $deleteQuery);
    
    $insertQuery = "INSERT INTO t_agents (agent, heartbeat, system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization) VALUES ('johndoe_90214c1_agt', '2017-04-15 07:46:12', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.7', 'John Doe', 'BASELINE', 'male', '12723', '8', '10', '7'), ('nigel_abc14c1_agt', '2017-04-15 08:21:10', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.8', 'Nigel Eagle', 'BASELINE', 'female', '7321', '25', '0', '0'), ('desmond_402vcc4_agt', '2017-04-15 09:34:18', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.9', 'Desmond Wiedenbauer', 'BASELINE', 'male', '1983', '0', '25', '0'), ('spruce_s0214ck_agt', '2017-04-06 05:36:20', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.10', 'Spruce Bellevedere', 'BASELINE', 'male', '3000', '0', '0', '25'), ('fletch_80j14g1_agt', '2017-04-15 17:01:12', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.11', 'Fletch Nigel', 'BASELINE', 'male', '1560', '10', '10', '5'), ('ingredia_tq2v4c1_agt', '2017-04-06 03:11:02', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.12', 'Ingredia Douchebag', 'BASELINE', 'female', '3489', '5', '5', '15'), ('archibald_b0314cm_agt', '2017-04-06 09:14:37', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.13', 'Archibald Gibson', 'BASELINE', 'male', '921', '20', '2', '3'), ('niles_1011jcl_agt', '2017-04-15 02:37:13', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.14', 'Niles Ameter', 'BASELINE', 'male', '7528', '9', '13', '3'), ('lurch_t021ycp_agt', '2017-04-15 19:33:49', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.15', 'Lurch Barrow', 'BASELINE', 'male', '9800', '9', '5', '11'), ('eleanor_1114c3_agt', '2017-04-15 03:36:11', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.16', 'Eleanor Rails', 'BASELINE', 'female', '2899', '17', '3', '5'), ('gordon_bbb94cc_agt', '2017-04-15 04:16:09', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.17', 'Gordon Mondover', 'BASELINE', 'male', '1488', '7', '18', '0'), ('gustav_cht14f2_agt', '2017-04-15 06:46:22', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.18', 'Gustav Deck', 'BASELINE', 'male', '23900', '4', '9', '12'), ('jason_j8g12cg_agt', '2017-04-15 09:56:37', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.19', 'Jason Posture', 'BASELINE', 'male', '249', '0', '16', '9'), ('burgundy_18hg4cj_agt', '2017-04-15 17:12:43', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.20', 'Burgundy Skinner', 'BASELINE', 'male', '76', '9', '9', '7'), ('benjamin_0001kc9_agt', '2017-04-15 21:00:51', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.21', 'Benjamin Evalent', 'BASELINE', 'male', '7599', '7', '7', '11')";
    
    $resultQuery = mysqli_query($connection, $insertQuery);
}

/* Send log data to external file & syslog */

function logToFileAndSyslog($logType, $filename, $msg)
{
    $fd = fopen($filename, "a");
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; 
    fwrite($fd, $str . "\n");
    fclose($fd);
    
    /* Syslog integration */
    
    openlog("thefraudexplorer", LOG_PID, LOG_LOCAL0);
    syslog(($logType == "LOG_INFO" ? LOG_INFO : LOG_ALERT), $msg);
    closelog();
}

/* Compute the number of CPU cores */

function cpuCores()
{
    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $cpuCores = $configFile['cpu_cores'];
    
    return $cpuCores/2;
}

/* Get entry for agentid Data */

function getAgentIdData($agentID, $index, $alertType)
{
    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'size' => 10000,
            'query' => [
                'wildcard' => [ 'agentId' => $agentID ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->search($matchesParams);

    return $agentIdData;
}

/* Parse Fraud Triangle phrases for Artificial Intelligence Deduction Engine */

function AIparseFraudTrianglePhrases($fraudTriangleTerms, $stringOfWords, $jsonFT, $ruleset)
{
    $matched = FALSE;
    $countTriangle = ['pressure' => 0, 'opportunity' => 0, 'rationalization' => 0]; 

    foreach ($fraudTriangleTerms as $term => $value)
    {
        $rule = "BASELINE";

        if ($ruleset != "BASELINE") $steps = 2;
        else $steps = 1;

        for($i=1; $i<=$steps; $i++)
        {
            foreach ($jsonFT['dictionary'][$rule][$term] as $field => $termPhrase)
            {
                if (preg_match_all($termPhrase, $stringOfWords, $matches)) 
                {
                    $matched = TRUE;
                    $countTriangle[$term] = $countTriangle[$term] + 1;
                }
            }
            $rule = $ruleset;
        }
    }

    return $countTriangle;
}

/* Check in_array under associative model */

function is_in_array($array, $key, $key_value)
{
    $within_array = false;

    foreach($array as $k=>$v)
    {
        if(is_array($v))
        {
            $within_array = is_in_array($v, $key, $key_value);
            
            if( $within_array == 'yes' ) break;
        } 
        else 
        {
            if( $v == $key_value && $k == $key )
            {
                $within_array = true;
                break;
            }
        }
    }
    return $within_array;
}

/* Artificial Intelligence Deduction Engine */

function startAI($ESAlerterIndex, $fraudTriangleTerms, $jsonFT, $configFile)
{
    global $connection;

    echo "[INFO] Starting fraud inferences, checking alerts ...\n";

    /* SQL queries */

    include "../lbs/openDBconn.php";

    $endPointHasAlerts = "SELECT agent, ruleset, heartbeat, domain, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset, heartbeat, domain, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE trianglesum > 0 GROUP BY agent";
    $resultEndPointsHasAlerts = mysqli_query($connection, $endPointHasAlerts);

    /* Expert System Inference Engine */

    $fraudTriangleHeight = ['pressure' => 50, 
                            'opportunity' => 20, 
                            'rationalization' => 30];

    $fraudProbability = ['almost' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization'], 
                        'very' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['rationalization'], 
                        'maybe' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'], 
                        'less' => $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization']];

    /* Main Logic */

    if ($rowEndPointsHasAlerts = mysqli_fetch_array($resultEndPointsHasAlerts))
    {
        do
        {
            $endPoint = $rowEndPointsHasAlerts['agent']."*";
            $agentAlerts = getAgentIdData($endPoint, $ESAlerterIndex, "AlertEvent");
            $alertData = json_decode(json_encode($agentAlerts),true);
            $ruleset = $rowEndPointsHasAlerts['ruleset'];
            $stringHistoryArchive = array(array());
            $counter = 0;

            foreach ($alertData['hits']['hits'] as $result)
            {
                if (isset($result['_source']['tags'])) continue;

                $stringOfWords = decRijndael($result['_source']['stringHistory']); 
                $application = decRijndael($result['_source']['windowTitle']);
                $timeStamp = date('Y-m-d h:i:s', strtotime($result['_source']['sourceTimestamp']));
                $domain = $result['_source']['userDomain'];
                $alertID = $result['_id'];
                $countTriangleMatch = AIparseFraudTrianglePhrases($fraudTriangleTerms, $stringOfWords, $jsonFT, $ruleset);
                $deductionMatch = false;
                $matchReason = "N/A";

                if ($countTriangleMatch['pressure'] != 0 && $countTriangleMatch['opportunity'] != 0 && $countTriangleMatch['rationalization'] != 0) 
                {
                    /* Put the alert in DB with P+O+R */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue;

                    $deductionMatch = true;
                    $matchReason = "POR";
                    $fraudProbDeduction = $fraudProbability['almost'];
                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['pressure'] != 0 && $countTriangleMatch['opportunity'] != 0) 
                {
                    /* Put the alert in DB with P+O */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "PO";
                    $fraudProbDeduction = $fraudProbability['maybe'];
                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['pressure'] != 0 && $countTriangleMatch['rationalization'] != 0)
                {
                    /* Put the alert in DB with P+R */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "PR";
                    $fraudProbDeduction = $fraudProbability['very'];
                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['opportunity'] != 0 && $countTriangleMatch['rationalization'] != 0)
                {
                    /* Put the alert in DB with O+R */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "OR";
                    $fraudProbDeduction = $fraudProbability['less'];
                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }

                if ($deductionMatch == true)
                {
                    $queryDeductionsExist = "SELECT * FROM t_inferences WHERE alertid = '".$alertID."'";    
                    $resultDeductionsExist =  mysqli_query($connection, $queryDeductionsExist);

                    if (mysqli_num_rows($resultDeductionsExist) == 0) 
                    {
                        $queryDeduction = sprintf("INSERT INTO t_inferences (endpoint, domain, ruleset, application, date, reason, alertid, deduction) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", rtrim($endPoint, "*"), $domain, $ruleset, $application, $timeStamp, $matchReason, $alertID, $fraudProbDeduction);
                        $resultDeduction = mysqli_query($connection, $queryDeduction);

                        logToFileAndSyslog("LOG_ALERT", $configFile['log_file'], "[INFO] - Time[".$timeStamp."] - AgentID[".rtrim($endPoint, "*")."] A.I Deduction - Reason[".$matchReason."] Ruleset [".$ruleset."] Application[".$application."] Probability[".$fraudProbDeduction."]");
                    
                        /* Send message alert */

                        $mailEventPath = $configFile['php_document_root']."/lbs/mailEvent.php";
                        include $mailEventPath;
                        mail($to, $subject, $message, $headers);
                    }
                }

                $counter++;
            }
        }
        while ($rowEndPointsHasAlerts = mysqli_fetch_array($resultEndPointsHasAlerts));
    }
}

?>