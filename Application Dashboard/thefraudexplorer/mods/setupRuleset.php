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
 * Description: Code for ruleset setup
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

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-ruleset
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
        background-color: white;
    }

    .table-ruleset
    {
        font-family: 'FFont', sans-serif; font-size:10px;
        border: 0px solid gray;
        width: 100%;
        border-spacing: 0px;
        background-color: white;
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
        padding: 0px 0px 0px 0px;
        height: 45px;
    }

    .table-tbody-ruleset
    {
        display: block;
        border: 1px solid #e8e9e8;
        width: 100%;
        height: 302px !important; 
        max-height: 302px !important;
        overflow-y: scroll; 
        border-radius: 5px 5px 5px 5px;
        background-color: white;
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
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
    }
    
    .table-td-ruleset-total
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        background: #e8e9e8; 
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 2px solid white;
    }
    
    .table-td-ruleset-endpoint
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6 - 7);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 0px;
        text-align: center;
        border-right: 0px solid white;
    }
    
    .table-td-ruleset-name
    {
        border: 0px solid gray;
        width: calc(555px / 6);
        min-width: calc(555px / 6);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: center;
        border-right: 0px solid white;
        font-family: 'FFont', sans-serif; font-size: 10px;
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
    
    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .footer-statistics-ruleset
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        font-family: Verdana, sans-serif; font-size: 11px;
        line-height: 30px;
        margin-top: 15px;
        text-align: center;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .font-aw-color
    {
        color: #B4BCC2;
    }
    
    .btn-success, .btn-success:hover, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-success, .btn-success:active, .btn-success:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Ruleset Configuration</h4>
</div>

<div class="div-container">
    <table class="table-ruleset">
        <thead class="table-thead-ruleset">
            <th class="table-th-ruleset" style="text-align: left;">&ensp;RULESET</th>
            <th class="table-th-ruleset">PRESS</th>
            <th class="table-th-ruleset">OPPRT</th>
            <th class="table-th-ruleset">RATNL</th>
            <th class="table-th-ruleset">TOTAL</th>
            <th class="table-th-ruleset">FLAGS</th>
            <th class="table-th-ruleset">ENDPT</th>
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

            $fraudTriangleTerms = array('0'=>'pressure','1'=>'opportunity','2'=>'rationalization');
            $rulesetLanguage = $configFile['fta_lang_selection'];

            if ($rulesetLanguage == "fta_text_rule_multilanguage")
            {
                $numberOfLibraries = 2;
                $jsonFT[1] = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                $jsonFT[2] = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);
            }
            else
            {
                $numberOfLibraries = 1;
                $jsonFT[1] = json_decode(file_get_contents($configFile[$rulesetLanguage]), true);
            }

            $dictionaryCount = array();
            $phrasesCount = 0;

            foreach ($jsonFT[1]['dictionary'] as $ruleset => $value)
            {
                $flagsCount = 0;

                echo '<tr class="table-tr-ruleset">';
                echo '<td class="table-td-ruleset-name" style="text-align: left; border-right: 2px solid white;"><span class="fa fa-file-text-o font-icon-color-green fa-padding"></span>'.$ruleset.'</td>';

                foreach($fraudTriangleTerms as $term)
                {
                    for ($lib = 1; $lib<=$numberOfLibraries; $lib++)
                    {  
                        foreach ($jsonFT[$lib]['dictionary'][$ruleset][$term] as $field => $termPhrase)
                        {
                            @$dictionaryCount[$ruleset][$term]++;
                            $phrasesCount++;

                            if (strpos($field, '*') !== false) $flagsCount++;
                        }
                    }

                    if (empty($dictionaryCount[$ruleset][$term])) echo '<td class="table-td-ruleset"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>0</td>';
                    else echo '<td class="table-td-ruleset"><span class="fa fa-bookmark-o font-icon-gray fa-padding"></span>'.$dictionaryCount[$ruleset][$term].'</td>';
                }

                $total = @$dictionaryCount[$ruleset]['pressure'] + @$dictionaryCount[$ruleset]['opportunity'] + @$dictionaryCount[$ruleset]['rationalization'];	
                echo '<td class="table-td-ruleset-total">'.$total.'</td>';

                echo '<td class="table-td-ruleset">'.$flagsCount.'</td>';

                $rulesetQuery = mysqli_query($connection, sprintf($countQuery, $ruleset));
                $rule = mysqli_fetch_array($rulesetQuery);
                echo '<td class="table-td-ruleset-endpoint">'.$rule[0].'</td>';
                echo '</tr>';
            }
            
            $departmentsCount = count($dictionaryCount) + 1;

            ?>

        </tbody>
    </table>
    
    <?php
    
    echo '<div class="footer-statistics-ruleset"><span class="fa fa-area-chart font-aw-color fa-padding"></span>There are '.$phrasesCount.' fraud triangle rules (phrase expressions) defined under '.$departmentsCount.' corporate business units </div>';
    
    ?>

    <div class="modal-footer window-footer-ruleset">

        <a id="download-rules" class="btn btn-default" style="outline: 0 !important;">Download rules</a>
        
        <form action="mods/rulesetUpload" id="rulesetUpload" method="post" enctype="multipart/form-data">
            
            <?php 
            
            if ($session->username == "admin")  echo '<div class="fileUpload btn btn-success" style="outline: 0 !important;">';
            else echo '<div class="fileUpload btn btn-success disabled" style="outline: 0 !important;">';
            
            echo 'Upload rule file';
            echo '<input type="file" name="fileToUpload" id="fileToUpload" class="upload" />';
            echo '</div>';
            
            ?>
            
        </form>
    </div>
</div> 

<script>
    document.getElementById("fileToUpload").onchange = function() {
        document.getElementById("rulesetUpload").submit();
    }
</script>

<!-- Download multiple rule files -->

<script>
    $(document).ready(function(){
    $('#download-rules').click(function(){
    $.ajax({
        url: '../lbs/downloadRules.php',
        type: 'post',
        success: function(response){
            window.location = response;
        }
    });
    });
    });
</script>