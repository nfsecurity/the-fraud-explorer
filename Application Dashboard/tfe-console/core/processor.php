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
 * Revision: v0.9.7-beta
 *
 * Description: Main Application, Fraud Triangle Analytics Alerting
 */

 /* External includes */

 include "/var/www/html/tfe-console/inc/open-db-connection.php";

 /* Current time */

 $now = DateTime::createFromFormat('U.u', microtime(true));
 $time = $now->format("Y-m-d\TH:i:s.u");
 $time = substr($time, 0, -3);
 $GLOBALS['currentTime'] = (string)$time."Z";

 /* Load parameters, methods, functions and procedures from external files */

 $configFile = parse_ini_file("/var/www/html/tfe-console/config.ini");
 require 'vendor/autoload.php';
 include 'include/functions.php';

 /* Global variables */

 $sockLT = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
 $client = Elasticsearch\ClientBuilder::create()->build();
 $GLOBALS['matchesGlobalCount'] = 0;
 $startTime = microtime(true);
 $ESindex = $configFile['es_words_index'];
 $fistTimeIndex = true;
 $APCttl = $configFile['apc_ttl'];

 $AgentParams = [
 'index' => $ESindex, 
 'type' => 'TextEvent', 
 'body' => [
 	'size' => 0, 
  	'aggs' => [
		'agents' => [
			'terms' => [ 'field' => 'agentId.raw' ]
		]
	]
 ]];

 $allAgentList = $client->search($AgentParams);
 $fraudTriangleTerms = array('rationalization'=>'0 1 0','opportunity'=>'0 0 1','pressure'=>'1 0 0');
 $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);

 /* Unique agentID List */

 $GLOBALS['arrayPosition'] = 0;
 getArrayData($allAgentList, "key", "agentList");

 /* Start the loop for each agent */

 if (indexExist($configFile['es_alerter_status_index'], $configFile))
 {
	$firstTimeIndex = false;
	logToFile($configFile['log_file'], "[INFO] - The alerter index already exist, continue with data range matching ...");

 	$endDate = extractEndDateFromAlerter($configFile['es_alerter_status_index'], "AlertStatus");
	$GLOBALS['arrayPosition'] = 0;
        getArrayData($endDate, "endTime", 'lastAlertDate');

	logToFile($configFile['log_file'], "[INFO] - Checking events from last date: ".$GLOBALS['lastAlertDate'][0]."  ...");
	populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);
	
	foreach($GLOBALS['agentList'] as $agentID)
        {  
		$typedWords = extractTypedWordsFromAgentIDWithDate($agentID, $ESindex, $GLOBALS['lastAlertDate'][0], $GLOBALS['currentTime']);

		if ($typedWords['hits']['total'] == 0) continue;  
		else 
		{
			$ruleset = getRuleset($agentID);
			startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset);
		}
	}
 }
 else
 {
	logToFile($configFile['log_file'], "[INFO] - Alerter index not found, continue with all data matching ...");
	populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);

 	foreach($GLOBALS['agentList'] as $agentID)
 	{
		$typedWords = extractTypedWordsFromAgentID($agentID, $ESindex);

		if ($typedWords['hits']['total'] == 0) continue;
                else 
		{
			$ruleset = getRuleset($agentID);
			startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset);
        	}
	}
 }

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

 logToFile($configFile['log_file'], "[INFO] - Sending alert-status to index, StartTime[".$GLOBALS['lastAlertDate'][0]."], EndTime[".$endTime."] TimeTaken[".$timeTaken."] Triggered[".$GLOBALS['matchesGlobalCount']."]");
 include "/var/www/html/tfe-console/inc/close-db-connection.php";

?>
