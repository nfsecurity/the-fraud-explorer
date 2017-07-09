<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for graphic data
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

    .div-container
    {
        margin: 20px;
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
        height: 300px !important; 
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
    }

    .font-icon-color-green
    {
        color: green;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Graphic data</h4>
</div>

<div class="div-container">
    <table class="table-graphdata">
        <thead class="table-thead-graphdata">
            <th class="table-th-graphdata-endpoint" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color"></span>ENDPOINT</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>WORDS</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>PRESS</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>OPPRT</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>RATNL</th>
            <th class="table-th-graphdata-body"><span class="fa fa-bookmark-o font-icon-color"></span>TOTAL</th>
        </thead>
        <tbody class="table-tbody-graphdata">

            <?php

            $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC LIMIT 250";
            $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC LIMIT 250";                  
            $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY score DESC LIMIT 250";
            $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' GROUP BY agent ORDER BY score DESC LIMIT 250";
                    
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
                do
                {
                    $agentName = $endpointsFraud['agent'];
                    $agentEncoded = base64_encode(base64_encode($endpointsFraud['agent']));
                    $totalWordHits = $endpointsFraud['totalwords'];
                    $countPressure = $endpointsFraud['pressure'];
                    $countOpportunity = $endpointsFraud['opportunity'];
                    $countRationalization = $endpointsFraud['rationalization'];
                    $totalAlerts = $endpointsFraud['pressure'] + $endpointsFraud['opportunity'] + $endpointsFraud['rationalization'];
                    
                    echo '<tr class="table-tr-graphdata">';
                    
                    if ($totalAlerts != 0) 
                    {
                        echo '<td class="table-td-graphdata-endpoint" style="text-align: left; border-right: 2px solid white;"><span class="fa fa-user-circle font-icon-color-green">&nbsp;&nbsp;</span>';
                        echo '<span class="pseudolink" onclick="javascript:location.href=\'alertData?agent='.$agentEncoded.'\'">'.$agentName.'</span></td>';
                    }
                    else echo '<td class="table-td-graphdata-endpoint" style="text-align: left; border-right: 2px solid white;"><span class="fa fa-user-circle font-icon-color-gray">&nbsp;&nbsp;</span>'.$agentName.'</td>'; 
                        
                    echo '<td class="table-td-graphdata-body" style="text-align: center; border-right: 2px solid white;">'.$totalWordHits.'</td>';
                    echo '<td class="table-td-graphdata-body">'.$countPressure.'</td>';
                    echo '<td class="table-td-graphdata-body">'.$countOpportunity.'</td>';
                    echo '<td class="table-td-graphdata-body" style="text-align: center; border-right: 2px solid white;">'.$countRationalization.'</td>';
                    echo '<td class="table-td-graphdata-body">'.str_pad($totalAlerts, 2, '0', STR_PAD_LEFT).'</td>';
                    echo '</tr>';
                    
                }
                while ($endpointsFraud = mysql_fetch_assoc($queryEndpoints));
            }

            ?>

        </tbody>
    </table>

    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Accept</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
    </div>
</div> 