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
 * Date: 2020-05
 * Revision: v1.4.4-aim
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

/* Prevent direct access to this URL */

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

require '../vendor/autoload.php';
include "../lbs/globalVars.php";
include "../lbs/endpointMethods.php";
include "../lbs/elasticsearch.php";
include "../lbs/openDBconn.php";
include "../lbs/cryptography.php";

/* Local styles */

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
echo '.font-icon-color-green { color: #1E9141; }';
echo '.fa-padding { padding-right: 5px; }';
echo '</style>';

/* SQL Queries */

$queryConfig = "SELECT * FROM t_config";
$queryEndpointsSQL = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY SUM(agents.pressure+agents.opportunity+agents.rationalization)/3 DESC";
$queryEndpointsSQL_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent ORDER BY SUM(agents.pressure+agents.opportunity+agents.rationalization)/3 DESC";
$queryEndpointsSQLDomain = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY SUM(agents.pressure+agents.opportunity+agents.rationalization)/3 DESC";
$queryEndpointsSQLDomain_wOSampler = "SELECT agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, SUM(totalwords) AS totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, COUNT(agent) AS sessions FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, NOW(), system, version, status, domain, ipaddress, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE domain='".$session->domain."' GROUP BY agent ORDER BY SUM(agents.pressure+agents.opportunity+agents.rationalization)/3 DESC";

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
        $urlWords = "http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count";
        $urlAlerts = "http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize = "http://127.0.0.1:9200/_all/_stats";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords = curl_exec($ch);
        curl_close($ch);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts = curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQL);
    }
    else
    {
        $urlWords = 'http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts = "http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize = "http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords = curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts = curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords = 'http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts = "http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize = "http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords = curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts = curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQLDomain);
    }
    else
    {
        $urlWords = 'http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $urlAlerts = "http://127.0.0.1:9200/logstash-alerter-*/_count";
        $urlSize = "http://127.0.0.1:9200/_all/_stats";
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords = curl_exec($ch);
        curl_close($ch);
        
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } }, { "match" : { "falsePositive" : "1" } } ] } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts = curl_exec($ch);
        curl_close($ch);
        
        $result_a = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_ENCODING, ''); 
curl_setopt($ch, CURLOPT_URL,$urlSize);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$resultSize = curl_exec($ch);
curl_close($ch);

$resultWords = json_decode($resultWords, true);
$resultAlerts = json_decode($resultAlerts, true);
$resultSize = json_decode($resultSize, true);
$dataSize = $resultSize['_all']['primaries']['store']['size_in_bytes']/1024/1024;

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0";

?>

<div id="wrapper">
    <div class="spinner">
        <div class="rect1"></div>
        <div class="rect2"></div>
        <div class="rect3"></div>
        <div class="rect4"></div>
        <div class="rect5"></div>
    </div>
</div>

<table id="endpointsTable" class="tablesorter">
    <thead>
        <tr>
            <th class="detailsth" id="elm-details-dashboard"><span class="fa fa-list fa-lg"></span></th>
            <th class="endpointth" id="elm-endpoints-dashboard">AUDIENCE UNDER FRAUD ANALYTICS</th>
            <th class="totalwordsth"></th>
            <th class="compth" id="elm-ruleset-dashboard">RULE SET</th>
            <th class="verth" id="elm-version-dashboard">VERSION</th>
            <th class="stateth" id="elm-status-dashboard">STT</th>
            <th class="lastth" id="elm-last-dashboard">LAST</th>
            <th class="countpth">P</th>
            <th class="countoth" id="elm-triangle-dashboard">O</th>
            <th class="countrth">R</th>
            <th class="countcth" id="elm-level-dashboard">L</th>
            <th class="scoreth" id="elm-score-dashboard">SCORE</th>
            <th class="specialth" id="elm-delete-dashboard">DEL</th>
            <th class="specialth" id="elm-set-dashboard">SET</th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>

<?php

    /* Button to switch phrase collection */
    
    $xml = simplexml_load_file('../update.xml');
    $phraseCollectionStatus = decRijndael($xml->token[0]['arg']);
    
    if ($phraseCollectionStatus == "textAnalytics 1") $phraseStatus = "enabled";
    else $phraseStatus = "disabled";
    
    if ($session->username == "admin") echo '&nbsp;<a data-href="mods/switchPhraseCollection" data-toggle="modal" data-target="#switch-phrase-collection" href="#" class="enable-analytics-button" id="elm-switch-phrase-collection">Press to switch between enabled and disabled phrase collection on endpoints, this feature applies at the next reboot of the user machines. The current status of phrase collection is: '.$phraseStatus.'</a>';
    else echo '&nbsp;<a href="#" class="enable-analytics-button" id="elm-switch-phrase-collection">Press to switch between enabled and disabled phrase collection on endpoints, this feature applies at the next reboot of the user machines. The current status of phrase collection is: '.$phraseStatus.'</a>';

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
                    <span class="fa fa-fast-backward fa-lg first" id="backward"></span>
                    <span class="fa fa-arrow-circle-o-left fa-lg prev" id="left"></span>
                    <span class="pagedisplay"></span>
                    <span class="fa fa-arrow-circle-o-right fa-lg next" id="right"></span>
                    <span class="fa fa-fast-forward fa-lg last" id="forward"></span>&nbsp;
                    <select class="pagesize select-styled right">
                        <option value="20" selected="selected"> Show by 20 endpoints</option>
                        <option value="50"> Show by 50 endpoints</option>
                        <option value="100"> Show by 100 endpoints</option>
                        <option value="500"> Show by 500 endpoints</option>
                        <option value="all"> Show by all endpoints</option>
                    </select>    
                </form>
                              
                <a href="../mods/buildEndpoint" data-toggle="modal" class="build-endpoint-button" data-target="#build-endpoint" href="#" id="elm-build-endpoint">Build endpoint</a>
                <a href="mods/businessUnits" data-toggle="modal" class="departments-load" data-backdrop="static" data-keyboard="false" data-target="#business-units" href="#" id="elm-business-units">Business units</a>     
                        
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

<!-- Modal for Business Units -->

<script>
    $('#business-units').on('show.bs.modal', function(e){
        $(this).find('.business-units-button').attr('href', $(e.relatedTarget).data('href'));
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

<!-- Progress spinner on table -->

<script>
 
    $('.pagesize').change(function(){ 
        startSpinner();
    });

    $('#left, #right, #backward, #forward').click(function(){ 
        startSpinner();
    });

    function startSpinner() 
    {
        $('#wrapper').show();
        $('tbody').css("display","none");
    }

    function stopSpinner()
    {
        $('#wrapper').hide();
        $('tbody').css("display","block");
    }

</script>

<!-- Tablesorter script -->

<script>

$(function() {
$("#includedTopMenu").load("../helpers/topMenu.php?or=endpoints", function(){

    var timer, delay = 500;

    $('#search-box').bind('keydown', function(e) {
        var _this = $(this);
        clearTimeout(timer);
        timer = setTimeout(function() {
            startSpinner();
        }, delay);
    });

    $("#endpointsTable")
    .tablesorter({
        sortLocaleCompare: true,
        widgets: ['filter'],
        widgetOptions : 
        {
            filter_external: '.search_text',
            filter_columnFilters : false,
            pager_size: 20
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
            10:
            {
                sorter: false
            },
            11:
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
            },
        },
    })

    .tablesorterPager({
        container: $(".pager"),
        ajaxUrl : 'helpers/endpointsProcessing.php?page={page+1}&size={size}&{filterList:filter}&{sortList:col}&totalSystemWords=<?php echo $totalSystemWords; ?>',
        ajaxError: null,
        ajaxObject: {
        type: 'GET',
        dataType: 'json'
        },
        ajaxProcessing: function(data) {
        if (data && data.hasOwnProperty('rows')) {
            var indx, r, row, c, d = data.rows,
            total = data.total_rows,
            headers = data.headers,
            headerXref = headers.join(',').split(','),
            rows = [],
            len = d.length;
            for ( r=0; r < len; r++ ) {
            row = []; 
            for ( c in d[r] ) {
                if (typeof(c) === "string") {
                indx = $.inArray( c, headerXref );
                if (indx >= 0) {
                    row[indx] = d[r][c];
                }
                }
            }
            rows.push(row);
            }
            return [ total, rows, headers ];
        }
        },
        processAjaxOnInit: true,
        output: '{startRow} to {endRow} ({totalRows})',
        updateArrows: true,
        page: 0,
        size: 20,
        savePages: true,
        pageReset: 0,
        fixedHeight: false,
        removeRows: false,
        countChildRows: false,
    })
    
    .bind("pagerComplete",function() {

            /* Set CSS column styles */

            $('td:nth-child(1)').addClass("detailstd");
            $('td:nth-child(2)').addClass("endpointtd");
            $('td:nth-child(3)').addClass("totalwordstd");
            $('td:nth-child(4)').addClass("comptd");
            $('td:nth-child(5)').addClass("vertd");
            $('td:nth-child(6)').addClass("statetd");
            $('td:nth-child(7)').addClass("lasttd");
            $('td:nth-child(8)').addClass("countptd");
            $('td:nth-child(9)').addClass("countotd");
            $('td:nth-child(10)').addClass("countrtd");
            $('td:nth-child(11)').addClass("countctd");
            $('td:nth-child(12)').addClass("scoretd");
            $('td:nth-child(13)').addClass("specialtd");
            $('td:nth-child(14)').addClass("specialtd");

            /* Tooltipster callback */

            $('.tooltip-custom').tooltipster({
                    theme: 'tooltipster-custom',
                    contentAsHTML: true,
                    side: 'right',
                    delay: 0,
                    animationDuration: 0
            });

            /* Nice selects callback */

            $(document).ready(function() {
                $('select').niceSelect();
            });

            /* Hide spinner when finish load */

            stopSpinner();
    })

    .bind("sortStart",function() {
      startSpinner();
    });

    });
});

</script>