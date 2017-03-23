<?php

 /*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-06-30 15:12:41 -0500 (Wed, 30 Jun 2016)
 * Revision: v0.9.9-beta
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

 function getMultiArrayData($array, $field1, $field2, $field3, $globalVar)
 {
        foreach($array as $key => $value)
        {
                if (is_array($value)) getMultiArrayData($value, $field1, $field2, $field3, $globalVar);
                else
                {
                        if ($key == $field1 && $key != "sort")
                        {
                                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][0] = $value;
				$GLOBALS[$globalVar][$GLOBALS['arrayPosition']][1] = $array[$field2];
				$GLOBALS[$globalVar][$GLOBALS['arrayPosition']][2] = $array[$field3];
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
	]];

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
	]];

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
	]];

	$client = Elasticsearch\ClientBuilder::create()->build();
        $lastAlertTime = $client->search($endDateParams);

	return $lastAlertTime;
 }
 
 /* Start data procesing */ 

 function startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset)
 {
	echo "[INFO] Starting Fraud Triangle Analytics phrase matching for [".$agentID."] ...\n\n";

 	getMultiArrayData($typedWords, "typedWord", "applicationTitle", "sourceTimestamp", $agentID."_typedWords");
        $arrayOfWordsAndWindows = $GLOBALS[$agentID."_typedWords"];

	foreach($arrayOfWordsAndWindows as $arrayKey=>$arrayValue) echo "\t* Window [".decRijndael($arrayValue[1])."] - Word [".decRijndael($arrayValue[0])."] Date [".$arrayValue[2]."]\n";

        $lastWindowTitle = null;
        $lastTimeStamp = null;
        $stringOfWords = null;
        $counter = 0;

	$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
        $dictLan = $configFile['wc_language'];
        $dictEna = $configFile['wc_enabled'];

        foreach($arrayOfWordsAndWindows as $key=>$value)
        {
        	$windowTitle = decRijndael($value[1]);
                $timeStamp = $value[2];

                if ($windowTitle == $lastWindowTitle)
                {
                	$stringOfWords = $stringOfWords . " " .decRijndael($value[0]);
                }
                else if ($counter == 0)
                {
                	$stringOfWords = decRijndael($value[0]);
                }
                else
                {
			if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

			echo "\n[INFO] Parsing fraud Triangle Window [".$lastWindowTitle."] Phrases [".$stringOfWords."] with Timestam [".$lastTimeStamp."] for agent [".$agentID."]";
                	parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, "matchesGlobalCount", $configFile, $jsonFT, $ruleset);

                        $counter = 0;
			$stringOfWords = decRijndael($value[0]);
                }
		if ($key == count($arrayOfWordsAndWindows))
                {  
			$lastWindowTitle = $windowTitle;
                	$lastTimeStamp = $timeStamp; 

			if ($dictEna == "yes") $stringOfWords = checkPhrases($stringOfWords, $dictLan);

                        echo "\n[INFO] Parsing last fraud Triangle Window [".$lastWindowTitle."] Phrases [".$stringOfWords."] with Timestamp [".$lastTimeStamp."] for agent [".$agentID."]";
                        parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $lastWindowTitle, $lastTimeStamp, "matchesGlobalCount", $configFile, $jsonFT, $ruleset);
                }

                $counter++;
                $lastWindowTitle = $windowTitle;
                $lastTimeStamp = $timeStamp;
	}
 }

 /* Parse Fraud Triangle phrases */

 function parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $windowTitle, $timeStamp, $matchesGlobalCount, $configFile, $jsonFT, $ruleset)
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
                                	$msgData = $matchTime." ".$agentID." ".$domain." TextEvent - ".$term." e: ".$timeStamp." w: ".str_replace('/', '', $termPhrase)." s: ".$value." m: ".count($matches[0])." p: ".encRijndael($matches[0][0])." t: ".encRijndael($windowTitle)." z: ".encRijndael($stringOfWords);
                                	$lenData = strlen($msgData);
                                	socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_alerter_port']);       
                                	$GLOBALS[$matchesGlobalCount]++;

					if ($countOutput == 1) echo "\n\n";
 
					echo "\t* Matching for agent [".$agentID."] with term [".$term."] at window [".$windowTitle."] with word [".$matches[0][0]."] in phrase [".str_replace('/', '', $termPhrase)."] - score [".$value."], total matches [".count($matches[0])."]\n";

					logToFile($configFile['log_file'], "[INFO] - MatchTime[".$matchTime."] - EventTime[".$timeStamp."] AgentID[".$agentID."] TextEvent - Term[".$term."] Window[".$windowTitle."] Word[".$matches[0][0].
					"] Phrase[".str_replace('/', '', $termPhrase)."] Score[".$value."] TotalMatches[".count($matches[0])."]");
		      		
					$countOutput++;
				} 
                	}

			$rule = $ruleset;
		}
        }

	if ($matched == FALSE) echo "\n\n\t* There is no matching phrases for agent [".$agentID."] at this time on this window [".$windowTitle."].\n\n";
	else echo "\n";
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
				]
			]
		]
	]];
        
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
        ]];

        $client = Elasticsearch\ClientBuilder::create()->build();
        $agentIdMatches = $client->count($matchesParams);

        return $agentIdMatches;
 }

 /* Query agent data with APC caching */

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

			$result=mysql_query("Update t_agents set totalwords='.$totalWords.', pressure='.$totalPressure.', opportunity='.$totalOpportunity.', rationalization='.$totalRationalization.' where agent='".$row_a['agent']."'");
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
 	$unwanted_chars = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
               	                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
               	        	'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                               	'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                               	'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');

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

                       	if(strtolower($suggestion[0]) != strtolower($value))
                       	{
                               	$string[$key] = $suggestion[0];
                               	$replacement_suggest = true;
                       	}
               	}
        }

        return strtr(implode(' ', $string), $unwanted_chars);
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
