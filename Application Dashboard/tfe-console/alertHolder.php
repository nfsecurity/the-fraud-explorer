<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for paint agent data table
 */

session_start();

require 'vendor/autoload.php';
include "inc/global-vars.php";
include "inc/open-db-connection.php";
include "inc/agent_methods.php";
include "inc/check_perm.php";
include "inc/elasticsearch.php";

if(empty($_SESSION['connected']))
{
 	header ("Location: ".$serverURL);
 	exit;
}

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

function searchJsonFT($jsonFT, $searchValue)
{
	$fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');

	foreach($fraudTriangleTerms as $term)
        {	
        	foreach($jsonFT->dictionary->$term as $keyName => $value)
        	{
			if(strcmp($value, $searchValue) == 0) return $keyName;
		}
	}
}

/* Main Table */

echo '<table id="agentDataTable" class="tablesorter">';
echo '<thead><tr><th class="selectth"><img src="images/selection.svg" style="vertical-align: middle;"></th><th class="timestampth">EVENT TIMESTAMP</th><th class="alerttypeth">ALERT TYPE</th>
<th class="windowtitleth">WINDOW TITLE</th><th class="phrasetypedth">PHRASE</th><th class="phrasedictionaryth">PHRASE IN DICTIONARY</th><th class="deleteth">DELETE</th></tr>
</thead><tbody>';

$wordCounter = 0;

foreach ($agentData['hits']['hits'] as $result)
{
	echo '<tr>';

	/* Checking */

        $date = $result['_source']['sourceTimestamp'];
        $date = substr($date, 0, strpos($date, "."));
	$date = str_replace("T", " ", $date);

        echo '<td class="selecttd">';
        echo '<div class="checkboxRow"><input type="checkbox" value="" id="'.$date.'" name=""/><label for="'.$date.'"></label></div>';
        echo '</td>';

	/* Timestamp */

	echo '<td class="timestamptd">';
	echo '<span class="fa fa-clock-o">&nbsp;&nbsp;</span>'.$date;
	echo '</td>';

	/* AlertType */
        
        echo '<td class="alerttypetd">';
        echo '<span class="fa fa-tags">&nbsp;&nbsp;</span>'.ucfirst($result['_source']['alertType']);
        echo '</td>';

	/* Window title */

	if (isset($result['_source']['windowTitle']))
        {
                $windowTitle = $result['_source']['windowTitle'];

                echo '<td class="windowtitletd" title="'.$windowTitle.'">';
                echo '<span class="fa fa-list-alt">&nbsp;&nbsp;</span>'.$windowTitle;
                echo '</td>';
        }
        else
        {
                echo '<td class="windowtitletd" title="Unavailable window title">';
                echo '<span class="fa fa-list-alt">&nbsp;&nbsp;</span>Unavailable window title';
                echo '</td>';
        }

	/* Phrase typed */

	if (isset($result['_source']['wordTyped']))
	{
		$wordTyped = $result['_source']['wordTyped'];

		echo '<td class="phrasetypedtd" title="'.$wordTyped.'">';
	        echo '<span class="fa fa-pencil font-icon-green">&nbsp;&nbsp;</span>'.$wordTyped;
		echo '</td>';
	}
	else
	{
		echo '<td class="phrasetypedtd" title="Unavailable">';
	        echo '<span class="fa fa-pencil font-icon-green">&nbsp;&nbsp;</span>Unavailable';
		echo '</td>';
	}

	/* Regular expression dictionary */

	$searchValue = "/".$result['_source']['phraseMatch']."/";
	$searchResult = searchJsonFT($jsonFT, $searchValue);
	$regExpression = htmlentities($result['_source']['phraseMatch']);	

	echo '<td class="phrasedictionarytd" title="'.$regExpression.'">';
        echo '<span class="fa fa-font font-icon-gray">&nbsp;&nbsp;</span>'.$searchResult;
        echo '</td>';

	/* Delete row */

	echo '<td class="deletetd"><a data-href="deleteDoc?regid='.$result['_id'].'&agent='.$agent_enc.'&index=alerter" data-toggle="modal" data-target="#delete-reg" href="#">';
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
