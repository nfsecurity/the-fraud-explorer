<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for ruleset setup
 */

include "lbs/login/session.php";
include "lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";
include "lbs/agent_methods.php";

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

    .table-ruleset
    {
        font-family: 'FFont', sans-serif; font-size:10px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
    }

    .table-thead-ruleset
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

    .table-th-ruleset
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background: white;
        min-width: calc(555px / 6);
        width: calc(555px / 6);
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 45px;
    }

    .table-tbody-ruleset
    {
        display: block;
        border: 1px solid white;
        width: 100%;
        height: 300px !important; 
        max-height: 300px !important;
        overflow-y: scroll; 
    }

    .table-tr-ruleset
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-ruleset tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }

    .table-td-ruleset
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
    }

    form 
    {
        display: inline;
    }

    .fileUpload 
    {
        position: relative;
        overflow: hidden;
    }

    .fileUpload input.upload 
    {
        position: absolute;
        top: 0;
        right: 0;
        margin: 0;
        padding: 0;
        cursor: pointer;
        opacity: 0;
        filter: alpha(opacity=0);
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Ruleset Configuration</h4>
</div>

<div class="div-container">
    <table class="table-ruleset">
        <thead class="table-thead-ruleset">
            <th class="table-th-ruleset" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>RULESET</th>
            <th class="table-th-ruleset"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>PRESS</th>
            <th class="table-th-ruleset"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>OPPRT</th>
            <th class="table-th-ruleset"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>RATNL</th>
            <th class="table-th-ruleset"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>TOTAL</th>
            <th class="table-th-ruleset"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>ENDPT</th>
        </thead>
        <tbody class="table-tbody-ruleset">

            <?php

            /* SQL queries */

            if ($session->domain == "all")
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    $countQuery = "SELECT COUNT(*) AS total FROM (SELECT agent, ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, ruleset FROM t_agents) AS agents GROUP BY agent) AS count WHERE ruleset='%s'";
                }
                else
                {
                    $countQuery = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset FROM t_agents) AS agents GROUP BY agent) AS count WHERE domain NOT LIKE 'thefraudexplorer.com' AND ruleset='%s'";
                }
            }
            else
            {
                if (samplerStatus($session->domain) == "enabled")
                {
                    $countQuery = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset FROM t_agents) AS agents GROUP BY agent) AS count WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND ruleset='%s'";
                }
                else
                {
                    $countQuery = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, ruleset FROM t_agents) AS agents GROUP BY agent) AS count WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com' AND ruleset='%s'";
                }
            }
            
            //$countQuery = "SELECT COUNT(*) FROM (SELECT agent, heartbeat, ruleset FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, ruleset FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent) AS count WHERE ruleset='%s'";

            $fraudTriangleTerms = array('0'=>'pressure','1'=>'opportunity','2'=>'rationalization');
            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
            $dictionaryCount = array();

            foreach ($jsonFT['dictionary'] as $ruleset => $value)
            {
                echo '<tr class="table-tr-ruleset">';
                echo '<td class="table-td-ruleset" style="text-align: left; border-right: 2px solid white;">'.$ruleset.'</td>';

                foreach($fraudTriangleTerms as $term)
                {
                    foreach ($jsonFT['dictionary'][$ruleset][$term] as $field => $termPhrase)
                    {
                        @$dictionaryCount[$ruleset][$term]++;
                    }

                    if (empty($dictionaryCount[$ruleset][$term])) echo '<td class="table-td-ruleset">0</td>';
                    else echo '<td class="table-td-ruleset">'.$dictionaryCount[$ruleset][$term].'</td>';
                }

                $total = @$dictionaryCount[$ruleset]['pressure'] + @$dictionaryCount[$ruleset]['opportunity'] + @$dictionaryCount[$ruleset]['rationalization'];	
                echo '<td class="table-td-ruleset" style="border-left: 2px solid white;">'.$total.'</td>';

                $rulesetQuery = mysql_query(sprintf($countQuery,$ruleset));
                $rule = mysql_fetch_array($rulesetQuery);
                echo '<td class="table-td-ruleset">'.$rule[0].'</td>';
                echo '</tr>';
            }

            ?>

        </tbody>
    </table>

    <div class="modal-footer window-footer-config">
        <br>
        <a href="authAccess?file=core/rules/fta_text_spanish.json" class="btn btn-default" style="outline: 0 !important;">Download JSON file</a>
        <form action="rulesetUpload.php" id="rulesetUpload" method="post" enctype="multipart/form-data">
            
            <?php 
            
            if ($session->domain == "all") echo '<div class="fileUpload btn btn-default" style="outline: 0 !important;">';
            else echo '<div class="fileUpload btn btn-default disabled" style="outline: 0 !important;">';
            
            echo '<span>Upload JSON file</span>';
            echo '<input type="file" name="fileToUpload" id="fileToUpload" class="upload" />';
            echo '</div>';
            
            ?>
            
        </form>
        <button type="button" class="btn btn-danger" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
    </div>
</div> 

<script>
    document.getElementById("fileToUpload").onchange = function() {
        document.getElementById("rulesetUpload").submit();
    }
</script>