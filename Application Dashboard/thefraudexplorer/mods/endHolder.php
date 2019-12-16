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
 * Date: 2020-01
 * Revision: v1.4.1-ai
 *
 * Description: Code for paint main endpoints list
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
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

/* SQL Queries */

$queryConfig = "SELECT * FROM t_config";
$queryEndpointsSQL = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent";
$queryEndpointsSQL_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent";
$queryEndpointsSQLDomain = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent";
$queryEndpointsSQLDomain_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent";

/* Local styles */

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
echo '.font-icon-color-green { color: #1E9141; }';
echo '.fa-padding { padding-right: 5px; }';
echo '</style>';

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("../config.ini");
$ESindex = $configFile['es_words_index'];
$ESalerterIndex = $configFile['es_alerter_index'];
$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count";
        $urlAlerts="http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize="http://127.0.0.1:9200/_all/_stats";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQL);
    }
    else
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize="http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize="http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQLDomain);
    }
    else
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize="http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_TCP_FASTOPEN, true);
curl_setopt($ch, CURLOPT_URL,$urlSize);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$resultSize=curl_exec($ch);
curl_close($ch);

$resultWords = json_decode($resultWords, true);
$resultAlerts = json_decode($resultAlerts, true);
$resultSize = json_decode($resultSize, true);
$dataSize = $resultSize['_all']['primaries']['store']['size_in_bytes']/1024/1024;

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0"; 

/* Main data */

echo '<table id="tblData" class="tablesorter">';
echo '<thead><tr>';
echo '<th class="detailsth" id="elm-details-dashboard"><span class="fa fa-list fa-lg"></span></th>';
echo '<th class="totalwordsth"></th>';
echo '<th class="endpointth" id="elm-endpoints-dashboard">AUDIENCE UNDER FRAUD ANALYTICS</th>';
echo '<th class="compth" id="elm-ruleset-dashboard">RULE SET</th>';
echo '<th class="verth" id="elm-version-dashboard">VERSION</th>';
echo '<th class="stateth" id="elm-status-dashboard">STT</th>';
echo '<th class="lastth" id="elm-last-dashboard">LAST</th>';
echo '<th class="countpth">P</th><th class="countoth" id="elm-triangle-dashboard">O</th><th class="countrth">R</th>';
echo '<th class="countcth" id="elm-level-dashboard">L</th>';
echo '<th class="scoreth" id="elm-score-dashboard">SCORE</th>';
echo '<th class="specialth" id="elm-delete-dashboard">DEL</th>';
echo '<th class="specialth" id="elm-set-dashboard">SET</th></tr>';
echo '</thead><tbody>';

if ($row_a = mysqli_fetch_array($result_a))
{
    do
    {
        echo '<tr>';

        $endpointEnc = base64_encode(base64_encode($row_a["agent"]));
        $domain_enc = base64_encode(base64_encode($row_a["domain"]));

        /* Enpoint Details */

        echo '<td class="detailstd">';
        echo '<a class="endpoint-card-viewer" href="mods/endpointCard?id='.$row_a["agent"].'&domain='.$row_a["domain"].'" data-toggle="modal" data-target="#endpoint-card" href="#"><span class="fa fa-id-card-o fa-2x font-icon-color" style="font-size: 20px;"></span></a>&nbsp;&nbsp;';
        echo '</td>';

        /* Endpoint data retrieval */

        if($row_a['rationalization'] == NULL) $countRationalization = 0;
        else $countRationalization = $row_a['rationalization'];

        if($row_a['opportunity'] == NULL) $countOpportunity = 0;
        else $countOpportunity = $row_a['opportunity'];

        if($row_a['pressure'] == NULL) $countPressure = 0;
        else $countPressure = $row_a['pressure'];

        if($row_a['totalwords'] == NULL) $totalWordHits = 0;
        else $totalWordHits = $row_a['totalwords'];

        $score=($countPressure+$countOpportunity+$countRationalization)/3;
        if ($totalSystemWords != "0") $dataRepresentation = ($totalWordHits * 100)/$totalSystemWords;
        else $dataRepresentation = "0";

        /* Total words (hidden) sorting purpose */

        echo '<td class="totalwordstd">'.$totalWordHits.'</td>';

        /* Endpoint name */

        $endpointName = $row_a['agent']."@".between('@', '.', "@".$row_a['domain']);

        if ($row_a["name"] == NULL || $row_a["name"] == "NULL")
        {
            echo '<td class="endpointtd">';
            if ($row_a["gender"] == "male") endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            else if ($row_a["gender"] == "female") endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            else endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
        }
        else
        {
            $endpointName = $row_a['name']."@".between('@', '.', "@".$row_a['domain']);
            echo '<td class="endpointtd">';
            if ($row_a["gender"] == "male") endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            else if ($row_a["gender"] == "female") endpointInsights("endPoints", "female", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
            else echo endpointInsights("endPoints", "male", $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName);
        }

        /* Company, department or group */

        if ($row_a["ruleset"] == NULL || $row_a["ruleset"] == "NYET") echo '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>BASELINE</center></div></center></td>';
        else echo '<td class="comptd"><center><div class="ruleset-button"><center><div class="rule-title">ruleset</div></center><center>' . $row_a["ruleset"] . "</center></div></center></td>";

        /* Endpoint software version */

        echo '<td class="vertd"><span class="fa fa-codepen font-icon-color fa-padding"></span>' .$row_a["version"] .'</td>';

        /* Endpoint status */

        if($row_a["status"] == "active")
        {
            echo '<td class="statetd"><span class="fa fa-power-off fa-lg font-icon-color-green"></span></td>';
        }
        else
        {
            echo '<td class="statetd"><span class="fa fa-power-off fa-lg"></span></td>';
        }

        /* Last connection to the server */

        echo '<td class="lasttd">';
        echo '<center><div class="date-container">'.date('H:i',strtotime($row_a["heartbeat"])).'<br>'.'<div class="year-container">'.date('Y/m/d',strtotime($row_a["heartbeat"])).'</div></div></center>';
        echo '</td>';
        
        echo '<div id="fraudCounterHolder"></div>';

        /* Fraud triangle counts and score */

        $scoreQuery = mysqli_query($connection, $queryConfig);
        $scoreResult = mysqli_fetch_array($scoreQuery);

        $level = "low";
        if ($score >= $scoreResult['score_ts_low_from'] && $score <= $scoreResult['score_ts_low_to']) $level="low";
        if ($score >= $scoreResult['score_ts_medium_from'] && $score <= $scoreResult['score_ts_medium_to']) $level="med";
        if ($score >= $scoreResult['score_ts_high_from'] && $score <= $scoreResult['score_ts_high_to']) $level="high";
        if ($score >= $scoreResult['score_ts_critic_from']) $level="critic";

        echo '<td class="countptd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countPressure.'</td>';
        echo '<td class="countotd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countOpportunity.'</td>';
        echo '<td class="countrtd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countRationalization.'</td>';
        echo '<td class="countctd"><center><div class="score-container-underline">'.$level.'</div></center></td>';
        echo '<td class="scoretd"><a href=eventData?endpoint='.$endpointEnc.'>'.round($score, 1).'</a></td>';

        /* Option for delete the endpoint */

        echo '<td class="specialtd"><a class="delete-endpoint" data-href="mods/deleteEndpoint?endpoint='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-delete" href="#"><img src="images/delete-button.svg" onmouseover="this.src=\'images/delete-button-mo.svg\'" onmouseout="this.src=\'images/delete-button.svg\'" alt="" title=""/></a></td>';	

        /* Endpoint setup */

        echo '<td class="specialtd"><a class="setup-endpoint" href="mods/setupEndpoint?endpoint='.$endpointEnc.'" data-toggle="modal" data-target="#confirm-setup" href="#"><img src="images/setup.svg" onmouseover="this.src=\'images/setup-mo.svg\'" onmouseout="this.src=\'images/setup.svg\'" alt="" title=""/></a></td>';
        echo '</tr>';
    }
    while ($row_a = mysqli_fetch_array($result_a));

    echo '</tbody></table>'; 
    
    /* Button to switch phrase collection */
    
    $xml = simplexml_load_file('../update.xml');
    $phraseCollectionStatus = decRijndael($xml->token[0]['arg']);
    
    if ($phraseCollectionStatus == "textAnalytics 1") $phraseStatus = "enabled";
    else $phraseStatus = "disabled";
    
    if ($session->username == "admin") echo '&nbsp;<a data-href="mods/switchPhraseCollection" data-toggle="modal" data-target="#switch-phrase-collection" href="#" class="enable-analytics-button" id="elm-switch-phrase-collection">Press to switch between enabled and disabled phrase collection on endpoints, this feature applies at the next reboot of the user machines. The current status of phrase collection is: '.$phraseStatus.'</a>';
    else echo '&nbsp;<a href="#" class="enable-analytics-button" id="elm-switch-phrase-collection">Press to switch between enabled and disabled phrase collection on endpoints, this feature applies at the next reboot of the user machines. The current status of phrase collection is: '.$phraseStatus.'</a>';
}

?>

<!-- Pager bottom -->

<div id="pager" class="pager">
    <div class="pager-layout">
        <div class="pager-inside">
            <div class="pager-inside-endpoint" id="elm-pager">

                <?php
                
                if (array_key_exists('count', $resultWords)) $recordsCollected = number_format($resultWords['count'], 0, ',', '.');
                else $recordsCollected = "0";

                if (array_key_exists('count', $resultAlerts)) $fraudEvents = number_format($resultAlerts['count'], 0, ',', '.');	
                else $fraudEvents = "0";

                echo 'There are <span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.$recordsCollected.' records ';
                echo '<span class="fa fa-exclamation-triangle font-icon-color">&nbsp;&nbsp;</span>'.$fraudEvents.' fraud triangle events, ';
                echo 'ocupping <span class="fa fa-database font-icon-color">&nbsp;&nbsp;</span>'.number_format(round($dataSize,2), 2, ',', '.').' MBytes';
                
                ?>

            </div>

            <div class="pager-inside-pager">
                <form>
                    <span class="fa fa-fast-backward fa-lg first"></span>
                    <span class="fa fa-arrow-circle-o-left fa-lg prev"></span>
                    <span class="pagedisplay"></span>
                    <span class="fa fa-arrow-circle-o-right fa-lg next"></span>
                    <span class="fa fa-fast-forward fa-lg last"></span>&nbsp;
                    <select class="pagesize select-styled right">
                        <option value="20"> Show by 20 endpoints</option>
                        <option value="50"> Show by 50 endpoints</option>
                        <option value="100"> Show by 100 endpoints</option>
                        <option value="500"> Show by 500 endpoints</option>
                        <option value="all"> Show all Endpoints</option>
                    </select>
                    
                    <?php 
                    
                        echo '&nbsp;<button type="button" class="download-csv" id="elm-csv">Export & Download</button>&nbsp;';
                        echo '<a href="../mods/buildEndpoint" data-toggle="modal" class="build-endpoint-button" data-target="#build-endpoint" href="#" id="elm-build-endpoint">Build endpoint</a>';
                    ?>
                    
                </form>

                <form action="mods/departmentsUpload" id="departmentsUpload" method="post" enctype="multipart/form-data" accept-charset="utf-8">           
                    <div class="departmentsUploadStyle" style="outline: 0 !important;">                                             
                        <button type="button" class="departments-load" id="elm-departments" onclick="document.getElementById('departmentsToUpload').click();">Departmentalizing</button>            
                        <input type="file" name="departmentsToUpload" id="departmentsToUpload" class="upload" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById("departmentsToUpload").onchange = function() {
        document.getElementById("departmentsUpload").submit();
    }
</script>

<!-- Modal for delete dialog -->

<script>
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.delete').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Endpoint Card -->

<script>
    $('#endpoint-card').on('show.bs.modal', function(e) {
        $(this).find('.endpoint-card-viewer').attr('href', $(e.relatedTarget).data('href'));
    });
    
    $('#endpoint-card').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Modal for setup dialog -->

<script>
    $('#confirm-setup').on('show.bs.modal', function(e){
        $(this).find('.setup').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for main config -->

<script>
    $('#confirm-config').on('show.bs.modal', function(e){
        $(this).find('.config').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for switch phrase collection -->

<script>
    $('#switch-phrase-collection').on('show.bs.modal', function(e){
        $(this).find('.switch-phrase-collection-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for build endpoint -->

<script>
    $('#build-endpoint').on('show.bs.modal', function(e){
        $(this).find('.build-endpoint-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Table sorting -->

<script>
    $(document).ready(function(){
        
        $('.download-csv').click(function(){
            $("#tblData").trigger('outputTable');
        });
        
        $("#tblData").tablesorter({
            widgets: [ 'filter', 'output' ],
            widgetOptions : 
            {
                filter_external: '.search_text',
                filter_columnFilters : false,
                output_separator: ',',
                output_ignoreColumns : [ 0, 5, 12, 13, 14 ],
                output_dataAttrib: 'data-name',
                output_headerRows: false,
                output_delivery: 'download',
                output_saveRows: 'all',
                output_replaceQuote: '\u201c;',
                output_includeHTML: false,
                output_trimSpaces: true,
                output_wrapQuotes: false,
                output_saveFileName: 'endpointsList.csv',
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
                4:
                {
                    sorter: false
                },
                5:
                {
                    sorter: false
                },
                12:
                {
                    sorter: false
                },
                13:
                {
                    sorter: false
                },
                14:
                {
                    sorter: false
                }
            },
            sortList: [[11,1], [2,1]]
        })
            .tablesorterPager({
            container: $("#pager"),
            size: 20
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

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>