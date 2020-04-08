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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for view workflows
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
include "../lbs/cryptography.php";
include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";
require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

$workflowName = filter(decRijndael($_GET['ed']));

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

    .table-workflow
    {
        font-family: 'FFont', sans-serif; font-size:10px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
        border-collapse: collapse;
        border-radius: 5px;
    }

    .table-thead-workflow
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

    .table-th-workflow-eventtime
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 110px;
        width: 110px;
        text-align: left;
        padding: 0px 0px 0px 0px;
        height: 45px;
    }

    .table-th-workflow-endpoint
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 100px;
        width: 100px;
        text-align: left;
        padding: 0px 0px 0px 7px;
        height: 45px;
    }

    .table-th-workflow-application
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 292px;
        width: 292px;
        text-align: left;
        padding: 0px 0px 0px 7px;
        height: 45px;
    }

    .table-th-workflow-view
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 30px;
        width: 30px;
        text-align: left;
        padding: 0px 0px 0px 7px;
        height: 45px;
    }

    .table-tbody-workflow
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: auto !important; 
        max-height: 124px !important;
        overflow-y: scroll;
        border-radius: 5px;
    }

    .table-tr-workflow
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-workflow tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-td-workflow-endpoint
    {
        border: 0px solid gray;
        width: 100px;
        max-width: 100px;
        height: 30px;
        min-height: 30px;
        padding: 0px 5px 0px 5px;
        text-align: left;
        border-right: 2px solid white;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .table-td-workflow-application
    {
        border: 0px solid gray;
        width: 292px;
        max-width: 292px;
        height: 30px;
        min-height: 30px;
        padding: 0px 5px 0px 5px;
        text-align: left;
        border-right: 2px solid white;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    
    .table-td-workflow-view
    {
        border: 0px solid gray;
        width: 45px;
        min-width: 45px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 0px solid white;
    }
    
    .table-td-workflow-eventtime
    {
        border: 0px solid gray;
        width: 110px;
        min-width: 110px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 0px solid white;
        font-family: 'FFont', sans-serif; font-size: 10px;
    }

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
        margin: 0px 0px 15px 0px;
        text-align: center;
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
    <h4 class="modal-title window-title" id="myModalLabel"><?php echo $workflowName; ?></h4>
</div>

<?php

    /* Elasticsearch variables */

    $client = Elasticsearch\ClientBuilder::create()->build();
    $configFile = parse_ini_file("../config.ini");
    $ESalerterIndex = $configFile['es_alerter_index'];

    /* SQL queries */

    $queryTriggeredWorkflows = "SELECT * from t_wtriggers WHERE name='".$workflowName."'";        
    $result_a = mysqli_query($connection, $queryTriggeredWorkflows);
    $numberOfMatches = mysqli_num_rows($result_a);

?>

<div class="div-container">
    <table class="table-workflow">
        <thead class="table-thead-workflow">
            <th class="table-th-workflow-eventtime">&ensp;EVENT DATE</th>
            <th class="table-th-workflow-endpoint">ENDPOINT</th>
            <th class="table-th-workflow-application">APPLICATION</th>
            <th class="table-th-workflow-view">VIEW</th>
        </thead>
        <tbody class="table-tbody-workflow">

            <?php

            $agrupation = 0;

            if ($row_a = mysqli_fetch_array($result_a))
            {
                do
                {           
                    $alertIDs = explode(" ", $row_a['ids']);
                    
                    foreach ($alertIDs as $alert)
                    {
                        if (($agrupation % 2) == 0) $coloredClass = "font-icon-color-green";
                        else $coloredClass = "font-icon-gray";

                        $alertDocument = getAlertIdData($alert, $ESalerterIndex, "AlertEvent");
                        $datetime = $alertDocument['hits']['hits'][0]['_source']['eventTime'];
                        preg_match('/(.*) (.*),/', $datetime, $eventTime);
                        $agent = $alertDocument['hits']['hits'][0]['_source']['agentId'];
                        preg_match('/([a-z0-9]*)_/', $agent, $endpoint);
                        $application = decRijndael($alertDocument['hits']['hits'][0]['_source']['windowTitle']);

                        echo '<tr class="table-tr-workflow">';
                        echo '<td class="table-td-workflow-eventtime" style="border-right: 2px solid white;">'.$eventTime[1] . " ". $eventTime[2].'</td>';
                        echo '<td class="table-td-workflow-endpoint"><span class="fa fa-user-circle font-icon-color-green fa-padding"></span>'.$endpoint[1].'</td>';
                        echo '<td class="table-td-workflow-application"><span class="fa fa-window-maximize font-icon-gray fa-padding"></span>'.$application.'</td>';
                        echo '<td class="table-td-workflow-view"><a id="viewAlert" href="#" onclick="showAlert(this.id, \''.$alert.'\')"><span class="fa fa-diamond fa-lg '.$coloredClass.'"></span></a></td>';
                        echo '</tr>';
                    }
                    $agrupation++;
                }
                while ($row_a = mysqli_fetch_array($result_a));
            }    
            
            ?>

        </tbody>
    </table>

    <div id="alertPanelWF"></div>

    <?php
    
    echo '<br><div class="footer-statistics"><span class="fa fa-cogs font-aw-color fa-padding"></span>There are '.$numberOfMatches.' matches in the workflow defined by you that are matching the above alerts</div>';
    
    ?>

    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Ok I got it!</button>
    </div>
</div> 

<!-- Script for alert view -->

<script>

function showAlert(clicked_id, alertid)
{
    if (clicked_id == "viewAlert")
    {
        $("#alertPanelWF").load("mods/alertAIPhrases.php?id=" + alertid);
    }
 }

</script>