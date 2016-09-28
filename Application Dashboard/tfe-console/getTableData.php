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
$ESindex = $configFile['es_words_index'];
$agent_dec = base64_decode(base64_decode($_SESSION['agentID']));
$matchesDataAgent = getAgentIdData($agent_dec, $ESindex, "TextEvent");
$agentData = json_decode(json_encode($matchesDataAgent),true);

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
echo '</style>';

/* Main Table */

echo '<table id="agentDataTable" class="tablesorter">';
echo '<thead><tr><th class="timestampth">EVENT TIMESTAMP</th><th class="applicationtitleth">APPLICATION NAME</th><th class="ipaddressth">IP ADDRESS</th>
<th class="computernameth">COMPUTER NAME</th><th class="usernameth">USERNAME</th><th class="typedwordth">TYPED WORD</th></tr>
</thead><tbody>';

$wordCounter = 0;

foreach ($agentData['hits']['hits'] as $result)
{
	echo '<tr>';

	/* Timestamp */

	echo '<td class="timestamptd">';
	echo '<span class="fa fa-clock-o font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['sourceTimestamp'];
	echo '</td>';

	/* Application Name */
        
        echo '<td class="applicationtitletd">';
        echo '<span class="fa fa-windows font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['applicationTitle'];
        echo '</td>';

	/* IP Address */

        echo '<td class="ipaddresstd">';
        echo '<span class="fa fa-map-marker font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['hostPrivateIP'];
        echo '</td>';	

	/* Computer Name */

        echo '<td class="computernametd">';
        echo '<span class="fa fa-laptop font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['computerName'];
        echo '</td>';

	/* User Name */

        echo '<td class="usernametd">';
        echo '<span class="fa fa-user font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['userName'];
        echo '</td>';

	/* Typed word */
        
        echo '<td class="typedwordtd">';
        echo '<span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.$result['_source']['typedWord'];
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
			There are <?php echo $wordCounter; ?> words typed by <span class="fa fa-user font-icon-color">&nbsp;&nbsp;</span><?php echo $agent_dec ?> stored in database.
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
                                4: 
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
