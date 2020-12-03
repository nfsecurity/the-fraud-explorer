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
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for Build Advanced Reports
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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
require "../vendor/autoload.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/cryptography.php";

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];
$endpointDECES = decRijndael($_SESSION['endpointIDh'])."*";
$endpointDECSQL = decRijndael($_SESSION['endpointIDh']);
$endpointDec = $_SESSION['endpointIDh'];

/* POST Variables */

$typereport = filter($_POST['typereport']);
if (isset($_POST['typeinput'])) $typeinput = filter($_POST['typeinput']);

$daterangefrom = filter(str_replace("/", "-", $_POST['daterangefrom']));
$daterangeto = filter(str_replace("/", "-", $_POST['daterangeto']));

if (isset($_POST['alldaterange'])) $alldaterange = filter($_POST['alldaterange']);
else $alldaterange = NULL;

if (isset($_POST['pressure'])) $pressure = filter($_POST['pressure']);
else $pressure = "n/a";

if (isset($_POST['opportunity'])) $opportunity = filter($_POST['opportunity']);
else $opportunity = "n/a";

if (isset($_POST['rationalization'])) $rationalization = filter($_POST['rationalization']);
else $rationalization = "n/a";

if (isset($_POST['applications'])) $app = filter($_POST['applications']);
else $app = "all";

if (isset($_POST['allapplications'])) $allapplications = filter($_POST['allapplications']);
else $allapplications = "n/a";

if (isset($_POST['ruleset'])) $ruleset = filter($_POST['ruleset']);
else $ruleset = "all";

if (isset($_POST['alldepartments'])) $alldepartments = filter($_POST['alldepartments']);
else $alldepartments = "n/a";

if (isset($_POST['excluded'])) $excluded = filter($_POST['excluded']);
else $excluded = "all";

if (isset($_POST['allphrases'])) $allphrases = filter($_POST['allphrases']);
else $allphrases = "n/a";

if (isset($_POST['flagsonly'])) $flagsonly = filter($_POST['flagsonly']);
else $flagsonly = "n/a";

if ($alldaterange == "alldaterange")
{
    $daterangefrom = "2000-01-01";
    $daterangeto = date('Y-m-d');
}

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count";
        $params = '{ "query" : { "bool" : { "should" : { "range" : { "@timestamp" : { "gte" : "'.$daterangefrom.'T00:00:00.000", "lte" : "'.$daterangeto.'T23:59:59.999" } } }, "must_not" : { "wildcard" : { "userDomain.raw" : "*" } } } } }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "should" : { "range" : { "@timestamp" : { "gte" : "'.$daterangefrom.'T00:00:00.000", "lte" : "'.$daterangeto.'T23:59:59.999" } } }, "must_not" : { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords = curl_exec($ch);
        curl_close($ch);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query": { "bool": { "should" : [ { "range" : { "@timestamp" : { "gte" : "'.$daterangefrom.'T00:00:00.000", "lte" : "'.$daterangeto.'T23:59:59.999" } } }, { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must" : [{ "term" : { "userDomain" : "'.$session->domain.'" } }, { "range" : { "@timestamp" : { "gte" : "'.$daterangefrom.'T00:00:00.000", "lte" : "'.$daterangeto.'T23:59:59.999" } } }]}} }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}

$resultWords = json_decode($resultWords, true);
$allEventsSwitch = false;

if (array_key_exists('count', $resultWords)) $totalQueryWords = $resultWords['count'];
else $totalQueryWords= "0";

$wordCounter = 0;
$eventCounter = 0;

if ($endpointDECSQL != "all")
{
    $matchesDataEndpoint = getAgentIdData($endpointDECES, $ESAlerterIndex, "AlertEvent");
    $endpointData = json_decode(json_encode($matchesDataEndpoint),true);
}
else
{
    if ($session->domain != "all") 
    {
        if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleMatchesWithDateRange($ESAlerterIndex, $session->domain, "enabled", "allalerts", $daterangefrom, $daterangeto, $pressure, $opportunity, $rationalization);
        else $eventMatches = getAllFraudTriangleMatchesWithDateRange($ESAlerterIndex, $session->domain, "disabled", "allalerts", $daterangefrom, $daterangeto, $pressure, $opportunity, $rationalization);
    }
    else 
    {
        if (samplerStatus($session->domain) == "enabled") $eventMatches = getAllFraudTriangleMatchesWithDateRange($ESAlerterIndex, "all", "enabled", "allalerts", $daterangefrom, $daterangeto, $pressure, $opportunity, $rationalization);
        else 
        {
            $eventMatches = getAllFraudTriangleMatchesWithDateRange($ESAlerterIndex, "all", "disabled", "allalerts", $daterangefrom, $daterangeto, $pressure, $opportunity, $rationalization);
        }
    }
                
    $eventData = json_decode(json_encode($eventMatches), true);
    $allEventsSwitch = true;
}

/* SQL Queries */

$queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
$queryDomain = "SELECT domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled") 
    {                
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM (SELECT agent FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus;";
    }
    else 
    {
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain NOT LIKE 'thefraudexplorer.com'";
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled") 
    { 
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
    }
    else 
    {
        $queryTyping = "SELECT COUNT(*) AS total FROM (SELECT * FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM (SELECT agent, domain FROM t_agents WHERE totalwords <> '0') AS typing) AS totals GROUP BY agent) AS totalplus WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
    }
}

$countTyping = mysqli_fetch_assoc(mysqli_query($connection, $queryTyping));

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

/* Endpoint domain */

$domainQuery = mysqli_query($connection, sprintf($queryDomain, $endpointDECSQL));
$domain = mysqli_fetch_array($domainQuery);

/* PHPSpreadsheet includes */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column;

$reader = IOFactory::createReader('Xlsx');
$spreadsheet = $reader->load("templates/Fraud_Triangle_Analytics_Report.xlsx");
$spreadsheet->getDefaultStyle()->getFont()->setName('Century Gothic')->setSize(10);
$spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(10204);

/* Dump the report */

$contentStartRow = 20;
$currentContentRow = 20;
$endpointCounter = 1;

if ($endpointDECSQL == "all")
{    
    foreach ($eventData['hits']['hits'] as $result)
    {        
        $_SESSION['processingStatus'] = "pending";

        if (isset($result['_source']['tags'])) continue;

        /* Event Details */

        $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
        $wordTyped = decRijndael($result['_source']['wordTyped']);
        $stringHistory = decRijndael($result['_source']['stringHistory']);
        $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
        $endPoint = explode("_", $result['_source']['agentId']);
        $agentId = $result['_source']['agentId'];
        $messageFlag = "no";
        
        if (isset($result['_source']['messageFlag'])) 
        {
            if ($result['_source']['messageFlag'] == 0) $messageFlag = "no";
            else $messageFlag = "yes";
        }
        
        $endpointDECSQL = $endPoint[0];
        $regExpression = htmlentities($result['_source']['phraseMatch']);
        $queryUserDomain = mysqli_query($connection, sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
        $userDomain = mysqli_fetch_assoc($queryUserDomain);
        $multipleEndpoints[$endpointCounter] = $userDomain['agent']."@".between('@', '.', "@".$userDomain['domain']);
        $endpointWithDomain = $userDomain['agent']."@".between('@', '.', "@".$userDomain['domain']);

        /* Report type filter */

        if (strcmp($typereport, "byendpoint") == 0) 
        {
            if ($typeinput != $endpointWithDomain) 
            {
                continue;
            }
        }

        if (strcmp($typereport, "bydomain") === 0)
        {
            if (strcmp($typeinput, between('@', '.', "@" . $userDomain['domain'])) != 0) 
            { 
                continue;
            }
        }     

        /* Application filter */

        if ((strcmp($allapplications, "allapplications") != 0) && $app != "")
        {
            if (strpos($windowTitle, $app) === false) 
            {
                continue;
            }
        }

        /* Phrase filter */

        if ((strcmp($allphrases, "allphrases") != 0) && $excluded != "")
        {
            if (strpos($stringHistory, $excluded) === false) 
            {
                continue;
            }
        }

        /* Flags filter */

        if (strcmp($flagsonly, "flagsonly") == 0)
        {
            if (strcmp($messageFlag, "no") == 0) 
            {
                continue;
            }
        }

        /* Ruleset filter */

        if (strcmp($alldepartments, "alldepartments") != 0)
        {
            if (strpos($userDomain['ruleset'], $ruleset) === false) 
            {
                continue;
            }
        }

        /* Excel report */

        $stringHistory = phraseSanitization($stringHistory, $notwantedWords);
        $stringHistory = str_ireplace($wordTyped, "<b>".$wordTyped."</b>", $stringHistory);

        /* Capitalizing */

        $stringHistory = preg_replace_callback('/\.\s<b>\w/', create_function('$m','return strtoupper($m[0]);'), $stringHistory);

        if (substr($stringHistory, 0, 3) === "<b>")
        {
            $charUppr = strtoupper(substr($wordTyped, 0, 1));
            $stringHistory = preg_replace('/^<b>[a-z]/', '<b>'.$charUppr, $stringHistory);
        }

        $stringHistory = "<font face=\"Century Gothic\" color=\"#4C4D4B\">". $stringHistory. "</font>";
        
        $html = new PhpOffice\PhpSpreadsheet\Helper\Html();
        $richCellValue = $html->toRichTextObject($stringHistory);

        $spreadsheet->getActiveSheet()->insertNewRowBefore($currentContentRow+1, 1);
        $spreadsheet->getActiveSheet()
        ->setCellValue('B'.$currentContentRow, $date)
        ->setCellValue('D'.$currentContentRow, strtoupper(ucfirst($result['_source']['alertType'])))
        ->setCellValue('F'.$currentContentRow, $userDomain['domain'])
        ->setCellValue('H'.$currentContentRow, $userDomain['agent']."@".between('@', '.', "@".$userDomain['domain']))
        ->setCellValue('I'.$currentContentRow, $windowTitle)
        ->setCellValue('K'.$currentContentRow, $richCellValue)
        ->setCellValue('L'.$currentContentRow, $messageFlag);

        $spreadsheet->getActiveSheet()->getStyle('I'.$currentContentRow)->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getStyle('K'.$currentContentRow)->getAlignment()->setWrapText(true);
        $spreadsheet->getActiveSheet()->getRowDimension($currentContentRow)->setRowHeight(-1);
        $spreadsheet->getActiveSheet()->getStyle('B'.$currentContentRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DATETIME); 
        $spreadsheet->getActiveSheet()->getStyle('L'.$currentContentRow)->getAlignment()->setHorizontal('center');

        if($currentContentRow % 2 == 0) $spreadsheet->getActiveSheet()->getStyle('A'.$currentContentRow.':'.'L'.$currentContentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('EDEDED');

        $currentContentRow++;
        $endpointCounter++;
    }
}

// Remove last empty rows

$spreadsheet->getActiveSheet()->removeRow($currentContentRow, 2);

// Add filters

$spreadsheet->getActiveSheet()->setAutoFilter('D'.($contentStartRow-2).':H'.$currentContentRow);

// Populate report header

$spreadsheet->getActiveSheet()->setCellValue('K9', $totalQueryWords);
$uniqueEndpoints = array_unique($multipleEndpoints);
$spreadsheet->getActiveSheet()->setCellValue('K15', count($uniqueEndpoints) ." reporting from a total of ". $countTyping['total']);

if ($alldaterange == "alldaterange")
{
    $daterangefrom = "the beginning of time";
    $daterangeto = "now";
}

$spreadsheet->getActiveSheet()->setCellValue('H15', "From ".str_replace("-", "/", $daterangefrom)." to ".str_replace("-", "/", $daterangeto));

if (strcmp($alldepartments, "alldepartments") != 0) $spreadsheet->getActiveSheet()->setCellValue('K6', $ruleset); 
else $spreadsheet->getActiveSheet()->setCellValue('K6', 'ALL');

/* Download XLSX file */

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Fraud_Triangle_Analytics_Report.xlsx"');
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->setPreCalculateFormulas(false);
$writer->save('php://output');

$_SESSION['processingStatus'] = "finished";

?>
