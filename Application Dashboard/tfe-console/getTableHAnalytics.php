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
$matchesDataAgent = getAgentIdData($agent_dec, $ESAlerterIndex, "AlertEvent");
$agentData = json_decode(json_encode($matchesDataAgent),true);

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
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
echo '<thead><tr><th class="timestampth">EVENT TIMESTAMP</th><th class="alerttypeth">ALERT TYPE</th>
<th class="phrasematchth">REGULAR EXPRESSION MATCHED</th><th class="phraseth">PHRASE MATCHED</th><th class="matchnumberth">MATCHES</th></tr>
</thead><tbody>';

$wordCounter = 0;

foreach ($agentData['hits']['hits'] as $result)
{
	echo '<tr>';

	/* Timestamp */

	echo '<td class="timestamptd">';
	echo '<span class="fa fa-clock-o font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['sourceTimestamp'];
	echo '</td>';

	/* AlertType */
        
        echo '<td class="alerttypetd">';
        echo '<span class="fa fa-exclamation-circle font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['alertType'];
        echo '</td>';

	/* Regular expression matched */

        echo '<td class="phrasematchtd">';
        echo '<span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['phraseMatch'];
        echo '</td>';

	/* Regular expression matched */

	$searchValue = "/".$result['_source']['phraseMatch']."/";
	
	echo '<td class="phrasetd">';
        echo '<span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.searchJsonFT($jsonFT, $searchValue);
        echo '</td>';

	/* Matches */

        echo '<td class="matchnumbertd">';
        echo '<span class="fa fa-star font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['matchNumber'];
        echo '</td>';

	echo '</tr>';

	$wordCounter++;
} 

echo '</tbody></table>';

?>

<!-- Pager -->

<div id="pager" class="pager pager-layout">
	<div class="pager-inside">
		<div class="pager-inside-agent">
			There are <?php echo $wordCounter; ?> regular expressions matched by <span class="fa fa-user font-icon-color">&nbsp;&nbsp;</span><?php echo $agent_dec ?> stored in database.
		</div>

		<div class="pager-inside-pager">
		<form>
    			<span class="fa fa-fast-backward fa-lg font-icon-color first"></span>
    			<span class="fa fa-arrow-circle-o-left fa-lg font-icon-color prev"></span>
    			<span class="pagedisplay"></span>
    			<span class="fa fa-arrow-circle-o-right fa-lg font-icon-color next"></span>
    			<span class="fa fa-fast-forward fa-lg font-icon-color last"></span>&nbsp;
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

<!-- Table sorter -->

<script>

$(function()
{
 $("#agentDataTable").tablesorter({
	headers: 
                        { 
                                3: 
                                {
                                        sorter: false
                                }
                        }
	})
 	.tablesorterPager({
		container: $("#pager"),
		size: 50
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
