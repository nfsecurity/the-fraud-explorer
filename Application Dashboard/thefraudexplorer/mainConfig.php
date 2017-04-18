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
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for main config
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .input-value-text-config
    {
        width: 100%;
        height: 30px;
        padding: 5px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: 'FFont', sans-serif; font-size:12px;
    }

    .score-text
    {
        font-family: 'FFont', sans-serif; font-size:11.5px;
    }

    .title-score
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 0px;
        padding-top: 10px;
        display: block;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    input[type="number"] 
    {
        position: relative;
        margin: 0 0 1rem;
        border: 1px solid #c9c9c9;
        padding: .2rem;
        width: 120px;
        height: 30px;
        outline: 0 !important;
    }

    input[type="number"].mod::-webkit-outer-spin-button, input[type="number"].mod::-webkit-inner-spin-button 
    {
        -webkit-appearance: none;
        background: #FFF url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAKUlEQVQYlWNgwAT/sYhhKPiPT+F/LJgEsHv37v+EMGkmkuImoh2NoQAANlcun/q4OoYAAAAASUVORK5CYII=) no-repeat center center;
        width: 15px;
        height: 28px;
        border-left: 1px solid #BBB;
        opacity: .5; 
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
    }

    input[type="number"].mod::-webkit-inner-spin-button:hover, input[type="number"].mod::-webkit-inner-spin-button:active
    {
        box-shadow: 0 0 2px #0CF;
        opacity: .8;
    }

    .container-score-config
    {
        display: block;
    }

    .container-score-config::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .score-align-left
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.8%;height: 170px;
        float:left;
        margin: 10px 0px 0px 0px;
    }

    .score-align-right
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.8%;
        height: 170px;
        float:right;
        margin: 10px 0px 0px 0px;
    }
    
    .select-ruleset-styled
    {
        position: relative;
        border: 1px solid #ccc;
        width: 100%;
        font-family: 'FFont', sans-serif; font-size: 12px;
        color: #757575;
        height: 30px;
        overflow: scroll;
        background-color: #fff;
        outline: 0 !important;
    }

    .select-ruleset-styled:before
    {
        content: '';
        position: absolute;
        right: 5px;
        top: 7px;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 7px 5px 0 5px;
        border-color: #000000 transparent transparent transparent;
        z-index: 5;
        pointer-events: none;
    }

    .select-ruleset-styled select
    {
        padding: 5px 8px;
        width: 130%;
        border: none;
        box-shadow: none;
        background-color: transparent;
        background-image: none;
        appearance: none;
    }
    
    .master-container
    {
        width: 100%; 
        height: 70px;
    }
    
    .key-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .sample-calculation-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Main Configuration</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action="configParameters">

        <div class="master-container">
            <div class="key-container">
                <p class="title-config">Specify/change the key for agents connection</p><br>
                <input class="input-value-text-config" type="text" name="key" id="key" autocomplete="off" placeholder=":key here <?php $keyQuery = mysql_query("SELECT password FROM t_crypt"); $keyPass = mysql_fetch_array($keyQuery); echo '(current value:'.$keyPass[0].')'; ?>">
            </div>
            <div class="sample-calculation-container">
                <p class="title-config">Enable or disable sample data calculation</p><br>
                <select class="select-ruleset-styled" name="samplecalculation" id="samplecalculation">
                    <option selected="selected"> 
                        
                        <?php
                        
                        if($session->domain == "all")
                        {
                            $calculationQuery = mysql_query("SELECT sample_data_calculation FROM t_config"); 
                            $sampleQuery = mysql_fetch_array($calculationQuery);
                            echo $sampleQuery[0]; 
                        }
                        else
                        {
                            $domainConfigTable = "t_config_".str_replace(".", "_", $session->domain);
                            $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
                            $calculationQuery = mysql_query($queryCalc); 
                            $sampleQuery = mysql_fetch_array($calculationQuery); 
                            echo $sampleQuery[0];
                        }
                        
                        ?>
                        
                    </option>
                    <option value="enabled">enabled</option>
                    <option value="disabled">disabled</option>
                </select>
            </div>
        </div>

        <p class="title-config">Change 16Bit Encryption key & vector</p><br><input class="input-value-text-config" type="text" name="encryption" id="encryption" autocomplete="off" placeholder=":encryption key/vector here <?php $keyQuery = mysql_query("SELECT `key` FROM t_crypt"); $keyPass = mysql_fetch_array($keyQuery); echo '(current value:'.$keyPass[0].')'; ?>">

        <br><p class="title-config">Admin password modification</p><br>
        <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="off" placeholder=":new password here">

        <?php
        
        $scoreQuery = mysql_query("SELECT * FROM t_config");
        $scoreResult = mysql_fetch_array($scoreQuery);
        
        ?>

        <br><p class="title-score">Fraud score criticality configuration&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</p>
        <div class="container-score-config">
            <div class="score-align-left">
                <p class="score-text">Low score threshold<br><br></p>
                <input class="mod score-text" type="number" name="lowfrom" min="0" max="500" value="<?php echo $scoreResult[0]; ?>" required>
                <input class="mod score-text" type="number" name="lowto" min="0" max="500" value="<?php echo $scoreResult[1]; ?>" required><br>
                <p class="score-text">Medium score threshold<br><br></p>
                <input class="mod score-text" type="number" name="mediumfrom" min="0" max="500" value="<?php echo $scoreResult[2]; ?>" required>
                <input class="mod score-text" type="number" name="mediumto" min="0" max="500" value="<?php echo $scoreResult[3]; ?>" required>
            </div>
            <div class="score-align-right">
                <p class="score-text">High score threshold<br><br></p>
                <input class="mod score-text" type="number" name="highfrom" min="0" max="500" value="<?php echo $scoreResult[4]; ?>" required>
                <input class="mod score-text" type="number" name="highto" min="0" max="500" value="<?php echo $scoreResult[5]; ?>" required><br>
                <p class="score-text">Critical score threshold<br><br></p>
                <input class="mod score-text" type="number" name="criticfrom" min="0" max="500" value="<?php echo $scoreResult[6]; ?>" required>
                <input class="mod score-text" type="number" name="criticto" min="0" max="500" value="<?php echo $scoreResult[7]; ?>" required>
            </div>
        </div>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <input type="submit" class="btn btn-danger setup" value="Set values" style="outline: 0 !important;">
        </div>
    </form>
</div> 