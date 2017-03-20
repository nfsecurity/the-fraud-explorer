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

.font-aw-color
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

	<?php
		$scoreQuery = mysql_query("SELECT * FROM t_config");
                $scoreResult = mysql_fetch_array($scoreQuery);
	?>

	<span style="line-height: 0.3"><br></span>
	<table class="table-leyend">
		<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-aw-color">&nbsp;&nbsp;</span>Score leyend</th>
			<tr>
				<td class="table-leyend-point"><span class="point-red"></span><br><?php echo $scoreResult['score_ts_critic_from'].">"; ?></td>
				<td class="table-leyend-point"><span class="point-green"></span><br><?php echo $scoreResult['score_ts_high_from']."-".$scoreResult['score_ts_high_to']; ?></td>
			</tr>
			<tr>
				<td class="table-leyend-point"><span class="point-blue"></span><br><?php echo $scoreResult['score_ts_medium_from']."-".$scoreResult['score_ts_medium_to']; ?></td>
				<td class="table-leyend-point"><span class="point-yellow"></span><br><?php echo $scoreResult['score_ts_low_from']."-".$scoreResult['score_ts_low_to']; ?></td>
			</tr>
	</table>
	<span style="line-height: 0.1"><br></span>
	<table class="table-leyend">
        	<th colspan=2 class="table-leyend-header"><span class="fa fa-tags font-aw-color">&nbsp;&nbsp;</span>Opportunity</th>
                	<tr>
                                <td class="table-leyend-point"><span class="point-opportunity-low"></span><br><?php echo $scoreResult['score_ts_low_from']."-".$scoreResult['score_ts_low_to']; ?></td>
                                <td class="table-leyend-point"><span class="point-opportunity-medium"></span><br><?php echo $scoreResult['score_ts_medium_from']."-".$scoreResult['score_ts_medium_to']; ?></td>
                        </tr>
                        <tr>
                                <td class="table-leyend-point"><span class="point-opportunity-high"></span><br><?php echo $scoreResult['score_ts_high_from']."-".$scoreResult['score_ts_high_to']; ?></td>
                                <td class="table-leyend-point"><span class="point-opportunity-critic"></span><br><?php echo $scoreResult['score_ts_critic_from'].">"; ?></td>
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
                echo '<th colspan=2 class="table-insights-header"><span class="fa fa-align-justify font-aw-color">&nbsp;&nbsp;</span>Phrase counts</th>';
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
                echo '<th colspan=2 class="table-dictionary-header"><span class="fa fa-align-justify font-aw-color">&nbsp;&nbsp;</span>Dictionary DB</th>';
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
	<div class="y-axis-leyend"><span class="fa fa-bar-chart font-aw-color">&nbsp;&nbsp;</span>Incentive, Pressure to commit Fraud</div>

	<div class="x-axis-line-leyend">
        	<br><span class="fa fa-line-chart font-aw-color">&nbsp;&nbsp;</span>Unethical behavior, Rationalization
	</div>

        <div id="scatterplot">

		<?php

			function paintScatter($counter, $opportunityPoint, $agent, $score, $countPressure, $countOpportunity, $countRationalization)
			{
				echo '<span id="point'.$counter.'" class="'.$opportunityPoint.' tooltip-custom" title="<div class=tooltip-inside><b>'.$agent.'</b><table class=tooltip-table><body><tr><td>Total Fraud Score</td><td>'.$score.'</td></tr><tr>
				<td>Pressure count</td><td>'.$countPressure.'</td></tr><tr><td>Opportunity count</td><td>'.$countOpportunity.'</td></tr><tr><td>Rationalization count</td><td>'.$countRationalization.'</td></tr></table></div>">'."\n";
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
	
					if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= $scoreResult['score_ts_low_to'])
					{
                                                if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-low-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-low-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-low-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-low-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
					}
											
					if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= $scoreResult['score_ts_medium_to'])
                                        {
						if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-medium-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-medium-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-medium-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-medium-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }
			
					if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= $scoreResult['score_ts_high_to'])
                                        {
						if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-high-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-high-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-high-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-high-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                        }

					if ($countOpportunity >= $scoreResult['score_ts_critic_from'])
                                        {
						if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) paintScatter($counter, "point-opportunity-critic-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) paintScatter($counter, "point-opportunity-critic-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) paintScatter($counter, "point-opportunity-critic-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                if ($score >= $scoreResult['score_ts_critic_from']) paintScatter($counter, "point-opportunity-critic-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
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

        $result_a = mysql_query("SELECT agent, ruleset, pressure, opportunity, rationalization FROM t_agents");
	$result_b = mysql_query("SELECT agent, ruleset, pressure, opportunity, rationalization FROM t_agents");

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

				$GLOBALS['maxYAxis'] = max($countPressureT);
				$GLOBALS['maxXAxis'] = max($countRationalizationT);

				echo 'rows: 2,'; 
                		echo 'columns: 2,'; 
                		echo 'subsections: 0,'; 
                		echo 'responsive: true';
        			echo '});';
     			}

			/* Scoring calculation */

			$score=($countPressure+$countOpportunity+$countRationalization)/3;
		
			if($GLOBALS['maxYAxis'] == 0) $yAxis = ($countPressure*100)/1;
			else $yAxis = ($countPressure*100)/$GLOBALS['maxYAxis'];
                       
			if($GLOBALS['maxXAxis'] == 0) $xAxis = ($countRationalization*100)/1;
			else $xAxis = ($countRationalization*100)/$GLOBALS['maxXAxis'];

			/* Fix corners */

   			if ($xAxis == 100) $xAxis = $xAxis - 2;
			if ($yAxis == 100) $yAxis = $yAxis - 4.5;
			if ($xAxis == 0) $xAxis = $xAxis + 1.5;
                        if ($yAxis == 0) $yAxis = $yAxis + 3;	

                        if ($countOpportunity >= $scoreResult['score_ts_low_from'] && $countOpportunity <= $scoreResult['score_ts_low_to'])
                        {
       		                 if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= $scoreResult['score_ts_medium_from'] && $countOpportunity <= $scoreResult['score_ts_medium_to'])
                        {
                                 if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= $scoreResult['score_ts_high_from'] && $countOpportunity <= $scoreResult['score_ts_high_to'])
                        {
                                 if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }
		
			if ($countOpportunity >= $scoreResult['score_ts_critic_from'] && $countOpportunity <= $scoreResult['score_ts_critic_to'])
                        {
                                 if ($score > $scoreResult['score_ts_low_from'] && $score <= ($scoreResult['score_ts_low_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_medium_from'] && $score <= ($scoreResult['score_ts_medium_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_high_from'] && $score <= ($scoreResult['score_ts_high_to']+0.9)) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= $scoreResult['score_ts_critic_from']) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
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
