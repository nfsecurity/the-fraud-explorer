<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for paint main endpoints list
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

require 'vendor/autoload.php';
include "lbs/global-vars.php";
include "lbs/agent_methods.php";
include "lbs/elasticsearch.php";
include "lbs/open-db-connection.php";
include "lbs/cryptography.php";

$_SESSION['id_uniq_command']=null;

/* SQL Queries */

$queryConfig = "SELECT * FROM t_config";
$queryAgentsSQL = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent";
$queryAgentsSQL_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent";
$queryAgentsSQLDomain = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent";
$queryAgentsSQLDomain_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent";

/* Order the dashboard agent list */

discoverOnline();

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
echo '.font-icon-color-green { color: #1E9141; }';
echo '.fa-padding { padding-right: 5px; }';
echo '</style>';

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("config.ini");
$ESindex = $configFile['es_words_index'];
$ESalerterIndex = $configFile['es_alerter_index'];
$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://localhost:9200/logstash-thefraudexplorer-text-*/_count";
        $urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
        $urlSize="http://localhost:9200/_all/_stats";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysql_query($queryAgentsSQL);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
        $urlSize="http://localhost:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysql_query($queryAgentsSQL_wOSampler);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
        $urlSize="http://localhost:9200/_all/_stats";
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysql_query($queryAgentsSQLDomain);
    }
    else
    {
        $urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
        $urlSize="http://localhost:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultWords=curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysql_query($queryAgentsSQLDomain_wOSampler);
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL,$urlSize);
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
echo '<th class="agentth" id="elm-endpoints-dashboard">USERS UNDER ANALYTICS</th>';
echo '<th class="compth" id="elm-ruleset-dashboard">RULE SET</th>';
echo '<th class="verth" id="elm-version-dashboard">VER</th>';
echo '<th class="stateth" id="elm-status-dashboard">STT</th>';
echo '<th class="lastth" id="elm-last-dashboard">LAST</th>';
echo '<th class="countpth">P</th><th class="countoth" id="elm-triangle-dashboard">O</th><th class="countrth">R</th>';
echo '<th class="countcth" id="elm-level-dashboard">L</th>';
echo '<th class="scoreth" id="elm-score-dashboard">SCORE</th>';
echo '<th class="specialth" id="elm-command-dashboard">CMD</th>';
echo '<th class="specialth" id="elm-delete-dashboard">DEL</th>';
echo '<th class="specialth" id="elm-set-dashboard">SET</th></tr>';
echo '</thead><tbody>';

if ($row_a = mysql_fetch_array($result_a))
{
    do
    {
        echo '<tr>';

        $agent_enc = base64_encode(base64_encode($row_a["agent"]));
        $domain_enc = base64_encode(base64_encode($row_a["domain"]));

        /* Enpoint Details */

        echo '<td class="detailstd">';
        agentDetails($row_a['agent'], $row_a['domain'], getTextSist($row_a["system"]), $row_a['status'], $row_a['ipaddress'], $row_a['sessions']);
        echo '</td>';

        /* Agent data retrieval */

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

        /* Agent name */

        $agentName = $row_a["agent"] . "@" .$row_a["domain"];

        if ($row_a["name"] == NULL || $row_a["name"] == "NULL")
        {
            echo '<td class="agenttd">';
            if ($row_a["gender"] == "male") agentInsights("endPoints", "male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
            else if ($row_a["gender"] == "female") agentInsights("endPoints", "female", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
            else agentInsights("endPoints", "male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
        }
        else
        {
            $agentName = $row_a["name"];
            echo '<td class="agenttd">';
            if ($row_a["gender"] == "male") agentInsights("endPoints", "male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
            else if ($row_a["gender"] == "female") agentInsights("endPoints", "female", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
            else echo agentInsights("endPoints", "male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
        }

        /* Company, department or group */

        if ($row_a["ruleset"] == NULL || $row_a["ruleset"] == "NYET") echo '<td class="comptd">BASELINE</td>';
        else echo '<td class="comptd">' . $row_a["ruleset"] . "</td>";

        /* Agent software version */

        echo '<td class="vertd"><span class="fa fa-codepen font-icon-color fa-padding"></span>' .$row_a["version"] .'</td>';

        /* Agent status */

        if($row_a["status"] == "active")
        {
            echo '<td class="statetd"><span class="fa fa-power-off fa-lg font-icon-color-green"></span></td>';
        }
        else
        {
            echo '<td class="statetd"><span class="fa fa-power-off fa-lg"></span></td>';
        }

        /* Last connection to the server */

        echo '<td class="lasttd">'.str_replace(array("-"),array("/"),$row_a["heartbeat"]).'</td>';
        echo '<div id="fraudCounterHolder"></div>';

        /* Fraud triangle counts and score */

        $scoreQuery = mysql_query($queryConfig);
        $scoreResult = mysql_fetch_array($scoreQuery);

        $level = "low";
        if ($score >= $scoreResult['score_ts_low_from'] && $score <= $scoreResult['score_ts_low_to']) $level="low";
        if ($score >= $scoreResult['score_ts_medium_from'] && $score <= $scoreResult['score_ts_medium_to']) $level="medium";
        if ($score >= $scoreResult['score_ts_high_from'] && $score <= $scoreResult['score_ts_high_to']) $level="high";
        if ($score >= $scoreResult['score_ts_critic_from']) $level="critic";

        echo '<td class="countptd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countPressure.'</td>';
        echo '<td class="countotd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countOpportunity.'</td>';
        echo '<td class="countrtd"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>'.$countRationalization.'</td>';
        echo '<td class="countctd">'.$level.'</td>';
        echo '<td class="scoretd"><a href=alertData?agent='.$agent_enc.'>'.round($score, 1).'</a></td>';

        /* Agent selection for command retrieval */

        if(isConnected($row_a["heartbeat"], $row_a[2]))
        {
            if(isset($_SESSION['agentchecked']))
            {
                if($_SESSION['agentchecked'] == $row_a["agent"]) echo '<td class="specialtd"><a href="endPoints?agent='.$agent_enc.'&domain='.$domain_enc.'"><img src="images/cmd-ok.svg" onmouseover="this.src=\'images/cmd-mo-ok.svg\'" onmouseout="this.src=\'images/cmd-ok.svg\'" alt="" title="" /></a></td>';
                else echo '<td class="specialtd"><a href="endPoints?agent='.$agent_enc.'&domain='.$domain_enc.'"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="" title="" /></a></td>';  
            }
            else echo '<td class="specialtd"><a href="endPoints?agent='.$agent_enc.'&domain='.$domain_enc.'"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="" title="" /></a></td>';
        }
        else
        {
            if(isset($_SESSION['agentchecked']))
            {
                if($_SESSION['agentchecked'] == $row_a["agent"]) echo '<td class="specialtd"><img src="images/cmd-ok.svg" onmouseover="this.src=\'images/cmd-mo-ok.svg\'" onmouseout="this.src=\'images/cmd-ok.svg\'" alt="Agent down" title="Agent down" /></td>';
                else echo '<td class="specialtd"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="Agent down" title="Agent down" /></td>';
            }
            else echo '<td class="specialtd"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="Agent down" title="Agent down" /></td>'; 
        }

        /* Option for delete the agent */

        echo '<td class="specialtd"><a class="delete-agent" data-href="deleteAgent?agent='.$agent_enc.'" data-toggle="modal" data-target="#confirm-delete" href="#"><img src="images/delete-button.svg" onmouseover="this.src=\'images/delete-button-mo.svg\'" onmouseout="this.src=\'images/delete-button.svg\'" alt="" title=""/></a></td>';	

        /* Agent setup */

        echo '<td class="specialtd"><a class="setup-agent" href="setupAgent?agent='.$agent_enc.'" data-toggle="modal" data-target="#confirm-setup" href="#"><img src="images/setup.svg" onmouseover="this.src=\'images/setup-mo.svg\'" onmouseout="this.src=\'images/setup.svg\'" alt="" title=""/></a></td>';
        echo '</tr>';
    }
    while ($row_a = mysql_fetch_array($result_a));

    echo '</tbody></table>';
}

?>

<!-- Pager bottom -->

<div id="pager" class="pager">
    <div class="pager-layout">
        <div class="pager-inside">
            <div class="pager-inside-agent" id="elm-pager">

                <?php
                
                if (array_key_exists('count', $resultWords)) $recordsCollected = number_format($resultWords['count'], 0, ',', '.');
                else $recordsCollected = "0";

                if (array_key_exists('count', $resultAlerts)) $fraudAlerts = number_format($resultAlerts['count'], 0, ',', '.');	
                else $fraudAlerts = "0";

                echo 'There are <span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.$recordsCollected.' records collected and ';
                echo '<span class="fa fa-exclamation-triangle font-icon-color">&nbsp;&nbsp;</span>'.$fraudAlerts.' fraud triangle alerts triggered, ';
                echo 'all ocupping <span class="fa fa-database font-icon-color">&nbsp;&nbsp;</span>'.number_format(round($dataSize,2), 2, ',', '.').' MBytes in size';
                
                ?>

            </div>

            <div class="pager-inside-pager">
                <form>
                    <span class="fa fa-fast-backward fa-lg first"></span>
                    <span class="fa fa-arrow-circle-o-left fa-lg prev"></span>
                    <span class="pagedisplay"></span>
                    <span class="fa fa-arrow-circle-o-right fa-lg next"></span>
                    <span class="fa fa-fast-forward fa-lg last"></span>&nbsp;
                    <select class="pagesize select-styled">
                        <option value="20"> by 20 endpoints</option>
                        <option value="50"> by 50 endpoints</option>
                        <option value="100"> by 100 endpoints</option>
                        <option value="500"> by 500 endpoints</option>
                        <option value="all"> All Endpoints</option>
                    </select>
                    
                    <?php echo '&nbsp;<button type="button" class="download-csv">Download as CSV</button>'; ?>
                    
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for delete dialog -->

<script>
    $('#confirm-delete').on('show.bs.modal', function(e) {
        $(this).find('.delete').attr('href', $(e.relatedTarget).data('href'));
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
            theme: 'tooltipster-light',
            contentAsHTML: true,
            side: 'right'
        });
    });
</script>