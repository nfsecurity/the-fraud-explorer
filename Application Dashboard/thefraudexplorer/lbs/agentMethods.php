<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.1
 *
 * Description: Agent specific functions
 */ 

function queryOrDie($query)
{
    $query = mysql_query($query);
    if (! $query) exit(mysql_error());
    return $query;
}

function isConnected($t1, $t2)
{
    $dateUpper=strtotime($t2);
    $dateLower=strtotime($t1);
    $differenceMns = (int)(($dateUpper - $dateLower)/60);
    return $differenceMns<120;
}

function getTextSist($system)
{ 
    if($system=='5.1') return ' Windows XP';
    if($system=='6.1') return ' Windows 7'; 
    if($system=='6.2' || $system=='6.3') return ' Windows 8';
    if($system=='10.0') return ' Windows 10';
    else return ' Windows Vista'; 
}

function samplerStatus($sessionDomain)
{
    if ($sessionDomain != "all") 
    {
        $domainConfigTable = "t_config_".str_replace(".", "_", $sessionDomain);
        $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
        $calculationQuery = mysql_query($queryCalc); 
        $sampleQuery = mysql_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
    else
    {
        $calculationQuery = mysql_query("SELECT sample_data_calculation FROM t_config");
        $sampleQuery = mysql_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
}

function agentInsights($location, $gender, $agent_enc, $totalWordHits, $countPressure, $countOpportunity, $countRationalization, $score, $dataRepresentation, $agentName)
{
    if ($location == "endPoints") echo '<img src="images/'.$gender.'-agent.gif" class="gender-image">';
    
    if ($score == 0)
    {
        echo '<span class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div><div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by rationalization</div><div class=tooltip-value>'.$countRationalization.'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud triangle score</div><div class=tooltip-value>'.round($score, 1).'</div></div><div class=tooltip-row><div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div></div>"><span class="image-padding">'.$agentName.'</span></span></td>'; 
    }
    else
    {
        echo '<a class="tooltip-custom" href=alertData?agent='.$agent_enc.' title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div><div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by rationalization</div><div class=tooltip-value>'.$countRationalization.'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud triangle score</div><div class=tooltip-value>'.round($score, 1).'</div></div><div class=tooltip-row><div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div></div>"><span class="image-padding">'.$agentName.'</span></a></td>';        
    }
}

function discoverOnline()
{
    $orderQuery = "SELECT agent, heartbeat, now() FROM t_agents";
    $order = mysql_query($orderQuery);
    
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
}

function searchJsonFT($jsonFT, $searchValue, $agent_decSQ, $queryRuleset)
{
    $rulesetquery = mysql_query(sprintf($queryRuleset, $agent_decSQ));
    $ruleset = mysql_fetch_array($rulesetquery);
    
    if (is_null($ruleset[0])) $ruleset[0] = "BASELINE"; 
    
    $baselineRuleset = "BASELINE";
    $fraudTriangleTerms = array('0'=>'rationalization','1'=>'opportunity','2'=>'pressure');

    foreach($fraudTriangleTerms as $term)
    {
        foreach($jsonFT->dictionary->$ruleset[0]->$term as $keyName => $value) if(strcmp($value, $searchValue) == 0) return $keyName;
        foreach($jsonFT->dictionary->$baselineRuleset->$term as $keyName => $value) if(strcmp($value, $searchValue) == 0) return $keyName;
    }
}

function after ($this, $inthat)
{
    if (!is_bool(strpos($inthat, $this)))
    return substr($inthat, strpos($inthat,$this)+strlen($this));
}

function after_last ($this, $inthat)
{
    if (!is_bool(strrevpos($inthat, $this)))
    return substr($inthat, strrevpos($inthat, $this)+strlen($this));
}

function before ($this, $inthat)
{
    return substr($inthat, 0, strpos($inthat, $this));
}

function before_last ($this, $inthat)
{
    return substr($inthat, 0, strrevpos($inthat, $this));
}

function between ($this, $that, $inthat)
{
    return before ($that, after($this, $inthat));
}

function between_last ($this, $that, $inthat)
{
    return after_last($this, before_last($that, $inthat));
}

function strrevpos($instr, $needle)
{
    $rev_pos = strpos (strrev($instr), strrev($needle));
    if ($rev_pos===false) return false;
    else return strlen($instr) - $rev_pos - strlen($needle);
}

?>