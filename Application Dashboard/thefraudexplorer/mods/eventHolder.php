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
 * Description: Code for paint endpoint data table
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
include "../lbs/openDBconn.php";
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

/* Global data variables */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords="http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlWords);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query" : { "bool" : { "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultWords=curl_exec($ch);
        curl_close($ch);
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $urlWords='http://127.0.0.1:9200/logstash-thefraudexplorer-text-*/_count';
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "userDomain" : "'.$session->domain.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
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
        $params = '{ "query" : { "bool" : { "must" : [ { "term" : { "userDomain" : "'.$session->domain.'" } } ], "must_not" : [ { "match" : { "userDomain.raw" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlWords);
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

if (array_key_exists('count', $resultWords)) $totalSystemWords = $resultWords['count'];
else $totalSystemWords= "0";

$eventCounter = 0;
    
/* Local styles */

echo '<style>';
echo '.font-icon-gray { color: #B4BCC2; }';
echo '.font-icon-green { color: #1E9141; }';
echo '.fa-padding { padding-right: 5px; }';
echo '</style>';

/* SQL Queries */

$queryDomain = "SELECT domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";

/* Endpoint domain */

$domainQuery = mysqli_query($connection, sprintf($queryDomain, $endpointDECSQL));
$domain = mysqli_fetch_array($domainQuery);

/* Main Table */

if ($endpointDECSQL != "all")
{
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

    <table id="eventsTableSingle" class="tablesorter">
        <thead>
            <tr>
                <th class="detailsth" id="elm-details-event"><span class="fa fa-list fa-lg awfont-padding-right"></span></th>
                <th class="timestampth" id="elm-date-event"><span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE</th>
                <th class="eventtypeth" id="elm-type-event">BEHAVIOR</th>
                <th class="windowtitleth" id="elm-windowtitle-event"><span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE</th>
                <th class="metricsth" id="elm-endpoint-metrics">&nbsp;METRS</th>
                <th class="phrasetypedth" id="elm-phrasetyped-event"><span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING</th>
                <th style="display: none;">EXPRESSION HISTORY</th>
                <th class="falseth" id="elm-mark-event">MARK</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <?php
}
else
{
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

    <table id="eventsTableAll" class="tablesorter">
        <thead>
            <tr>
                <th class="detailsth-all" id="elm-details-event"><span class="fa fa-list fa-lg awfont-padding-right"></span></th>
                <th class="timestampth-all" id="elm-date-event"><span class="fa fa-calendar-o fa-lg font-icon-color-gray-low awfont-padding-right"></span>DATE</th>
                <th class="eventtypeth-all" id="elm-type-event">BEHAVIOR</th>
                <th class="endpointth-all" id="elm-endpoint-event"><span class="fa fa-briefcase fa-lg font-icon-color-gray-low awfont-padding-right"></span>HUMAN AUDIENCE</th>
                <th class="windowtitleth-all" id="elm-windowtitle-event"><span class="fa fa-list-alt fa-lg font-icon-color-gray-low awfont-padding-right"></span>APPLICATION AND INSTANCE</th>
                <th class="metricsth-all" id="elm-endpoint-metrics">&nbsp;METRS</th>
                <th class="phrasetypedth-all" id="elm-phrasetyped-event"><span class="fa fa-wpforms fa-lg font-icon-color-gray-low awfont-padding-right"></span>IS/EXPRESSING</th>
                <th class="falseth-all" id="elm-mark-event"><center>MARK</center></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    
    <?php
}

?>

<!-- Pager -->

<?php

if ($endpointDECSQL != "all")
{
    $countSingleEvents = countAgentIdEvents($endpointDECES, $ESAlerterIndex, "AlertEvent");

    /* Pager Single */

    $totalEventsSum = $countSingleEvents['count'];

    echo '<div id="pagerSingle" class="pager pagerSingle">';
    echo '<div class="pager-layout" id="elm-pager-events">';
    echo '<div class="pager-inside">';
    echo '<div class="pager-inside-endpoint">';
    
    $endpointName = $endpointDECSQL."@".$domain[0];
    
    echo 'There are '.$totalEventsSum.' regular expressions matched by <span class="fa fa-user">&nbsp;&nbsp;</span>'.$endpointName.' stored in database';
    echo '</div>';

    echo '<div class="pager-inside-pager">';
    echo '<form>';
    echo '<span class="fa fa-fast-backward fa-lg first" id="backward"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-left fa-lg prev" id="left"></span>&nbsp;';
    echo '<span class="pagedisplay"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-right fa-lg next" id="right"></span>&nbsp;';
    echo '<span class="fa fa-fast-forward fa-lg last" id="forward"></span>&nbsp;&nbsp;';

    echo '<select class="pagesize select-styled right">';
    echo '<option value="20"> Show by 20 events</option>';
    echo '<option value="50"> Show by 50 events</option>';
    echo '<option value="100"> Show by 100 events</option>';
    echo '<option value="500"> Show by 500 events</option>';
    echo '<option value="all"> Show all Events</option>';
    echo '</select>';

    echo '<a href="../mods/advancedReports" data-toggle="modal" class="advanced-reports-button" data-target="#advanced-reports" href="#" id="elm-advanced-reports">Advanced Reports</a>';
    
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
        if ($samplerStatus == "enabled") $queryTerms = mysqli_query($connection, $queryTermsSQL);
        else $queryTerms = mysqli_query($connection, $queryTermsSQL_wOSampler);
    }
    else
    {
        if ($samplerStatus == "enabled") $queryTerms = mysqli_query($connection, $queryTermsSQLDomain);
        else $queryTerms = mysqli_query($connection, $queryTermsSQLDomain_wOSampler);
    }
        
    $fraudTerms = mysqli_fetch_assoc($queryTerms);
    $fraudScore = ($fraudTerms['pressure'] + $fraudTerms['opportunity'] + $fraudTerms['rationalization'])/3;
    
    /* Pager All */

    $totalEventsSum = intval($fraudTerms['pressure']) + intval($fraudTerms['opportunity']) + intval($fraudTerms['rationalization']);
    
    echo '<div id="pagerAll" class="pager pagerAll pager-screen">';
    echo '<div class="pager-layout" id="elm-pager-events">';
    echo '<div class="pager-inside">';
    echo '<div class="pager-inside-endpoint">';
    echo 'There are '.$totalEventsSum.' total events, '.$fraudTerms['pressure'].' from pressure, '.$fraudTerms['opportunity'].' from opportunity and '.$fraudTerms['rationalization'].' from rationalization';
    echo '</div>';

    echo '<div class="pager-inside-pager">';
    echo '<form>';
    echo '<span class="fa fa-fast-backward fa-lg first" id="backward"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-left fa-lg prev" id="left"></span>&nbsp;';
    echo '<span class="pagedisplay"></span>&nbsp;';
    echo '<span class="fa fa-arrow-circle-o-right fa-lg next" id="right"></span>&nbsp;';
    echo '<span class="fa fa-fast-forward fa-lg last" id="forward"></span>&nbsp;&nbsp;';
    
    echo '<select class="pagesize select-styled right">';
    echo '<option value="20"> Show by 20 events</option>';
    echo '<option value="50"> Show by 50 events</option>';
    echo '<option value="100"> Show by 100 events</option>';
    echo '<option value="500"> Show by 500 events</option>';
    echo '<option value="all"> Show all Events</option>';
    echo '</select>';
    echo '<a href="../mods/advancedReports" data-toggle="modal" class="advanced-reports-button" data-target="#advanced-reports" href="#" id="elm-advanced-reports">Advanced Reports</a>';
                    
    echo '</form>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

?>

<!-- Modal for Phrase viewer -->

<script>
    $('#event-phrases').on('show.bs.modal', function(e) {
        $(this).find('.event-phrase-viewer').attr('href', $(e.relatedTarget).data('href'));
    });
    
    $('#event-phrases').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
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

<!-- Modal for Fraud Metrics -->

<script>
    $(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
    });

    $('#endpoint-metrics').on('show.bs.modal', function(e){
        $(this).find('.endpoint-metrics-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for Advanced Reports -->

<script>
    $('#advanced-reports').on('show.bs.modal', function(e){
        $(this).find('.advanced-reports-button').attr('href', $(e.relatedTarget).data('href'));
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
$("#includedTopMenu").load("../helpers/topMenu.php?or=events", function(){

    var timer, delay = 500;

    $('#search-box').bind('keydown', function(e) {
        var _this = $(this);
        clearTimeout(timer);
        timer = setTimeout(function() {
            startSpinner();
        }, delay);
    });

    /* Single events */

    $("#eventsTableSingle")
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
            1:
            {
                sorter: "shortDate", dateFormat: "yyymmdd"
            },
            3:
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
            6:
            {
                sorter: false
            },
            7:
            {
                sorter: false
            },
        }
    })

    .tablesorterPager({
        container: $(".pagerSingle"),
        ajaxUrl : 'helpers/eventsProcessing.php?page={page+1}&size={size}&{filterList:filter}&{sortList:col}&totalSystemWords=<?php echo $totalSystemWords; ?>&view=<?php echo $endpointDECSQL; ?>&totalEvents=<?php echo $totalEventsSum; ?>',
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
            $('td:nth-child(2)').addClass("timestamptd");
            $('td:nth-child(3)').addClass("eventtypetd");
            $('td:nth-child(4)').addClass("windowtitletd");
            $('td:nth-child(5)').addClass("metricstd");
            $('td:nth-child(6)').addClass("phrasetypedtd");
            $('td:nth-child(7)').addClass("falsetd");

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

    /* All events */

    $("#eventsTableAll")
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
            1:
            {
                sorter: "shortDate", dateFormat: "yyymmdd"
            },
            4:
            {
                sorter: false
            },
            5:
            {
                sorter: false
            },
            6:
            {
                sorter: false
            },
            7:
            {
                sorter: false
            }
        }
    })

    .tablesorterPager({
        container: $(".pagerAll"),
        ajaxUrl : 'helpers/eventsProcessing.php?page={page+1}&size={size}&{filterList:filter}&{sortList:col}&totalSystemWords=<?php echo $totalSystemWords; ?>&view=<?php echo $endpointDECSQL; ?>&totalEvents=<?php echo $totalEventsSum; ?>',
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

            $('td:nth-child(1)').addClass("detailstd-all");
            $('td:nth-child(2)').addClass("timestamptd-all");
            $('td:nth-child(3)').addClass("eventtypetd-all");
            $('td:nth-child(4)').addClass("endpointtd-all");
            $('td:nth-child(5)').addClass("windowtitletd-all");
            $('td:nth-child(6)').addClass("metricstd-all");
            $('td:nth-child(7)').addClass("phrasetypedtd-all");
            $('td:nth-child(8)').addClass("falsetd-all");

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