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
 * Description: Code for view audit trail
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

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config-audit
    {
        padding: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .table-audit
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
        border-collapse: collapse;
        border-radius: 5px;
    }

    .table-thead-audit
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

    .table-th-audit-eventtime
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
        padding: 0px 0px 0px 10px;
        height: 45px;
    }

    .table-th-audit-user
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
        padding: 0px 0px 0px 10px;
        height: 45px;
    }

    .table-th-audit-ipaddress
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
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-th-audit-browser
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 25px;
        width: 25px;
        text-align: left;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-th-audit-module
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
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-th-audit-action
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: 385px;
        width: 385px;
        text-align: left;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-tbody-audit
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: auto !important; 
        max-height: 241px !important;
        overflow-y: scroll;
        border-radius: 5px;
    }

    .table-tr-audit
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-audit tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-tbody-audit tr:nth-child(even)
    {
        background-color: white !important;
    }

    .table-td-audit-eventtime
    {
        border: 0px solid gray;
        border-right: 2px solid white;
        width: 110px;
        min-width: 110px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: left;
        border-right: 0px solid white;
        font-family: 'FFont', sans-serif; font-size: 11px;
        text-align: center;
        cursor: default;
    }

    .table-td-audit-user
    {
        border: 0px solid gray;
        width: 110px;
        max-width: 110px;
        height: 30px;
        min-height: 30px;
        padding: 0px 5px 0px 10px;
        text-align: left;
        border-right: 2px solid white;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        background-color: #EDEDED;
        cursor: default;
    }

    .table-td-audit-ipaddress
    {
        border: 0px solid gray;
        width: 110px;
        max-width: 110px;
        height: 30px;
        min-height: 30px;
        padding: 0px 5px 0px 5px;
        text-align: left;
        border-right: 2px solid white;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        cursor: default;
    }
    
    .table-td-audit-browser
    {
        border: 0px solid gray;
        width: 25px;
        min-width: 25px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        border-right: 2px solid white;
        cursor: default;
    }

    .table-td-audit-module
    {
        border: 0px solid gray;
        width: 100px;
        min-width: 100px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        border-right: 2px solid white;
        cursor: default;
    }

    .table-td-audit-action
    {
        border: 0px solid gray;
        width: 385px;
        min-width: 385px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        border-right: 0px solid white;
        cursor: default;
    }

    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics-audit
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
        margin: 15px 0px 15px 0px;
        text-align: center;
        font-family: 'FFont', sans-serif; font-size: 11px;
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

    .mightOverflow
    {
        width: 360px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: default;
    }

    .tooltip .tooltip-inner 
    {
        background-color: #E8E9E8; 
        color: #666666;
        padding: 10px;
        max-width: 500px !important;
        border: 2px solid #9A9A9A;
    } 

    .tooltip.top .tooltip-arrow 
    {
        border-top-color: #9A9A9A;
    }

    .tooltip.in
    {
        opacity:1 !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Audit & Movement Trails </h4>
</div>

<?php

    /* Global data variables */

    if ($session->username == "admin")
    {
        $urlAudit = "http://127.0.0.1:9200/tfe-audit-trail/_count";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAudit);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAudit = curl_exec($ch);
        curl_close($ch);
    }
    else
    {
        $urlAudit = 'http://127.0.0.1:9200/tfe-audit-trail/_count';
        $params = '{ "query": { "bool": { "should" : [ { "term" : { "eventUser" : "'.$session->username.'" } }, { "term" : { "userDomain" : "thefraudexplorer.com" } } ] } } }';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAudit);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_ENCODING, ''); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAudit = curl_exec($ch);
        curl_close($ch);
    }

    $resultAudit = json_decode($resultAudit, true);

    if (array_key_exists('count', $resultAudit)) $totalAuditEvents = $resultAudit['count'];
    else $totalAuditEvents = "0";

    $pageSize = 10;

?>

<div class="div-container">
    <table class="table-audit tablesorter" id="tableaudit">
        <thead class="table-thead-audit">
            <th class="table-th-audit-eventtime"><span class="fa fa-calendar font-icon-gray fa-padding"></span>DATE</th>
            <th class="table-th-audit-user">USER</th>
            <th class="table-th-audit-ipaddress">IPADDRESS</th>
            <th class="table-th-audit-browser"><span class="fa fa-id-card-o font-icon-gray fa-padding"></span></th>
            <th class="table-th-audit-module">MODULE</th>
            <th class="table-th-audit-action"><span class="fa fa-hand-pointer-o font-icon-gray fa-padding"></span>ACTION EXECUTED</th>
        </thead>
        <tbody class="table-tbody-audit">
        </tbody>
    </table>

    <!-- Pager -->

    <div id="pagerAT" class="pager pagerAT footer-statistics-audit">
        <div style="float:left;">
            <span class="fa fa-cogs font-aw-color fa-padding"></span>There are <?php echo $totalAuditEvents; ?> in the audit trail eventlog representing all the movements in this platform
        </div>
        <div style="float: right;">
            <form>
                <span class="fa fa-fast-backward fa-lg first" id="backward"></span>&nbsp;
                <span class="fa fa-arrow-circle-o-left fa-lg prev" id="left"></span>&nbsp;
                <span class="pagedisplay"></span>&nbsp;
                <span class="fa fa-arrow-circle-o-right fa-lg next" id="right"></span>&nbsp;
                <span class="fa fa-fast-forward fa-lg last" id="forward"></span>&nbsp;&nbsp;
                <select id="pagerSelect" class="pagesize select-styled right" style="display: none;">
                    <option value="10" id="opt10">10</option>
                    <option value="11" id="opt11">11</option>
                    <option value="12" id="opt12">12</option>
                </select>    
            </form>
        </div>
    </div>

    <div class="modal-footer window-footer-config-audit">
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Ok I got it!</button>
    </div>
</div>

<!-- Tablesorter script -->

<script>

$(function() {

    $("#tableaudit")
    .tablesorter({
        sortLocaleCompare: true,
        widgets: ['filter'],
        widgetOptions : 
        {
            filter_columnFilters : false,
            pager_size: 10
        },
        headers:
        {
            0:
            {
                sorter: false
            },
            3:
            {
                sorter: false
            },
            5:
            {
                sorter: false
            }
        }
    })

    .tablesorterPager({
        container: $(".pagerAT"),
        ajaxUrl : 'helpers/auditProcessing.php?page={page+1}&{sortList:col}&size=<?php echo $pageSize; ?>&numberofmatches=<?php echo $totalAuditEvents; ?>&view=<?php echo $session->username; ?>',
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

        $('.table-tbody-audit td:nth-child(1)').addClass("table-td-audit-eventtime");
        $('.table-tbody-audit td:nth-child(2)').addClass("table-td-audit-user");
        $('.table-tbody-audit td:nth-child(3)').addClass("table-td-audit-ipaddress");
        $('.table-tbody-audit td:nth-child(4)').addClass("table-td-audit-browser");
        $('.table-tbody-audit td:nth-child(5)').addClass("table-td-audit-module");
        $('.table-tbody-audit td:nth-child(6)').addClass("table-td-audit-action");

        /* Set pager option */

        $("#pagerSelect option[value='<?php echo $pageSize; ?>']").attr("selected", true);
        $("#opt<?php echo $pageSize; ?>").trigger("change");

        /* Set tooltip */

        $.fn.tooltipOnOverflow = function(options) {
            $(this).on("mouseenter", function() {
            if (this.offsetWidth < this.scrollWidth) {
                options = options || { placement: "auto"}
                options.title = $(this).text();
            $(this).tooltip(options);
            $(this).tooltip("show");
            } else {
            if ($(this).data("bs.tooltip")) {
                $tooltip.tooltip("hide");
                $tooltip.removeData("bs.tooltip");
            }
            }
        });
        };

        $('.mightOverflow').tooltipOnOverflow();

    })

});

</script>