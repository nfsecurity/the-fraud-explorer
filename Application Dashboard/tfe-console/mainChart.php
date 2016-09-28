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
 * Description: Code for Chart
 */

session_start();

if(empty($_SESSION['connected']))
{
 header ("Location: index");
 exit;
}

require 'vendor/autoload.php';
include "inc/open-db-connection.php";
include "inc/agent_methods.php";
include "inc/check_perm.php";
include "inc/elasticsearch.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>TFE - Chart</title>
	<meta http-equiv="X-Frame-Options" content="deny">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<!-- JQuery 11 inclusion -->

	<script type="text/javascript" src="js/jquery.min.js"></script>

	<!-- Styles and JS for modal dialogs -->

        <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
        <script src="js/bootstrap.js"></script>


	<!-- JS/CSS for Tooltip -->

	<link rel="stylesheet" type="text/css" href="css/tooltipster.bundle.css"/>
	<link rel="stylesheet" type="text/css" href="css/tooltipster-themes/tooltipster-sideTip-light.min.css">
	<script type="text/javascript" src="js/tooltipster.bundle.js"></script>

	<!-- JS functions -->

	<script type="text/javascript" src="js/mainChart.js"></script>
	<link rel="stylesheet" type="text/css" href="css/mainChart.css"/>

	<!-- Load ScatterPlotChart -->

        <link href="css/scatterplot.css" rel="stylesheet" type="text/css" />
        <script src="js/scatterplot.js"></script>

	<!-- Charts CSS -->

        <link rel="stylesheet" type="text/css" href="css/chartAnalytics.css" media="screen" />
</head>
<body>
	<div align="center">

		<!-- Top main menu -->

		<div id="includedTopMenu"></div>

		<!-- Chart -->

	        <center>
                <div class="content-graph">
			<div class="graph-insights">
				
				<!-- Leyend -->

				<table class="table-leyend">
				<th colspan=2 class="table-leyend-header">Score leyend</th>
				<tr>
					<td class="table-leyend-point"><span class="point-red"></span><br>31></td>
					<td class="table-leyend-point"><span class="point-green"></span><br>21-30</td>
				</tr>
				<tr>
					<td class="table-leyend-point"><span class="point-blue"></span><br>11-20</td>
					<td class="table-leyend-point"><span class="point-yellow"></span><br>0-10</td>
				</tr>
				</table>
				<br>
				<table class="table-leyend">
                                <th colspan=2 class="table-leyend-header">Opportunity</th>
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
				<br>

				<!-- Insights -->

				<?php

					$client = Elasticsearch\ClientBuilder::create()->build();
                                	$configFile = parse_ini_file("config.ini");
                                	$ESindex = $configFile['es_words_index'];
                                	$ESalerterIndex = $configFile['es_alerter_index'];
                                	$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

                                	$matchesRationalizationCount = countAllFraudTriangleMatches($fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                                	$matchesOpportunityCount = countAllFraudTriangleMatches($fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                                	$matchesPressureCount = countAllFraudTriangleMatches($fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                                	if ($matchesRationalizationCount['hits']['total'] != 0)
                                	{
                                        	$GLOBALS['arrayPosition'] = 0;
                                                getArrayData($matchesRationalizationCount, "matchNumber", 'numberOfRMatchesCount');
                                                $countRationalizationTotal = array_sum($GLOBALS['numberOfRMatchesCount']);
                                        }
					else $countRationalizationTotal = 0;

					if ($matchesOpportunityCount['hits']['total'] != 0)
                                        {
                                                $GLOBALS['arrayPosition'] = 0;
                                                getArrayData($matchesOpportunityCount, "matchNumber", 'numberOfOMatchesCount');
                                                $countOpportunityTotal = array_sum($GLOBALS['numberOfOMatchesCount']);
                                        }
                                        else $countOpportunityTotal = 0;

					if ($matchesPressureCount['hits']['total'] != 0)
                                        {
                                                $GLOBALS['arrayPosition'] = 0;
                                                getArrayData($matchesPressureCount, "matchNumber", 'numberOfPMatchesCount');
                                                $countPressureTotal = array_sum($GLOBALS['numberOfPMatchesCount']);
                                        }
                                        else $countPressureTotal = 0;

					echo '<table class="table-insights">';
                                	echo '<th colspan=2 class="table-insights-header">Phrase counts</th>';
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
					echo '<br>';

				?>

				<!-- Dictionary -->

				<?php
					$fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');
					$jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
					$dictionaryCount = array('pressure'=>'1', 'opportunity'=>'1', 'rationalization'=>'1');

					foreach($fraudTriangleTerms as $term)
					{
						foreach ($jsonFT['dictionary'][$term] as $field => $termPhrase)
						{
							$dictionaryCount[$term]++;		
						}
					}

                                	echo '<table class="table-dictionary">';
                                	echo '<th colspan=2 class="table-dictionary-header">Dictionary phrases</th>';
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
				<div class="y-axis-leyend"><b>Incentive, Pressure to commit Fraud</b></div>

				<div class="x-axis-line-leyend">
                                	<br><b>Unethical behavior, Rationalization</b>
				</div>

                        <div id="scatterplot">

				<?php

				function paintScatter($counter, $opportunityPoint, $agent, $score, $countPressure, $countOpportunity, $countRationalization)
				{
					echo '<span id="point'.$counter.'" class="'.$opportunityPoint.' tooltip" title="<div class=tooltip-inside><b>'.$agent.'</b><table class=tooltip-table><body><tr><td>Total Fraud Score</td><td>'.$score.'</td></tr><tr>
					<td>Pressure count</td><td>'.$countPressure.'</td></tr><tr><td>Opportunity count</td><td>'.$countOpportunity.'</td></tr><tr><td>Rationalization count</td><td>'.$countRationalization.'</td></tr></table>"</div></span>'."\n";
				}

				/* Elasticsearch querys for fraud triangle counts and score */

				$fraudTriangleTerms = array('r'=>'rationalization','o'=>'opportunity','p'=>'pressure','c'=>'custom');

				/* Database querys */

				$result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

				/* Logic */

				$counter = 1;

				if ($row_a = mysql_fetch_array($result_a))
				{
					do
					{
						$matchesRationalization = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                				$matchesOpportunity = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                				$matchesPressure = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

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
				
						$score= ($countPressure+$countOpportunity+$countRationalization)/3;		
						$score = round($score, 1);	

						unset($GLOBALS['numberOfRMatches']);
                                                unset($GLOBALS['numberOfOMatches']);
                                                unset($GLOBALS['numberOfPMatches']);
                                                unset($GLOBALS['numberOfCMatches']);
	
						if ($countOpportunity >= 0 && $countOpportunity <= 10)
						{
                                                        if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-0-10-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
							if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-0-10-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
							if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-0-10-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
							if ($score >= 31) paintScatter($counter, "point-opportunity-0-10-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
						}
											
						if ($countOpportunity >= 11 && $countOpportunity <= 30)
                                                {
							if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-11-30-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-11-30-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-11-30-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 31) paintScatter($counter, "point-opportunity-11-30-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                }
			
						if ($countOpportunity >= 31 && $countOpportunity <= 60)
                                                {
							if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-31-60-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-31-60-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-31-60-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 31) paintScatter($counter, "point-opportunity-31-60-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                }

						if ($countOpportunity >= 61 && $countOpportunity <= 100)
                                                {
							if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-61-100-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-61-100-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-61-100-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 31) paintScatter($counter, "point-opportunity-61-100-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                }

						if ($countOpportunity >= 101 && $countOpportunity <= 500)
                                                {
							if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-101-500-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-101-500-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-101-500-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 31) paintScatter($counter, "point-opportunity-101-500-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                }

						if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                                                {
							if ($score > 0 && $score <= 10) paintScatter($counter, "point-opportunity-501-1000-yellow", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 11 && $score <= 20) paintScatter($counter, "point-opportunity-501-1000-blue", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 21 && $score <= 30) paintScatter($counter, "point-opportunity-501-1000-green", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                        if ($score >= 31) paintScatter($counter, "point-opportunity-501-1000-red", $row_a["agent"], $score, $countPressure, $countOpportunity, $countRationalization);
                                                }

						$counter++;
					}
					while ($row_a = mysql_fetch_array($result_a));
				}

				?>
                        </div>
                </div>
        	</center>


	</div>

	<!-- Footer -->

        <div id="includedGenericFooterContent"></div>
</body>

<!-- Scatterplot -->

<script type="text/javascript">
$(document).ready(function () {
        $('#scatterplot').scatter({
                color: '#ededed', 
	<?php

        /* Database querys */

        $result_a = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");
	$result_b = mysql_query("SELECT agent,heartbeat, now(), system, version, status, name, owner, gender FROM t_agents ORDER BY FIELD(status, 'active','inactive'), agent ASC");

        /* Logic */

        $counter = 1;
	
	if ($row_a = mysql_fetch_array($result_a))
        {
        	do
               	{
			$matchesRationalization = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                        $matchesOpportunity = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['o'], $configFile['es_alerter_index']);
                        $matchesPressure = countFraudTriangleMatches($row_a["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

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

			/*  Draw axis units */

			if ($counter == 1)
			{
				$subCounter = 1;

				/* Get max count value for both axis */
			
				if ($row_aT = mysql_fetch_array($result_b))
        			{
                			do
                			{
                        			$matchesRationalizationT = countFraudTriangleMatches($row_aT["agent"], $fraudTriangleTerms['r'], $configFile['es_alerter_index']);
                        			$matchesPressureT = countFraudTriangleMatches($row_aT["agent"], $fraudTriangleTerms['p'], $configFile['es_alerter_index']);

                        			if ($matchesRationalizationT['hits']['total'] != 0)
                        			{
                                			$GLOBALS['arrayPosition'] = 0;
                                			getArrayData($matchesRationalizationT, "matchNumber", 'numberOfRTMatches');
                                			$countRationalizationT[$subCounter] = array_sum($GLOBALS['numberOfRTMatches']);
                        			}
                        			else $countRationalizationT[$subCounter] = 0;

                        			if ($matchesPressureT['hits']['total'] != 0)
                        			{
                                			$GLOBALS['arrayPosition'] = 0;
                                			getArrayData($matchesPressureT, "matchNumber", 'numberOfPTMatches');
                                			$countPressureT[$subCounter] = array_sum($GLOBALS['numberOfPTMatches']);
                        			}
                        			else $countPressureT[$subCounter] = 0;
							
						unset($GLOBALS['numberOfRTMatches']);
						unset($GLOBALS['numberOfPTMatches']);
	
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

                        unset($GLOBALS['numberOfRMatches']);
                        unset($GLOBALS['numberOfOMatches']);
                        unset($GLOBALS['numberOfPMatches']);
                        unset($GLOBALS['numberOfCMatches']);

			/* Scoring calculation */

			$score=($countPressure+$countOpportunity+$countRationalization)/3;
                        $xAxis = ($countPressure*100)/$GLOBALS['maxXAxis'];
                        $yAxis = ($countRationalization*100)/$GLOBALS['maxYAxis'];

			/* Fix corners */

   			if ($xAxis == 100) $xAxis = $xAxis - 2;
			if ($yAxis == 100) $yAxis = $yAxis - 5;
			if ($xAxis == 0) $xAxis = $xAxis + 2;
                        if ($yAxis == 0) $yAxis = $yAxis + 3;			

                        if ($countOpportunity >= 0 && $countOpportunity <= 10)
                        {
       		                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 11 && $countOpportunity <= 30)
                        {
                                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 31 && $countOpportunity <= 60)
                        {
                                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }
		
			if ($countOpportunity >= 61 && $countOpportunity <= 100)
                        {
                                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 101 && $countOpportunity <= 500)
                        {
                                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        if ($countOpportunity >= 501 && $countOpportunity <= 1000)
                        {
                                 if ($score > 0 && $score <= 10) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 11 && $score <= 20) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 21 && $score <= 30) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                                 if ($score >= 31) echo '$(\'#point'.$counter.'\').plot({ xPos: \''.$xAxis.'%\', yPos: \''.$yAxis.'%\'});';
                        }

                        $counter++;
		}
		while ($row_a = mysql_fetch_array($result_a));
	}
	?>
});
</script>

</html>
