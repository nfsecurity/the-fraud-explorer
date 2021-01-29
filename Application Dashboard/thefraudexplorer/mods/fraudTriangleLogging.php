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
 * Description: Code for logging
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
require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-logging
    {
        padding: 15px 0px 0px 0px;
    }

    .div-container-logging
    {
        margin: 20px;
    }

    .table-logging
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
        border-collapse: collapse;
        border-radius: 5px !important;
        background-color: white !important;
    }

    .table-thead-logging
    {
        display: block;
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        width: 100%;
        height: 45px;
        background-color: white !important;
    }

    .table-th-logging
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: calc(369px / 4);
        width: calc(369px / 4);
        text-align: center;
        padding: 0px 0px 0px 0px;
        height: 45px;
    }
    
    .table-th-logging-date
    {
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 180px;
        width: 180px;
        text-align: left;
        padding: 0px 0px 0px 9px;
        height: 45px;
    }

    .table-th-logging-matches
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 88px;
        width: 88px;
        text-align: center;
        padding: 0px 0px 0px 0px;
        height: 45px;
    }

    .table-tbody-logging
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: auto !important; 
        max-height: 302px !important;
        overflow-y: scroll;
        border-radius: 5px;
        font-size: 11px;
        background-color: white !important;
    }

    .table-tr-logging
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
        font-size: 11px;
    }

    .table-tbody-logging tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-tbody-logging tr:nth-child(even)
    {
        background-color: white !important;
    }

    .table-td-logging
    {
        border: 0px solid gray;
        width: calc(369px / 4);
        min-width: calc(369px / 4);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
        font-size: 11px;
    }
    
    .table-td-logging
    {
        width: calc(369px / 4);
        min-width: calc(369px / 4);
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
    
    .table-td-logging-date
    {
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        border: 0px solid gray;
        width: 180px;
        min-width: 180px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        border-radius: 0px 0px 0px 0px; 
        text-align: left; 
        border-right: 2px solid white;
        font-size: 11px;
    }

    .table-td-logging-matches
    {
        width: 88px;
        min-width: 88px;
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

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics-logging
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

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .font-aw-color
    {
        color: #B4BCC2;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Fraud Triangle logging</h4>
</div>

<?php

    /* Elastic Queries */

    $eventData = extractDataFromAlerterStatus();
    $latestAlerterEvents = json_decode(json_encode($eventData), true);

?>

<div class="div-container-logging">
    <table class="table-logging">
        <thead class="table-thead-logging">
            <th class="table-th-logging-date"><span class="fa fa-calendar font-icon-gray fa-padding"></span>FTA LAUNCH DATE</th>
            <th class="table-th-logging">START</th>
            <th class="table-th-logging">FINISH</th>
            <th class="table-th-logging">TOOK</th>
            <th class="table-th-logging"><span class="fa fa-file-text-o font-icon-gray fa-padding"></span>WORDS</th>
            <th class="table-th-logging-matches">MATCHES</th>
        </thead>
        <tbody class="table-tbody-logging">

            <?php

                $counter = 0;
                
                foreach ($latestAlerterEvents['hits']['hits'] as $result) 
                {
                    $date = date('l, M d, Y', strtotime($result['_source']['@timestamp'])); 
                    $startTime = date('H:i', strtotime($result['_source']['startTime'])); 
                    $endTime = date('H:i', strtotime($result['_source']['@timestamp']));
                    $timeTaken = round(($result['_source']['timeTaken']/60));
                    $timeTaken = ($timeTaken < 10) ? "0{$timeTaken}" : $timeTaken;
                    $wordCount = (isset($result['_source']['wordCount'])) ? $result['_source']['wordCount'] : "0";
                    $matchCount = $result['_source']['matchCount'];
                        
                    echo '<td class="table-td-logging-date">&nbsp;<span class="fa fa-calendar font-icon-gray fa-padding"></span>'.$date.'</td>';
                    echo '<td class="table-td-logging"><span class="fa fa-clock-o font-icon-gray fa-padding"></span>'.$startTime.'</td>';
                    echo '<td class="table-td-logging"><span class="fa fa-clock-o font-icon-gray fa-padding"></span>'.$endTime.'</td>';
                    echo '<td class="table-td-logging">'.$timeTaken.' min</td>';
                    echo '<td class="table-td-logging">'.number_format($wordCount).'</td>';
                    echo '<td class="table-td-logging-matches">'.$matchCount.'</td>';
                    echo '</tr>';
                    
                    $counter++;
                }

            ?>

        </tbody>
    </table>
    
    <?php
    
    echo '<div class="footer-statistics-logging"><span class="fa fa-area-chart font-aw-color">&nbsp;&nbsp;</span>System and audit log - showing the last '.$counter.' Fraud Triangle Analytics AI-NLP/NLU processor executions</div>';
    
    ?>
    
    <div class="modal-footer window-footer-logging">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
    </div>

</div>