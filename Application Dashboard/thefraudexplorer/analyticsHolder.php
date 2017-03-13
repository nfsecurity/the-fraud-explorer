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
 * Description: Code for Chart
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

require 'vendor/autoload.php';
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";
include "lbs/elasticsearch.php";

?>

<!-- Styles -->

<style>

.font-icon-color 
{ 
    color: #B4BCC2; 
}

</style>

<!-- Chart -->

<center>
	<div class="content-graph">
	<div class="graph-insights">
		
	<!-- Graph scope -->
	
	<form name="scope" method="post">

		<select class="select-scope-styled" name="ruleset" id="ruleset">
                	<option selected="selected"> <?php echo $_SESSION['rulesetScope']; ?></option>

                	<?php	

                        	$configFile = parse_ini_file("config.ini");
                        	$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                        	$GLOBALS['listRuleset'] = null;

				echo '<option value="ALL">ALL</option>';

                        	foreach ($jsonFT['dictionary'] as $ruleset => $value)
                        	{
                                	echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                        	}
                	?>

        	</select>
		
		<span style="line-height: 0.7"><br><br></span>	
		<input type="submit" name="submit" id="submit" value="Refresh graph" class="btn btn-default" style="width: 100%;" />
	</form>
		
	<!-- Leyend -->

	<span style="line-height: 0.3"><br></span>
	<table class="table-leyend">
		<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-icon-color">&nbsp;&nbsp;</span>Score leyend</th>
			<tr>
				<td class="table-leyend-point"><span class="point-red"></span><br>31></td>
				<td class="table-leyend-point"><span class="point-green"></span><br>21-30</td>
			</tr>
			<tr>
				<td class="table-leyend-point"><span class="point-blue"></span><br>11-20</td>
				<td class="table-leyend-point"><span class="point-yellow"></span><br>0-10</td>
			</tr>
	</table>
	<span style="line-height: 0.1"><br></span>
	<table class="table-leyend">
        	<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-icon-color">&nbsp;&nbsp;</span>Opportunity</th>
                	<tr>
                                <td class="table-leyend-point"><span class="point-opportunity-0-10"></span><br>0-10</td>
                                <td class="table-leyend-point"><span class="point-opportunity-11-30"></span><br>11-30</td>
                        </tr>
                        <tr>
                                <td class="table-leyend-point"><span class="point-opportunity-31-60"></span><br>31-60</td>
                                <td class="table-leyend-point"><span class="point-opportunity-61-100"></span><br>61-100</td>
                        </tr>
			<tr>
                                <td class="table-leyend-point"><span class="point-opportunity-101-500"></span><br>101-500</td>
                                <td class="table-leyend-point"><span class="point-opportunity-501-1000"></span><br>501-1000</td>
                        </tr>
	</table>
	<span style="line-height: 0.1"><br></span>

	<!-- Insights -->

	<?php

		$client = Elasticsearch\ClientBuilder::create()->build();
                $configFile = parse_ini_file("config.ini");
                $ESindex = $configFile['es_words_index'];
                $ESalerterIndex = $configFile['es_alerter_index'];
                $fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');
		$APCttl = $configFile['apc_ttl'];

		/* Matches data with APC caching */

		$matchesRationalizationCount_CACHED = "matchesRationalizationCount";
		$matchesOpportunityCount_CACHED = "matchesOpportunityCount";
		$matchesPressureCount_CACHED = "matchesPressureCount";

		$matchesRationalizationCount_FETCH = apc_fetch($matchesRationalizationCount_CACHED);
		$matchesOpportunityCount_FETCH = apc_fetch($matchesOpportunityCount_CACHED);
		$matchesPressureCount_FETCH = apc_fetch($matchesPressureCount_CACHED);

		if(!$matchesRationalizationCount_FETCH || !$matchesOpportunityCount_FETCH || !$matchesPressureCount_FETCH) 
		{
			$matchesRationalizationCount = countAllFraudTriangleMatches($fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                	$matchesOpportunityCount = countAllFraudTriangleMatches($fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                	$matchesPressureCount = countAllFraudTriangleMatches($fraudTriangleTerms['p'], $configFile['es_alerter_index']);

			apc_store($matchesRationalizationCount_CACHED, $matchesRationalizationCount, $APCttl);
			apc_store($matchesOpportunityCount_CACHED, $matchesOpportunityCount, $APCttl);
			apc_store($matchesPressureCount_CACHED, $matchesPressureCount, $APCttl);
		}

		$CcountRationalizationTotal = apc_fetch($matchesRationalizationCount_CACHED);
		$countRationalizationTotal = $CcountRationalizationTotal['count'];
		$CcountOpportunityTotal = apc_fetch($matchesOpportunityCount_CACHED);		
                $countOpportunityTotal = $CcountOpportunityTotal['count'];
		$CcountPressureTotal = apc_fetch($matchesPressureCount_CACHED);
                $countPressureTotal = $CcountPressureTotal['count'];

		echo '<table class="table-insights">';
                echo '<th colspan=2 class="table-insights-header"><span class="fa fa-align-justify font-icon-color">&nbsp;&nbsp;</span>Phrase counts</th>';
                echo '<tr>';
                echo '<td class="table-insights-triangle">Pressure</td>';
                echo '<td class="table-insights-score">'.$countPressureTotal.'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td class="table-insights-triangle">Opportunity</td>';
                echo '<td class="table-insights-score">'.$countOpportunityTotal.'</td>';
                echo '</tr>';
		echo '<tr>';
                echo '<td class="table-insights-triangle">Rationalization</td>';
                echo '<td class="table-insights-score">'.$countRationalizationTotal.'</td>';
                echo '</tr>';
                echo '</table>';
		echo '<span style="line-height: 0.1"><br></span>';

	?>

	<?php
		$fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');
		$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
		$dictionaryCount = array('pressure'=>'0', 'opportunity'=>'0', 'rationalization'=>'0');

		foreach ($jsonFT['dictionary'] as $ruleset => $value)
                {
			foreach($fraudTriangleTerms as $term)
			{
				foreach ($jsonFT['dictionary'][$ruleset][$term] as $field => $termPhrase)
				{
					$dictionaryCount[$term]++;		
				}
			}
		}

                echo '<table class="table-dictionary">';
                echo '<th colspan=2 class="table-dictionary-header"><span class="fa fa-align-justify font-icon-color">&nbsp;&nbsp;</span>Dictionary DB</th>';
                echo ' <tr>';
                echo '<td class="table-dictionary-triangle">Pressure</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['pressure'].'</td>';
                echo ' </tr>';
                echo ' <tr>';
                echo '<td class="table-dictionary-triangle">Opportunity</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['opportunity'].'</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td class="table-dictionary-triangle">Rationalization</td>';
                echo '<td class="table-dictionary-score">'.$dictionaryCount['rationalization'].'</td>';
                echo '</tr>';
                echo '</table>';
                echo '<br>';
		echo '</div>';
	?>

	<div class="y-axis-line"></div>
	<div class="y-axis-leyend"><span class="fa fa-bar-chart font-icon-color">&nbsp;&nbsp;</span>Incentive, Pressure to commit Fraud</div>

	<div class="x-axis-line-leyend">
        	<br><span class="fa fa-line-chart font-icon-color">&nbsp;&nbsp;</span>Unethical behavior, Rationalization
	</div>

        <div id="scatterplot">

		<?php

			function paintScatter($counter, $opportunityPoint, $agent, $score, $countPressure, $countOpportunity, $countRationalization)
			{
				echo '<span id="point'.$counter.'" class="'.$opportunityPoint.' tooltip-custom" title="<div class=tooltip-inside><b>'.$agent.'</b><table class=tooltip-table><body><tr><td>Total Fraud Score</td><td>'.$score.'</td></tr><tr>
				<td>Pressure count</td><td>'.$countPressure.'</td></tr><tr><td>Opportunity count</td><td>'.$countOpportunity.'</td></tr><tr><td>Rationalization count</td><td>'.$countRationalization.'</td></tr></table>"</div></span>'."\n";
			}

			/* Elasticsearch querys for fraud triangle counts and score */

			$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

			/* Database querys */

			if($session->domain == "all")
			{
				if ($_SESSION['rulesetScope'] == "ALL") $result_a = mysql_query("SELECT agent, name, ruleset, pressure, opportunity, rationalization FROM t_agents");
				else $result_a = mysql_query("SELECT agent, name, ruleset, pressure, opportunity, rationalization FROM t_agents WHERE ruleset = '".$_SESSION['rulesetScope']."'");
			}
			else
			{
				if ($_SESSION['rulesetScope'] == "ALL") $result_a = mysql_query("SELECT agent, name, ruleset, pressure, opportunity, rationalization FROM t_agents WHERE domain='".$session->domain."'");
                                else $result_a = mysql_query("SELECT agent, name, ruleset, pressure, opportunity, rationalization FROM t_agents WHERE ruleset = '".$_SESSION['rulesetScope']."' AND domain='".$session->domain."'");
			}

			/* Logic */

			$counter = 1;

			if ($row_a = mysql_fetch_array($result_a))
			{
				do
				{
					/* Agent data with APC caching */

					$countRationalization = $row_a['rationalization'];
			                $countOpportunity = $row_a['opportunity'];
                			$countPressure = $row_a['pressure'];
					$score=($countPressure+$countOpportunity+$countRationalization)/3;
					$score = round($score, 1);	

					unset($GLOBALS['numberOfRMatches']);
                                        unset($GLOBALS['numberOfOMatches']);
                                        unset($GLOBALS['numberOfPMatches']);
                                        unset($GLOBALS['numberOfCMatches']);
	
					if ($countOpportunity >= 0 && $countOpportunity <= 10)
					{
                                                if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-0-10-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-0-10-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-0-10-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= 31.0) paintScatter($counter, "point-opportunity-0-10-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
					}
											
					if ($countOpportunity >= 11 && $countOpportunity <= 30)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-11-30-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-11-30-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-11-30-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-11-30-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }
			
					if ($countOpportunity >= 31 && $countOpportunity <= 60)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-31-60-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-31-60-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-31-60-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-31-60-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 61 && $countOpportunity <= 100)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-61-100-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-61-100-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-61-100-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-61-100-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 101 && $countOpportunity <= 500)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-101-500-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-101-500-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-101-500-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-101-500-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                                        {
						if ($score > 0.0 && $score <= 10.9) paintScatter($counter, "point-opportunity-501-1000-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 11.0 && $score <= 20.9) paintScatter($counter, "point-opportunity-501-1000-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 21.0 && $score <= 30.9) paintScatter($counter, "point-opportunity-501-1000-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= 31.0) paintScatter($counter, "point-opportunity-501-1000-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					$counter++;
				}
				while ($row_a = mysql_fetch_array($result_a));
			}

		?>
	</div>
	</div>
	</div>
</center>

<!-- Scatterplot -->

<script type="text/javascript">
$(document).ready(function () {
        $('#scatterplot').scatter({
                color: '#ededed', 
	<?php

        /* Database querys */

        $result_a = mysql_query("SELECT agent, heartbeat, now(), system, version, status, name, ruleset, gender, pressure, opportunity, rationalization FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");
	$result_b = mysql_query("SELECT agent, heartbeat, now(), system, version, status, name, ruleset, gender, pressure, opportunity, rationalization FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

        /* Logic */

        $counter = 1;
	
	if ($row_a = mysql_fetch_array($result_a))
        {
        	do
               	{
			/* Agent data with APC caching */
		
			$countRationalization = $row_a['rationalization'];
                        $countOpportunity = $row_a['opportunity'];
                        $countPressure = $row_a['pressure'];

			/*  Draw axis units */

			if ($counter == 1)
			{
				$subCounter = 1;

				/* Get max count value for both axis */
			
				if ($row_aT = mysql_fetch_array($result_b))
        			{
                			do
                			{
						/* Agent data with APC caching */

                                		$countRationalizationT[$subCounter] = $row_aT['rationalization'];
                                		$countPressureT[$subCounter] = $row_aT['pressure'];
	
						$subCounter++;
					}
                			while ($row_aT = mysql_fetch_array($result_b));
				}

				$GLOBALS['maxXAxis'] = max($countPressureT);
				$GLOBALS['maxYAxis'] = max($countRationalizationT);

				echo 'rows: '.$maxYAxis.','; 
                		echo 'columns: 0,'."\n"; 
                		echo 'subsections: '.$maxXAxis.','; 
                		echo 'responsive: true';
        			echo '});';
     			}

			/* Scoring calculation */

			$score=($countPressure+$countOpportunity+$countRationalization)/3;
		
			if($GLOBALS['maxXAxis'] == 0) $xAxis = ($countPressure*100)/1;
			else $xAxis = ($countPressure*100)/$GLOBALS['maxXAxis'];
                       
			if($GLOBALS['maxYAxis'] == 0) $yAxis = ($countRationalization*100)/1;
			else $yAxis = ($countRationalization*100)/$GLOBALS['maxYAxis'];

			/* Fix corners */

   			if ($xAxis == 100) $xAxis = $xAxis - 2;
			if ($yAxis == 100) $yAxis = $yAxis - 5;
			if ($xAxis == 0) $xAxis = $xAxis + 2;
                        if ($yAxis == 0) $yAxis = $yAxis + 3;			

                        if ($countOpportunity >= 0 && $countOpportunity <= 10)
                        {
       		                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 11 && $countOpportunity <= 30)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 31 && $countOpportunity <= 60)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }
		
			if ($countOpportunity >= 61 && $countOpportunity <= 100)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 101 && $countOpportunity <= 500)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                        {
                                 if ($score > 0.0 && $score <= 10.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11.0 && $score <= 20.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21.0 && $score <= 30.9) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31.0) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        $counter++;
		}
		while ($row_a = mysql_fetch_array($result_a));
	}
	?>
});
</script>

<!-- Tooltipster -->

<script>
	$(document).ready(function()
	{
        	$('.tooltip-custom').tooltipster(
       	 	{
               	 	theme: 'tooltipster-light',
                	contentAsHTML: true
        	});
	});
</script>
