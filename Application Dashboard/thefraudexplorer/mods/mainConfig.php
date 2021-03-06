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
 * Description: Code for main config
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
include "../lbs/cronManager.php";

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

    .input-value-text-config
    {
        width: 100%;
        height: 30px;
        padding: 5px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: 'FFont', sans-serif; font-size:12px;
        border-radius: 5px;
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

    .window-footer-main-config
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
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
        padding-left: 10px;
        width: 120px;
        height: 30px;
        outline: 0 !important;
        border-radius: 5px
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
        max-height: 30px !important;
        min-height: 30px !important;
        border: 1px solid #ccc !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        line-height: 11.6px !important;
        padding: 8px 0px 0px 10px !important;
        position: relative;
    }

    .select-ruleset-styled .list
    {
        margin-left: 5px;
        overflow-y: scroll !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) !important;
        max-height: 240px !important;
        background: #f9f9f9 !important;
    }

    .select-ftacron-styled
    {
        max-height: 30px !important;
        width: 132px;
        min-height: 30px !important;
        border: 1px solid #ccc !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        line-height: 11.6px !important;
        padding: 8px 0px 0px 10px !important;
        position: relative;
    }

    .select-ftacron-styled .list
    {
        margin-left: 5px;
        width: 132px;
        max-height: 120px;
        overflow-y: scroll !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) !important;
        background: #f9f9f9 !important;
    }

    .select-librarylanguage-styled
    {
        max-height: 30px !important;
        min-height: 30px !important;
        border: 1px solid #ccc !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        line-height: 11.6px !important;
        padding: 8px 0px 0px 10px !important;
        position: relative;
    }
    
    .select-librarylanguage-styled .list
    {
        margin-left: 5px;
        max-height: 120px;
        overflow-y: scroll !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) !important;
        background: #f9f9f9 !important;
    }

    .master-container
    {
        width: 100%; 
        height: 70px;
    }
    
    .key-container, .encryption-container, .adminpassword-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .sample-rule-container, .cron-container, .librarylanguage-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .language-subcontainer, .defaultrule-subcontainer
    {
        height: 100%; 
        width: 132px; 
        float: left;"
    }    

    .spellchecker-subcontainer, .sample-subcontainer
    {
        height: 100%; 
        width: 132px; 
        float: right;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

    .btn-fta-run, .btn-fta-run:active, .btn-fta-run:visited
    {
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        display: inline;
        width: 132px;
        height: 30px;
        outline: none !important;
        float: right;
        text-align: left;
        padding-left: 9px;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Main Configuration</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action="mods/configParameters">

        <div class="master-container">
            <div class="key-container">              
                <p class="title-config">Endpoint to Server password</p><br>
                <input class="input-value-text-config" type="text" name="key" id="key" autocomplete="new-password" placeholder=":password here" <?php if ($session->domain != "all") echo 'disabled'; ?>>
            </div>

            <div class="sample-rule-container">

                <div class="defaultrule-subcontainer">

                    <p class="title-config">Rule enrollment</p><br>

                    <select class="select-ruleset-styled wide" name="defaultruleset" id="defaultruleset">
                        
                        <?php

                            $configFile = parse_ini_file("../config.ini");
                            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);

                            echo '<option selected="selected" value="'.$configFile['singup_ruleset'].'">'.$configFile['singup_ruleset'].'</option>';

                            foreach ($jsonFT['dictionary'] as $ruleset => $value) 
                            {
                                if ($ruleset == $configFile['singup_ruleset']) continue;
                                else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                            }

                        ?>

                    </select>

                </div>

                <div class="sample-subcontainer">

                    <p class="title-config">Sample data</p><br>
                    <select class="select-ruleset-styled wide" name="samplecalculation" id="samplecalculation">
                        <option selected="selected"> 
                            
                            <?php
                            
                            if($session->domain == "all")
                            {
                                $calculationQuery = mysqli_query($connection, "SELECT sample_data_calculation FROM t_config"); 
                                $sampleQuery = mysqli_fetch_array($calculationQuery);
                                echo $sampleQuery[0]; 
                            }
                            else
                            {
                                $domainConfigTable = "t_config_".str_replace(".", "_", $session->domain);
                                $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
                                $calculationQuery = mysqli_query($connection, $queryCalc); 
                                $sampleQuery = mysqli_fetch_array($calculationQuery); 
                                echo $sampleQuery[0];
                            }
                            
                            ?>
                            
                        </option>
                        <?php if ($sampleQuery[0] == "disabled") echo '<option value="enabled">enabled</option>'; else echo '<option value="disabled">disabled</option>';  ?>
                    </select>

                </div>
        
            </div>
        </div>

        <div class="master-container">
            <div class="encryption-container">
                <p class="title-config">Change 16Bit Encryption key & vector</p><br>
                <input class="input-value-text-config" type="text" name="encryption" id="encryption" autocomplete="new-password" placeholder=":encryption key/vector here" <?php if ($session->domain != "all") echo 'disabled'; ?>>
            </div>

            <div class="cron-container">
                <p class="title-config">Schedule FTA Processor & Run on demand</p><br>
                <select class="select-ftacron-styled" name="ftacron" id="ftacron" <?php if ($session->domain != "all") echo 'disabled'; ?>>
                     <option value="<?php $cron_manager = new CronManager(); $minutes = $cron_manager->cron_get_minutes("fta-ai-processor"); if ($minutes != "false") echo $minutes; else echo "disabled"; ?>" selected="selected"> 
                        
                        <?php
                            $cron_manager = new CronManager();
                            $minutes = $cron_manager->cron_get_minutes("fta-ai-processor");
                            if ($minutes != "false") echo $minutes . " minutes";
                            else echo "disabled";
                        ?>
                        
                    </option>
                    <?php if ($minutes != "false") echo '<option value="disabled">disabled</option>'; ?>
                    <?php if ($minutes != "30") echo '<option value="30">30 minutes</option>'; ?>
                    <?php if ($minutes != "60") echo '<option value="60">60 minutes</option>'; ?>
                    <?php if ($minutes != "90") echo '<option value="90">90 minutes</option>'; ?>
                    <?php if ($minutes != "120") echo '<option value="120">120 minutes</option>'; ?>
                </select>      

                <button type="button" class="btn btn-default btn-fta-run" id="btnftanow" style="font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;" data-loading-text="<i class='fa fa-refresh fa-spin fa-fw'></i>&nbsp;Running">Run FTA/AI right now</button>

            </div>
        </div>

        <div class="master-container">
            <div class="adminpassword-container">
                <p class="title-config">Admin password modification</p><br>
                <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="new-password" placeholder=":new password here" <?php if ($session->domain != "all") echo 'disabled'; ?>>
            </div>

            <div class="librarylanguage-container">

                <div class="language-subcontainer">

                    <p class="title-config">Library language</p><br>
                    <select class="select-librarylanguage-styled wide" name="librarylanguage" id="librarylanguage" <?php if ($session->domain != "all") echo 'disabled'; ?>>

                        <?php

                            $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");

                            if ($configFile["wc_language"] == "es")
                            {
                                echo '<option value="es" selected="selected">Spanish</option>';
                                echo '<option value="en">English</option>';
                                echo '<option value="hu">Multi language</option>';
                            }
                            else if ($configFile["wc_language"] == "en")
                            {
                                echo '<option value="es">Spanish</option>';
                                echo '<option value="en" selected="selected">English</option>';
                                echo '<option value="hu">Multi language</option>';
                            }
                            else
                            {
                                echo '<option value="es">Spanish</option>';
                                echo '<option value="en">English</option>';
                                echo '<option value="hu" selected="selected">Multi language</option>';
                            }

                        ?>
                    
                    </select>    

                </div>

                <div class="spellchecker-subcontainer">

                    <p class="title-config">Spelling corrector</p><br>
                    <select class="select-librarylanguage-styled wide" name="spellchecker" id="spellchecker"> 

                        <?php

                            $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");

                            if ($configFile["wc_enabled"] == "yes")
                            {
                                echo '<option value="yes" selected="selected">active</option>';
                                echo '<option value="no">de-activate</option>';
                            }
                            else
                            {
                                echo '<option value="yes">activate</option>';
                                echo '<option value="no" selected="selected">inactive</option>';
                            }

                        ?>

                    </select>

                </div>

            </div>

        </div>

        <?php
        
        $scoreQuery = mysqli_query($connection, "SELECT * FROM t_config");
        $scoreResult = mysqli_fetch_array($scoreQuery);
        
        ?>

        <p class="title-score">Fraud score criticality configuration&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</p>
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

        <div class="modal-footer window-footer-main-config">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Exit without saving</button>
            <button type="submit" id="btn-apply-configuration" data-loading-text="<i class='fa fa-refresh fa-spin fa-fw'></i>&nbsp;Applying, please wait" class="btn btn-danger setup" style="outline: 0 !important;">Apply configuration</button>
        </div>
    </form>
</div>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Button applying -->

<script>

var $btn;

$("#btn-apply-configuration").click(function() {
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

<!-- Button Run FTA now -->

<script>

$(document).ready(function () {
        $("#btnftanow").click(function () {
            var $btn = $(this);
            $btn.button('loading');
            $.ajax({
                    type: "POST",
                    url: "../mods/runFTAnow.php",
                    data: {
                        params: "n/a"
                    }
                })
                .done(function () {
                    $btn.button('reset');
                });
        });
    });

</script>
