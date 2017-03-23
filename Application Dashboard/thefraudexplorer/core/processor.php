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
 * Description: Main Application, Fraud Triangle Analytics Alerting
 */

 /* External includes */

 include "/var/www/html/thefraudexplorer/lbs/cryptography.php";

 /* Current time */

 $now = DateTime::createFromFormat('U.u', microtime(true));
 $time = $now->format("Y-m-d\TH:i:s.u");
 $time = substr($time, 0, -3);
 $GLOBALS['currentTime'] = (string)$time."Z";

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
 $fistTimeIndex = true;

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

 if(!isset($GLOBALS['agentList'])) exit;

 /* Start the loop for each agent */

 echo "\n[INFO] Starting Fraud Triangle Analytics phrase matching processor ...\n";

 if (indexExist($configFile['es_alerter_status_index'], $configFile))
 {
	echo "[INFO] Index ".$configFile['es_alerter_status_index']." already exist, continue ...\n";

	$firstTimeIndex = false;
	logToFile($configFile['log_file'], "[INFO] - The alerter index already exist, continue with data range matching ...");

 	$endDate = extractEndDateFromAlerter($configFile['es_alerter_status_index'], "AlertStatus");
	$GLOBALS['arrayPosition'] = 0;
        getArrayData($endDate, "endTime", 'lastAlertDate');

	echo "[INFO] Checking events from latest alert date: ".$GLOBALS['lastAlertDate'][0]." ...\n";

	logToFile($configFile['log_file'], "[INFO] - Checking events from last date: ".$GLOBALS['lastAlertDate'][0]."  ...");
	populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);
	
	echo "\n[INFO] *** Searching for typedwords by agent ***\n\n";

	foreach($GLOBALS['agentList'] as $agentID)
        {  
		$typedWords = extractTypedWordsFromAgentIDWithDate($agentID, $ESindex, $GLOBALS['lastAlertDate'][0], $GLOBALS['currentTime']);

		if ($typedWords['hits']['total'] == 0) 
		{
			echo "[INFO] There is no typed words from agent [".$agentID."] from the latest alert date.\n";
			continue; 
		} 
		else 
		{
			$ruleset = getRuleset($agentID);
			echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
			startFTAProcess($agentID, $typedWords, $sockLT, $fraudTriangleTerms, $configFile, $jsonFT, $ruleset);
		}
	}
 }
 else
 {
	echo "[INFO] Index ".$configFile['es_alerter_status_index']." doesn't exist, continue ...\n";

	logToFile($configFile['log_file'], "[INFO] - Alerter index not found, continue with all data matching ...");
	populateTriangleByAgent($ESindex, $configFile['es_alerter_index']);

	echo "[INFO] Checking events from now ...\n";

 	foreach($GLOBALS['agentList'] as $agentID)
 	{
		$typedWords = extractTypedWordsFromAgentID($agentID, $ESindex);

		if ($typedWords['hits']['total'] == 0) 
		{
			echo "[INFO] There is no typed words from agent [".$agentID."] from the latest alert date.\n";
			continue;
                }
		else 
		{
			$ruleset = getRuleset($agentID);
			echo "[INFO] Agent [".$agentID."] - Ruleset [".$ruleset."] - Number of words typed from latest alert date [".$typedWords['hits']['total']."]\n";
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

 echo "\n[INFO] *** Sending this alert status to log file ***\n";

 logToFile($configFile['log_file'], "[INFO] - Sending alert-status to index, StartTime[".$GLOBALS['lastAlertDate'][0]."], EndTime[".$endTime."] TimeTaken[".$timeTaken."] Triggered[".$GLOBALS['matchesGlobalCount']."]");
 include "/var/www/html/thefraudexplorer/lbs/close-db-connection.php";

 echo "[INFO] Exiting Fraud Triangle Analytics phrase matching processor ...\n\n";

?>
