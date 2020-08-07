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
 * Date: 2020-08
 * Revision: v1.4.7-aim
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
        min-width: 140px;
        width: 140px;
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
        min-width: 262px;
        width: 262px;
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
        width: 262px;
        max-width: 262px;
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
        border-right: 2px solid white;
        width: 140px;
        min-width: 140px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
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
        margin: 0px 0px 0px 0px;
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

    .fa-custom-size
    {
        font-size: 15px;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel"><?php echo $workflowName; ?></h4>
</div>

<?php

    /* SQL queries */

    $queryTriggeredWorkflows = "SELECT * from t_wtriggers WHERE name='".$workflowName."'";        
    $result_a = mysqli_query($connection, $queryTriggeredWorkflows);
    $numberOfMatches = mysqli_num_rows($result_a);

    while ($row = mysqli_fetch_assoc($result_a)) 
    {
        $numberOfIds = substr_count($row['ids'], ' ') + 1;
        break;
    }

    if ($numberOfIds == 1 || $numberOfIds == 2 || $numberOfIds == 4) $pageSize = 4;
    else if ($numberOfIds == 3 || $numberOfIds == 6) $pageSize = 6;
    else $pageSize = 5;

?>

<div class="div-container">
    <table class="table-workflow tablesorter" id="tableworkflow">
        <thead class="table-thead-workflow">
            <th class="table-th-workflow-eventtime">&ensp;EVENT DATE</th>
            <th class="table-th-workflow-endpoint">ENDPOINT</th>
            <th class="table-th-workflow-application">APPLICATION</th>
            <th class="table-th-workflow-view">VIEW</th>
        </thead>
        <tbody class="table-tbody-workflow">
        </tbody>
    </table>

    <!-- Pager -->

    <br>

    <div id="pagerWF" class="pager pagerWF pager-screen footer-statistics">
        <div style="float:left;">
            <span class="fa fa-cogs font-aw-color fa-padding"></span>This workflow has <?php echo $numberOfMatches; ?> fraud triangle matches
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
  
    <div id="alertPanelWF"></div>

    <br>

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

<!-- Tablesorter script -->

<script>

$(function() {

    $("#tableworkflow")
    .tablesorter({
        sortLocaleCompare: true,
        widgets: ['filter'],
        widgetOptions : 
        {
            filter_columnFilters : false,
            pager_size: 4
        },
        headers:
        {
            0:
            {
                sorter: false
            },
            1:
            {
                sorter: false
            },
            2:
            {
                sorter: false
            },
            3:
            {
                sorter: false
            }
        }
    })

    .tablesorterPager({
        container: $(".pagerWF"),
        ajaxUrl : 'helpers/flowsProcessing.php?page={page+1}&size=<?php echo $pageSize; ?>&numberofmatches=<?php echo $numberOfMatches; ?>&workflowname=<?php echo $workflowName; ?>',
        ajaxError: null,
        ajaxObject: {
        type: 'GET',
        dataType: 'json'
        },
        ajaxProcessing: function(data) {
        if (data && data.hasOwnProperty('rows')) {
            var indx, r, row, c, d = data.rows,
            total = data.total_rows,
            headers = data.headers,
            headerXref = headers.join(',').split(','),
            rows = [],
            len = d.length;
            for ( r=0; r < len; r++ ) {
            row = []; 
            for ( c in d[r] ) {
                if (typeof(c) === "string") {
                indx = $.inArray( c, headerXref );
                if (indx >= 0) {
                    row[indx] = d[r][c];
                }
                }
            }
            rows.push(row);
            }
            return [ total, rows, headers ];
        }
        },
        processAjaxOnInit: true,
        output: '{startRow} to {endRow} ({totalRows})',
        updateArrows: true,
        page: 0,
        size: 4,
        savePages: true,
        pageReset: 0,
        fixedHeight: false,
        removeRows: false,
        countChildRows: false,
    })
    
    .bind("pagerComplete",function() {

        /* Set CSS column styles */

        $('.table-tbody-workflow td:nth-child(1)').addClass("table-td-workflow-eventtime");
        $('.table-tbody-workflow td:nth-child(2)').addClass("table-td-workflow-endpoint");
        $('.table-tbody-workflow td:nth-child(3)').addClass("table-td-workflow-application");
        $('.table-tbody-workflow td:nth-child(4)').addClass("table-td-workflow-view");

        /* Set pager option */

        $("#pagerSelect option[value='<?php echo $pageSize; ?>']").attr("selected", true);
        $("#opt<?php echo $pageSize; ?>").trigger("change");

    })

});

</script>
