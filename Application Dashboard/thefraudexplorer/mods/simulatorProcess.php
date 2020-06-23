<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-06
 * Revision: v1.4.5-aim
 *
 * Description: Code for process simulator data
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

sleep(1);

require '../vendor/autoload.php';
include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/elasticsearch.php";

/* Simulator methods */

function phraseFixes($rawPhrase)
{
    $rawPhrase = preg_replace('/\.+/', '.', $rawPhrase);
    $rawPhrase = str_replace('.', '. ', $rawPhrase);
    $rawPhrase = str_replace(' .', '.', $rawPhrase);
    $rawPhrase = str_replace(' ,', ',', $rawPhrase);
    $rawPhrase = str_replace(',', ', ', $rawPhrase);
    $rawPhrase = trim($rawPhrase);

    $unwanted_chars = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 
    'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 
    'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 
    'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 
    'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y');

    $sanitizedPhrase = strtr($rawPhrase, $unwanted_chars);
    $sanitizedPhrase = strtolower($sanitizedPhrase);
    $sanitizedPhrase = preg_replace('/[\x80-\xFF]/i', '', $sanitizedPhrase);
    $rawPhrase = preg_replace('/\s+/', ' ', $rawPhrase);

    return $sanitizedPhrase;
}

/* POST variables */

if (isset($_POST['action'])) $simulatorAction = $_POST['action'];
if (isset($_POST['rulesetSimulator'])) $rulesetSimulator = $_POST['rulesetSimulator'];
if (isset($_POST['applicationSimulator'])) $applicationSimulator = $_POST['applicationSimulator'];
if (isset($_POST['simulatorPhrases'])) $simulatorPhrases = $_POST['simulatorPhrases'];

/* Run check button pressed */

if ($simulatorAction == "runCheck") 
{
    /* Traverse phrase library searching for matched phrases */

    $ruleset = $rulesetSimulator;
    $sanitizedPhrases = phraseFixes($simulatorPhrases);

    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $fta_lang = $configFile['fta_lang_selection'];

    if ($fta_lang == "fta_text_rule_multilanguage") 
    {
        $numberOfLibraries = 2;
        $jsonFT[1] = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
        $jsonFT[2] = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);
    }
    else 
    {
        $numberOfLibraries = 1;
        $jsonFT[1] = json_decode(file_get_contents($configFile[$fta_lang]), true);
    }

    for ($lib = 1; $lib<=$numberOfLibraries; $lib++)
    {        
        $fraudTriangleTerms = array('pressure', 'opportunity', 'rationalization');
        $rule = "BASELINE";

        if ($ruleset != "BASELINE") $steps = 2;
        else $steps = 1;

        for($i=1; $i<=$steps; $i++)
        {
            foreach ($fraudTriangleTerms as $term)
            {
                foreach ($jsonFT[$lib]['dictionary'][$rule][$term] as $field => $termPhrase)
                {
                    if (preg_match_all($termPhrase."i", $sanitizedPhrases, $matches))
                    {
                        $phrasesMatched[][$term] = $matches[0][0];
                    }
                }
            }
            $rule = $ruleset;
        }
    }

    /* Return JSON data */

    header('Content-Type: application/json');

    if (!isset($phrasesMatched)) 
    {
        $json = "nodata";
        echo json_encode($json, JSON_PRETTY_PRINT);
    }
    else
    {
        $json = $phrasesMatched;
        echo json_encode($json, JSON_PRETTY_PRINT);
    }
}
else if ($simulatorAction == "putEvent")
{    
    $endpoint = "eleanor_1114c3_agt";
    $ipAddress = "172.16.10.16";
    $domain = "thefraudexplorer.com";
    $phrases = phraseFixes($simulatorPhrases);

    $eventRequest = array(
        'hostPrivateIP' => $ipAddress,
        'userDomain' => $domain,
        'appTitle' => $applicationSimulator,
        'phrases' => $phrases
    );

    $fp = fopen('dataPhrases.txt', 'w');
    fwrite($fp, $eventRequest['phrases']);
    fclose($fp);

    $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
    $timeZone = $configFile['php_timezone'];
    $sockLT = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    $textPhrases = $eventRequest['phrases'];
    $words = explode(" ", $textPhrases);

    foreach ($words as $word)
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $now->setTimezone(new DateTimeZone($timeZone));
        $wordTime = $now->format("Y-m-d H:i:s,v");
        usleep(10 * 1000);

        $msgData = $wordTime." a: ".$eventRequest['hostPrivateIP']." b: ".$eventRequest['userDomain']." c: ".$endpoint." d: TextEvent - e: ".encRijndael($eventRequest['appTitle'])." f: ".encRijndael($word);
        $lenData = strlen($msgData);
        socket_sendto($sockLT, $msgData, $lenData, 0, $configFile['net_logstash_host'], $configFile['net_logstash_webservice_text_port']);
    }

    header('Content-Type: application/json');

    $json = "eventputted";
    echo json_encode($json, JSON_PRETTY_PRINT);
}