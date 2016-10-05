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
 * Description: Code for paint main table
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

$_SESSION['id_uniq_command']=null;

/* Order the dashboard agent list */

$order = mysql_query("SELECT agent,heartbeat, now() FROM t_agents");

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

/* Show main table and telemetry with the agent list */

if ($userConnected != 'admin') $result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents WHERE owner='".$userScope."' ORDER BY FIELD(status, 'active','inactive'), agent ASC");
else $result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

/* Main Table */

echo '<table id="tblData" class="tablesorter">';
echo '<thead><tr><th class="selectth"><img src="images/selection.svg" style="vertical-align: middle;"></th><th class="osth">OS</th><th class="agentth">PEOPLE REGISTERED</th><th class="compth">GROUP</th>
<th class="verth">VER</th><th class="stateth">STT</th><th class="lastth">LAST</th><th class="countpth">P</th><th class="countoth">O</th><th class="countrth">R</th><th class="countcth">L</th>
<th class="scoreth">SCORE</th><th class="specialth">CMD</th><th class="specialth">DEL</th><th class="specialth">SET</th></tr>
</thead><tbody>';

if ($row_a = mysql_fetch_array($result_a))
{
 	do 
 	{
		echo '<tr>';

		/* Checking */

		echo '<td class="selecttd">';
		echo '<div class="checkboxAgent"><input type="checkbox" value="" id="'.$row_a["agent"].'" name=""/><label for="'.$row_a["agent"].'"></label></div>';
		echo '</td>';

  		$agent_enc=base64_encode(base64_encode($row_a["agent"]));

		/* Operating system */

  		echo '<td class="ostd"><span class="fa fa-windows fa-lg font-icon-color">&nbsp;&nbsp;</span>'. getTextSist($row_a["system"]) .'</td>';

		/* Gender identification */

		if ($row_a["name"] == NULL) 
		{
			echo '<td class="agenttd">';
			if ($row_a["gender"] == "male") echo '<img src="images/male-agent.gif" class="gender-image">&nbsp;&nbsp;<a href=alertData?agent='.$agent_enc.'>' . $row_a["agent"] . '</a></td>';
			else if ($row_a["gender"] == "female") echo '<img src="images/female-agent.gif" class="gender-image">&nbsp;&nbspe<a href=alertData?agent='.$agent_enc.'>' . $row_a["agent"] . '</a></td>';
			else echo '<img src="images/male-agent.gif" class="gender-image">&nbsp;&nbsp;<a href=alertData?agent='.$agent_enc.'>' . $row_a["agent"] . '</a></td>';
		}
		else
		{
			echo '<td class="agenttd">';
			if ($row_a["gender"] == "male") echo '<img src="images/male-agent.gif" class="gender-image">&nbsp;&nbsp;<a href=alertData?agent='.$agent_enc.'>' . $row_a["name"] . '</a></td>';
			else if ($row_a["gender"] == "female") echo '<img src="images/female-agent.gif" class="gender-image">&nbsp;&nbsp;<a href=alertData?agent='.$agent_enc.'>' . $row_a["name"] . '</a></td>';
			else echo '<img src="images/male-agent.gif" class="gender-image">&nbsp;&nbsp;<a href=alertData?agent='.$agent_enc.'>' . $row_a["name"] . '</a></td>';
		}

		/* Company, department or group */

		if ($row_a["owner"] == NULL) echo '<td class="comptd">NYET</td>';
                else echo '<td class="comptd">' . $row_a["owner"] . "</td>";

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
		
		$matchesRationalization = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                $matchesOpportunity = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                $matchesPressure = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);
                $matchesCustom = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['c'], $configFile['es_alerter_index']);

		if ($matchesRationalization['hits']['total'] != 0)
		{
			$GLOBALS['arrayPosition'] = 0;
			getArrayData($matchesRationalization, "matchNumber", 'numberOfRMatches');
        		$countRationalization = array_sum($GLOBALS['numberOfRMatches']);
		} 
		else $countRationalization = 0;

  	        if ($matchesOpportunity['hits']['total'] != 0) 
		{
			$GLOBALS['arrayPosition'] = 0;
			getArrayData($matchesOpportunity, "matchNumber", 'numberOfOMatches');
			$countOpportunity = array_sum($GLOBALS['numberOfOMatches']);
		}
		else $countOpportunity = 0; 

                if ($matchesPressure['hits']['total'] != 0) 
		{
			$GLOBALS['arrayPosition'] = 0;
			getArrayData($matchesPressure, "matchNumber", 'numberOfPMatches');
			$countPressure = array_sum($GLOBALS['numberOfPMatches']);
		}
		else $countPressure = 0;

		if ($matchesCustom['hits']['total'] != 0) 
		{
			$GLOBALS['arrayPosition'] = 0;
			getArrayData($matchesCustom, "matchNumber", 'numberOfCMatches');
			$countCustom = array_sum($GLOBALS['numberOfCMatches']);
		}
		else $countCustom = 0;
	
	 	$score=($countPressure+$countOpportunity+$countRationalization)/3;
                $level="low";
                if ($score >= 6 && $score <= 15) $level="medium";
                if ($score >= 15) $level="high";
	
		echo '<td class="countptd">'.$countPressure.'</td>';
		echo '<td class="countotd">'.$countOpportunity.'</td>';
		echo '<td class="countrtd">'.$countRationalization.'</td>';
		echo '<td class="countctd">'.$level.'</td>';
		echo '<td class="scoretd"><a href=alertData?agent='.$agent_enc.'>'.round($score, 1).'</a></td>';  

		unset($GLOBALS['numberOfRMatches']);
		unset($GLOBALS['numberOfOMatches']);
		unset($GLOBALS['numberOfPMatches']);
		unset($GLOBALS['numberOfCMatches']);

		/* Agent selection for command retrieval */

		if(isConnected($row_a["heartbeat"], $row_a[2]) && $userPermissions != "view")
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

  		echo '<td class="specialtd"><a data-href="deleteAgent?agent='.$agent_enc.'" data-toggle="modal" data-target="#confirm-delete" href="#"><img src="images/delete-button.svg" onmouseover="this.src=\'images/delete-button-mo.svg\'" onmouseout="this.src=\'images/delete-button.svg\'" alt="" title=""/></a></td>';	

		/* Agent setup */

		echo '<td class="specialtd"><a href="setupAgent?agent='.$agent_enc.'" data-toggle="modal" data-target="#confirm-setup" href="#"><img src="images/setup.svg" onmouseover="this.src=\'images/setup-mo.svg\'" onmouseout="this.src=\'images/setup.svg\'" alt="" title=""/></a></td>';

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
				$urlWords="http://localhost:9200/logstash-thefraudexplorer-text-*/_stats/docs";
				$urlAlerts="http://localhost:9200/logstash-alerter-*/_stats/docs";
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

				$ch = curl_init();
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_URL,$urlSize);
                                $resultSize=curl_exec($ch);
                                curl_close($ch);

				$resultWords = json_decode($resultWords, true);
				$resultAlerts = json_decode($resultAlerts, true);
				$resultSize = json_decode($resultSize, true);
				$dataSize = ($resultSize['_all']['primaries']['store']['size_in_bytes']/1024/1024);	

				echo 'There are <span class="fa fa-font font-icon-color">&nbsp;&nbsp;</span>'.number_format($resultWords['_all']['primaries']['docs']['count'], 0, ',', '.').' words collected and ';
				echo '<span class="fa fa-exclamation-triangle font-icon-color">&nbsp;&nbsp;</span>'.number_format($resultAlerts['_all']['primaries']['docs']['count'], 0, ',', '.').' fraud triangle alerts triggered, ';
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
				1: 
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
			sortList: [[11,1]]
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
