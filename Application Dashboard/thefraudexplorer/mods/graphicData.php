<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
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

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-graphic
    {
        padding: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .table-graphdata
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
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
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
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

    .table-th-graphdata-domain
    {
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 150px;
        width: 150px;
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
        font-size: 11px;
    }

    .table-td-graphdata-domain
    {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        vertical-align: middle;
        border: 0px solid gray;
        width: 150px;
        min-width: 150px;
        max-width: 150px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        border-radius: 0px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
        font-size: 11px;
    }

    .not-ruleset
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics-graphic
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
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .btn-default, .btn-default:active, .btn-default:visited 
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

    .pseudolink 
    {
        outline: none;
        cursor: pointer;
        font-size: 11px;
    }

    .pseudolink:hover 
    {
        color: #4B906F;
        text-decoration: none;
    }

    .font-aw-color
    {
        color: #B4BCC2;
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
        $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE trianglesum > 0 GROUP BY agent ORDER BY score DESC";                  
        $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
    }
    else
    {
        $queryEndpointsSQL = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQL_wOSampler = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents WHERE domain NOT LIKE 'thefraudexplorer.com' GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
        $queryEndpointsSQLDomain_wOSampler = "SELECT agent, name, ruleset, domain, flags, totalwords, SUM(pressure) AS pressure, SUM(opportunity) AS opportunity, SUM(rationalization) AS rationalization, (SUM(pressure) + SUM(opportunity) + SUM(rationalization)) / 3 AS score, trianglesum FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, name, ruleset, heartbeat, domain, flags, totalwords, pressure, opportunity, rationalization, (pressure + opportunity + rationalization) AS trianglesum FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS tbl WHERE domain='".$session->domain."' AND ruleset = '".$_SESSION['rulesetScope']."' AND trianglesum > 0 GROUP BY agent ORDER BY score DESC";
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
        echo '<div class="footer-statistics-graphic"><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>There are 0 endpoints with a point in the analytics graph</div>';
        echo '<div class="modal-footer window-footer-graphic">';
        echo '<a class="btn btn-default" style="outline: 0 !important;" href="eventData?nt'.encRijndael("all").'">Access all events</a>';
        echo '<button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>';
        echo '</div>';
        echo '</div>';

        exit();
    }

?>

<div class="div-container">
    <table class="table-graphdata tablesorter" id="tablesights">
        <thead class="table-thead-graphdata">
            <th class="table-th-graphdata-endpoint">ENDPOINT</th>
            <th class="table-th-graphdata-domain">DOMAIN</th>
            <th class="table-th-graphdata-body">WORDS</th>
            <th class="table-th-graphdata-body">PRESS</th>
            <th class="table-th-graphdata-body">OPPRT</th>
            <th class="table-th-graphdata-body">RATNL</th>
            <th class="table-th-graphdata-body">FLAGS</th>
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
                    $endpointEncoded = encRijndael($endpointsFraud['agent']);
                    $totalWordHits = $endpointsFraud['totalwords'];
                    $endpointDomain = $endpointsFraud['domain'];
                    $countPressure = $endpointsFraud['pressure'];
                    $countOpportunity = $endpointsFraud['opportunity'];
                    $countRationalization = $endpointsFraud['rationalization'];
                    $totalEvents = $endpointsFraud['pressure'] + $endpointsFraud['opportunity'] + $endpointsFraud['rationalization'];
                    $countFlags = $endpointsFraud['flags'];
                                    
                    if ($totalEvents != 0) 
                    {
                        echo '<tr class="table-tr-graphdata">';                        
                        echo '<td class="table-td-graphdata-endpoint"><span class="fa fa-user-circle font-icon-color-green">&nbsp;&nbsp;</span>';
                        echo '<span class="pseudolink" onclick="javascript:location.href=\'eventData?nt='.$endpointEncoded.'\'">'.$endpointName.'</span></td>';
                    }
                    else continue;
                        
                    echo '<td class="table-td-graphdata-domain"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$endpointDomain.'</td>';
                    echo '<td class="table-td-graphdata-body">'.$totalWordHits.'</td>';
                    echo '<td class="table-td-graphdata-body"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countPressure.'</td>';
                    echo '<td class="table-td-graphdata-body-opportunity"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countOpportunity.'</td>';
                    echo '<td class="table-td-graphdata-body"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countRationalization.'</td>';
                    echo '<td class="table-td-graphdata-body"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$countFlags.'</td>';
                    echo '<td class="table-td-graphdata-score">'.number_format($totalEvents/3, 1).'</td>';
                    echo '</tr>';
                    
                    $counter++;
                }
                while ($endpointsFraud = mysqli_fetch_assoc($queryEndpoints));
            }

            ?>

        </tbody>
    </table>

    <!-- Pager -->

    <div id="pagerSG" class="pager pagerSG pager-screen footer-statistics-graphic">
        <div style="float:left;">
            <span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>There are <?php echo $counter; ?> endpoints with a point in the scatter graph reflecting fraud triangle events
        </div>
        <div style="float: right;">
            <form>
                <span class="fa fa-fast-backward fa-lg first" id="backward"></span>&nbsp;
                <span class="fa fa-arrow-circle-o-left fa-lg prev" id="left"></span>&nbsp;
                <span class="pagedisplay"></span>&nbsp;
                <span class="fa fa-arrow-circle-o-right fa-lg next" id="right"></span>&nbsp;
                <span class="fa fa-fast-forward fa-lg last" id="forward"></span>&nbsp;&nbsp;
                <select id="pagerSelect" class="pagesize select-styled right" style="display: none;">
                    <option value="4" id="opt4">4</option>
                    <option value="5" id="opt5">5</option>
                    <option value="6" id="opt6">6</option>
                </select>    
            </form>
        </div>
    </div>
        
    <div class="modal-footer window-footer-graphic">
        <a class="btn btn-default" style="outline: 0 !important;" href="eventData?nt=<?php echo encRijndael("all"); ?>">Access all events</a>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
    </div>

</div>

<!-- Tablesorter script -->

<script>

$(function() {

    $("#tablesights")
    .tablesorter({
        sortLocaleCompare: true,
        widgets: ['filter'],
        sortList: [[6,1],[7,1]], 
        widgetOptions : 
        {
            filter_columnFilters : false,
            pager_size: 20
        },
        headers:
        {
            0:
            {
                sorter: false
            }
        }
    })

    .tablesorterPager({
        container: $(".pagerSG"),
        output: '{startRow} to {endRow} ({totalRows})',
        page: 0,
        size: 20
    })

});

</script>