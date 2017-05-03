<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
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
    return $differenceMns<70;
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
    if ($location == "endPoints") echo '<img src="images/'.$gender.'-agent.gif" class="gender-image">&nbsp;&nbsp;';
    echo '<a class="tooltip-custom" href=alertData?agent='.$agent_enc.' title="<div class=tooltip-container><div class=tooltip-title>Fraud Triangle Insights</div><div class=tooltip-row><div class=tooltip-item>Records stored</div>
    <div class=tooltip-value>'.number_format($totalWordHits, 0, ',', '.').'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by pressure</div><div class=tooltip-value>'.$countPressure.'</div></div>
    <div class=tooltip-row><div class=tooltip-item>Alerts by opportunity</div><div class=tooltip-value>'.$countOpportunity.'</div></div><div class=tooltip-row><div class=tooltip-item>Alerts by rationalization</div>
    <div class=tooltip-value>'.$countRationalization.'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud score</div><div class=tooltip-value>'.round($score, 1).'</div></div><div class=tooltip-row>
    <div class=tooltip-item>Data representation</div><div class=tooltip-value>'.round($dataRepresentation, 1).' %</div></div>
    </div>">' . $agentName . '</a></td>';
}

function agentDetails($agent_dec, $agentDomain, $osVersion, $status, $ipaddress, $sessions)
{
    echo '<a class="tooltip-custom" title="<div class=tooltip-container><div class=tooltip-title>Agent details</div><div class=tooltip-row><div class=tooltip-item>Identification</div><div class=tooltip-value-lefta>'.$agent_dec.'</div></div><div class=tooltip-row><div class=tooltip-item>Corporate domain</div><div class=tooltip-value-lefta>'.$agentDomain.'</div></div><div class=tooltip-row><div class=tooltip-item>Operating system</div><div class=tooltip-value-lefta>'.$osVersion.'</div></div><div class=tooltip-row><div class=tooltip-item>Connection status</div><div class=tooltip-value-lefta>'.$status.'</div></div><div class=tooltip-row><div class=tooltip-item>IP Address</div><div class=tooltip-value-lefta>'.$ipaddress.'</div></div><div class=tooltip-row><div class=tooltip-item>Number of sessions</div><div class=tooltip-value-lefta>'.$sessions.'</div></div></div>"><span class="fa fa-building-o fa-lg font-icon-color">&nbsp;&nbsp;</span></a>';
}

?>