<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 * 
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
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

/* Count words typed depending of the last date */

function countTypedWordsWithDate($index, $from, $to)
{
    $entireTypedWordsParams = [
        'index' => $index, 
        'body' =>[
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            '@timestamp' => [ 'gte' => $from, 'lte' => $to ]
                        ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $typedWords = $client->count($entireTypedWordsParams);

    return $typedWords;
}

/* Count words typed */

function countAllTypedWords($index)
{
    $entireTypedWordsParams = [
        'index' => $index, 
        'body' =>[
            'query' => [
                'match_all' => []
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $typedWords = $client->count($entireTypedWordsParams);

    return $typedWords;
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
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $urlAlertData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultAlertData=curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $urlAlertStatus);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    $resultAlertStatus=curl_exec($ch);
    curl_close($ch);
}

/* Start data procesing */ 

function startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement, $socketIPC)
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
    $totalMatches = 0;

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
	    $stringOfWords = processBackspaces($stringOfWords);

            if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

            $totalMatches = $totalMatches + parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, $configFile, $jsonFT, $ruleset, $lastArrayElement);
            $counter = 0;
            $stringOfWords = decRijndael($value[0]);
        }
        
        /* Process the last Window */
        
        $countWindows++;
        
        if ($countWindows === $numberOfWindowsAndWords)
        {            
            $lastWindowTitle = $windowTitle;
            $lastTimeStamp = $timeStamp; 
	    $stringOfWords = processBackspaces($stringOfWords);

            if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

            $totalMatches = $totalMatches + parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, $configFile, $jsonFT, $ruleset, $lastArrayElement);
        }

        $counter++;
        $lastWindowTitle = $windowTitle;
        $lastTimeStamp = $timeStamp;
    }

    socket_write($socketIPC[$agentID][0], str_pad($totalMatches, 1024), 1024);
    socket_close($socketIPC[$agentID][0]);
}

/* Parse Fraud Triangle phrases */

function parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $windowTitle, $timeStamp, $configFile, $jsonFT, $ruleset, $lastArrayElement)
{
    $timeStartparseFraudTrianglePhrases = microtime(true); 
    
    $matched = FALSE;
    $countOutput = 1;
    $matchesGlobalCount = 0;

    for ($lib = 1; $lib<=count($jsonFT); $lib++)
    {   
        foreach ($fraudTriangleTerms as $term => $value)
        {
            $rule = "BASELINE";

            if ($ruleset != "BASELINE") $steps = 2;
            else $steps = 1;

            for($i=1; $i<=$steps; $i++)
            {
                foreach ($jsonFT[$lib]['dictionary'][$rule][$term] as $field => $termPhrase)
                {
                    if (preg_match_all($termPhrase, $stringOfWords, $matches)) 
                    {
                        $matched = TRUE;
                        $now = DateTime::createFromFormat('U.u', microtime(true));
                        $end = $now->format("Y-m-d\TH:i:s.u");
                        $end = substr($end, 0, -3);
                        $matchTime = (string)$end."Z";
                        $domain = getUserDomain($agentID);
                        $matchCount = count($matches[0]);
                        $tone = "0";
                        $flag = "0"; 

                        /* Check phrase tone */

                        if (checkTone($stringOfWords, $lib) == true) $tone = "1";

                        /* Check phrase flag */

                        if (strpos($field, '*') !== false) $flag = "1";

                        /* Prepare the message to send through socket */

                        for ($j=0; $j<$matchCount; $j++)
                        {
                            $msgData = $matchTime." ".$agentID." ".$domain." TextEvent - ".$term." e: ".$timeStamp." w: ".str_replace('/', '', $termPhrase)." s: ".$value." m: ".$matchCount." p: ".$matches[0][$j]." t: ".$windowTitle." z: ".encRijndael($stringOfWords)." f: 0 n: ".$tone . " g: ".$flag;
                            $lenData = strlen($msgData);

                            /* Send message to Logstash */

                            socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_alerter_port']);       
                            
                            $matchesGlobalCount++;

                            /* Send message to Logfile */

                            logToFileAndSyslog("LOG_ALERT", $configFile['log_file'], "[INFO] - MatchTime[".$matchTime."] - EventTime[".$timeStamp."] AgentID[".$agentID."] TextEvent - Term[".$term."] Window[".$windowTitle."] Word[".$matches[0][$j]."] Phrase[".str_replace('/', '', $termPhrase)."] Score[".$value."] TotalMatches[".$matchCount."] Tone[".$tone."] Flag[".$flag."]");
                        
                            $countOutput++;
                        }                   
                    }
                }
                $rule = $ruleset;
            }
        }
    }
    
    $timeEndparseFraudTrianglePhrases = microtime(true);
    $executionTimeparseFraudTrianglePhrases = ($timeEndparseFraudTrianglePhrases - $timeStartparseFraudTrianglePhrases);

    return $matchesGlobalCount;
    
    // echo "Time taken parseFraudTrianglePhrases in seconds: ".$executionTimeparseFraudTrianglePhrases."\n";
}

/* Check for message tone */

function checkTone($message, $library)
{
    $toneSpanishFile = file("tone/negative_spanish.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
    $toneEnglishFile = file("tone/negative_english.txt", FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

    if ($library == 1) $lines = $toneSpanishFile;
    else $lines = $toneEnglishFile;

    foreach ($lines as $numLine => $line)
    {
        $toneWord = $line;
        $toneWordExpression = "/\\b(".$toneWord.")\\b/i";

        if (preg_match($toneWordExpression, $message)) return true;
    }    
    
    return false;
}

/* Check regular expressions */

function checkRegexp($fraudTriangleTerms, $jsonFT, $ruleset)
{
    $errors = false;
    $numberOfTerms = 0;
    
    if (!isset($jsonFT[1]['dictionary'][$ruleset]))
    {
        echo "[ERROR] The specified rule doesn't exist, please check ... \n";
        echo "[INFO] Exiting Fraud Triangle Analytics phrase matching processor ...\n\n";
        exit;
    }
    
    echo "[INFO] Start checking regular expressions on fraud triangle phrases ... \n";
    
    for ($lib = 1; $lib<=count($jsonFT); $lib++)
    {   
        foreach ($fraudTriangleTerms as $term => $value)
        {
            foreach ($jsonFT[$lib]['dictionary'][$ruleset][$term] as $field => $termPhrase)
            {
                if (@preg_match_all($termPhrase, null) === false) 
                {
                    $errors = true;
                    echo "[ERROR] Invalid regular expression in rule [".$ruleset."] term [".$term."] and phrase [".$field."]\n";
                }
                
                $numberOfTerms++;
            }
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

/* Count Message Flags per Endpoint */

function countMessageFlags($agentID, $index)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'term' => [ 'agentId' => $agentID ] ],
                        [ 'term' => [ 'messageFlag' => '1' ] ]
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
                               
                $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure');
                $totalWordCount = countWordsTypedByAgent($row_a['agent'], "TextEvent", $ESindex);
                $matchesRationalization = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['r'], $configFile_es_alerter_index);
                $matchesOpportunity = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['o'], $configFile_es_alerter_index);
                $matchesPressure = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['p'], $configFile_es_alerter_index);
                $messageFlags = countMessageFlags($row_a['agent'], $configFile_es_alerter_index);
                $totalWords = $totalWordCount['count'];
                $totalPressure = $matchesPressure['count'];
                $totalOpportunity = $matchesOpportunity['count'];
                $totalRationalization = $matchesRationalization['count'];
                $totalFlags = $messageFlags['count'];

                $result = mysqli_query($connection, "UPDATE t_agents SET totalwords='.$totalWords.', pressure='.$totalPressure.', opportunity='.$totalOpportunity.', rationalization='.$totalRationalization.', flags='.$totalFlags.' WHERE agent='".$row_a['agent']."'");

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
    ini_set('default_charset', 'utf-8');

    if (preg_match('#[0-9]#', $string)) return $string;

    $unwanted_chars = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', '\''=>'');

    $config_dic = pspell_config_create($language);
    pspell_config_mode($config_dic, PSPELL_FAST);
    $dictionary = pspell_new_config($config_dic);
    $replacement_suggest = false;
    $comma = false;
    $dot = false;
    $string = explode(' ', $string);

    foreach ($string as $key => $value)
    {
        if (strpos($value, ',') !== false)
        {
            $comma = true;
            $value = str_replace(",", "", $value);
        }
        else if (strpos($value, '.') !== false)
        {
            $dot = true;
            $value = str_replace(".", "", $value);
        }

        if(!pspell_check($dictionary, $value))
        {
            $suggestion = pspell_suggest($dictionary, $value);
            
            if (array_key_exists('0', $suggestion)) 
            {
                if(strtolower($suggestion[0]) != strtolower($value))
                {
                    if ($comma == true) $string[$key] = $suggestion[0] . ",";
                    else if ($dot == true) $string[$key] = $suggestion[0] . ".";
                    else $string[$key] = $suggestion[0];
                    $replacement_suggest = true;
                }
            }
        }
    }

    $finalWord = iconv("iso-8859-1","UTF-8//IGNORE", implode(' ', $string));

    return strtr($finalWord, $unwanted_chars);
}

/* Process Backspaces */

function processBackspaces($strInput)
{
    $strInput = str_replace("oemquotes", "", $strInput);
    $strInput = ltrim($strInput, "# ");

    if (strpos($strInput, '_') !== false)
    {
        $strInput = str_replace(" ","", $strInput);
    }

    $stringStack = new SplQueue();
    $poppedChar = null;
    $finalString = null;
    $search_char = "#";

    for($x = "0"; $x < strlen($strInput); $x++)
    {
        $currentChar = substr($strInput, $x, 1);

        switch($currentChar)
        {
            case "#":

                try
                {
                    $poppedChar = $stringStack->top();
                    $stringStack->pop();
                }
                catch (Exception $e) {}

                break;
            default:
                $stringStack->push($currentChar);
                break;
        }
    }

    $stringStack->rewind();

    while($stringStack->valid())
    {
        $finalString = $finalString . (string)$stringStack->current();
        $stringStack->next();
    }

    $finalString = str_replace("_"," ", $finalString);
    $finalString = str_replace("^","", $finalString);
    $finalString = str_replace("|","", $finalString);
    $finalString = str_replace(",",", ", $finalString);

    return $finalString;
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

    for ($lib = 1; $lib<=count($jsonFT); $lib++)
    {   
        foreach ($fraudTriangleTerms as $term => $value)
        {
            $rule = "BASELINE";

            if ($ruleset != "BASELINE") $steps = 2;
            else $steps = 1;

            for($i=1; $i<=$steps; $i++)
            {
                foreach ($jsonFT[$lib]['dictionary'][$rule][$term] as $field => $termPhrase)
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

    $fraudTriangleHeight = ['pressure' => 30, 
                            'opportunity' => 40, 
                            'rationalization' => 20];

    $flagHeight = 10;

    $fraudProbability = ['almost' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization'], 
                        'very' => $fraudTriangleHeight['opportunity'] + $fraudTriangleHeight['rationalization'], 
                        'maybe' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['opportunity'], 
                        'less' => $fraudTriangleHeight['pressure'] + $fraudTriangleHeight['rationalization']];

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
                $flag = $result['_source']['messageFlag'];
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

                    if ($flag != "0") 
                    {
                        $matchReason = $matchReason . "*";
                        $fraudProbDeduction = $fraudProbDeduction + $flagHeight;
                    }
                    
                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['pressure'] != 0 && $countTriangleMatch['opportunity'] != 0) 
                {
                    /* Put the alert in DB with P+O */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "PO";
                    $fraudProbDeduction = $fraudProbability['maybe'];

                    if ($flag != "0") 
                    {
                        $matchReason = $matchReason . "*";
                        $fraudProbDeduction = $fraudProbDeduction + $flagHeight;
                    }

                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['pressure'] != 0 && $countTriangleMatch['rationalization'] != 0)
                {
                    /* Put the alert in DB with P+R */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "PR";
                    $fraudProbDeduction = $fraudProbability['very'];

                    if ($flag != "0") 
                    {
                        $matchReason = $matchReason . "*";
                        $fraudProbDeduction = $fraudProbDeduction + $flagHeight;
                    }

                    $stringHistoryArchive = [$counter => [ 'phrase' => substr($stringOfWords, 0, 256)]];
                }
                else if ($countTriangleMatch['opportunity'] != 0 && $countTriangleMatch['rationalization'] != 0)
                {
                    /* Put the alert in DB with O+R */

                    if (is_in_array($stringHistoryArchive, "phrase", substr($stringOfWords, 0, 256))) continue; 

                    $deductionMatch = true;
                    $matchReason = "OR";
                    $fraudProbDeduction = $fraudProbability['less'];

                    if ($flag != "0") 
                    {
                        $matchReason = $matchReason . "*";
                        $fraudProbDeduction = $fraudProbDeduction + $flagHeight;
                    }

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
                    }
                }

                $counter++;
            }
        }
        while ($rowEndPointsHasAlerts = mysqli_fetch_array($resultEndPointsHasAlerts));
    }
}

/* Workflows engine */

function startWorkflows($ESAlerterIndex)
{
    global $connection;
    $superFinalQuery = array();

    /* SQL queries */

    include "../lbs/openDBconn.php";

    echo "[INFO] Starting Workflow Engine, analyzing flows ...\n";

    /* Database */

    mysqli_query($connection, "DROP TABLE t_wevents"); 
    mysqli_query($connection, "CREATE TABLE t_wevents (alertId varchar(512) PRIMARY KEY, indexId varchar(256) not null, department varchar(256) not null, agentId varchar(256) not null, alertType varchar(256) not null, eventTime datetime DEFAULT NULL, falsePositive int DEFAULT 0, messageTone int DEFAULT 0, messageFlag int DEFAULT 0, domain varchar(256) not null, application varchar(1024) not null, phrase varchar(512) not null)");

    /* Start */

    $eventMatches = getAllFraudTriangleAlerts($ESAlerterIndex);
    $eventData = json_decode(json_encode($eventMatches), true);

    foreach ($eventData['hits']['hits'] as $result)
    {
        if (isset($result['_source']['tags'])) continue;

        $departmentQuery = mysqli_query($connection, sprintf("SELECT ruleset from t_agents WHERE agent='%s'", $result["_source"]["agentId"])); 
        $departmentResult = mysqli_fetch_assoc($departmentQuery);
        $messageTone = 0;
        $messageFlag = 0;

        if (isset($result["_source"]["messageTone"])) $messageTone = $result["_source"]["messageTone"];
        if (isset($result["_source"]["messageFlag"])) $messageFlag = $result["_source"]["messageFlag"];

        mysqli_query($connection, sprintf("INSERT INTO t_wevents values('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s')", $result["_id"], $result["_index"], $departmentResult["ruleset"], $result["_source"]["agentId"], $result["_source"]["alertType"], $result["_source"]["eventTime"], $result["_source"]["falsePositive"], $result["_source"]["messageTone"], $result["_source"]["messageFlag"], $result["_source"]["userDomain"], decRijndael($result['_source']['windowTitle']), decRijndael($result['_source']['wordTyped'])));    
    }

    $queryWorkflows = mysqli_query($connection, "SELECT * FROM t_workflows");

    /* Traverse Workflows */

    while ($row = mysqli_fetch_array($queryWorkflows))
    {
        $flow = explode(",", $row["workflow"]);

        $elements = array("department", "alertType", "domain", "agentId", "application", "phrase", "operator");
        $counter = 0;
        $queryCounter = 1;

        $sqlQuery[$row["name"]][$queryCounter] = "SELECT * FROM t_wevents WHERE";

        foreach ($flow as $field)
        {   
            $clearField = explode("=", $field);   
            $workflowQuery[$row["name"]][$queryCounter][$elements[$counter]] = $field;
            $sqlQuery[$row["name"]][$queryCounter] = $sqlQuery[$row["name"]][$queryCounter] . " ".$elements[$counter]."='".$clearField[1]."' AND";
            $counter++;
            
            if ($counter == 7)
            {
                $queryCounter++;
                $sqlQuery[$row["name"]][$queryCounter] = "SELECT * FROM t_wevents WHERE";
                $counter = 0;
            }
        }

        /* Organize final SQL Queries */
        
        array_pop($sqlQuery[$row["name"]]);

        $sqlQuery[$row["name"]] = str_replace("AND operator='END' AND", "--", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND operator='AND' AND", "--", $sqlQuery[$row["name"]]);

        $sqlQuery[$row["name"]] = str_replace("PRESSURE", "pressure", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("OPPORTUNITY", "opportunity", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("RATIONALIZATION", "rationalization", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND alertType='ALL VERTICES'", "", $sqlQuery[$row["name"]]);

        $sqlQuery[$row["name"]] = str_replace("AND agentId='ALLE'", "", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND application='ALLA'", "", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND phrase='ALLP'", "", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND alertType='ALLV'", "", $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace("AND domain='ALLD'", "", $sqlQuery[$row["name"]]);

        $regApp = '/application=\'(\w*)(( \w*){1,})?(\w*)?\'/';
        $subApp = 'application LIKE \'%${1}${2}%\'';
        $sqlQuery[$row["name"]] = preg_replace($regApp, $subApp, $sqlQuery[$row["name"]]);

        $regPhrase = '/phrase=\'(\w*)(( \w*){1,})?(\w*)?\'/';
        $subPhrase = 'phrase LIKE \'%${1}${2}%\'';
        $sqlQuery[$row["name"]] = preg_replace($regPhrase, $subPhrase, $sqlQuery[$row["name"]]);

        $regAgent = '/agentId=\'(\w*)\'/';
        $subAgent = 'agentId LIKE \'${1}_%\'';
        $sqlQuery[$row["name"]] = preg_replace($regAgent, $subAgent, $sqlQuery[$row["name"]]);

        $sqlQuery[$row["name"]] = preg_replace('/\s+/', ' ', $sqlQuery[$row["name"]]);
        $sqlQuery[$row["name"]] = str_replace(" --", " AND falsePositive='0' ORDER BY eventTime ASC", $sqlQuery[$row["name"]]);
    }

    /* Traverse generated queries */

    foreach ($sqlQuery as $name => $query)
    {
        $counter = 1;

        /* Verify if query works or not */ 

        foreach ($query as $key => $value)
        {
            $superQuery = mysqli_query($connection, $value);

            if (mysqli_num_rows($superQuery) != 0) 
            { 
                $validation[$name][$counter] = true;
            }
            else $validation[$name][$counter] = false;

            $counter++;
        }
    }

    /* Trigger workflow or not depending if query works */

    foreach ($validation as $name => $query)
    {
        $checkPoint = true;

        foreach ($query as $key => $value)
        {
            if ($value == false) 
            {
                $checkPoint = false;
                break;
            }
        }
        if ($checkPoint == true) 
        {
            /* Only triggered true has the following loop searching for interval match */

            foreach ($sqlQuery as $nameID => $query)
            {
                /* Only do that for TRUE Workflows */

                if ($name == $nameID)
                {
                    /* if it's only one query, trigger it */

                    if (count($query) == 1)
                    {
                        $queryTrueWorkflow[$name][] = $query[1];
                        $superFinalQuery[$name] = $query[1];

                        /* Search the workflow interval, custodian, flag and tone */

                        $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                        while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                        {
                            $interval = $row["interval"];
                            $custodian = $row["custodian"];
                            $tone = $row["tone"];
                            $flag = $row["flag"];
                        }      

                        /* Finally execute the query and populate triggered table */

                        $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                        $rowCount = mysqli_num_rows($resultQuery);

                        if ($rowCount > 0)
                        {
                            $realMatches = 0;

                            while ($row = mysqli_fetch_array($resultQuery))
                            {
                                $idS = $row["alertId"];

                                /* Verify if the trigger already exist */

                                $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                                $existCount = mysqli_num_rows($existQuery);

                                /* If not exist, insert trigger & send alert */

                                if ($existCount == 0)
                                {
                                    $alert_workflowName = $name;
                                    $alert_amount = count($query);
                                    $eventIds = explode(" ", $idS);
                                    
                                    for ($i=0; $i<count($query); $i++)
                                    {        
                                        $idsQuery[$i] = $eventIds[$i];

                                        $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                        $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);

                                        $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                        $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                        $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                        $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                        $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                        $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                        $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                        $alert_eventCompany[$i] = $alert_eventDomain[0];
                                        $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                        $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                        $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                                    }

                                    /* Check if the event was tone negative and have flags */

                                    $sumTones = 0;
                                    $proceedTones = false;
                                    $sumFlags = 0;
                                    $proceedFlags = false;

                                    foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                                    foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                                    if ($sumTones >= $tone) $proceedTones = true;
                                    if ($sumFlags >= $flag) $proceedFlags = true;

                                    if ($proceedTones == true && $proceedFlags == true)
                                    {
                                        $realMatches++;

                                        mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                        /* Send message alert */

                                        $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                        include $mailEventWFPath;
                                        mail($to, $subject, wordwrap($message), $headers);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        /* If there is more than one query */

                        foreach ($query as $i => $j)
                        {
                            /* Store true workflow queries, so then we can loop over it  */

                            $queryTrueWorkflow[$name][] = $j;
                        }
                    }
                }
            }
        }
    }

    /* Build the final queries for workflow matching with interval (compound workflows) */

    if (isset($queryTrueWorkflow))
    {
        foreach ($queryTrueWorkflow as $name => $query)
        {
            /* How many queries has the workflow */

            if(count($query) == 2)
            {
                /* Search the workflow interval, custodian, flag and tone */

                $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                {
                    $interval = $row["interval"];
                    $custodian = $row["custodian"];
                    $tone = $row["tone"];
                    $flag = $row["flag"];
                }      

                /* Join the final query */

                $left = "SELECT A.alertId as alertIdA, A.indexid as indexIdA, A.department as departmentA, A.agentId as agentIdA, A.alertType as alertTypeA, A.domain as domainA, A.phrase as phraseA, A.eventTime as eventTimeA, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, timestampdiff(day, A.eventTime, B.eventTime) AS timeDifference FROM (";
                $right = " WHERE timestampdiff(day, A.eventTime, B.eventTime) BETWEEN -".$interval." AND ".$interval.";";
                $superFinalQuery[$name] = $left . $queryTrueWorkflow[$name][0] . ") AS A, (" . $queryTrueWorkflow[$name][1] . ") AS B" . $right;

                /* Finally execute the query and populate triggered table */

                $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                $rowCount = mysqli_num_rows($resultQuery);

                if ($rowCount > 0)
                {
                    while ($row = mysqli_fetch_array($resultQuery))
                    {
                        $idS = $row["alertIdA"] . " " . $row["alertIdB"];

                        /* Verify if the trigger already exist */

                        $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                        $existCount = mysqli_num_rows($existQuery);

                        /* If not exist, insert trigger & send alert */

                        if ($existCount == 0)
                        {
                            $alert_workflowName = $name;
                            $alert_amount = count($query);
                            $eventIds = explode(" ", $idS);
                            
                            for ($i=0; $i<count($query); $i++)
                            {        
                                $idsQuery[$i] = $eventIds[$i];

                                $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);

                                $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                $alert_eventCompany[$i] = $alert_eventDomain[0];
                                $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                            }

                            /* Check at least one event with specified tone if selected was negative and flag */

                            $sumTones = 0;
                            $proceedTones = false;
                            $sumFlags = 0;
                            $proceedFlags = false;

                            foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                            foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                            if ($sumTones >= $tone) $proceedTones = true;
                            if ($sumFlags >= $flag) $proceedFlags = true;

                            if ($proceedTones == true && $proceedFlags == true)
                            {
                                mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                /* Send message alert */

                                $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                include $mailEventWFPath;
                                mail($to, $subject, wordwrap($message), $headers);
                            }
                        }
                    }
                }
            }
            else if(count($query) == 3)
            {
                /* Search the workflow interval, custodian, flag and tone */

                $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                {
                    $interval = $row["interval"];
                    $custodian = $row["custodian"];
                    $tone = $row["tone"];
                    $flag = $row["flag"];
                }      

                /* Join the final query */

                $left = "SELECT A.alertId as alertIdA, A.indexid as indexIdA, A.department as departmentA, A.agentId as agentIdA, A.alertType as alertTypeA, A.domain as domainA, A.phrase as phraseA, A.eventTime as eventTimeA, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, C.alertId as alertIdC, C.indexid as indexIdC, C.department as departmentC, C.agentId as agentIdC, C.alertType as alertTypeC, C.domain as domainC, C.phrase as phraseC, C.eventTime as eventTimeC, timestampdiff(day, A.eventTime, B.eventTime) AS timeDifferenceB, timestampdiff(day, A.eventTime, C.eventTime) AS timeDifferenceC FROM (";
                $right = " WHERE (timestampdiff(day, A.eventTime, B.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, C.eventTime) BETWEEN -".$interval." AND ".$interval.");";
                $superFinalQuery[$name] = $left . $queryTrueWorkflow[$name][0] . ") AS A, (" . $queryTrueWorkflow[$name][1] . ") AS B, (" . $queryTrueWorkflow[$name][2] . ") AS C" . $right;

                /* Finally execute the query and populate triggered table */

                $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                $rowCount = mysqli_num_rows($resultQuery);

                if ($rowCount > 0)
                {
                    while ($row = mysqli_fetch_array($resultQuery))
                    {
                        $idS = $row["alertIdA"] . " " . $row["alertIdB"] . " " . $row["alertIdC"];

                        /* Verify if the trigger already exist */

                        $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                        $existCount = mysqli_num_rows($existQuery);

                        /* If not exist, insert trigger & send alert */

                        if ($existCount == 0)
                        {
                            $alert_workflowName = $name;
                            $alert_amount = count($query);
                            $eventIds = explode(" ", $idS);
                            
                            for ($i=0; $i<count($query); $i++)
                            {        
                                $idsQuery[$i] = $eventIds[$i];

                                $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);

                                $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                $alert_eventCompany[$i] = $alert_eventDomain[0];
                                $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                            }

                            /* Check at least one event with specified tone if selected was negative and flag */

                            $sumTones = 0;
                            $proceedTones = false;
                            $sumFlags = 0;
                            $proceedFlags = false;

                            foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                            foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                            if ($sumTones >= $tone) $proceedTones = true;
                            if ($sumFlags >= $flag) $proceedFlags = true;

                            if ($proceedTones == true && $proceedFlags == true)
                            {
                                mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                /* Send message alert */

                                $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                include $mailEventWFPath;
                                mail($to, $subject, wordwrap($message), $headers);
                            }
                        }
                    }
                }
            }
            else if(count($query) == 4)
            {
                /* Search the workflow interval, custodian, flag and tone */

                $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                {
                    $interval = $row["interval"];
                    $custodian = $row["custodian"];
                    $tone = $row["tone"];
                    $flag = $row["flag"];
                }

                /* Join the final query */

                $left = "SELECT A.alertId as alertIdA, A.indexid as indexIdA, A.department as departmentA, A.agentId as agentIdA, A.alertType as alertTypeA, A.domain as domainA, A.phrase as phraseA, A.eventTime as eventTimeA, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, C.alertId as alertIdC, C.indexid as indexIdC, C.department as departmentC, C.agentId as agentIdC, C.alertType as alertTypeC, C.domain as domainC, C.phrase as phraseC, C.eventTime as eventTimeC, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, D.alertId as alertIdD, D.indexid as indexIdD, D.department as departmentD, D.agentId as agentIdD, D.alertType as alertTypeD, D.domain as domainD, D.phrase as phraseD, D.eventTime as eventTimeD, timestampdiff(day, A.eventTime, B.eventTime) AS timeDifferenceB, timestampdiff(day, A.eventTime, C.eventTime) AS timeDifferenceC, timestampdiff(day, A.eventTime, D.eventTime) AS timeDifferenceD FROM (";
                $right = " WHERE (timestampdiff(day, A.eventTime, B.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, C.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, D.eventTime) BETWEEN -".$interval." AND ".$interval.");";
                $superFinalQuery[$name] = $left . $queryTrueWorkflow[$name][0] . ") AS A, (" . $queryTrueWorkflow[$name][1] . ") AS B, (" . $queryTrueWorkflow[$name][2] . ") AS C, (" . $queryTrueWorkflow[$name][3] . ") AS D" . $right;

                /* Finally execute the query and populate triggered table */

                $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                $rowCount = mysqli_num_rows($resultQuery);

                if ($rowCount > 0)
                {
                    while ($row = mysqli_fetch_array($resultQuery))
                    {
                        $idS = $row["alertIdA"] . " " . $row["alertIdB"] . " " . $row["alertIdC"] . " " . $row["alertIdD"];

                        /* Verify if the trigger already exist */

                        $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                        $existCount = mysqli_num_rows($existQuery);

                        /* If not exist, insert trigger & send alert */

                        if ($existCount == 0)
                        {
                            $alert_workflowName = $name;
                            $alert_amount = count($query);
                            $eventIds = explode(" ", $idS);
                            
                            for ($i=0; $i<count($query); $i++)
                            {        
                                $idsQuery[$i] = $eventIds[$i];

                                $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);

                                $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                $alert_eventCompany[$i] = $alert_eventDomain[0];
                                $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                            }

                            /* Check at least one event with specified tone if selected was negative and flag */

                            $sumTones = 0;
                            $proceedTones = false;
                            $sumFlags = 0;
                            $proceedFlags = false;

                            foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                            foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                            if ($sumTones >= $tone) $proceedTones = true;
                            if ($sumFlags >= $flag) $proceedFlags = true;

                            if ($proceedTones == true && $proceedFlags == true)
                            {
                                mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                /* Send message alert */

                                $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                include $mailEventWFPath;
                                mail($to, $subject, wordwrap($message), $headers);
                            }
                        }
                    }
                }
            }
            else if(count($query) == 5)
            {
                /* Search the workflow interval, custodian, flag and tone */

                $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                {
                    $interval = $row["interval"];
                    $custodian = $row["custodian"];
                    $tone = $row["tone"];
                    $flag = $row["flag"];
                }

                /* Join the final query */

                $left = "SELECT A.alertId as alertIdA, A.indexid as indexIdA, A.department as departmentA, A.agentId as agentIdA, A.alertType as alertTypeA, A.domain as domainA, A.phrase as phraseA, A.eventTime as eventTimeA, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, C.alertId as alertIdC, C.indexid as indexIdC, C.department as departmentC, C.agentId as agentIdC, C.alertType as alertTypeC, C.domain as domainC, C.phrase as phraseC, C.eventTime as eventTimeC, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, D.alertId as alertIdD, D.indexid as indexIdD, D.department as departmentD, D.agentId as agentIdD, D.alertType as alertTypeD, D.domain as domainD, D.phrase as phraseD, D.eventTime as eventTimeD, E.alertId as alertIdE, E.indexid as indexIdE, E.department as departmentE, E.agentId as agentIdE, E.alertType as alertTypeE, E.domain as domainE, E.phrase as phraseE, E.eventTime as eventTimeE, timestampdiff(day, A.eventTime, B.eventTime) AS timeDifferenceB, timestampdiff(day, A.eventTime, C.eventTime) AS timeDifferenceC, timestampdiff(day, A.eventTime, D.eventTime) AS timeDifferenceD, timestampdiff(day, A.eventTime, E.eventTime) AS timeDifferenceE FROM (";
                $right = " WHERE (timestampdiff(day, A.eventTime, B.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, C.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, D.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, E.eventTime) BETWEEN -".$interval." AND ".$interval.");";
                $superFinalQuery[$name] = $left . $queryTrueWorkflow[$name][0] . ") AS A, (" . $queryTrueWorkflow[$name][1] . ") AS B, (" . $queryTrueWorkflow[$name][2] . ") AS C, (" . $queryTrueWorkflow[$name][3] . ") AS D, (" . $queryTrueWorkflow[$name][4] . ") AS E" . $right;

                /* Finally execute the query and populate triggered table */

                $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                $rowCount = mysqli_num_rows($resultQuery);

                if ($rowCount > 0)
                {
                    while ($row = mysqli_fetch_array($resultQuery))
                    {
                        $idS = $row["alertIdA"] . " " . $row["alertIdB"] . " " . $row["alertIdC"] . " " . $row["alertIdD"] . " " . $row["alertIdE"];

                        /* Verify if the trigger already exist */

                        $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                        $existCount = mysqli_num_rows($existQuery);

                        /* If not exist, insert trigger & send alert */

                        if ($existCount == 0)
                        {
                            $alert_workflowName = $name;
                            $alert_amount = count($query);
                            $eventIds = explode(" ", $idS);
                                
                            for ($i=0; $i<count($query); $i++)
                            {        
                                $idsQuery[$i] = $eventIds[$i];
    
                                $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);
    
                                $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                $alert_eventCompany[$i] = $alert_eventDomain[0];
                                $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                            }

                            /* Check at least one event with specified tone if selected was negative and flag */

                            $sumTones = 0;
                            $proceedTones = false;
                            $sumFlags = 0;
                            $proceedFlags = false;

                            foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                            foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                            if ($sumTones >= $tone) $proceedTones = true;
                            if ($sumFlags >= $flag) $proceedFlags = true;

                            if ($proceedTones == true && $proceedFlags == true)
                            {
                                mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                /* Send message alert */

                                $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                include $mailEventWFPath;
                                mail($to, $subject, wordwrap($message), $headers);
                            }
                        }
                    }
                }
            }
            else if(count($query) == 6)
            {
                /* Search the workflow interval, custodian, flag and tone */

                $intervalCustodianToneFlagQuery = mysqli_query($connection, sprintf("SELECT * FROM t_workflows WHERE name='%s'", $name));

                while ($row = mysqli_fetch_array($intervalCustodianToneFlagQuery)) 
                {
                    $interval = $row["interval"];
                    $custodian = $row["custodian"];
                    $tone = $row["tone"];
                    $flag = $row["flag"];
                }

                /* Join the final query */

                $left = "SELECT A.alertId as alertIdA, A.indexid as indexIdA, A.department as departmentA, A.agentId as agentIdA, A.alertType as alertTypeA, A.domain as domainA, A.phrase as phraseA, A.eventTime as eventTimeA, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, C.alertId as alertIdC, C.indexid as indexIdC, C.department as departmentC, C.agentId as agentIdC, C.alertType as alertTypeC, C.domain as domainC, C.phrase as phraseC, C.eventTime as eventTimeC, B.alertId as alertIdB, B.indexid as indexIdB, B.department as departmentB, B.agentId as agentIdB, B.alertType as alertTypeB, B.domain as domainB, B.phrase as phraseB, B.eventTime as eventTimeB, D.alertId as alertIdD, D.indexid as indexIdD, D.department as departmentD, D.agentId as agentIdD, D.alertType as alertTypeD, D.domain as domainD, D.phrase as phraseD, D.eventTime as eventTimeD, E.alertId as alertIdE, E.indexid as indexIdE, E.department as departmentE, E.agentId as agentIdE, E.alertType as alertTypeE, E.domain as domainE, E.phrase as phraseE, E.eventTime as eventTimeE, F.alertId as alertIdF, F.indexid as indexIdF, F.department as departmentF, F.agentId as agentIdF, F.alertType as alertTypeF, F.domain as domainF, F.phrase as phraseF, F.eventTime as eventTimeF, timestampdiff(day, A.eventTime, B.eventTime) AS timeDifferenceB, timestampdiff(day, A.eventTime, C.eventTime) AS timeDifferenceC, timestampdiff(day, A.eventTime, D.eventTime) AS timeDifferenceD, timestampdiff(day, A.eventTime, E.eventTime) AS timeDifferenceE, timestampdiff(day, A.eventTime, F.eventTime) AS timeDifferenceF FROM (";
                $right = " WHERE (timestampdiff(day, A.eventTime, B.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, C.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, D.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, E.eventTime) BETWEEN -".$interval." AND ".$interval.") AND (timestampdiff(day, A.eventTime, F.eventTime) BETWEEN -".$interval." AND ".$interval.");";
                $superFinalQuery[$name] = $left . $queryTrueWorkflow[$name][0] . ") AS A, (" . $queryTrueWorkflow[$name][1] . ") AS B, (" . $queryTrueWorkflow[$name][2] . ") AS C, (" . $queryTrueWorkflow[$name][3] . ") AS D, (" . $queryTrueWorkflow[$name][4] . ") AS E, (" . $queryTrueWorkflow[$name][5] . ") AS F" . $right;

                /* Finally execute the query and populate triggered table */

                $resultQuery = mysqli_query($connection, $superFinalQuery[$name]);
                $rowCount = mysqli_num_rows($resultQuery);

                if ($rowCount > 0)
                {
                    while ($row = mysqli_fetch_array($resultQuery))
                    {
                        $idS = $row["alertIdA"] . " " . $row["alertIdB"] . " " . $row["alertIdC"] . " " . $row["alertIdD"] . " " . $row["alertIdE"] . " " . $row["alertIdF"];

                        /* Verify if the trigger already exist */

                        $existQuery = mysqli_query($connection, sprintf("SELECT * FROM t_wtriggers WHERE ids='%s' AND name='%s'", $idS, $name));
                        $existCount = mysqli_num_rows($existQuery);

                        /* If not exist, insert trigger & send alert */

                        if ($existCount == 0)
                        {
                            $alert_workflowName = $name;
                            $alert_amount = count($query);
                            $eventIds = explode(" ", $idS);
                            
                            for ($i=0; $i<count($query); $i++)
                            {        
                                $idsQuery[$i] = $eventIds[$i];

                                $allEventsQuery = mysqli_query($connection, sprintf("SELECT * from t_wevents WHERE alertId='%s'", $idsQuery[$i])); 
                                $allEventsQueryResult = mysqli_fetch_assoc($allEventsQuery);

                                $alert_eventTone[$i] = $allEventsQueryResult['messageTone'];
                                $alert_eventFlag[$i] = $allEventsQueryResult['messageFlag'];
                                $alert_eventDate[$i] = $allEventsQueryResult['eventTime'];
                                $alert_eventAgentId = preg_split('/_/', $allEventsQueryResult['agentId']);
                                $alert_eventEndpoint[$i] = $alert_eventAgentId[0];
                                $alert_eventType[$i] = $allEventsQueryResult['alertType'];
                                $alert_eventDomain = preg_split('/\./', $allEventsQueryResult['domain']);
                                $alert_eventCompany[$i] = $alert_eventDomain[0];
                                $alert_eventApplication[$i] = $allEventsQueryResult['application'];
                                $alert_eventDepartment[$i] = $allEventsQueryResult['department'];
                                $alert_eventPhrase[$i] = $allEventsQueryResult['phrase'];
                            }

                            /* Check at least one event with specified tone if selected was negative and flag */

                            $sumTones = 0;
                            $proceedTones = false;
                            $sumFlags = 0;
                            $proceedFlags = false;

                            foreach ($alert_eventTone as $key => $value) $sumTones = $sumTones + $value;
                            foreach ($alert_eventFlag as $key => $value) $sumFlags = $sumFlags + $value;

                            if ($sumTones >= $tone) $proceedTones = true;
                            if ($sumFlags >= $flag) $proceedFlags = true;

                            if ($proceedTones == true && $proceedFlags == true)
                            {
                                mysqli_query($connection, sprintf("INSERT INTO t_wtriggers(date, name, ids) values('%s','%s','%s')", date('Y-m-d H:i:s.u'), $name, $idS));

                                /* Send message alert */

                                $mailEventWFPath = $configFile['php_document_root']."/lbs/mailEventWF.php";
                                include $mailEventWFPath;
                                mail($to, $subject, wordwrap($message), $headers);
                            }
                        }
                    }
                }
            }
        }
    }

    /* Drop temporary tables */

    mysqli_query($connection, "DROP TABLE t_wevents");

    /* Troubleshooting for validation queries */

    // var_dump($validation); var_dump($sqlQuery); var_dump($queryTrueWorkflow); var_dump($superFinalQuery);

    /* Compute workflow triggers */

    $workflowNameQuery = mysqli_query($connection, "SELECT name FROM t_workflows");

    while ($row = mysqli_fetch_array($workflowNameQuery))
    {
        $flowName = $row["name"];
        $countFlowQuery = mysqli_query($connection, "SELECT COUNT(*) AS workflowCount FROM t_wtriggers WHERE name=\"" . $flowName . "\"");
        $countFlowFetch = mysqli_fetch_assoc($countFlowQuery);
        $flowCount = $countFlowFetch['workflowCount'];
    
        /* Put trigger count in main Workflows table */
    
        mysqli_query($connection, sprintf("UPDATE t_workflows SET triggers='%s' WHERE name='%s'", $flowCount, $flowName));
    }
}

/* Search all Fraud Triangle Matches */

function getAllFraudTriangleAlerts($index)
{
    $querySize = 10000;

    $matchesParams = [
        'index' => $index,
        'type' => 'AlertEvent',
        'body' => [
            'size' => $querySize,
            'sort' => [
                [ '@timestamp' => [ 'order' => 'desc' ] ]
            ],
            '_source' => [
                'exclude' => [ 'stringHistory', 'message' ]
            ],
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match_all' => [ 'boost' => 1 ] ]
                    ],
                    'must_not' => [
                        [ 'match' => [ 'falsePositive' => '2'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
}

/* Fraud Triangle Metrics */

function fraudTriangleMetrics()
{
    global $connection;

    echo "[INFO] Starting Fraud Triangle Metrics, processing data ...\n";

    $fraudVertices = array("Pressure", "Opportunity", "Rationalization");
    $queryEndpointsSQLRuleset = "SELECT agent, ruleset, domain, fraudtriangle FROM (SELECT agent, domain, ruleset, SUM(pressure+opportunity+rationalization) AS fraudtriangle FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset, pressure, opportunity, rationalization FROM t_agents GROUP BY agent) AS agents GROUP BY agent) AS duplicates WHERE fraudtriangle > 0";
    $resultSQLRuleset = mysqli_query($connection, $queryEndpointsSQLRuleset);

    while ($row = mysqli_fetch_array($resultSQLRuleset))
    { 
        $endpointID = $row['agent'] . "_*";
        $queryEndpoint = mysqli_query($connection, sprintf("SELECT * FROM t_metrics WHERE endpoint = '%s'", $row['agent']));
            
        if (mysqli_num_rows($queryEndpoint) == 0) $queryResult = mysqli_query($connection, sprintf("INSERT INTO t_metrics(endpoint, domain, ruleset) values ('%s', '%s', '%s')", $row['agent'], $row['domain'], $row['ruleset']));

        for ($i = 0; $i <= 11; $i++) 
        {
            $months[] = date("Y-m", strtotime( date( 'Y-m-01' )." -$i months"));
            $daterangefrom = $months[$i] . "-01";
            $daterangeto = $months[$i] . "-18||/M";
            $monthName[] = substr(date("F", strtotime($months[$i])), 0, 3);

            foreach($fraudVertices as $vertice)
            {
                if ($vertice == "Pressure") $resultAlert = countFraudTriangleMatchesWithDateRangeWithTermWithAgentID("1 0 0", "logstash-alerter-*", $daterangefrom, $daterangeto, $endpointID);
                if ($vertice == "Opportunity") $resultAlert = countFraudTriangleMatchesWithDateRangeWithTermWithAgentID("0 1 0", "logstash-alerter-*", $daterangefrom, $daterangeto, $endpointID);
                if ($vertice == "Rationalization") $resultAlert = countFraudTriangleMatchesWithDateRangeWithTermWithAgentID("0 0 1", "logstash-alerter-*", $daterangefrom, $daterangeto, $endpointID);
                    
                $queryResult = mysqli_query($connection, sprintf("UPDATE t_metrics SET %s = '%s' WHERE endpoint = '%s'", $i.substr($vertice, 0, 1), $resultAlert['count'], $row['agent']));
            }
        }
    }
}

/* Count All Fraud Triangle matches by date range with fraud term with Agent ID */

function countFraudTriangleMatchesWithDateRangeWithTermWithAgentID($fraudTerms, $index, $from, $to, $agentID)
{
    $terms = explode(" ", $fraudTerms);
    $pressureValue = $terms[0];
    $opportunityValue = $terms[1];
    $rationalizationValue = $terms[2];

    if ($pressureValue == "1" && $opportunityValue == "1" && $rationalizationValue == "1")
    {
        $matchesParams = [
            'index' => $index,
            'type' => 'AlertEvent',
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'pressure' ] ],
                            [ 'term' => [ 'alertType' => 'opportunity' ] ],
                            [ 'term' => [ 'alertType' => 'rationalization' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "1" && $opportunityValue == "1" && $rationalizationValue == "0")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'pressure' ] ],
                            [ 'term' => [ 'alertType' => 'opportunity' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "1" && $opportunityValue == "0" && $rationalizationValue == "0")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'pressure' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "0" && $opportunityValue == "1" && $rationalizationValue == "1")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'opportunity' ] ],
                            [ 'term' => [ 'alertType' => 'rationalization' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "0" && $opportunityValue == "1" && $rationalizationValue == "0")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'opportunity' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "0" && $opportunityValue == "0" && $rationalizationValue == "1")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'rationalization' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }
    else if ($pressureValue == "1" && $opportunityValue == "0" && $rationalizationValue == "1")
    {
        $matchesParams = [
            'index' => $index, 
            'type' => 'AlertEvent', 
            'body' => [ 
                'query' => [
                    'bool' => [
                        'minimum_should_match' => '1',
                        'must' => [
                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                            [ 'wildcard' => [ 'agentId' => $agentID ] ]
                        ],
                        'should' => [
                            [ 'term' => [ 'alertType' => 'pressure' ] ],
                            [ 'term' => [ 'alertType' => 'rationalization' ] ]
                        ],
                        'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                        ]
                    ]
                ]
            ]
        ];
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
}
?>
