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
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Functions extension file for elasticsearch querys
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
 
 /* Count Fraud Triangle matches */

 function countAllFraudTriangleMatches($fraudTerm, $index)
 {
        $matchesParams = [
        'index' => $index,
        'type' => 'AlertEvent',
        'body' => [
                'query' => [
                        'bool' => [
                                'must' => [
                                                [ 'term' => [ 'eventType.raw' => 'TextEvent' ] ],
                                                [ 'term' => [ 'alertType.raw' => $fraudTerm ] ]
                                ]
                        ]
                ]
        ]];

        $client = Elasticsearch\ClientBuilder::create()->build();
        $countMatches = $client->count($matchesParams);

        return $countMatches;
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

 /* Check if Elasticsearch alerter index exists */

 function indexExist($indexName, $configFile)
 {
	$url = $configFile['es_host'].$indexName;
    	$status = get_headers($url, 1);
	if (strpos($status[0], "OK") != false) return true;
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
                	'term' => [ 'agentId.raw' => $agentID ] 
			]
                ]
        ];

        $client = Elasticsearch\ClientBuilder::create()->build();
        $agentIdData = $client->search($matchesParams);

        return $agentIdData;
 }
?>
