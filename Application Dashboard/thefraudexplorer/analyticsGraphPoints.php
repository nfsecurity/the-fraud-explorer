<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.0
 *
 * Description: Code for graph points visualization
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";

$coordinates = filter($_GET['coordinates']);
$coordinates = str_replace('\\', '', $coordinates);
$graphPoints = json_decode($coordinates, true);

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-points
    {
        margin: 0px;
    }

    .table-graphdata
    {
        font-family: 'FFont', sans-serif; font-size:10px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
    }

    .table-thead-graphdata
    {
        display: block;
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        width: 100%;
        height: 45px;
    }

    .table-th-graphdata-body
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: calc(435px / 5);
        width: calc(435px / 5);
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }
    
    .table-th-graphdata-endpoint
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 120px;
        width: 120px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-tbody-graphdata
    {
        display: block;
        border: 1px solid white;
        width: 100%;
        height: auto !important; 
        max-height: 300px !important;
        overflow-y: scroll; 
    }

    .table-tr-graphdata
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-graphdata tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-td-graphdata-body
    {
        border: 0px solid gray;
        width: calc(435px / 5);
        min-width: calc(435px / 5);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-right: 2px solid white;
    }
    
    .table-td-graphdata-body-opportunity
    {
        width: calc(435px / 5);
        min-width: calc(435px / 5);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-right: 2px solid white;
        background: #d9d9d9; 
        font-family: 'FFont-Bold'; 
        border: 1px solid white; 
        border-radius: 3px 3px 3px 3px;
    }
    
    .table-td-graphdata-endpoint
    {
        border: 0px solid gray;
        width: 120px;
        min-width: 120px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-radius: 10px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
    }
    
    .table-td-graphdata-endpoint-woradius
    {
        border: 0px solid gray;
        width: 120px;
        min-width: 120px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-radius: 0px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
    }

    .font-icon-color-green
    {
        color: green;
    }
    
    .footer-statistics
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
    }

</style>

<div class="div-container-points">
    <table class="table-graphdata">
        <thead class="table-thead-graphdata">
            <th class="table-th-graphdata-endpoint" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color"></span>ENDPOINT</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>WORDS</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>PRESS</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>OPPRT</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>RATNL</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>SCORE</th>
        </thead>
        <tbody class="table-tbody-graphdata">

            <?php

            $queryEndpointsSQL = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
            $queryEndpointsSQL_wOSampler = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";                  
            $queryEndpointsSQLDomain = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
            $queryEndpointsSQLDomain_wOSampler = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
                    
            if ($session->domain == "all")
            {
                if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysql_query($queryEndpointsSQL);
                else $queryEndpoints = mysql_query($queryEndpointsSQL_wOSampler);
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysql_query($queryEndpointsSQLDomain);
                else $queryEndpoints = mysql_query($queryEndpointsSQLDomain_wOSampler);
            }

            if($endpointsFraud = mysql_fetch_assoc($queryEndpoints))
            {
                $counter = 0;
                
                do
                {
                    $agentName = $endpointsFraud['agent'];
                    $agentEncoded = base64_encode(base64_encode($endpointsFraud['agent']));
                    $totalWordHits = $endpointsFraud['totalwords'];
                    $countPressure = $endpointsFraud['pressure'];
                    $countOpportunity = $endpointsFraud['opportunity'];
                    $countRationalization = $endpointsFraud['rationalization'];
                    $totalAlerts = $endpointsFraud['pressure'] + $endpointsFraud['opportunity'] + $endpointsFraud['rationalization'];
                    
                    if ($totalAlerts != 0) 
                    {
                        echo '<tr class="table-tr-graphdata">';
                        
                        if ($counter == 0) echo '<td class="table-td-graphdata-endpoint"><span class="fa fa-user-circle font-icon-color-green">&nbsp;&nbsp;</span>';
                        else echo '<td class="table-td-graphdata-endpoint-woradius"><span class="fa fa-user-circle font-icon-color-green">&nbsp;&nbsp;</span>';
                        echo '<span class="pseudolink" onclick="javascript:location.href=\'alertData?agent='.$agentEncoded.'\'">'.$agentName.'</span></td>';
                    }
                    else continue;
                        
                    echo '<td class="table-td-graphdata-body">'.$totalWordHits.'</td>';
                    echo '<td class="table-td-graphdata-body">'.$countPressure.'</td>';
                    echo '<td class="table-td-graphdata-body-opportunity">'.$countOpportunity.'</td>';
                    echo '<td class="table-td-graphdata-body">'.$countRationalization.'</td>';
                    echo '<td class="table-td-graphdata-body">'.number_format($totalAlerts/3, 1).'</td>';
                    echo '</tr>';
                    
                    $counter++;
                }
                while ($endpointsFraud = mysql_fetch_assoc($queryEndpoints));
            }

            ?>

        </tbody>
    </table>
    
    <?php
    
    echo '<br><div class="footer-statistics"><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>There are '.$counter.' endpoints matching this coordinate in the graph</div><br>';
    
    ?>

</div> 