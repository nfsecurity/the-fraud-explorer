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
 * Date: 2020-01
 * Revision: v1.4.1-ai
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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

$_SESSION['processingStatus'] = "notstarted";

?>

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
}

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Advanced Reports</h4>
</div>

<div class="div-container-reports">
    <form id="formBuild" name="formBuild" method="post" action="mods/buildAdvancedReport">

        <div class="master-container-reports">
            <div class="left-container-reports">              
                
                <p class="title-config">Report type</p><br>
                <select class="select-option-typereport-styled wide" name="typereport" id="typereport" onChange="changeBehavior();">
                    <option value="byendpoint">By endpoint</option>
                    <option value="bydomain">By domain</option>
                    <option value="allendpoints" selected="selected">All endpoints</option>
                    <option value="alldomains">All domains</option>
                </select>
                <div style="line-height:60px; border: 1px solid white;"><br></div>
                <input type="text" name="typeinput" id="typeinput" autocomplete="off" placeholder="endpoint, domain" class="input-value-text" style="text-indent:5px;" disabled>
                
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Date range</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="daterangefrom" id="daterangefrom" autocomplete="off" placeholder="YYYY/MM/DD" class="input-value-text-date" style="text-indent:5px;"> to
                <input type="text" name="daterangeto" id="daterangeto" autocomplete="off" placeholder="YYYY/MM/DD" class="input-value-text-date" style="text-indent:5px;">
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="alldaterange" value="alldaterange" autocomplete="off" checked>I want all date range
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
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="pressure" value="pressure" autocomplete="off" checked>Pressure
                    </label>
                </div>

                <div style="line-height:8px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="opportunity" value="opportunity" autocomplete="off" checked>Opportunity
                    </label>
                </div>

                <div style="line-height:8px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="rationalization" value="rationalization" autocomplete="off" checked>Rationalization
                    </label>
                </div>
              
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Applications</p><br><br>
                <div style="line-height:9px; border: 1px solid white;"><br></div>
                <input type="text" name="applications" id="applications" autocomplete="off" placeholder="Teams, Skype, Outlook" class="input-value-text" style="text-indent:5px;">
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="allapplications" value="allapplications" autocomplete="off" checked>I want all applications
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
                
                <p class="title-config">Departments</p><br>
                
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
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="alldepartments" value="alldepartments" autocomplete="off" checked>I want all departments
                    </label>
                </div> 
                
            </div>
            <div class="right-container-reports">
                   
                <p class="title-config">Filter by phrase match</p><br>
                <input type="text" name="excluded" id="excluded" autocomplete="off" placeholder="me parece injusto, por una buena causa" class="input-value-text" style="text-indent:5px;">   
                <div style="line-height:6px; border: 1px solid white;"><br></div>

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="btn btn-default btn-sm active" style="width: 100%; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="allphrases" value="allphrases" autocomplete="off" checked>I want all phrases
                    </label>
                </div>

            </div>
        </div>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to back</button>
            
            <?php    
            
            if ($session->username != "admin") echo '<input type="submit" class="btn btn-success setup disabled" value="Make report" style="outline: 0 !important;">';
            else 
            {
                echo '<button type="submit" id="btn-excel" class="btn btn-success setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Generating, please wait" style="outline: 0 !important;">';
                echo 'Make report';
                echo '</button>';
            }
            ?>
        
        </div>

    </form>
</div>

<!-- Button loading -->

<script>

var $btn;

$("#btn-excel").click(function() {
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

<!-- Disable or enable items -->

<script type="text/javascript">
    function changeBehavior()
    {
        if (document.getElementById("typereport").value == "allendpoints" || document.getElementById("typereport").value == "alldomains") 
        {
            document.getElementById("typeinput").disabled = true;
        }
        else
        {
            document.getElementById("typeinput").disabled = false;
        }
    }
</script> 