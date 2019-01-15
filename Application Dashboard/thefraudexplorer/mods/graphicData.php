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
 * Date: 2019-02
 * Revision: v1.3.1-ai
 *
 * Description: Code for graphic data
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";

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
        border-collapse: collapse;
        border-radius: 5px !important;
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
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
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
    }
    
    .table-td-graphdata-endpoint
    {
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        border: 0px solid gray;
        width: 120px;
        min-width: 120px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        border-radius: 0px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
    }

    .not-ruleset
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
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
        margin: 0px 0px 15px 0px;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;; 
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Endpoint triangle events</h4>
</div>

<?php

    /* SQL Queries */

    if ($_SESSION['rulesetScope'] == "ALL")
    {
        $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE trianglesum > 0 GROUP BY agent ORDER BY score DESC";                  
        $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
    }
    else
    {
        $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' AND ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
    }
     
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

    if(mysqli_num_rows($queryEndpoints) == 0)
    {
        echo '<div class="div-container">';
        echo '<p class="not-ruleset">There is no data at this time regarding this ruleset, maybe you did not have categorized/organized your endpoints according to the organization chart. Please spend some time clasifying your users and get back later to see their representation.</p>';
        echo '<br><div class="footer-statistics"><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>There are 0 endpoints with a point in the graph</div>';
        echo '<div class="modal-footer window-footer-config">';
        echo '<br>';
        echo '<a class="btn btn-default" style="outline: 0 !important;" href="eventData?endpoint=WVd4cw==">Access all events</a>';
        echo '<button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to graph</button>';
        echo '</div>';
        echo '</div>';

        exit();
    }

?>

<div class="div-container">
    <table class="table-graphdata">
        <thead class="table-thead-graphdata">
            <th class="table-th-graphdata-endpoint">ENDPOINT</th>
            <th class="table-th-graphdata-body">WORDS</th>
            <th class="table-th-graphdata-body">PRESS</th>
            <th class="table-th-graphdata-body">OPPRT</th>
            <th class="table-th-graphdata-body">RATNL</th>
            <th class="table-th-graphdata-body">SCORE</th>
        </thead>
        <tbody class="table-tbody-graphdata">

            <?php

            if($endpointsFraud = mysqli_fetch_assoc($queryEndpoints))
            {
                $counter = 0;
                
                do
                {
                    $endpointName = (strlen($endpointsFraud['agent']) > 12) ? substr($endpointsFraud['agent'], 0, 12) . ' ...' : $endpointsFraud['agent'];
                    $endpointEncoded = base64_encode(base64_encode($endpointsFraud['agent']));
                    $totalWordHits = $endpointsFraud['totalwords'];
                    $countPressure = $endpointsFraud['pressure'];
                    $countOpportunity = $endpointsFraud['opportunity'];
                    $countRationalization = $endpointsFraud['rationalization'];
                    $totalEvents = $endpointsFraud['pressure'] + $endpointsFraud['opportunity'] + $endpointsFraud['rationalization'];
                                    
                    if ($totalEvents != 0) 
                    {
                        echo '<tr class="table-tr-graphdata">';
                        
                        echo '<td class="table-td-graphdata-endpoint"><span class="fa fa-user-circle font-icon-color-green">&nbsp;&nbsp;</span>';
                        echo '<span class="pseudolink" onclick="javascript:location.href=\'eventData?endpoint='.$endpointEncoded.'\'">'.$endpointName.'</span></td>';
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
    
    echo '<br><div class="footer-statistics"><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>There are '.$counter.' endpoints with a point in the graph</div>';
    
    ?>
    
    <div class="modal-footer window-footer-config">
        <br>
        <a class="btn btn-default" style="outline: 0 !important;" href="eventData?endpoint=WVd4cw==">Access all events</a>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to graph</button>
    </div>

</div>