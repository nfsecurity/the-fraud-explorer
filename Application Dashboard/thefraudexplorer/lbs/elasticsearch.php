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

/* Search all Fraud Triangle Matches */

function getAllFraudTriangleMatches($index, $domain, $samplerStatus, $context)
{
    if ($context == "allalerts") $querySize = 10000;
    else $querySize = 50;

    if ($context != "allalerts")
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
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
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
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
                                    [ 'match' => [ 'falsePositive' => '1'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
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
                                'should' => [
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
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
                                'should' => [
                                    'match' => [ 'userDomain' => $domain ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }
    else
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
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
            }
            else
            {
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
                                    [ 'match' => [ 'falsePositive' => '2'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
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
                                'should' => [
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
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
                                'should' => [
                                    'match' => [ 'userDomain' => $domain ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
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

/* Count All Fraud Triangle matches by date range */

function countFraudTriangleMatchesWithDateRange($fraudTerm, $index, $from, $to)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ],
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
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
}

/* Count All Fraud Triangle matches by date range without fraud term */

function countFraudTriangleMatchesWithDateRangeWithoutTerm($index, $from, $to)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ]
                    ],
                    'must_not' => [
                            [ 'match' => [ 'falsePositive' => '1'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
}

/* Count All Fraud Triangle matches by date range without fraud term with Domain */

function countFraudTriangleMatchesWithDateRangeWithoutTermWithDomain($index, $from, $to, $userDomain)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                        [ 'match' => [ 'userDomain' => $userDomain ] ]
                    ],
                    'must_not' => [
                            [ 'match' => [ 'falsePositive' => '1'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
}

/* Count All Fraud Triangle matches by date range without fraud term with Agent ID */

function countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentID($index, $from, $to, $agentID)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                        [ 'wildcard' => [ 'agentId' => $agentID ] ]
                    ],
                    'must_not' => [
                            [ 'match' => [ 'falsePositive' => '1'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
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

/* Count All Fraud Triangle matches by date range without fraud term with Agent ID with Domain */

function countFraudTriangleMatchesWithDateRangeWithoutTermWithAgentIDWithDomain($index, $from, $to, $agentID, $userDomain)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                        [ 'wildcard' => [ 'agentId' => $agentID ] ],
                        [ 'match' => [ 'userDomain' => $userDomain ] ]
                    ],
                    'must_not' => [
                            [ 'match' => [ 'falsePositive' => '1'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $eventMatches = $client->count($matchesParams);

    return $eventMatches;
}

/* Search all Fraud Triangle Matches with date range */

function getAllFraudTriangleMatchesWithDateRange($index, $domain, $samplerStatus, $context, $from, $to, $pressure, $opportunity, $rationalization)
{
    if ($context == "allalerts") $querySize = 10000;
    else $querySize = 50;

    if ($context != "allalerts")
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'should' => [
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'should' => [
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'match' => [ 'userDomain' => $domain ],
                                    'match' => [ 'alertType' => $pressure ],
                                    'match' => [ 'alertType' => $opportunity ],
                                    'match' => [ 'alertType' => $rationalization ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }
    else
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'should' => [
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'should' => [
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            [ '@timestamp' => [ 'order' => 'desc' ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    'bool' => [
                                        'minimum_should_match' => '1',
                                        'must' => [
                                            [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                        ],
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ],
                                            [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                        ]
                                    ]     
                                ],
                                'should' => [    
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'size' => $querySize,
                        'sort' => [
                            '@timestamp' => [ 'order' => 'desc' ]
                        ],
                        '_source' => [
                            'exclude' => [ 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ],
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to.'T23:59:59.999'] ] ]
                                ],
                                'should' => [
                                    [ 'match' => [ 'alertType' => $pressure ] ],
                                    [ 'match' => [ 'alertType' => $opportunity ] ],
                                    [ 'match' => [ 'alertType' => $rationalization ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
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
                        [ 'term' => [ 'agentId' => $agentID ] ],
                        [ 'term' => [ 'eventType' => $alertType ] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdMatches = $client->count($matchesParams);

    return $agentIdMatches;
}

/* Search all Fraud Triangle Matches for maintenance purge */

function getAllFraudTriangleMatchesMonthsBack($index, $monthsBack)
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
                'exclude' => [ 'message' ]
            ],
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'match_all' => [ 'boost' => 1 ] ],
                        [ 'range' => [ '@timestamp' => [ 'gte' => 'now-'.$monthsBack.'' ] ] ]
                    ],
                    'must_not' => [
                        [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                    ]
                ]
            ]
        ]
    ]; 

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
}

/* Sum word count by date range */

function sumWordsWithDateRange($index, $from, $to)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'AlertStatus', 
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ]
                    ]
                ]
            ],
            'aggs' => [
                'sumQuantity' => [
                    'sum' => [
                        'field' => 'wordCount'
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $sumMatches = $client->search($matchesParams);

    return $sumMatches;
}

/* Count All Typed Words by date range */

function countWordsWithDateRange($index, $from, $to)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'TextEvent', 
        'body' => [
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $phraseMatches = $client->count($matchesParams);

    return $phraseMatches;
}

/* Count All Typed Words by date range with Domain */

function countWordsWithDateRangeWithDomain($index, $from, $to, $userDomain)
{
    $matchesParams = [
        'index' => $index, 
        'type' => 'TextEvent', 
        'body' => [ 
            'query' => [
                'bool' => [
                    'must' => [
                        [ 'range' => [ '@timestamp' => ['gte' => $from.'T00:00:00.000', 'lte'=> $to ] ] ],
                        [ 'match' => [ 'userDomain' => $userDomain ] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $phraseMatches = $client->count($matchesParams);

    return $phraseMatches;
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
            '_source' => [
                'exclude' => [ 'message' ]
            ],
            'query' => [
                'wildcard' => [ 'agentId' => $agentID ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->search($matchesParams);

    return $agentIdData;
}

/* Get entry for Alert ID Data */

function getAlertIdData($documentId, $index, $alertType)
{
    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'size' => 10000,
            'query' => [
                'term' => [ '_id' => $documentId ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $alertIdData = $client->search($matchesParams);

    return $alertIdData;
}

/* Create index */

function createIndex($indexName)
{
    $params = [
        'index' => $indexName,
        'body' => [
            'settings' => [
                'number_of_shards' => 2,
                'number_of_replicas' => 0
            ]
        ]
    ];
    
    $client = Elasticsearch\ClientBuilder::create()->build();
    $response = $client->indices()->create($params);
}

/* Insert a Document */

function insertAlertDocument($indexName, $type, $agentId, $alertType, $matchNumber, $opportunityScore, $pressureScore, $rationalizationScore, $phraseMatch, $windowTitle, $wordTyped, $stringHistory, $messageFlag)
{
    $randHours = rand(10,23);
    $randMinutes = rand(10,59);
    $randSeconds = rand(10,59);
    $randAtom = rand(100,999);
    $randYMD = "2017-04-15";
    
    $dateCalc = $randYMD." ".$randHours.":".$randMinutes.":".$randSeconds.",".$randAtom;
    $dateTZ = $randYMD."T".$randHours.":".$randMinutes.":".$randSeconds.".".$randAtom."Z";
    
    $params = [
        'index' => $indexName,
        'type' => $type,
        'body' => [ 
            'agentId' => $agentId,
            'alertType' => $alertType,
            'eventTime' => $dateCalc,
            'eventType' => 'TextEvent',
            'host' => '172.17.7.'.rand(1,253),
            'matchNumber' => $matchNumber,
            'message' => 'sample data',
            'opportunityScore' => $opportunityScore,
            'pressureScore' => $pressureScore,
            'rationalizationScore' => $rationalizationScore,
            'phraseMatch' => $phraseMatch,
            'sourceTimestamp' => $dateTZ,
            'userDomain' => 'thefraudexplorer.com',
            'windowTitle' => $windowTitle,
            'wordTyped' => $wordTyped,
            'stringHistory' =>  $stringHistory,
            'messageFlag' => $messageFlag,
            'type' => $type,
            'falsePositive' => '0',
            '@timestamp' => $dateTZ,
            '@version' => '1'
        ]
    ];
    
    $client = Elasticsearch\ClientBuilder::create()->build();
    $response = $client->index($params);
}

function insertSampleData($configFile)
{
    if (!indexExist($configFile['es_sample_alerter_index'], $configFile))
    {
        createIndex($configFile['es_sample_alerter_index']);
        $csvFile = file($configFile['es_sample_csv']);
        $data = [];

        foreach ($csvFile as $line) $data[] = str_getcsv($line);
        
        foreach ($data as $row)
        {
            insertAlertDocument($configFile['es_sample_alerter_index'], "AlertEvent", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], $row[7], $row[8], encRijndael($row[9]), $row[10]);
        }
    }
}

function deleteIndex($index, $configFile)
{
    if (indexExist($configFile['es_sample_alerter_index'], $configFile))
    {
        $params = ['index' => $index];
        $client = Elasticsearch\ClientBuilder::create()->build();
        $response = $client->indices()->delete($params);
    }
}

/* Extract data from alerter status */

function extractDataFromAlerterStatus()
{
    $endDateParams = [
        'index' => "tfe-alerter-status",
        'type' => "AlertStatus",
        'body' =>[
            'size' => 500,
            'query' => [
                'term' => [ 'host' => '127.0.0.1' ]
            ],
            'sort' => [
                'endTime' => [ 'order' => 'desc' ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $latestEvents = $client->search($endDateParams);

    return $latestEvents;
}

/* Extract las event from alerter status */

function extractLastEventFromAlerterStatus()
{
    $endDateParams = [
        'index' => "tfe-alerter-status",
        'type' => "AlertStatus",
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
    $latestEvents = $client->search($endDateParams);

    return $latestEvents;
}

/* Search all Fraud Triangle Events with size and offset */

function getAllFraudTriangleEvents($index, $domain, $samplerStatus, $context, $size, $offset, $sortOrder, $sortColumn)
{
    $querySize = $size;

    if ($sortColumn != "@timestamp" && $sortColumn != "messageFlag") $sortColumn = $sortColumn.".keyword";

    if ($context != "allalerts")
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
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
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
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
                                    [ 'match' => [ 'falsePositive' => '1'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'should' => [
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'should' => [
                                    'match' => [ 'userDomain' => $domain ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }
    else
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
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
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
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
                                    [ 'match' => [ 'falsePositive' => '2'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'should' => [
                                    [ 'match' => [ 'userDomain' => $domain ] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'should' => [
                                    'match' => [ 'userDomain' => $domain ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
}

/* Search specific Fraud Triangle Events with size and offset */

function getSpecificFraudTriangleEvents($index, $domain, $samplerStatus, $context, $size, $offset, $sortOrder, $sortColumn, $searchString)
{
    $querySize = $size;
    $searchString = "*".$searchString."*";

    if ($sortColumn != "@timestamp" && $sortColumn != "messageFlag") $sortColumn = $sortColumn.".keyword";

    if ($context != "allalerts")
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ],
                                            [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ]
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }
    else
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ],
                                            [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'from' => $offset,
                        'size' => $querySize,
                        'sort' => [
                            [ $sortColumn => [ 'order' => $sortOrder ] ]
                        ],
                        '_source' => [
                            'exclude' => [ 'stringHistory', 'message' ]
                        ],
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ]
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->search($matchesParams);

    return $getAlerts;
}

/* Count specific Fraud Triangle Events with size and offset */

function countSpecificFraudTriangleEvents($index, $domain, $samplerStatus, $context, $searchString)
{
    $searchString = "*".$searchString."*";

    if ($context != "allalerts")
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ],
                                            [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ]
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '1'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }
    else
    {
        if ($domain == "all")
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'must' => [
                                    [ 'match_all' => [ 'boost' => 1 ] ]
                                ],
                                'should' => [
                                    [ 'wildcard' => [ 'agentId' => $searchString] ],
                                    [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                    [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                    [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ],
                                    [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                                ]
                            ]
                        ]
                    ]
                ]; 
            }
        }
        else
        {
            if ($samplerStatus == "enabled")
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ],
                                            [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            else
            {
                $matchesParams = [
                    'index' => $index,
                    'type' => 'AlertEvent',
                    'body' => [
                        'query' => [
                            'bool' => [
                                'minimum_should_match' => '1',
                                'should' => [
                                    'bool' => [
                                        'should' => [
                                            [ 'match' => [ 'userDomain' => $domain ] ]
                                        ]
                                    ],
                                    'bool' => [
                                        'should' => [
                                            [ 'wildcard' => [ 'agentId' => $searchString] ],
                                            [ 'wildcard' => [ 'userDomain' => $searchString ] ],
                                            [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                                            [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                                        ]
                                    ]
                                ],
                                'must_not' => [
                                    [ 'match' => [ 'falsePositive' => '2'] ]
                                ]
                            ]
                        ]
                    ]
                ];   
            }
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getCount = $client->count($matchesParams);

    return $getCount;
}

/* Get AgentId alert events */

function getAgentIdEvents($agentID, $index, $alertType, $size, $offset, $sortOrder, $sortColumn)
{
    if ($sortColumn != "@timestamp" && $sortColumn != "messageFlag") $sortColumn = $sortColumn.".keyword";

    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'from' => $offset,
            'size' => $size,
            'sort' => [
                [ $sortColumn => [ 'order' => $sortOrder ] ]
            ],
            '_source' => [
                'exclude' => [ 'message' ]
            ],
            'query' => [
                'wildcard' => [ 'agentId' => $agentID ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->search($matchesParams);

    return $agentIdData;
}

/* Count AgentId alert events */

function countAgentIdEvents($agentID, $index, $alertType)
{
    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'query' => [
                'wildcard' => [ 'agentId' => $agentID ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->count($matchesParams);

    return $agentIdData;
}

/* Get audit trail events */

function getAuditTrailEvents($view, $index, $alertType, $size, $offset, $sortOrder, $sortColumn)
{
    if ($sortColumn != "@timestamp") $sortColumn = $sortColumn.".keyword";

    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'from' => $offset,
            'size' => $size,
            'sort' => [
                [ $sortColumn => [ 'order' => $sortOrder ] ]
            ],
            'query' => [
                'wildcard' => [ 'eventUser' => $view ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $auditData = $client->search($matchesParams);

    return $auditData;
}

/* Count audit trail events */

function countAuditTrailEvents($view, $index, $alertType)
{
    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'query' => [
                'wildcard' => [ 'eventUser' => $view ] 
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $auditData = $client->count($matchesParams);

    return $auditData;
}

/* Get AgentId alert specific events */

function getSpecificAgentIdEvents($agentID, $index, $alertType, $size, $offset, $sortOrder, $sortColumn, $searchString)
{
    if ($sortColumn != "@timestamp" && $sortColumn != "messageFlag") $sortColumn = $sortColumn.".keyword";
    $searchString = "*".$searchString."*";

    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'from' => $offset,
            'size' => $size,
            'sort' => [
                [ $sortColumn => [ 'order' => $sortOrder ] ]
            ],
            '_source' => [
                'exclude' => [ 'message' ]
            ],
            'query' => [
                'bool' => [
                    'minimum_should_match' => '1',
                    'must' => [
                        [ 'wildcard' => [ 'agentId' => $agentID ] ],
                    ],
                    'should' => [
                        [ 'wildcard' => [ 'alertType' => $searchString] ],
                        [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                        [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                    ],
                    'must_not' => [
                        [ 'match' => [ 'falsePositive' => '2'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->search($matchesParams);

    return $agentIdData;
}

/* Count AgentId alert specific events */

function countSpecificAgentIdEvents($agentID, $index, $alertType, $searchString)
{
    $searchString = "*".$searchString."*";

    $matchesParams = [
        'index' => $index,
        'type' => $alertType,
        'body' => [
            'query' => [
                'bool' => [
                    'minimum_should_match' => '1',
                    'must' => [
                        'wildcard' => [ 'agentId' => $agentID ]
                    ],
                    'should' => [
                        [ 'wildcard' => [ 'alertType' => $searchString] ],
                        [ 'wildcard' => [ 'windowTitle' => $searchString ] ],
                        [ 'wildcard' => [ 'wordTyped' => $searchString ] ]
                    ],
                    'must_not' => [
                        [ 'match' => [ 'falsePositive' => '2'] ]
                    ]
                ]
            ]
        ]
    ];

    $client = Elasticsearch\ClientBuilder::create()->build();
    $agentIdData = $client->count($matchesParams);

    return $agentIdData;
}

/* Count specific Fraud Triangle Matches one week before */

function countSpecificFraudTriangleMatchesOneWeekBefore($index, $domain, $samplerStatus, $vertice)
{
    if ($domain == "all")
    {
        if ($samplerStatus == "enabled")
        {
            $matchesParams = [
                'index' => $index,
                'type' => 'AlertEvent',
                'body' => [
                    'query' => [
                        'bool' => [
                            'minimum_should_match' => '1',
                            'must' => [
                                [ 'match_all' => [ 'boost' => 1 ] ],
                                [ 'range' => [ '@timestamp' => [ 'gte' => 'now-7d/d', 'lte'=> 'now/d' ] ] ]
                            ],
                            'should' => [
                                [ 'match' => [ 'alertType' => $vertice] ]
                            ],
                            'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        else
        {
            $matchesParams = [
                'index' => $index,
                'type' => 'AlertEvent',
                'body' => [
                    'query' => [
                        'bool' => [
                            'minimum_should_match' => '1',
                            'must' => [
                                [ 'match_all' => [ 'boost' => 1 ] ],
                                [ 'range' => [ '@timestamp' => [ 'gte' => 'now-7d/d', 'lte'=> 'now/d' ] ] ]
                            ],
                            'should' => [
                                [ 'match' => [ 'alertType' => $vertice ] ]
                            ],
                            'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ],
                                [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ]
                            ]
                        ]
                    ]
                ]
            ]; 
        }
    }
    else
    {
        if ($samplerStatus == "enabled")
        {
            $matchesParams = [
                'index' => $index,
                'type' => 'AlertEvent',
                'body' => [
                    'query' => [
                        'bool' => [
                            'minimum_should_match' => '1',
                            'should' => [
                                [ 'match' => [ 'userDomain' => $domain ] ],
                                [ 'match' => [ 'userDomain' => 'thefraudexplorer.com' ] ],
                                [ 'match' => [ 'alertType' => $vertice ] ],
                                [ 'range' => [ '@timestamp' => [ 'gte' => 'now-7d/d', 'lte'=> 'now/d' ] ] ]
                            ],
                            'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        else
        {
            $matchesParams = [
                'index' => $index,
                'type' => 'AlertEvent',
                'body' => [
                    'query' => [
                        'bool' => [
                            'minimum_should_match' => '1',
                            'should' => [
                                'match' => [ 'userDomain' => $domain ],
                                'match' => [ 'alertType' => $vertice ],
                                [ 'range' => [ '@timestamp' => [ 'gte' => 'now-7d/d', 'lte'=> 'now/d' ] ] ]
                            ],
                            'must_not' => [
                                [ 'match' => [ 'falsePositive' => '1'] ]
                            ]
                        ]
                    ]
                ]
            ];   
        }
    }

    $client = Elasticsearch\ClientBuilder::create()->build();
    $getAlerts = $client->count($matchesParams);

    return $getAlerts;
}

?>