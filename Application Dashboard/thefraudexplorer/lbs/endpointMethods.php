<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Endpoint specific functions
 */ 

function queryOrDie($query)
{
    global $connection;

    $query = mysqli_query($connection, $query);
    if (! $query) exit(mysqli_error());
    return $query;
}

function isConnected($t1, $t2)
{
    $dateUpper = strtotime($t2);
    $dateLower = strtotime($t1);
    $differenceMns = (int)(($dateUpper - $dateLower)/60);
    return $differenceMns < 120;
}

function getTextSist($system)
{ 
    if ($system == '5.1') return ' Windows XP';
    if ($system == '6.1') return ' Windows 7'; 
    if ($system == '6.2' || $system == '6.3') return ' Windows 8';
    if ($system == '10.0') return ' Windows 10';
    else return ' Windows Vista'; 
}

function samplerStatus($sessionDomain)
{
    global $connection;

    if ($sessionDomain != "all") 
    {
        $domainConfigTable = "t_config_".str_replace(".", "_", $sessionDomain);
        $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
        $calculationQuery = mysqli_query($connection, $queryCalc); 
        $sampleQuery = mysqli_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
    else
    {
        $calculationQuery = mysqli_query($connection, "SELECT sample_data_calculation FROM t_config");
        $sampleQuery = mysqli_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
}

function endpointInsights($location, $gender, $endpointEnc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $endpointName)
{
    $endpointData = explode("@", $endpointName);
    $endpointDomainWithoutTLD = between('@', '.', "@".$endpointData[1]);
    $spaces = false;

    if (strpos($endpointData[0], ' ') !== false) $spaces = true;

    if ($location == "endPoints" || $location == "eventData") echo '<div class="gender-container"><img src="images/'.$gender.'-user.svg" class="gender-image"></div>';
    
    if ($score == 0)
    {
        echo '<span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div><div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div><div class=tooltip-row><div class=tooltip-item>Events by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div><div class=tooltip-row><div class=tooltip-item>Events by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div><div class=tooltip-row><div class=tooltip-item>Events by rationalization</div><div class=tooltip-value>'.$countRationalization.'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud triangle score</div><div class=tooltip-value>'.round($score, 1).'</div></div><div class=tooltip-row><div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div></div>"><span class="image-padding"><span class="fa fa-id-badge awfont-padding-right font-icon-color-gray"></span>'.($spaces == true ? $endpointData[0] : $endpointData[0].'@'.$endpointDomainWithoutTLD).'<br><span class="fa fa-chevron-right awfont-padding-right font-icon-color-gray"></span><p style="font-family: \'FFont-Bold\'; display: inline;"><b>'.$endpointData[1].'</b></p></span></a></td>'; 
    }
    else
    {
        echo '<a class="tooltip-custom" style="color: #555;" href=eventData?nt='.$endpointEnc.' title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div><div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div><div class=tooltip-row><div class=tooltip-item>Events by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div><div class=tooltip-row><div class=tooltip-item>Events by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div><div class=tooltip-row><div class=tooltip-item>Events by rationalization</div><div class=tooltip-value>'.$countRationalization.'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud triangle score</div><div class=tooltip-value>'.round($score, 1).'</div></div><div class=tooltip-row><div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div></div>"><span class="image-padding"><span class="fa fa-id-badge awfont-padding-right font-icon-color-gray"></span>'.($spaces == true ? $endpointData[0] : $endpointData[0].'@'.$endpointDomainWithoutTLD).'<br><span class="fa fa-chevron-right awfont-padding-right font-icon-color-gray"></span><p style="font-family: \'FFont-Bold\'; display: inline;"><b>'.$endpointData[1].'</b></p></span></a></td>';        
    }
}

function discoverOnline()
{
    global $connection;

    $orderQuery = "SELECT agent, heartbeat, now() FROM t_agents";
    $order = mysqli_query($connection, $orderQuery);
    
    if ($row = mysqli_fetch_array($order))
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
        while ($row = mysqli_fetch_array($order));
    }
}

function searchJsonFT($jsonFT, $searchValue, $endpointDECSQL, $queryRuleset)
{
    global $connection;

    $rulesetquery = mysqli_query($connection, sprintf($queryRuleset, $endpointDECSQL));
    $ruleset = mysqli_fetch_array($rulesetquery);
    
    if (is_null($ruleset[0])) $ruleset[0] = "BASELINE";
    $rule = $ruleset[0];
    
    $baselineRuleset = "BASELINE";
    $dict = "dictionary";
    $fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');

    foreach($fraudTriangleTerms as $term)
    {
        foreach($jsonFT->$dict->$rule->$term as $keyName => $value) 
        {
            if(strcmp($value, $searchValue) == 0) return $keyName;
        }
        foreach($jsonFT->$dict->$baselineRuleset->$term as $keyName => $value) 
        {
            if(strcmp($value, $searchValue) == 0) return $keyName;
        }
    }
}

function after ($thisparam, $inthat)
{
    if (!is_bool(strpos($inthat, $thisparam)))
    return substr($inthat, strpos($inthat,$thisparam)+strlen($thisparam));
}

function after_last ($thisparam, $inthat)
{
    if (!is_bool(strrevpos($inthat, $thisparam)))
    return substr($inthat, strrevpos($inthat, $thisparam)+strlen($thisparam));
}

function before ($thisparam, $inthat)
{
    return substr($inthat, 0, strpos($inthat, $thisparam));
}

function before_last ($thisparam, $inthat)
{
    return substr($inthat, 0, strrevpos($inthat, $thisparam));
}

function between ($thisparam, $that, $inthat)
{
    return before ($that, after($thisparam, $inthat));
}

function between_last ($thisparam, $that, $inthat)
{
    return after_last($thisparam, before_last($that, $inthat));
}

function strrevpos($instr, $needle)
{
    $rev_pos = strpos (strrev($instr), strrev($needle));
    if ($rev_pos===false) return false;
    else return strlen($instr) - $rev_pos - strlen($needle);
}

?>