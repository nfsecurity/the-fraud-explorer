<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
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
                'term' => [ 'agentId.raw' => $agentID ]
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
                        'term' => [ 'agentId.raw' => $agentID ]
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

/*  Count words by days */

function wordsByDays($value, $domain)
{   
    $userDomain = str_replace(".", "_", $domain);
    $query = "SELECT * FROM t_words_".$userDomain;
    $result = mysql_query($query);

    if(empty($result)) 
    {
        $query = "CREATE TABLE t_words_".$userDomain." (
        monday int DEFAULT NULL,
        tuesday int DEFAULT NULL,
        wednesday int DEFAULT NULL,
        thursday int DEFAULT NULL,
        friday int DEFAULT NULL,
        saturday int DEFAULT NULL,
        sunday int DEFAULT NULL)";
        
        $insert = "INSERT INTO t_words_".$userDomain." (
        monday, tuesday, wednesday, thursday, friday, saturday, sunday) 
        VALUES ('0', '0', '0', '0', '0', '0', '0')";
        
        $resultQuery = mysql_query($query);
        $resultInsert = mysql_query($insert);     
    }
    
    $origDate = explode(" ", $value);
    $destTime = strtotime($origDate[0]);
    $weekDay = date('l', $destTime);
    $weekDay = strtolower($weekDay);
    
    mysql_query(sprintf("UPDATE t_words_%s SET %s=%s + 1", $userDomain, $weekDay, $weekDay));
    mysql_query(sprintf("UPDATE t_words SET %s=%s + 1", $weekDay, $weekDay));
    
    return $weekDay;
}

/*  Clear all word counters */

function clearWords()
{   
    $query = "SELECT domain FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY domain";    
    $result = mysql_query($query);

    while($row = mysql_fetch_array($result))
    {
        $domain = str_replace(".", "_", $row['domain']);
        $queryWords = "UPDATE t_words_".$domain." SET monday=0, tuesday=0, wednesday=0, thursday=0, friday=0, saturday=0, sunday=0";
        $resultQuery = mysql_query($queryWords);
    }
    
    $queryTotalWords = "UPDATE t_words SET monday=0, tuesday=0, wednesday=0, thursday=0, friday=0, saturday=0, sunday=0";
    $resultTotalQuery = mysql_query($queryTotalWords);
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
    $resultAlertData=curl_exec($ch);
    curl_close($ch);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_URL, $urlAlertStatus);
    $resultAlertStatus=curl_exec($ch);
    curl_close($ch);
}

/* Start data procesing */ 

function startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset, $lastArrayElement)
{   
    $GLOBALS['arrayPosition'] = 0;
    getMultiArrayData($typedWords, "typedWord", "applicationTitle", "sourceTimestamp", "userDomain", $agentID."_typedWords");
    $arrayOfWordsAndWindows = $GLOBALS[$agentID."_typedWords"];
    
    foreach($arrayOfWordsAndWindows as $arrayKey=>$arrayValue) wordsByDays($arrayValue[2], $arrayValue[3]);
    
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

                    logToFile($configFile['log_file'], "[INFO] - MatchTime[".$matchTime."] - EventTime[".$timeStamp."] AgentID[".$agentID."] TextEvent - Term[".$term."] Window[".$windowTitle."] Word[".$matches[0][0]."] Phrase[".str_replace('/', '', $termPhrase)."] Score[".$value."] TotalMatches[".count($matches[0])."]");

                    $countOutput++;
                }
            }
            $rule = $ruleset;
        }
    }
}

/* Get ruleset from agent */

function getRuleset($agentID)
{
    $rulesetQuery = sprintf("SELECT ruleset FROM t_agents WHERE agent='%s'", $agentID);
    $rulesetExecution = mysql_query($rulesetQuery);
    $rowRuleset = mysql_fetch_assoc($rulesetExecution);
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
                        [ 'term' => [ 'agentId.raw' => $agentID ] ],
                        [ 'term' => [ 'alertType.raw' => $fraudTerm ] ]
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
                'bool' => [
                    'must' => [
                        [ 'term' => [ 'agentId.raw' => $agentID ] ],
                        [ 'term' => [ 'eventType.raw' => $alertType ] ]
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
    echo "[INFO] Populating SQL-Database with Fraud Triangle Analytics Insights by agent ...\n";

    $resultQuery = mysql_query("SELECT agent FROM t_agents");

    if ($row_a = mysql_fetch_array($resultQuery))
    {
        do
        {
            $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');
            $totalWordCount = countWordsTypedByAgent($row_a['agent'], "TextEvent", $ESindex);
            $matchesRationalization = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['r'], $configFile_es_alerter_index);
            $matchesOpportunity = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['o'], $configFile_es_alerter_index);
            $matchesPressure = countFraudTriangleMatches($row_a['agent'], $fraudTriangleTerms['p'], $configFile_es_alerter_index);
            $totalWords = $totalWordCount['count'];
            $totalPressure = $matchesPressure['count'];
            $totalOpportunity = $matchesOpportunity['count'];
            $totalRationalization = $matchesRationalization['count'];

            $result = mysql_query("UPDATE t_agents SET totalwords='.$totalWords.', pressure='.$totalPressure.', opportunity='.$totalOpportunity.', rationalization='.$totalRationalization.' WHERE agent='".$row_a['agent']."'");
        }
        while ($row_a = mysql_fetch_array($resultQuery));
    }
}

function getUserDomain($agentID)
{
    $result = mysql_query("SELECT domain FROM t_agents WHERE agent='".$agentID."'");
    $row = mysql_fetch_array($result);
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
    $deleteQuery = "DELETE FROM t_agents WHERE agent in ('johndoe_90214c1_agt', 'nigel_abc14c1_agt', 'desmond_402vcc4_agt', 'spruce_s0214ck_agt', 'fletch_80j14g1_agt', 'ingredia_tq2v4c1_agt', 'archibald_b0314cm_agt', 'niles_1011jcl_agt', 'lurch_t021ycp_agt', 'eleanor_1114c3_agt', 'gordon_bbb94cc_agt', 'gustav_cht14f2_agt', 'jason_j8g12cg_agt', 'burgundy_18hg4cj_agt', 'benjamin_0001kc9_agt')";
    
    $resultQuery = mysql_query($deleteQuery);
    
    $insertQuery = "INSERT INTO t_agents (agent, heartbeat, system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization) VALUES ('johndoe_90214c1_agt', '2017-04-15 07:46:12', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.7', 'John Doe', 'BASELINE', 'male', '12723', '8', '10', '7'), ('nigel_abc14c1_agt', '2017-04-15 08:21:10', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.8', 'Nigel Eagle', 'BASELINE', 'female', '7321', '25', '0', '0'), ('desmond_402vcc4_agt', '2017-04-15 09:34:18', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.9', 'Desmond Wiedenbauer', 'BASELINE', 'male', '1983', '0', '25', '0'), ('spruce_s0214ck_agt', '2017-04-06 05:36:20', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.10', 'Spruce Bellevedere', 'BASELINE', 'male', '3000', '0', '0', '25'), ('fletch_80j14g1_agt', '2017-04-15 17:01:12', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.11', 'Fletch Nigel', 'BASELINE', 'male', '1560', '10', '10', '5'), ('ingredia_tq2v4c1_agt', '2017-04-06 03:11:02', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.12', 'Ingredia Douchebag', 'BASELINE', 'female', '3489', '5', '5', '15'), ('archibald_b0314cm_agt', '2017-04-06 09:14:37', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.13', 'Archibald Gibson', 'BASELINE', 'male', '921', '20', '2', '3'), ('niles_1011jcl_agt', '2017-04-15 02:37:13', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.14', 'Niles Ameter', 'BASELINE', 'male', '7528', '9', '13', '3'), ('lurch_t021ycp_agt', '2017-04-15 19:33:49', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.15', 'Lurch Barrow', 'BASELINE', 'male', '9800', '9', '5', '11'), ('eleanor_1114c3_agt', '2017-04-15 03:36:11', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.16', 'Eleanor Rails', 'BASELINE', 'female', '2899', '17', '3', '5'), ('gordon_bbb94cc_agt', '2017-04-15 04:16:09', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.17', 'Gordon Mondover', 'BASELINE', 'male', '1488', '7', '18', '0'), ('gustav_cht14f2_agt', '2017-04-15 06:46:22', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.18', 'Gustav Deck', 'BASELINE', 'male', '23900', '4', '9', '12'), ('jason_j8g12cg_agt', '2017-04-15 09:56:37', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.19', 'Jason Posture', 'BASELINE', 'male', '249', '0', '16', '9'), ('burgundy_18hg4cj_agt', '2017-04-15 17:12:43', '6.2', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.20', 'Burgundy Skinner', 'BASELINE', 'male', '76', '9', '9', '7'), ('benjamin_0001kc9_agt', '2017-04-15 21:00:51', '6.1', 'v1.0.0', 'inactive', 'thefraudexplorer.com', '172.16.10.21', 'Benjamin Evalent', 'BASELINE', 'male', '7599', '7', '7', '11')";
    
    $resultQuery = mysql_query($insertQuery);
}

/* Send log data to external file */

function logToFile($filename, $msg)
{
    $fd = fopen($filename, "a");
    $str = "[" . date("Y/m/d h:i:s", mktime()) . "] " . $msg; 
    fwrite($fd, $str . "\n");
    fclose($fd);
}

?>