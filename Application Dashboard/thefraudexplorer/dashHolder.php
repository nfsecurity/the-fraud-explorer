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
 * Description: Code for paint main table
 */

include "lbs/login/session.php";

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

$_SESSION['id_uniq_command']=null;

/* Order the dashboard agent list */

$order = mysql_query("SELECT agent, heartbeat, now() FROM t_agents", $connection);

if ($row = mysql_fetch_array($order))
{
	do
	{
		if(isConnected($row["heartbeat"], $row[2])) 
                {
			$sendquery="UPDATE t_agents SET status='active' where agent='" .$row["agent"]. "'"; 
			queryOrDie($sendquery);
                }
                else 
                { 
                        $sendquery="UPDATE t_agents SET status='inactive' where agent='" .$row["agent"]. "'";
                	queryOrDie($sendquery);
		}
	}
	while ($row = mysql_fetch_array($order));
}

echo '<style>';
echo '.font-icon-color { color: #B4BCC2; }';
echo '.font-icon-color-green { color: #1E9141; }';
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
	$urlWords="http://localhost:9200/logstash-thefraudexplorer-text-*/_count";
	$urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
	$urlSize="http://localhost:9200/_all/_stats";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$urlWords);
	$resultWords=curl_exec($ch);
	curl_close($ch);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$urlAlerts);
	$resultAlerts=curl_exec($ch);
	curl_close($ch);
}
else
{
	$urlWords='http://localhost:9200/logstash-thefraudexplorer-text-*/_count';
	$urlAlerts="http://localhost:9200/logstash-alerter-*/_count";
        $urlSize="http://localhost:9200/_all/_stats";
	
	$params = '{ "query": { "term" : { "userDomain" : "'.$session->domain.'" } } }';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL,$urlWords);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	$resultWords=curl_exec($ch);
	curl_close($ch);

	$params = '{ "query": { "term" : { "userDomain" : "'.$session->domain.'" } } }';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
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

/* Function to handle data insights */

function agentInsights($gender, $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName)
{
	echo '<img src="images/'.$gender.'-agent.gif" class="gender-image">&nbsp;&nbsp;<a class="tooltip-custom" href=alertData?agent='.$agent_enc.' 
        title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div><div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Alerts by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Alerts by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Alerts by rationalization</div><div class=tooltip-value>'.$countRationalization.'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Fraud score</div><div class=tooltip-value>'.round($score, 1).'</div></div>
        <div class=tooltip-row><div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div>
        </div>">' . $agentName . '</a></td>';
}

function agentDetails($agent_dec, $agentDomain, $osVersion, $status, $ipaddress)
{
	echo '<a class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Agent details</div><div class=tooltip-row><div class=tooltip-item>Identification</div><div class=tooltip-value-lefta>'.$agent_dec.'</div></div> 
	<div class=tooltip-row><div class=tooltip-item>Corporate domain</div><div class=tooltip-value-lefta>'.$agentDomain.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Operating system</div><div class=tooltip-value-lefta>'.$osVersion.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>Connection status</div><div class=tooltip-value-lefta>'.$status.'</div></div>
	<div class=tooltip-row><div class=tooltip-item>IP Address</div><div class=tooltip-value-lefta>'.$ipaddress.'</div></div>
	</div>"><span class="fa fa-building-o fa-lg font-icon-color">&nbsp;&nbsp;</span></a>';
}

/* Show main table and telemetry with the agent list */

if($session->domain == "all") $result_a = mysql_query("SELECT agent, heartbeat, now(), system, version, domain, ipaddress, status, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");
else $result_a = mysql_query("SELECT agent, heartbeat, now(), system, version, domain, ipaddress, status, name, ruleset, gender, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain='".$session->domain."' ORDER BY FIELD(status, 'active','inactive'), agent ASC");


/* Main Table */

echo '<table id="tblData" class="tablesorter">';
echo '<thead><tr><th class="detailsth"><span class="fa fa-list fa-lg">&nbsp;&nbsp;</span></th><th class="totalwordsth"></th><th class="agentth">USERS UNDER ANALYTICS</th><th class="compth">RULE SET</th>
<th class="verth">VER</th><th class="stateth">STT</th><th class="lastth">LAST</th><th class="countpth">P</th><th class="countoth">O</th><th class="countrth">R</th><th class="countcth">L</th>
<th class="scoreth">SCORE</th><th class="specialth">CMD</th><th class="specialth">DEL</th><th class="specialth">SET</th></tr>
</thead><tbody>';

if ($row_a = mysql_fetch_array($result_a))
{
 	do 
 	{
		echo '<tr>';

  		$agent_enc=base64_encode(base64_encode($row_a["agent"]));

		/* User Domain */

  		echo '<td class="detailstd">';
		agentDetails($row_a['agent'], $row_a['domain'], getTextSist($row_a["system"]), $row_a['status'], $row_a['ipaddress']);
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

		$agentName = substr($row_a["agent"], 0, strpos($row_a["agent"], "_")) . "@" .$row_a["domain"];		

		if ($row_a["name"] == NULL) 
		{
			echo '<td class="agenttd">';
			if ($row_a["gender"] == "male") agentInsights("male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
			else if ($row_a["gender"] == "female") agentInsights("female", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
			else agentInsights("male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
		}
		else
		{
			echo '<td class="agenttd">';
			if ($row_a["gender"] == "male") agentInsights("male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
			else if ($row_a["gender"] == "female") agentInsights("female", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
			else echo agentInsights("male", $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName);
		}

		/* Company, department or group */

		if ($row_a["ruleset"] == NULL || $row_a["ruleset"] == "NYET") echo '<td class="comptd">GENERIC</td>';
                else echo '<td class="comptd">' . $row_a["ruleset"] . "</td>";

		/* Agent software version */

 	 	echo '<td class="vertd"><span class="fa fa-codepen font-icon-color">&nbsp;&nbsp;</span>' .$row_a["version"] .'</td>';

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

  		echo '<td class="lasttd">'.str_replace(array("-"),array("/"),$row_a["heartbeat"]).'&nbsp;</td>';
  		$result_b=mysql_query("SELECT command FROM t_".str_replace(array("."),array("_"),$row_a["agent"])." order by date desc limit 1");
  		$row_b = mysql_fetch_array($result_b);

		echo '<div id="fraudCounterHolder"></div>';

		/* Fraud triangle counts and score */
		
		$scoreQuery = mysql_query("SELECT * FROM t_config");
                $scoreResult = mysql_fetch_array($scoreQuery);

		$level = "low";
                if ($score >= $scoreResult['score_ts_low_from'] && $score <= $scoreResult['score_ts_low_to']) $level="low";
                if ($score >= $scoreResult['score_ts_medium_from'] && $score <= $scoreResult['score_ts_medium_to']) $level="medium";
    		if ($score >= $scoreResult['score_ts_high_from'] && $score <= $scoreResult['score_ts_high_to']) $level="high";
	        if ($score >= $scoreResult['score_ts_critic_from']) $level="critic";
	
		echo '<td class="countptd"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>'.$countPressure.'</td>';
		echo '<td class="countotd"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>'.$countOpportunity.'</td>';
		echo '<td class="countrtd"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>'.$countRationalization.'</td>';
		echo '<td class="countctd">'.$level.'</td>';
		echo '<td class="scoretd"><a href=alertData?agent='.$agent_enc.'>'.round($score, 1).'</a></td>';  

		/* Agent selection for command retrieval */

		if(isConnected($row_a["heartbeat"], $row_a[2]))
  		{
			if(isset($_SESSION['agentchecked']))
                        {
				if($_SESSION['agentchecked'] == $row_a["agent"]) echo '<td class="specialtd"><a href="dashBoard?agent='.$agent_enc.'"><img src="images/cmd-ok.svg" onmouseover="this.src=\'images/cmd-mo-ok.svg\'" onmouseout="this.src=\'images/cmd-ok.svg\'" alt="" title="" /></a></td>';
				else echo '<td class="specialtd"><a href="dashBoard?agent='.$agent_enc.'"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="" title="" /></a></td>';  
			}
			else echo '<td class="specialtd"><a href="dashBoard?agent='.$agent_enc.'"><img src="images/cmd.svg" onmouseover="this.src=\'images/cmd-mo.svg\'" onmouseout="this.src=\'images/cmd.svg\'" alt="" title="" /></a></td>';
		}
  		else
  		{	if(isset($_SESSION['agentchecked']))
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
                <div class="pager-inside-agent">

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
                                <option value="20"> by 20 rows</option>
                                <option value="50"> by 50 rows</option>
                                <option value="100"> by 100 rows</option>
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
	$('#confirm-delete').on('show.bs.modal', function(e) 
        {
        	$(this).find('.delete').attr('href', $(e.relatedTarget).data('href'));
        }); 
</script>

<!-- Modal for setup dialog -->

<script>
        $('#confirm-setup').on('show.bs.modal', function(e) 
	{
        	$(this).find('.setup').attr('href', $(e.relatedTarget).data('href'));
        });
</script>

<!-- Modal for main config -->

<script>
        $('#confirm-config').on('show.bs.modal', function(e) 
        {
                $(this).find('.config').attr('href', $(e.relatedTarget).data('href'));
        });
</script>

<!-- Table sorting -->

<script>
	$(document).ready(function() 
    	{ 
        	$("#tblData").tablesorter({ 
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
		var table = $('#tblData');

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

