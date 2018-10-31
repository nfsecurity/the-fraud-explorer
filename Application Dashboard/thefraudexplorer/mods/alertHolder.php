<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.1
 *
 * Description: Code for paint agent data table
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

require '../vendor/autoload.php';
include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/agentMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/cryptography.php";

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];
$agent_decES = base64_decode(base64_decode($_SESSION['agentIDh']))."*";
$agent_decSQ = base64_decode(base64_decode($_SESSION['agentIDh']));
$agent_enc = $_SESSION['agentIDh'];

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://localhost:9200/logstash-thefraudexplorer-text-*/_count";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}

$resultWords = json_decode($resultWords, true);
$allAlertsSwitch = false;

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0";

$wordCounter = 0;
$alertCounter = 0;

if ($agent_decSQ != "all")
{
    $matchesDataAgent = getAgentIdData($agent_decES, $ESAlerterIndex, "AlertEvent");
    $agentData = json_decode(json_encode($matchesDataAgent),true);
}
else
{
    if ($session->domain != "all") 
    {
        if (samplerStatus($session->domain) == "enabled") $alertMatches = getAllFraudTriangleMatches($ESAlerterIndex, $session->domain, "enabled", "allalerts");
        else $alertMatches = getAllFraudTriangleMatches($ESAlerterIndex, $session->domain, "disabled", "allalerts");
    }
    else 
    {
        if (samplerStatus($session->domain) == "enabled") $alertMatches = getAllFraudTriangleMatches($ESAlerterIndex, "all", "enabled", "allalerts");
        else $alertMatches = getAllFraudTriangleMatches($ESAlerterIndex, "all", "disabled", "allalerts");
    }
                
    $alertData = json_decode(json_encode($alertMatches), true);
    $allAlertsSwitch = true;
}
    
/* Local styles */

echo '<style>';
echo '.font-icon-gray { color: #B4BCC2; }';
echo '.font-icon-green { color: #1E9141; }';
echo '.fa-padding { padding-right: 5px; }';
echo '</style>';

/* SQL Queries */

$queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
$queryDomain = "SELECT domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

/* Endpoint domain */

$domainQuery = mysql_query(sprintf($queryDomain, $agent_decSQ));
$domain = mysql_fetch_array($domainQuery);

/* Main Table */

if ($agent_decSQ != "all")
{
    echo '<table id="agentDataTable" class="tablesorter">';
    echo '<thead><tr>';
    echo '<th class="detailsth" id="elm-details-alert"><span class="fa fa-list fa-lg">&nbsp;</span></th>';
    echo '<th class="timestampth" id="elm-date-alert"><span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE</th>';
    echo '<th class="alerttypeth" id="elm-type-alert">VERTICE</th>';
    echo '<th class="windowtitleth" id="elm-windowtitle-alert"><span class="fa fa-window-restore fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION CONTEXT</th>';
    echo '<th class="phrasetypedth" id="elm-phrasetyped-alert"><span class="fa fa-pencil fa-lg font-icon-color-gray-low awfont-padding-right"></span>PHRASE TYPED</th>';
    echo '<th style="display: none;">PHRASE HISTORY</th>';
    echo '<th class="falseth" id="elm-mark-alert">MARK</th>';
    echo '</tr></thead><tbody>';

    foreach ($agentData['hits']['hits'] as $result)
    {        
        echo '<tr>';

        /* Alert Details */

        $date = $result['_source']['eventTime'];
        $date = substr($date, 0, strpos($date, ","));
        $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
        $wordTyped = decRijndael($result['_source']['wordTyped']);
        $stringHistory = decRijndael($result['_source']['stringHistory']);
        $searchValue = "/".$result['_source']['phraseMatch']."/";
        $searchResult = searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset);
        $regExpression = htmlentities($result['_source']['phraseMatch']);
    
        echo '<td class="detailstd">';
        echo '<span class="fa fa-id-card-o fa-2x font-icon-color-gray-low" style="font-size: 20px;">&ensp;</span>';
        echo '</td>';

        /* Timestamp */

        echo '<td class="timestamptd">';
        $date = date_create($date);
        echo date_format($date, 'Y-m-d H:i');
        echo '</td>';
        
        /* AlertType */

        echo '<td class="alerttypetd">';
        echo '<center>'.strtoupper(ucfirst($result['_source']['alertType'])).'</center>';
        echo '</td>';

        /* Window title */

        echo '<td class="windowtitletd">';
        echo '<span class="fa fa-list-alt font-icon-gray fa-padding"></span>'.$windowTitle;
        echo '</td>';

        /* Phrase typed */

        echo '<td class="phrasetypedtd">';
        echo '<span class="fa fa-pencil-square-o font-icon-color-green fa-padding"></span><a class="alert-phrase-viewer" href="mods/alertPhrases?id='.$result['_id'].'&idx='.$result['_index'].'&regexp='.base64_encode($regExpression).'" data-toggle="modal" data-target="#alert-phrases" href="#">'.$wordTyped.'</a>';
        echo '</td>';

        /* Hidden Phrase zoom, for CSV purposes */
        
        echo '<td style="display: none;">'.$stringHistory.'</td>';

        /* Mark false positive */
    
        $index = $result['_index'];
        $type = $result['_type'];
        $regid = $result['_id'];
        $agentId = $result['_source']['agentId'];
    
        $urlAlertValue="http://localhost:9200/".$index."/".$type."/".$regid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlertValue);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultValues=curl_exec($ch);
        curl_close($ch);
    
        $jsonResultValue = json_decode($resultValues);
        $falsePositiveValue = $jsonResultValue->_source->falsePositive;
    
        echo '<td class="falsetd">';
        
        echo '<a class="false-positive" href="mods/alertMarking?regid='.$result['_id'].'&agent='.$agentId.'&index='.$result['_index'].'&type='.$result['_type'].'&urlrefer=singlealerts" data-toggle="modal" data-target="#alertMarking" href="#">';
    
        if ($falsePositiveValue == "0") echo '<span class="fa fa-check-square fa-lg font-icon-green"></span></a></td>';
        else echo '<span class="fa fa-check-square fa-lg font-icon-gray"></span></a></td>';

        echo '</tr>';

        $wordCounter++;
    }

    echo '</tbody></table>';
}
else
{
    echo '<table id="allalerts" class="tablesorter">';
    echo '<thead>';
    echo '<tr>';
    echo '<th class="detailsth-all" id="elm-details-alert">';
    echo '<span class="fa fa-list fa-lg awfont-padding-right"></span>';
    echo '</th>';
    echo '<th class="timestampth-all" id="elm-date-alert">';
    echo '<span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE';
    echo '</th>';
    echo '<th class="alerttypeth-all" id="elm-type-alert">';
    echo 'VERTICE';
    echo '</th>';
    echo '<th class="endpointth-all" id="elm-endpoint-alert">';
    echo '<span class="fa fa-users fa-lg font-icon-color-gray-low awfont-padding-right"></span>COMPANY PEOPLE';
    echo '</th>';
    echo '<th class="windowtitleth-all" id="elm-windowtitle-alert">';
    echo '<span class="fa fa-window-restore fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION CONTEXT';
    echo '</th>';
    echo '<th class="phrasetypedth-all" id="elm-phrasetyped-alert">';
    echo '<span class="fa fa-pencil fa-lg font-icon-color-gray-low awfont-padding-right"></span>PHRASE TYPED';
    echo '</th>';
    echo '<th style="display: none;">PHRASE HISTORY</th>';
    echo '<th class="falseth-all" id="elm-mark-alert">';
    echo '<center>MARK</center>';
    echo '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    foreach ($alertData['hits']['hits'] as $result)
    {
        if (isset($result['_source']['tags'])) continue;
        
        echo '<tr>';
        echo '<td class="detailstd-all">';
                   
        $date = date('Y-m-d H:i', strtotime($result['_source']['sourceTimestamp']));
        $wordTyped = decRijndael($result['_source']['wordTyped']);
        $stringHistory = decRijndael($result['_source']['stringHistory']);
        $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
        $searchValue = "/".$result['_source']['phraseMatch']."/";
        $endPoint = explode("_", $result['_source']['agentId']);
        $agent_decSQ = $endPoint[0];
        $queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";                 
        $searchResult = searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset);
        $regExpression = htmlentities($result['_source']['phraseMatch']);
                    
        /* Details */
        
        echo '<span class="fa fa-id-card-o fa-2x font-icon-color-gray-low" style="font-size: 20px;">&ensp;</span>';
        
        echo '</td>';
        
        /* Date */
        
        echo '<td class="timestamptd-all">';       
        echo $date;                 
        echo '</td>';
        
        /* Alert type */
                    
        echo '<td class="alerttypetd-all">';
        echo '<center>'.strtoupper($result['_source']['alertType']).'</center>';
        echo '</td>';
        
        /* Endpoint */
        
        echo '<td class="endpointtd-all">';
         
        $queryUserDomain = mysql_query(sprintf("SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) as tbl WHERE agent='%s' group by agent order by score desc", $endPoint[0]));
                    
        $userDomain = mysql_fetch_assoc($queryUserDomain);
        $agentName = $userDomain['agent']."@".between('@', '.', "@".$userDomain['domain']);
        $agent_enc = base64_encode(base64_encode($userDomain['agent']));
        $totalWordHits = $userDomain['totalwords'];
        $countPressure = $userDomain['pressure'];
        $countOpportunity = $userDomain['opportunity'];
        $countRationalization = $userDomain['rationalization'];
        $score = $userDomain['score'];
                            
        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
        else $dataRepresentation = "0";
                    
        echo '<span class="fa fa-laptop font-icon-color-green awfont-padding-right"></span>';
                                    
        if ($userDomain["name"] == NULL || $userDomain['name'] == "NULL") agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
        else 
        {
            $agentName = $userDomain['name'];
            agentInsights("dashBoard", "na", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
        }
                    
        echo '</td>';
        
        /* Application */
        
        echo '<td class="windowtitletd-all">';
        echo '<span class="fa fa-list-alt font-icon-color-gray-low awfont-padding-right"></span>'.$windowTitle;
        echo '</td>';
        
        /* Phrase typed */
      
        echo '<td class="phrasetypedtd-all">';
        echo '<span class="fa fa-pencil-square-o font-icon-color-green fa-padding"></span><a class="alert-phrase-viewer" href="mods/alertPhrases?id='.$result['_id'].'&idx='.$result['_index'].'&regexp='.base64_encode($regExpression).'" data-toggle="modal" data-target="#alert-phrases" href="#">'.$wordTyped.'</a>';
        echo '</td>';

        /* Hidden Phrase zoom, for CSV purposes */
        
        echo '<td style="display: none;">'.$stringHistory.'</td>';
        
        /* Mark false positive */
    
        $index = $result['_index'];
        $type = $result['_type'];
        $regid = $result['_id'];
        $agentId = $result['_source']['agentId'];
    
        $urlAlertValue="http://localhost:9200/".$index."/".$type."/".$regid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlertValue);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultValues=curl_exec($ch);
        curl_close($ch);
    
        $jsonResultValue = json_decode($resultValues);
        $falsePositiveValue = $jsonResultValue->_source->falsePositive;
    
        echo '<td class="falsetd-all">';
        echo '<a class="false-positive" href="mods/alertMarking?regid='.$result['_id'].'&agent='.$agentId.'&index='.$result['_index'].'&type='.$result['_type'].'&urlrefer=allalerts" data-toggle="modal" data-target="#alertMarking" href="#">';
        
        if ($falsePositiveValue == "0") echo '<span class="fa fa-check-square fa-lg font-icon-green"></span></a></td>';
        else echo '<span class="fa fa-check-square fa-lg font-icon-gray"></span></a></td>';

        echo '</tr>';
        
        $alertCounter++;
    }
}

?>

<!-- Pager -->

<?php

if ($allAlertsSwitch != true)
{
    echo '<div id="pager" class="pager">';
    echo '<div class="pager-layout" id="elm-pager-alerts">';
    echo '<div class="pager-inside">';
    echo '<div class="pager-inside-agent">';
    
    $endpointName = $agent_decSQ."@".between('@', '.', "@".$domain[0]);
    
    echo 'There are '.$wordCounter.' regular expressions matched by <span class="fa fa-user">&nbsp;&nbsp;</span>'.$endpointName.' stored in database';
    echo '</div>';

    echo '<div class="pager-inside-pager">';
    echo '<form>';
    echo '<span class="fa fa-fast-backward fa-lg first"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-left fa-lg prev"></span>&nbsp;';
    echo '<span class="pagedisplay"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-right fa-lg next"></span>&nbsp;';
    echo '<span class="fa fa-fast-forward fa-lg last"></span>&nbsp;&nbsp;';
    echo '<select class="pagesize select-styled">';
    echo '<option value="20"> by 20 alerts</option>';
    echo '<option value="50"> by 50 alerts</option>';
    echo '<option value="100"> by 100 alerts</option>';
    echo '<option value="500"> by 500 alerts</option>';
    echo '<option value="all"> All Alerts</option>';
    echo '</select>';
                    
    echo '&nbsp;<button type="button" class="download-csv">Download as CSV</button>';
                    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
else
{
    /* Term statistics calculation */
    
    $queryTermsSQL = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents;";
    $queryTermsSQL_wOSampler = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com'";
    $queryTermsSQLDomain_wOSampler = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='".$session->domain."'";
    $queryTermsSQLDomain = "SELECT SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization FROM t_agents WHERE domain='thefraudexplorer.com' OR domain='".$session->domain."'";
    
    $samplerStatus = samplerStatus($session->domain);
    
    if ($session->domain == "all")
    {
        if ($samplerStatus == "enabled") $queryTerms = mysql_query($queryTermsSQL);
        else $queryTerms = mysql_query($queryTermsSQL_wOSampler);
    }
    else
    {
        if ($samplerStatus == "enabled") $queryTerms = mysql_query($queryTermsSQLDomain);
        else $queryTerms = mysql_query($queryTermsSQLDomain_wOSampler);
    }
        
    $fraudTerms = mysql_fetch_assoc($queryTerms);
    $fraudScore = ($fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'])/3;
    
    /* Pager */
    
    echo '<div id="pagerAll" class="pager pager-screen">';
    echo '<div class="pager-layout" id="elm-pager-alerts">';
    echo '<div class="pager-inside">';
    echo '<div class="pager-inside-agent">';
    echo 'There are '.$alertCounter.' total alerts, '.$fraudTerms['pressure'].' from pressure, '.$fraudTerms['opportunity'].' from opportunity and '.$fraudTerms['rationalization'].' from rationalization';
    echo '</div>';

    echo '<div class="pager-inside-pager">';
    echo '<form>';
    echo '<span class="fa fa-fast-backward fa-lg first"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-left fa-lg prev"></span>&nbsp;';
    echo '<span class="pagedisplay"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-right fa-lg next"></span>&nbsp;';
    echo '<span class="fa fa-fast-forward fa-lg last"></span>&nbsp;&nbsp;';
    echo '<select class="pagesize select-styled">';
    echo '<option value="20"> by 20 alerts</option>';
    echo '<option value="50"> by 50 alerts</option>';
    echo '<option value="100"> by 100 alerts</option>';
    echo '<option value="500"> by 500 alerts</option>';
    echo '<option value="all"> All Alerts</option>';
    echo '</select>';
                    
    echo '&nbsp;<button type="button" class="download-csv">Download as CSV</button>';
                    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

?>

<!-- Modal for Phrase viewer -->

<script>
    $('#alert-phrases').on('show.bs.modal', function(e) {
        $(this).find('.alert-phrase-viewer').attr('href', $(e.relatedTarget).data('href'));
    });
    
    $('#alert-phrases').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Table sorter -->

<script>
    $(function(){
        
        $('.download-csv').click(function(){
            $("#agentDataTable").trigger('outputTable');
        });
        
        $("#agentDataTable").tablesorter({
            widgets: [ 'filter', 'output' ],
            widgetOptions : 
            {
                filter_external: '.search_text',
                filter_columnFilters : false,
                output_separator: ',',
                output_ignoreColumns : [ 0, 6 ],
                output_hiddenColumns: true,
                output_dataAttrib: 'data-name',
                output_headerRows: false,
                output_delivery: 'download',
                output_saveRows: 'all',
                output_replaceQuote: '\u201c;',
                output_includeHTML: false,
                output_trimSpaces: true,
                output_wrapQuotes: false,
                output_saveFileName: 'alertsList.csv',
                output_callback: function (data) {
                    return true;
                },
                output_callbackJSON: function ($cell, txt, cellIndex) {
                    return txt + '(' + (cellIndex + col) + ')';
                }
            },
            headers:
            {
                0:
                {
                    sorter: false
                },
                1:
                {
                    sorter: "shortDate", dateFormat: "yyymmdd"
                },
                6:
                {
                    sorter: false
                },
            },
            sortList: [[1,1]]
        })
            .tablesorterPager({
            container: $("#pager"),
            size: 50,
            widgetOptions:
            {
                pager_removeRows: true
            }
        });
        
        $('.download-csv').click(function(){
            $("#allalerts").trigger('outputTable');
        });
        
        $("#allalerts").tablesorter({
            widgets: [ 'filter', 'output' ],
            widgetOptions : 
            {
                filter_external: '.search_text',
                filter_columnFilters : false,
                output_separator: ',',
                output_ignoreColumns : [ 0, 7 ],
                output_hiddenColumns: true,
                output_dataAttrib: 'data-name',
                output_headerRows: false,
                output_delivery: 'download',
                output_saveRows: 'all',
                output_replaceQuote: '\u201c;',
                output_includeHTML: false,
                output_trimSpaces: true,
                output_wrapQuotes: false,
                output_saveFileName: 'allAlertsList.csv',
                output_callback: function (data) {
                    return true;
                },
                output_callbackJSON: function ($cell, txt, cellIndex) {
                    return txt + '(' + (cellIndex + col) + ')';
                }
            },
            headers:
            {
                0:
                {
                    sorter: false
                },
                1:
                {
                    sorter: "shortDate", dateFormat: "yyymmdd"
                },
                7:
                {
                    sorter: false
                },
            },
            sortList: [[1,1]]
        })
            .tablesorterPager({
            container: $("#pagerAll"),
            size: 50,
            widgetOptions:
            {
                pager_removeRows: true
            }
        });
        
    });
</script>

<!-- Tooltipster -->

<script>
    $(document).ready(function(){
        $('.tooltip-custom').tooltipster({
            theme: 'tooltipster-custom',
            contentAsHTML: true,
            side: 'right',
            delay: 0,
            animationDuration: 0
        });
    });
</script>