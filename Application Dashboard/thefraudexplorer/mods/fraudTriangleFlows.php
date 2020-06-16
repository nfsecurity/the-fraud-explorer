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
 * Date: 2020-06
 * Revision: v1.4.5-aim
 *
 * Description: Code for Fraud Tree
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

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .master-container-workflows
    {
        width: 100%; 
    }

    .div-container-flow
    {
        margin: 20px;
    }
    
    .font-icon-color-green
    {
        color: #4B906F;
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
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .build-workflows-container
    {
        padding: 22px 15px 15px 15px;
        border: 0px solid gray;
        border-radius: 3px;
        background: #FAFAFA;
        z-index: -1;
    }

    .select-option-styled-rulesflow, .select-option-styled-verticeflow
    {
        width: 165px;
        height: 30px;
        line-height: 30px;
        position: relative;
    }

    .select-option-styled-rulesflow .list, .select-option-styled-verticeflow .list
    {
        width: 165px;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .select-option-styled-operator
    {
        width: 70px;
        height: 30px;
        line-height: 30px;
    }

    .select-option-styled-operator .list
    {
        width: 70px;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .select-option-styled-interval
    {
        width: 100%;
        height: 30px;
        line-height: 30px;
    }

    .select-option-styled-interval .list
    {
        width: 100%;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .input-value-text-endpoint, .input-value-text-application, .input-value-text-phrase
    {
        width: 150px; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
        margin-left: 5px !important;
    }

    .input-value-text-phrase
    {
        margin-right: 5px;
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

    .title-workflows
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        text-align: left;
        margin-bottom: 8px;
        margin-left: 3px;
    }

    .flow-table
    {
        display: table;
        margin-bottom: 8px;
    }

    .flow-cell
    {
        display: table-cell; 
        vertical-align: middle;   
    }
    
    .left-container-flow
    {
        width: calc(33.3% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }

    .middle-container-flow
    {
        width: calc(33.3% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
        margin-left: 8px;
    }
    
    .right-container-flow
    {
        width: calc(33.3% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .container-status-flow
    {
        display: block;
    }

    .container-status-flow::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .status-align-left-flow
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: calc(33.3% - 5px); 
        height: 33px;
        float:left;
        margin: 10px 0px 0px 0px;
    }

    .status-align-middle-flow
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: calc(33.3% - 5px); 
        height: 33px;
        float:left;
        margin: 10px 0px 0px 8px;
    }

    .status-align-right-flow
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: calc(33.3% - 5px); 
        height: 33px;
        float:right;
        margin: 10px 0px 0px 0px;
    }

    .table-flows
    {
        font-family: 'FFont', sans-serif; font-size: 10px;
        border: 1px solid #C9C9C9;
        width: 100%;
        border-collapse: separate !important;
        border-radius: 5px 5px 5px 5px;
    }

    .table-flows tbody 
    {
        background-image: none;
        background-color: white;
    }

    .table-thead-flows
    {
        display: block;
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        border-radius: 5px 5px 0px 0px;
        width: 100%;
        height: 30px;
    }

    .table-th-flows-name
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        min-width: 215px;
        width: 215px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-th-flows-workflow
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        min-width: 388px;
        width: 388px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-th-flows-interval
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        max-width: 80px;
        width: 80px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-th-flows-custodian
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        min-width: 200px;
        width: 200px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-th-flows-trigers
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        max-width: 45px;
        width: 45px;
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-tbody-flows
    {
        display: block;
        width: 100%;
        height: 60px !important;
        max-height: 60px !important;
        overflow-y: scroll;
        border-collapse: separate !important;
        border-radius: 0px 0px 5px 5px;
    }

    .table-tr-flows
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-flows tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }
    
    .table-tbody-flows tr:nth-child(even)
    {
        background: #FFFFFF;
    }

    .table-td-flows-name
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: 215px;
        min-width: 215px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-td-flows-workflow
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: 388px;
        max-width: 388px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mightOverflow
    {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-td-flows-interval
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: 80px;
        max-width: 80px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-td-flows-custodian
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: 200px;
        min-width: 200px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-td-flows-triggers
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: 45px;
        max-width: 45px;
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .workflowCheck input[type="checkbox"] 
    {
	    display: none;
    }

    .workflowCheck input[type="checkbox"] + label span 
    {
        display: inline-block;
        width: 13px;
        height: 13px;
        margin: -1px 4px 0 0;
        vertical-align: middle;
        background: url(../images/checkbox.svg);
        background-size: cover;
        cursor: pointer;
    }

    .workflowCheck input[type="checkbox"]:checked + label span 
    {
	    background: url(../images/checkboxChecked.svg);
	    background-size: cover;
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
    <h4 class="modal-title window-title" id="myModalLabel">Fraud Triangle Alerting Workflows</h4>
</div>

<div class="div-container-flow">

    <form id="formWorkflow" name="formWorkflow" method="post" action="mods/processWorkflows">

        <div class="master-container-workflows">

            <p class="title-workflows">Please add or delete fraud triangle rules: specify which vertice, business unit, what and who</p>
            
            <div class="build-workflows-container">

                <table class="form-table" id="customFields">
	                <tr valign="top">
		                <th scope="row"><label for="customFieldName"></label></th>
		                <td>

                            <div class="flow-table">

                                <div class="flow-cell">
                                    <select class="select-option-styled-rulesflow" name="rulesetFlow[]" id="ruleset-flow">

                                        <?php

                                            $configFile = parse_ini_file("../config.ini");
                                            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);

                                            foreach ($jsonFT['dictionary'] as $ruleset => $value)
                                            {
                                                if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected="selected">ALL DEPARTMENTS</option>';
                                                else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                                            }

                                        ?>

                                    </select>
                                </div>

                                <div class="flow-cell">
                                    <select class="select-option-styled-verticeflow" name="fraudverticeFlow[]" id="fraudvertice-flow">
                                        <option selected="selected">ALL VERTICES</option>
                                        <option>PRESSURE</option>
                                        <option>OPPORTUNITY</option>
                                        <option>RATIONALIZATION</option>
                                    </select>
                                </div>

                                <div class="flow-cell">
                                    <input type="text" class="code input-value-text-endpoint" id="endpointsFlow" name="endpointsFlow[]" value="" placeholder="All Endpoints" />
                                    <input type="text" class="code input-value-text-application" id="applicationsFlow" name="applicationsFlow[]" value="" placeholder="All Applications" />
                                    <input type="text" class="code input-value-text-phrase" id="phrasesFlow" name="phrasesFlow[]" value="" placeholder="All Phrases" />
                                </div>

                                <div class="flow-cell">
                                    <select class="select-option-styled-operator" name="fraudOperator[]" id="fraudOperator-flow">
                                        <option>AND</option>
                                        <option selected="selected">END</option>
                                    </select>
                                </div>

                                <div class="flow-cell">
                                    <a href="javascript:void(0);" class="addCF">&nbsp;&nbsp;<i class="fa fa-plus fa-lg" aria-hidden="true"></i></a>
                                </div>

                            </div>
		                </td>
	                </tr>
                </table>

            </div>

        </div>

        <div class="master-container-workflows">
            <div class="left-container-flow">              
                    
                <p class="title-config">Workflow identification</p><br>
                <input type="text" class="code input-value-text" id="workflowName" name="workflowName" value="" placeholder="Workflow name" />
                    
            </div>

            <div class="middle-container-flow">              

                <div style="width: 49%; display: inline-block; vertical-align: top;">

                    <p class="title-config">Workflow interval</p><br>
                    <select class="select-option-styled-interval wide" name="workflowInterval" id="workflowInterval">
                        <option value="1">1 day</option>
                        <option value="8">8 days</option>
                        <option value="15">15 days</option>
                        <option value="30">1 month</option>
                        <option value="0" selected="selected">Without interval</option>
                    </select>  

                </div>

                <div style="width: 49%; display: inline-block; vertical-align: top;">

                    <p class="title-config">Workflow domain</p><br>
                    <input type="text" class="code input-value-text" id="workflowDomain" name="workflowDomain" value="" placeholder="Company domain" />

                </div>

            </div>

            <div class="right-container-flow">
                    
                <p class="title-config">Workflow custodian</p><br>
                <input type="text" class="code input-value-text" id="custodianEmail" name="custodianEmail" value="" placeholder="Custodian e-mail" />      
                        
            </div>
        </div>

        <div class="container-status-flow">
            <div class="status-align-left-flow">
                    
                Specify the name of the flow
                    
            </div>

            <div class="status-align-middle-flow">
                    
                Define time interval and company domain
                        
            </div>

            <div class="status-align-right-flow">
                
                Send alert when workflow is triggered
                    
            </div>
        </div>

        <p class="title-config">List of current defined & working workflows</p><br>

        <table class="table-flows">
            <thead class="table-thead-flows">                       
                <th class="table-th-flows-name" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Workflow name</th>
                <th class="table-th-flows-workflow" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Fraud Triangle Flow</th>
                <th class="table-th-flows-interval" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Interval</th>
                <th class="table-th-flows-custodian" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Custodian</th>
                <th class="table-th-flows-triggers" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Hits</th>
            </thead>
            <tbody class="table-tbody-flows ruleset-scroll">

                <?php

                $workflowQuery = mysqli_query($connection, "SELECT * FROM t_workflows"); 

                while($workflowEntry = mysqli_fetch_assoc($workflowQuery))
                {
                    $permitted_chars = 'abcdefghijklmnopqrstuvwxyz';
                    $labelCheckbox = substr(str_shuffle($permitted_chars), 0, 10);

                    echo '<tr class="table-tr-flows">';
                    echo '<td class="table-td-flows-name"><div class="workflowCheck"><input type="checkbox" id="'.$labelCheckbox.'" name="workflowSelection[]" value="'.$workflowEntry['name'].'"><label for="'.$labelCheckbox.'"><span></span>'.$workflowEntry['name'].'</label></div></td>';
                    echo '<td class="table-td-flows-workflow"><p class="mightOverflow"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$workflowEntry['workflow'].'</p></td>';
                    echo '<td class="table-td-flows-interval"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$workflowEntry['interval'].'</td>';
                    echo '<td class="table-td-flows-custodian"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$workflowEntry['custodian'].'</td>';

                    if ($workflowEntry['triggers'] != 0)
                    {
                        echo '<td class="table-td-flows-triggers"><span class="fa fa-globe font-icon-gray fa-padding"></span><a href="../mods/viewWorkflow?ed='.encRijndael($workflowEntry['name']).'" data-toggle="modal" data-dismiss="modal" class="viewworkflow-button" data-target="#viewWorkflow" href="#" id="elm-view-workflow">'.$workflowEntry['triggers'].'</a></td>';
                    }
                    else
                    {
                        echo '<td class="table-td-flows-triggers"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$workflowEntry['triggers'].'</td>';
                    }
                    
                    echo '</tr>';
                }

                ?>

            </tbody>
        </table>
        <br>

        <div class="modal-footer window-footer-config">
            <br>
                
                <?php    
                
                if ($session->username != "admin") 
                {
                    echo '<input type="submit" class="btn btn-danger setup disabled" value="Delete workflow" name="delete" style="outline: 0 !important;">';
                    echo '<input type="submit" class="btn btn-success setup disabled" value="Add workflow" name="add" style="outline: 0 !important;">';
                }
                else 
                {
                    echo '<input type="submit" class="btn btn-danger setup" value="Delete workflow" name="delete" style="outline: 0 !important;">';
                    echo '<input type="submit" class="btn btn-success setup" value="Add workflow" name="add" style="outline: 0 !important;">';
                }

                ?>
            
        </div>

    </form>

</div>

<!-- Add or remove workflows -->

<script>

$(document).ready(function()
{
    var x;

	$(".addCF").click(function()
    {
        x = document.getElementById("customFields").rows.length;
    
        if(x > 5) return;

        $("#customFields").append('<tr valign="top"><th scope="row"><label for="customFieldName"></label></th><td><div class="flow-table"><div class="flow-cell"><select class="select-option-styled-rulesflow" name="rulesetFlow[]" id="rulesetFlow"><?php $configFile = parse_ini_file("../config.ini"); $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true); foreach ($jsonFT['dictionary'] as $ruleset => $value) { if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected="selected">ALL DEPARTMENTS</option>'; else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>'; } ?> </select></div><div class="flow-cell"><select class="select-option-styled-verticeflow" name="fraudverticeFlow[]" id="fraudverticeFlow"><option selected="selected">ALL VERTICES</option><option>PRESSURE</option><option>OPPORTUNITY</option><option>RATIONALIZATION</option></select></div><div class="flow-cell"><input type="text" class="code input-value-text-endpoint" style="margin-right: 3px;" id="endpointsFlow" name="endpointsFlow[]" value="" placeholder="All Endpoints" /><input type="text" class="code input-value-text-application" style="margin-right: 3px;" id="applicationsFlow" name="applicationsFlow[]" value="" placeholder="All Applications" /><input type="text" class="code input-value-text-phrase" style="margin-right: 7px;" id="phrasesFlow" name="phrasesFlow[]" value="" placeholder="All Phrases" /></div><div class="flow-cell"><select class="select-option-styled-operator" name="fraudOperator[]" id="fraudOperator-flow"><option>AND</option><option selected="selected">END</option></select></div><div class="flow-cell"><a href="javascript:void(0);" onclick="deleteRow(this)">&nbsp;&nbsp;<i class="fa fa-minus fa-lg" aria-hidden="true"></i></a></div></div></td></tr>');
        $(document).ready(function() {
            $('select').niceSelect();
        });
    });
});

function deleteRow(el) 
{
    while (el.parentNode && el.tagName.toLowerCase() != 'tr') 
    {
        el = el.parentNode;
    }

    if (el.parentNode && el.parentNode.rows.length > 1) 
    {
        el.parentNode.removeChild(el);
    }
}

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Workflow tooltip -->

<script>

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

</script>

<!-- Modal for Workflow View -->

<script>
    $(document).on('hidden.bs.modal', function (e) {
    $(e.target).removeData('bs.modal');
    });

    $('#viewWorkflow').on('show.bs.modal', function(e){
        $(this).find('.viewworkflow-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>