<?php

 /*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-06-30 15:12:41 -0500 (Wed, 30 Jun 2016)
 * Revision: v0.9.6-beta
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

 function getMultiArrayData($array, $field1, $field2, $globalVar)
 {
        foreach($array as $key => $value)
        {
                if (is_array($value)) getMultiArrayData($value, $field1, $field2, $globalVar);
                else
                {
                        if ($key == $field1 && $key != "sort")
                        {
                                $GLOBALS[$globalVar][$GLOBALS['arrayPosition']][0] = $value;
				$GLOBALS[$globalVar][$GLOBALS['arrayPosition']][1] = $array[$field2];
                                $GLOBALS['arrayPosition']++;
                        }
                }
        }
 }

 /* Extract all words typed by an agent */

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

 /* Extract words typed by an agent depending of the last date */

 function extractTypedWordsFromAgentIDWithDate($agentID, $index, $from, $to)
 {
	$specificAgentTypedWordsParams = [
	'index' => $index, 
	'type' => 'TextEvent',
	'body' =>[
		'size' => 10000,
		'query' => [
			'filtered' => [
				'query' => [
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
			'endTime' => [ 'order' => 'desc', 'ignore_unmapped' => 'true' ]
		]
	]];

	$client = Elasticsearch\ClientBuilder::create()->build();
        $lastAlertTime = $client->search($endDateParams);

	return $lastAlertTime;
 }
 
 /* Parse Fraud Triangle phrases */

 function parseFraudTrianglePhrases($agentID, $sockLT, $fraudTriangleTerms, $stringOfWords, $windowTitle, $matchesGlobalCount, $configFile, $jsonFT)
 {
	foreach ($fraudTriangleTerms as $term => $value)
        {
        	foreach ($jsonFT['dictionary'][$term] as $field => $termPhrase) 
                {
                	if (preg_match_all($termPhrase, $stringOfWords, $matches)) 
                        {
				$now = DateTime::createFromFormat('U.u', microtime(true));
				$end = $now->format("Y-m-d\TH:i:s.u");
 				$end = substr($end, 0, -3);
 				$matchTime = (string)$end."Z";
                                $msgData = $matchTime." ".$agentID." TextEvent - ".$term." w: ".str_replace('/', '', $termPhrase)." s: ".$value." m: ".count($matches[0])." p: ".$matches[0][0]." t: ".$windowTitle;
                                $lenData = strlen($msgData);
                                socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_alerter_port']);       
                                $GLOBALS[$matchesGlobalCount]++;
 
				logToFile($configFile['log_file'], "[INFO] - MatchTime[".$matchTime."] - AgentID[".$agentID."] TextEvent - Term[".$term."] Window[".$windowTitle."] Word[".$matches[0][0]."] Phrase[".str_replace('/', '', $termPhrase)."] Score[".$value."] TotalMatches[".count($matches[0])."]");
		      } 
                }
        }
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
