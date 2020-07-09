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
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Code for Advanced Reports
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

$_SESSION['processingStatus'] = "notstarted";

?>

<!-- Date range picker -->

<link rel="stylesheet" type="text/css" href="../css/datepicker.css" />

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .input-value-text
    {
        width: 100%; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .input-value-text-date
    {
        width: 127px; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container-reports
    {
        margin: 20px;
    }

    .container-status-reports
    {
        display: block;
    }

    .container-status-reports::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .status-align-left-reports
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:left;
        margin: 10px 0px 0px 0px;
    }

    .status-align-right-reports
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:right;
        margin: 10px 0px 0px 0px;
    }
    
    .select-option-typereport-styled
    {
        margin-right: 0px;
        min-height: 30px !important;
        max-height: 30px !important;
        padding: 8px 0px 8px 10px;
        line-height: 11.6px;
        border: 1px solid #ccc;
        color: #757575;
    }

    .select-option-typereport-styled .list
    {
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        max-height: 200px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }
    
    .master-container-reports
    {
        width: 100%; 
        height: 105px;
    }
    
    .left-container-reports
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .right-container-reports
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .select-ruleset-styled
    {
        margin-right: 0px;
        min-height: 30px !important;
        max-height: 30px !important;
        padding: 8px 0px 8px 10px;
        line-height: 11.6px;
        border: 1px solid #ccc;
        color: #757575;
    }

    .select-ruleset-styled .list
    {
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        max-height: 200px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    @keyframes blink 
    { 
        50% 
        { 
            border: 1px solid white;
        } 
    }

    .blink-check
    {
        -webkit-animation: blink .1s step-end 6 alternate;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Advanced Reports</h4>
</div>

<div class="div-container-reports">
    <form id="formReport" name="formReport" method="post" action="mods/buildAdvancedReport">

        <div class="master-container-reports">
            <div class="left-container-reports">              
                
                <p class="title-config">Report type</p><br>
                <select class="select-option-typereport-styled wide" name="typereport" id="typereport" onchange="checklistReportType()">
                    <option value="byendpoint">By endpoint</option>
                    <?php if($session->domain == "all") echo '<option value="bydomain">By domain</option>'; ?>
                    <option value="allendpoints" selected="selected">All endpoints & domains</option>
                </select>
                <div style="line-height:60px; border: 1px solid white;"><br></div>
                <input type="text" name="typeinput" disabled="disabled" id="typeinput" autocomplete="off" placeholder="endpoint, domain" class="input-value-text" style="text-indent:5px;">
                
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Date range</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="daterangefrom" id="daterangefrom" autocomplete="off" value="<?php $date = new DateTime('7 days ago'); echo $date->format('Y/m/d'); ?>" class="input-value-text-date start-date" style="text-indent:5px;" data-toggle="datepicker"> to
                <input type="text" name="daterangeto" id="daterangeto" autocomplete="off" value="<?php $date = new DateTime(); echo $date->format('Y/m/d'); ?>" class="input-value-text-date end-date" style="text-indent:5px;" data-toggle="datepicker">
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm" id="button-all-date-range" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" onchange="checkboxAllDateRange()" id="checkbox-all-date-range" name="alldaterange" value="alldaterange" autocomplete="off">I want all date range
                    </label>
                </div>

            </div>
        </div>

        <div class="container-status-reports">
            <div class="status-align-left-reports">
                
                <?php

                echo "Select the report type";

                ?>
                
            </div>
            <div class="status-align-right-reports">
               
                <?php
                        
                echo "Select the desired date range";
                
                ?>
                
            </div>
        </div>
        
        <div class="master-container-reports">
            <div class="left-container-reports">              
                
                <p class="title-config">Filter by Fraud Triangle Vertices</p><br>
                <div style="line-height:10px; border: 1px solid white;"><br></div>
                            
                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-pressure" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" onchange="checkboxPressureButton()" id="checkbox-pressure" name="pressure" value="pressure" autocomplete="off" checked>Pressure
                    </label>
                </div>

                <div style="line-height:8px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-opportunity" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" onchange="checkboxOpportunityButton()" id="checkbox-opportunity" name="opportunity" value="opportunity" autocomplete="off" checked>Opportunity
                    </label>
                </div>

                <div style="line-height:8px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-rationalization" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" onchange="checkboxRationalizationButton()" id="checkbox-rationalization" name="rationalization" value="rationalization" autocomplete="off" checked>Rationalization
                    </label>
                </div>
              
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Application</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="applications" disabled="disabled" id="applications" autocomplete="off" placeholder="Microsoft Teams" class="input-value-text" style="text-indent:5px;">
                <div style="line-height:8px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-all-applications" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" onchange="checkboxAllApplicationsButton()" id="checkbox-all-applications" name="allapplications" value="allapplications" autocomplete="off" checked>I want all applications
                    </label>
                </div>           
                    
            </div>
        </div>

        <div class="container-status-reports">
            

            <div class="status-align-right-reports">
               
                <?php
                             
                echo "Filter by corporate applications";

                ?>
                
            </div>
        </div>

        <div class="master-container-reports">
            <div class="left-container-reports">              
                
                <p class="title-config">Business units</p><br>
                
                <select class="select-ruleset-styled wide" name="ruleset" id="ruleset">
                    
                    <?php

                        $configFile = parse_ini_file("../config.ini");
                        $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                        $GLOBALS['listRuleset'] = null;

                        foreach ($jsonFT['dictionary'] as $ruleset => $value)
                        {
                            if ($ruleset == "BASELINE") continue;
                            else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                        }

                    ?>

                </select>
                <div style="line-height:60px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-all-departments" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" name="alldepartments" onchange="checkboxAllDepartmentsButton()" id="checkbox-all-departments" value="alldepartments" autocomplete="off" checked>I want all departments
                    </label>
                </div> 
                
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Filter by phrase match</p><br>
                <input type="text" name="excluded" disabled="disabled" id="excluded" autocomplete="off" placeholder="me parece injusto, por una buena causa" class="input-value-text" style="text-indent:5px;">   
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" id="button-all-phrases" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important; font-family: Verdana, sans-serif; font-size: 12px !important;">
                        <input type="checkbox" name="allphrases" onchange="checkboxAllPhrasesButton()" id="checkbox-all-phrases" value="allphrases" autocomplete="off" checked>I want all phrases
                    </label>
                </div>

            </div>
        </div>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
            
            <?php    
            
                echo '<button type="button" id="btn-excel" class="btn btn-success setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Generating, please wait" style="outline: 0 !important;">';
                echo 'Make report';
                echo '</button>';
        
            ?>
        
        </div>

    </form>
</div>

<!-- Date picker -->

<script type="text/javascript" src="../js/datepicker.js"></script>

<script>

$(function() {
    var $startDate = $('.start-date');
    var $endDate = $('.end-date');

    $('[data-toggle="datepicker"]').datepicker({
        autoHide: true,
        zIndex: 2048,
        format: 'yyyy/mm/dd'
    });

    $startDate.datepicker({
        autoHide: true,
    });
      
    $endDate.datepicker({
        autoHide: true,
        startDate: $startDate.datepicker('getDate'),
    });

    $startDate.on('change', function () {
        $endDate.datepicker('setStartDate', $startDate.datepicker('getDate'));
    });
});

</script>

<!-- Button loading -->

<script>

var $btn;

$("#btn-excel").click(function() {

    var reporttype = $("#typereport option:selected").val();
    var reporttext = $('#typeinput').val();
    var datefrom = $("#daterangefrom").val();
    var dateto = $("#daterangeto").val();
    var applications = $('#applications').val();
    var excluded = $('#excluded').val();
    var checkboxdate = document.getElementById('checkbox-all-date-range');
    var checkboxapplications = document.getElementById('checkbox-all-applications');
    var checkboxphrases = document.getElementById('checkbox-all-phrases');
    var allvalues = new Array(reporttext, datefrom, dateto, applications, excluded);

    if (reporttype != "allendpoints" || checkboxdate.checked === false || checkboxapplications.checked === false || checkboxphrases.checked === false)
    {
        var reporttextfield = "#typeinput,";
        var datefromfield = "#daterangefrom,";
        var datetofield = "#daterangeto,";
        var applicationsfield = "#applications,";
        var excludedfield = "#excluded,";
        var finalfield = "";
        var continueAnyway = true;

        if (allvalues[0] == "" && reporttype != "allendpoints") 
        {
            finalfield = reporttextfield;
            continueAnyway = false;
        }
        if (allvalues[1] == "" && checkboxdate.checked === false) 
        {
            finalfield = finalfield + datefromfield;
            continueAnyway = false;
        }
        if (allvalues[2] == "" && checkboxdate.checked === false) 
        {
            finalfield = finalfield + datetofield;
            continueAnyway = false;
        }
        if (allvalues[3] == "" && checkboxapplications.checked === false) 
        {
            finalfield = finalfield + applicationsfield;
            continueAnyway = false;
        }
        if (allvalues[4] == "" && checkboxphrases.checked === false) 
        {
            finalfield = finalfield + excludedfield;
            continueAnyway = false;
        }
        
        if (continueAnyway == true) $('#formReport').submit();
        else
        {
            finalfield = finalfield.replace(/(,$)/g, "");

            setTimeout("$('"+finalfield+"').addClass('blink-check');", 100);
            setTimeout("$('"+finalfield+"').removeClass('blink-check');", 1000);

            return;
        }
    }
    else
    {
        $('#formReport').submit();
    }

    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

function getstatus()
{
    $.ajax({
        url: "../helpers/processingStatus.php",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#statusmessage').html(data.message);
            if(data.status=="pending")
              setTimeout('getstatus()', 1000);
            else
                $btn.button('reset');
        }
    });
}

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Checkbox background changer -->

<script>

    function checklistReportType()
    {
        var select = $("#typereport option:selected").val();
        
        if (select == "allendpoints") $('#typeinput').attr("disabled", "disabled");
        else $('#typeinput').removeAttr("disabled"); 
    }

    function checkboxAllDateRange()
    {
        var checkbox = document.getElementById('checkbox-all-date-range');
        var checkboxGeneral = document.getElementById('button-all-date-range');

        if(checkbox.checked === true)
        {
            $('#daterangefrom, #daterangeto').attr("disabled", "disabled");
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            $('#daterangefrom, #daterangeto').removeAttr("disabled");
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxPressureButton()
    {
        var checkbox = document.getElementById('checkbox-pressure');
        var checkboxGeneral = document.getElementById('button-pressure');

        if(checkbox.checked === true)
        {
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxOpportunityButton()
    {
        var checkbox = document.getElementById('checkbox-opportunity');
        var checkboxGeneral = document.getElementById('button-opportunity');

        if(checkbox.checked === true)
        {
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxRationalizationButton()
    {
        var checkbox = document.getElementById('checkbox-rationalization');
        var checkboxGeneral = document.getElementById('button-rationalization');

        if(checkbox.checked === true)
        {
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxAllApplicationsButton()
    {
        var checkbox = document.getElementById('checkbox-all-applications');
        var checkboxGeneral = document.getElementById('button-all-applications');

        if(checkbox.checked === true)
        {
            $('#applications').attr("disabled", "disabled");
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            $('#applications').removeAttr("disabled");
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxAllDepartmentsButton()
    {
        var checkbox = document.getElementById('checkbox-all-departments');
        var checkboxGeneral = document.getElementById('button-all-departments');

        if(checkbox.checked === true)
        {
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            checkboxGeneral.style.background = "white";
        }
    }

    function checkboxAllPhrasesButton()
    {
        var checkbox = document.getElementById('checkbox-all-phrases');
        var checkboxGeneral = document.getElementById('button-all-phrases');

        if(checkbox.checked === true)
        {
            $('#excluded').attr("disabled", "disabled");
            checkboxGeneral.style.background = "#E0E0E0";
        }
        else
        {
            $('#excluded').removeAttr("disabled");
            checkboxGeneral.style.background = "white";
        }
    }

</script>