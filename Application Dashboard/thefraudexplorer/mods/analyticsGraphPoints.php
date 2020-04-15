<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for graph points visualization
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
include "../lbs/cryptography.php";

$coordinates = filter($_GET['oo']);
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

    .window-footer-point
    {
        padding: 15px 0px 0px 0px;
    }

    .div-container-points
    {
        margin-bottom: 5px;
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
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
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
        padding: 0px 0px 0px 0px;
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
        text-align: left;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-tbody-graphdata
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: auto !important; 
        max-height: 302px !important;
        overflow-y: scroll;
        border-radius: 5px;
        font-size: 11px;
    }

    .table-tr-graphdata
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
        font-size: 11px;
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
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
        font-size: 11px;
    }
    
    .table-td-graphdata-score
    {
        border: 0px solid gray;
        width: calc(435px / 5);
        min-width: calc(435px / 5 - 7);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 0px solid white;
        font-size: 11px;
    }
    
    .table-td-graphdata-body-opportunity
    {
        width: calc(435px / 5);
        min-width: calc(435px / 5);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        background: #e8e9e8; 
        font-family: 'FFont'; 
        border: 0px solid white;
        border-right: 2px solid white;
        border-radius: 0px 0px 0px 0px;
        font-size: 11px;
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
        border-radius: 0px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
        font-size: 11px;
    }

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics-point
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
        margin: 15px 0px 15px 0px;
        text-align: center;
        font-family: Verdana, sans-serif; font-size: 11px !important;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;; 
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .font-aw-color
    {
        color: #B4BCC2;
    }

    .pseudolink 
    {
        outline: none;
        cursor: pointer;
        font-size: 11px;
    }

    .btn-success, .btn-success:active, .btn-success:visited, .btn-default, .btn-default:active, .btn-default:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

</style>

<div class="div-container-points">
    <table class="table-graphdata">
        <thead class="table-thead-graphdata">
            <th class="table-th-graphdata-endpoint" style="text-align: left;">ENDPOINT</th>
            <th class="table-th-graphdata-body">WORDS</th>
            <th class="table-th-graphdata-body">PRESS</th>
            <th class="table-th-graphdata-body">OPPRT</th>
            <th class="table-th-graphdata-body">RATNL</th>
            <th class="table-th-graphdata-body">SCORE</th>
        </thead>
        <tbody class="table-tbody-graphdata">

            <?php

            $queryEndpointsSQL = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
            $queryEndpointsSQL_wOSampler = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";                  
            $queryEndpointsSQLDomain = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
            $queryEndpointsSQLDomain_wOSampler = "SELECT * FROM (SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' GROUP BY agent ORDER BY score DESC) AS Points WHERE pressure='".(int)$graphPoints['y']."' AND rationalization='".(int)$graphPoints['x']."'";
                    
            if ($session->domain == "all")
            {
                if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysqli_query($connection, $queryEndpointsSQL);
                else $queryEndpoints = mysqli_query($connection, $queryEndpointsSQL_wOSampler);
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled") $queryEndpoints = mysqli_query($connection, $queryEndpointsSQLDomain);
                else $queryEndpoints = mysqli_query($connection, $queryEndpointsSQLDomain_wOSampler);
            }
            
            $counter = 0;

            if($endpointsFraud = mysqli_fetch_assoc($queryEndpoints))
            {
                do
                {
                    $endpointName = (strlen($endpointsFraud['agent']) > 12) ? substr($endpointsFraud['agent'], 0, 12) . ' ...' : $endpointsFraud['agent'];
                    $endpointEncoded = encRijndael($endpointsFraud['agent']);
                    $totalWordHits = $endpointsFraud['totalwords'];
                    $countPressure = $endpointsFraud['pressure'];
                    $countOpportunity = $endpointsFraud['opportunity'];
                    $countRationalization = $endpointsFraud['rationalization'];
                    $totalEvents = $endpointsFraud['pressure'] + $endpointsFraud['opportunity'] + $endpointsFraud['rationalization'];
                    
                    if ($totalEvents != 0) 
                    {
                        echo '<tr class="table-tr-graphdata">';
                        echo '<td class="table-td-graphdata-endpoint"><span class="fa fa-user-circle font-icon-color-green fa-padding"></span>';
                        echo '<span class="pseudolink" onclick="javascript:location.href=\'eventData?nt='.$endpointEncoded.'\'">'.$endpointName.'</span></td>';
                    }
                    else continue;
                        
                    echo '<td class="table-td-graphdata-body">'.$totalWordHits.'</td>';
                    echo '<td class="table-td-graphdata-body"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countPressure.'</td>';
                    echo '<td class="table-td-graphdata-body-opportunity"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countOpportunity.'</td>';
                    echo '<td class="table-td-graphdata-body"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countRationalization.'</td>';
                    echo '<td class="table-td-graphdata-score">'.number_format($totalEvents/3, 1).'</td>';
                    echo '</tr>';
                    
                    $counter++;
                }
                while ($endpointsFraud = mysqli_fetch_assoc($queryEndpoints));
            }

            ?>

        </tbody>
    </table>
    
    <?php
    
    echo '<div class="footer-statistics-point"><span class="fa fa-area-chart font-aw-color fa-padding"></span>There are '.$counter.' endpoints matching this coordinate in the graph</div>';
    
    ?>
    
    <div class="modal-footer window-footer-point">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to graph</button>
    </div>

</div> 