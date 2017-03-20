<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for paint agent data table
 */

include "lbs/login/session.php";

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

/* Elasticsearch querys for fraud triangle counts and score */

$client = Elasticsearch\ClientBuilder::create()->build();
$configFile = parse_ini_file("config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];
$agent_dec = base64_decode(base64_decode($_SESSION['agentIDh']));
$agent_enc = $_SESSION['agentIDh'];
$matchesDataAgent = getAgentIdData($agent_dec, $ESAlerterIndex, "AlertEvent");
$agentData = json_decode(json_encode($matchesDataAgent),true);

echo '<style>';
echo '.font-icon-gray { color: #B4BCC2; }';
echo '.font-icon-green { color: #1E9141; }';
echo '</style>';

/* JSON dictionary load */

$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']));

function searchJsonFT($jsonFT, $searchValue, $agent_dec)
{
	$rulesetquery = mysql_query(sprintf("SELECT ruleset FROM t_agents WHERE agent='%s'",$agent_dec));
	$ruleset = mysql_fetch_array($rulesetquery);
	$fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');

	foreach($fraudTriangleTerms as $term)
        {	
        	foreach($jsonFT->dictionary->$ruleset[0]->$term as $keyName => $value)
        	{
			if(strcmp($value, $searchValue) == 0) return $keyName;
		}
	}
}

/* Main Table */

echo '<table id="agentDataTable" class="tablesorter">';
echo '<thead><tr><th class="selectth"><span class="fa fa-list fa-lg">&nbsp;&nbsp;</span></th><th class="timestampth">EVENT TIMESTAMP</th><th class="alerttypeth">ALERT TYPE</th>
<th class="windowtitleth">WINDOW TITLE</th><th class="phrasetypedth">PHRASE</th><th class="phrasedictionaryth">PHRASE IN DICTIONARY</th><th class="deleteth">DELETE</th></tr>
</thead><tbody>';

$wordCounter = 0;

foreach ($agentData['hits']['hits'] as $result)
{
	echo '<tr>';

	/* Checking */

        $date = $result['_source']['eventTime'];
        $date = substr($date, 0, strpos($date, ","));

        echo '<td class="selecttd">';
        echo '<div class="checkboxRow"><input type="checkbox" value="" id="'.$date.'" name=""/><label for="'.$date.'"></label></div>';
        echo '</td>';

	/* Timestamp */

	echo '<td class="timestamptd">';
	echo '<span class="fa fa-clock-o">&nbsp;&nbsp;</span>'.$date;
	echo '</td>';

	/* AlertType */
       
	$windowTitle = htmlentities($result['_source']['windowTitle']);
	$wordTyped = $result['_source']['wordTyped'];
	$searchValue = "/".$result['_source']['phraseMatch']."/";
        $searchResult = searchJsonFT($jsonFT, $searchValue, $agent_dec);
        $regExpression = htmlentities($result['_source']['phraseMatch']);
	$stringHistory = htmlentities($result['_source']['stringHistory']);

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

        echo '<td class="alerttypetd">';
        echo '<span class="fa fa-tags">&nbsp;&nbsp;</span><div class="tooltip-custom tooltip-father" 
	title="<div class=tooltip-container><div class=tooltip-title>Alert Consolidation Data</div><div class=tooltip-row><div class=tooltip-item>Window Title</div><div class=tooltip-value>'.$windowTitle.'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Alert time source</div><div class=tooltip-value>'.$date.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Phrase or word typed</div><div class=tooltip-value>'.$wordTyped.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Phrase or word in Dictionary</div><div class=tooltip-value>'.$searchResult.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Phrase zoom (after, before)</div><div class=tooltip-value>'.$phraseZoom.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Regular expression matching</div><div class=tooltip-value>'.$regExpression.'</div></div>
        ">'.ucfirst($result['_source']['alertType']).'</div>';
        echo '</td>';

	/* Window title */

        echo '<td class="windowtitletd">';
        echo '<span class="fa fa-list-alt">&nbsp;&nbsp;</span>'.$windowTitle;
        echo '</td>';

	/* Phrase typed */

	echo '<td class="phrasetypedtd">';
	echo '<span class="fa fa-pencil font-icon-green">&nbsp;&nbsp;</span>'.$wordTyped;
	echo '</td>';

	/* Regular expression dictionary */

	echo '<td class="phrasedictionarytd">';
        echo '<span class="fa fa-font font-icon-gray">&nbsp;&nbsp;</span>'.$searchResult;
        echo '</td>';

	/* Delete row */

	echo '<td class="deletetd"><a data-href="deleteDoc?regid='.$result['_id'].'&agent='.$agent_enc.'&index='.$result['_index'].'&type='.$result['_type'].'" data-toggle="modal" data-target="#delete-reg" href="#">';
	echo '<img src="images/delete-button-analytics.svg" onmouseover="this.src=\'images/delete-button-analytics-mo.svg\'" onmouseout="this.src=\'images/delete-button-analytics.svg\'" alt="" title=""/></a></td>';

	echo '</tr>';

	$wordCounter++;
} 

echo '</tbody></table>';

?>

<!-- Pager -->

<div id="pager" class="pager">

    <div class="pager-top-layout">
	<div class="pager-inside">
		<div class="pager-inside-agent">
			There are <?php echo $wordCounter; ?> regular expressions matched by <span class="fa fa-user font-icon-color">&nbsp;&nbsp;</span><?php echo $agent_dec ?> stored in database.
		</div>

		<div class="pager-inside-pager">
		<form>
    			<span class="fa fa-fast-backward fa-lg first"></span>
    			<span class="fa fa-arrow-circle-o-left fa-lg prev"></span>
    			<span class="pagedisplay"></span>
    			<span class="fa fa-arrow-circle-o-right fa-lg next"></span>
    			<span class="fa fa-fast-forward fa-lg last"></span>&nbsp;
    			<select class="pagesize select-styled">
      				<option value="50"> by 50 rows</option>
      				<option value="100"> by 100 rows</option>
      				<option value="500"> by 500 rows</option>
      				<option value="1000"> by 1000 rows</option>
      				<option value="all"> All Rows</option>
    			</select>
  		</form>
		</div>
	</div>
    </div>

    <div class="pager-layout">
        <div class="pager-inside">
                <div class="pager-inside-agent">
                        There are <?php echo $wordCounter; ?> regular expressions matched by <span class="fa fa-user">&nbsp;&nbsp;</span><?php echo $agent_dec ?> stored in database.
                </div>

                <div class="pager-inside-pager">
                <form>
                        <span class="fa fa-fast-backward fa-lg first"></span>
                        <span class="fa fa-arrow-circle-o-left fa-lg prev"></span>
                        <span class="pagedisplay"></span>
                        <span class="fa fa-arrow-circle-o-right fa-lg next"></span>
                        <span class="fa fa-fast-forward fa-lg last"></span>&nbsp;
                        <select class="pagesize select-styled">
                                <option value="50"> by 50 rows</option>
                                <option value="100"> by 100 rows</option>
                                <option value="500"> by 500 rows</option>
                                <option value="1000"> by 1000 rows</option>
                                <option value="all"> All Rows</option>
                        </select>
                </form>
                </div>
        </div>
    </div>

</div>

<!-- Modal for delete dialog -->

<script>
        $('#delete-reg').on('show.bs.modal', function(e) 
        {
                $(this).find('.delete-reg-button').attr('href', $(e.relatedTarget).data('href'));
        }); 
</script>

<!-- Table sorter -->

<script>

$(function()
{
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
	$(document).ready(function()
	{
		$('#search-box').keyup(function()
		{
			searchTable($(this).val());
		});
	});
		
	function searchTable(inputVal)
	{
		var table = $('#agentDataTable');

		table.find('tr').each(function(index, row)
		{
			var allCells = $(row).find('td');
					
			if(allCells.length > 0)
			{
				var found = false;
				
				allCells.each(function(index, td)
				{
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
        $(document).ready(function()
        {
                $('.tooltip-custom').tooltipster(
                {
                        theme: 'tooltipster-light',
                        contentAsHTML: true,
			side: 'right'
                });
        });
</script>
