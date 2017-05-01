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
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for paint agent data table
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
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";
include "lbs/elasticsearch.php";
include "lbs/cryptography.php";

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];
$agent_decES = base64_decode(base64_decode($_SESSION['agentIDh']))."*";
$agent_decSQ = base64_decode(base64_decode($_SESSION['agentIDh']));
$agent_enc = $_SESSION['agentIDh'];
$matchesDataAgent = getAgentIdData($agent_decES, $ESAlerterIndex, "AlertEvent");
$agentData = json_decode(json_encode($matchesDataAgent),true);

echo '<style>';
echo '.font-icon-gray { color: #B4BCC2; }';
echo '.font-icon-green { color: #1E9141; }';
echo '</style>';

/* SQL Queries */

$queryRuleset = "SELECT ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";
$queryDomain = "SELECT domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents WHERE agent='%s' GROUP BY agent";

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

/* Endpoint domain */

$domainQuery = mysql_query(sprintf($queryDomain, $agent_decSQ));
$domain = mysql_fetch_array($domainQuery);

function searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset)
{
    $rulesetquery = mysql_query(sprintf($queryRuleset, $agent_decSQ));
    $ruleset = mysql_fetch_array($rulesetquery);
    $baselineRuleset = "BASELINE";
    $fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');

    foreach($fraudTriangleTerms as $term)
    {
        foreach($jsonFT->dictionary->$ruleset[0]->$term as $keyName => $value) if(strcmp($value, $searchValue) == 0) return $keyName;
        foreach($jsonFT->dictionary->$baselineRuleset->$term as $keyName => $value) if(strcmp($value, $searchValue) == 0) return $keyName;
    }
}

function alertDetails($date, $wordTyped, $windowTitle, $searchResult, $phraseZoom, $regExpression, $result)
{
    echo '<a class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Alert Consolidation Data</div><div class=tooltip-row><div class=tooltip-item>Window Title</div><div class=tooltip-value>'.$windowTitle.'</div></div><div class=tooltip-row><div class=tooltip-item>Alert time source</div><div class=tooltip-value>'.$date.'</div></div><div class=tooltip-row><div class=tooltip-item>Phrase or word typed</div><div class=tooltip-value>'.$wordTyped.'</div></div><div class=tooltip-row><div class=tooltip-item>Phrase or word in Dictionary</div><div class=tooltip-value>'.$searchResult.'</div></div><div class=tooltip-row><div class=tooltip-item>Phrase zoom (after, before)</div><div class=tooltip-value>'.$phraseZoom.'</div></div><div class=tooltip-row><div class=tooltip-item>Regular expression matching</div><div class=tooltip-value>'.$regExpression.'</div></div>"><span class="fa fa-building-o fa-lg font-icon-gray">&nbsp;&nbsp;</span></a>';
}

/* Main Table */

echo '<table id="agentDataTable" class="tablesorter">';
echo '<thead><tr><th class="detailsth"><span class="fa fa-list fa-lg">&nbsp;&nbsp;</span></th><th class="timestampth">EVENT TIMESTAMP</th><th class="alerttypeth">ALERT TYPE</th>
<th class="windowtitleth">APPLICATION CONTEXT</th><th class="phrasetypedth">PHRASE TYPED</th><th class="phrasedictionaryth">PHRASE IN DICTIONARY</th><th class="falseth">MARK</th></tr>
</thead><tbody>';

$wordCounter = 0;

foreach ($agentData['hits']['hits'] as $result)
{
    echo '<tr>';

    /* Alert Details */

    $date = $result['_source']['eventTime'];
    $date = substr($date, 0, strpos($date, ","));
    
    /* AlertType */

    $windowTitle = decRijndael(htmlentities($result['_source']['windowTitle']));
    $wordTyped = decRijndael($result['_source']['wordTyped']);
    $searchValue = "/".$result['_source']['phraseMatch']."/";
    $searchResult = searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset);
    $regExpression = htmlentities($result['_source']['phraseMatch']);
    $stringHistory = decRijndael(htmlentities($result['_source']['stringHistory']));
    
    /* Phrase zoom */

    $pieces = explode(" ", $stringHistory);

    foreach($pieces as $key => $value)
    {
        if($pieces[$key] == $wordTyped)
        {
            if (array_key_exists($key-1, $pieces))
            {
                if (array_key_exists($key-2, $pieces)) $leftWords = $pieces[$key-2]." ".$pieces[$key-1];
                else $leftWords = $pieces[$key-1];
            }
            else $leftWords = "";

            if (array_key_exists($key+1, $pieces))
            {
                if (array_key_exists($key+2, $pieces)) $rightWords = $pieces[$key+1]." ".$pieces[$key+2];
                else $rightWords = $pieces[$key+1];
            }
            else $rightWords = "";

            $phraseZoom = $leftWords." ".$wordTyped." ".$rightWords;
            break;
        }
    }

    echo '<td class="detailstd">';
    alertDetails($date, $wordTyped, $windowTitle, $searchResult, $phraseZoom, $regExpression, $result);
    echo '</td>';

    /* Timestamp */

    echo '<td class="timestamptd">';
    echo '<span class="fa fa-clock-o font-icon-gray">&nbsp;&nbsp;</span>'.$date;
    echo '</td>';

    echo '<td class="alerttypetd">';
    echo '<span class="fa fa-tags font-icon-gray">&nbsp;&nbsp;</span>'.ucfirst($result['_source']['alertType']);
    echo '</td>';

    /* Window title */

    echo '<td class="windowtitletd">';
    echo '<span class="fa fa-list-alt font-icon-gray">&nbsp;&nbsp;</span>'.$windowTitle;
    echo '</td>';

    /* Phrase typed */

    echo '<td class="phrasetypedtd">';
    echo '<span class="fa fa-pencil font-icon-green">&nbsp;&nbsp;</span>'.$wordTyped;
    echo '</td>';

    /* Regular expression dictionary */

    echo '<td class="phrasedictionarytd">';
    echo '<span class="fa fa-font font-icon-gray">&nbsp;&nbsp;</span>'.$searchResult;
    echo '</td>';

    /* Mark false positive */
    
    $index = $result['_index'];
    $type = $result['_type'];
    $regid = $result['_id'];
    
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
    
    echo '<td class="falsetd"><a class="false-positive" data-href="toggleAlertMark?regid='.$result['_id'].'&agent='.$agent_enc.'&index='.$result['_index'].'&type='.$result['_type'].'" data-toggle="modal" data-target="#false-positive" href="#">';
    
    if ($falsePositiveValue == "0") echo '<span class="fa fa-check-square fa-lg font-icon-green"></span></a></td>';
    else echo '<span class="fa fa-check-square fa-lg font-icon-gray"></span></a></td>';

    echo '</tr>';

    $wordCounter++;
}

echo '</tbody></table>';

?>

<!-- Pager -->

<div id="pager" class="pager">
    <div class="pager-layout">
        <div class="pager-inside">
            <div class="pager-inside-agent">
                There are <?php echo $wordCounter; ?> regular expressions matched by <span class="fa fa-user">&nbsp;&nbsp;</span><?php echo $agent_decSQ.'@'.$domain[0]; ?> stored in database
            </div>

            <div class="pager-inside-pager">
                <form>
                    <span class="fa fa-fast-backward fa-lg first"></span>
                    <span class="fa fa-arrow-circle-o-left fa-lg prev"></span>
                    <span class="pagedisplay"></span>
                    <span class="fa fa-arrow-circle-o-right fa-lg next"></span>
                    <span class="fa fa-fast-forward fa-lg last"></span>&nbsp;
                    <select class="pagesize select-styled">
                        <option value="20"> by 20 alerts</option>
                        <option value="50"> by 50 alerts</option>
                        <option value="100"> by 100 alerts</option>
                        <option value="500"> by 500 alerts</option>
                        <option value="all"> All Alerts</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for delete dialog -->

<script>
    $('#false-positive').on('show.bs.modal', function(e){
        $(this).find('.false-positive-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Table sorter -->

<script>
    $(function(){
        $("#agentDataTable").tablesorter({
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
    });
</script>

<!-- Table search -->

<script type="text/javascript">
    $(document).ready(function(){
        $('#search-box').keyup(function(){
            searchTable($(this).val());
        });
    });

    function searchTable(inputVal)
    {
        var table = $('#agentDataTable');
        table.find('tr').each(function(index, row){
            var allCells = $(row).find('td');

            if(allCells.length > 0)
            {
                var found = false;

                allCells.each(function(index, td){
                    var regExp = new RegExp(inputVal, 'i');

                    if(regExp.test($(td).text()))
                    {
                        found = true;
                        return false;
                    }
                });

                if(found == true)$(row).show();else $(row).hide();
            }
        });
    }
</script>

<!-- Tooltipster -->

<script>
    $(document).ready(function(){
        $('.tooltip-custom').tooltipster(
            {
                theme: 'tooltipster-light',
                contentAsHTML: true,
                side: 'right'
            });
    });
</script>