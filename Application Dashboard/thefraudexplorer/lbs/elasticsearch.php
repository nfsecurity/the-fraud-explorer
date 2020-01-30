<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-02
 * Revision: v1.4.2-aim
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

function insertAlertDocument($indexName, $type, $agentId, $alertType, $matchNumber, $opportunityScore, $pressureScore, $rationalizationScore, $phraseMatch, $windowTitle, $wordTyped, $stringHistory)
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
            insertAlertDocument($configFile['es_sample_alerter_index'], "AlertEvent", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $row[6], encRijndael($row[7]), encRijndael($row[8]), encRijndael($row[9]));
        }
    }
}

?>