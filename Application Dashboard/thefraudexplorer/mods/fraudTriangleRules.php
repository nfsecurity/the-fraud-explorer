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
 * Description: Code for fraud triangle rules
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

/* Procedure to check internet connection to license server */

function is_internet()
{
    $connected = @fsockopen("licensing.thefraudexplorer.com", 443); 

    if ($connected)
    {
        $is_conn = true;
        fclose($connected);
    }
    else $is_conn = false;

    return $is_conn;
}

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        text-align: left;
        width: 100%;
        padding-bottom: 10px;
        padding-top: 10px;
        margin-left: 2px;
    }

    .title-config-bold
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        float: left;
        text-align: left;
        width: 100%;
        padding-bottom: 10px;
        padding-top: 10px;
        margin-left: 2px;
    }

    .input-value-text-id-add
    {
        width: 233px; 
        height: 30px;
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .input-value-text-id-delmodify
    {
        width: 233px; 
        height: 30px; 
        padding: 5px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .input-value-text-regexp-add
    {
        width: 513px;
        height: 30px; 
        padding: 5px;
        margin-left: 0px !important;
        border-left: 0px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: Courier; font-size: 12px;
        border-radius: 0px 0px 0px 0px;
    }

    .input-value-text-regexp-delmodify
    {
        width: 513px;
        height: 30px; 
        padding: 5px;
        margin-left: 0px !important;
        border-left: 0px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: Courier; font-size: 12px;
        border-radius: 0px 0px 0px 0px;
    }

    .window-footer-library
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .select-option-styled-rulesworkshop
    {
        width: 165px;
        height: 30px;
        border: 1px solid #ccc;
        color: #757575;
        padding: 8px 0px 0px 10px;
        position: relative;
        line-height: 11.6px;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-rulesworkshop .list
    {
        width: 165px;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        overflow-y: scroll;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-fraudvertice
    {
        width: 140px;
        margin-right: 0px;
        height: 30px;
        border: 1px solid #ccc;
        color: #757575;
        padding: 8px 0px 0px 10px;
        position: relative;
        line-height: 11.6px;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-language-add
    {
        width: 137px;
        margin-top: 0px;
        margin-right: 0px;
        height: 30px;
        border: 1px solid #ccc;
        color: #757575;
        padding: 8px 0px 0px 10px;
        position: relative;
        line-height: 11.6px;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-language-add .list
    {
        width: 137px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        overflow-y: scroll;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-language-search
    {
        width: 137px;
        margin-top: 0px;
        margin-right: 0px;
        height: 30px;
        border: 1px solid #ccc;
        color: #757575;
        padding: 8px 0px 0px 10px;
        position: relative;
        line-height: 11.6px;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-language-search .list
    {
        width: 137px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        overflow-y: scroll;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .select-option-styled-fraudvertice .list
    {
        width: 140px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        overflow-y: scroll;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .master-container-library
    {
        width: 100%; 
        border-radius: 5px;
        background: #f2f2f2;
        padding: 0px 10px 15px 10px;
        margin-bottom: 15px;
    }

    .container-status-library
    {
        width: 100%; 
        border-radius: 5px;
        background: #f2f2f2;
        padding: 0px 10px 15px 10px;
        height: 30px;
        margin-top: 15px;
        text-align: center;
    }
    
    .left-container-library
    {
        width: calc(25% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
        text-align: left;
    }
    
    .right-container-library-mod
    {
        width: 385px; 
        height: 100%; 
        padding-left: 10px;
        display: inline; 
        float: left;
        text-align: left;
    }

    .right-container-library-add
    {
        width: 385px; 
        height: 100%; 
        padding-left: 10px;
        display: inline; 
        float: left;
        text-align: left;
    }

    .right-container-library-language
    {
        width: 140px;
        float: right;
    }

    .rule-button-add
    {
        width: 102px;
        height: 30px;
        min-height: 30px;
        border-radius: 5px;
        outline: 0 !important;
        background: white;
        border: 1px solid #BFC0BF;
        font-family: 'FFont', sans-serif; font-size: 11px !important;
        color: #525252;
    }

    .flag-enable-disable-button
    {
        border-radius: 5px;
        outline: 0 !important;
        background: white;
        border: 1px solid #BFC0BF;
    }

    .rule-button-searchdelmodify
    {
        width: 42px;
        height: 30px;
        min-height: 30px;
        background: white;
        border: 1px solid #BFC0BF;
        border-radius: 5px;
        outline: 0 !important;
        font-family: 'FFont', sans-serif; font-size: 11px !important;
        color: #525252;
    }

    .regexp-container
    {
        text-align: left;
    }

    .input-regexp-left
    {
        background: #c9c9c9;
        width: 20px;
        height: 30px;
        display: inline-block;
        border-radius: 5px 0px 0px 5px;
        margin-left: 2px;
        font-family: Courier; font-size: 14px;
        text-align: center;
        line-height: 28px;
        margin-right: -4px;
    }

    .input-regexp-right
    {
        background: #c9c9c9;
        width: 20px;
        display: inline-block;
        height: 30px;
        margin-left: -6px;
        margin-top: 1;
        margin-right: 2px;
        border-radius: 0px 5px 5px 0px;
        font-family: Courier; font-size: 14px;
        line-height: 28px;
        text-align: center;
    }

    .warning
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        line-height: 30px;
    }

    .btn-success, .btn-success:active, .btn-success:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
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

    .font-icon-color-gray 
    { 
        color: #B4BCC2; 
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Phrase library workshop</h4>
</div>

<div class="div-container">
    <form id="formRules" name="formRules" method="post" action="mods/fraudTriangleRulesParams">

        <div class="master-container-library">
            <div class="left-container-library">              
                
                <p class="title-config-bold"><i class="fa fa-plus-circle" aria-hidden="true"></i>&nbsp;New phrase from</p><br>

                <!-- Rule department -->

                <select class="select-option-styled-rulesworkshop" name="ruleset-add" id="ruleset-add">

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);

                    foreach ($jsonFT['dictionary'] as $ruleset => $value)
                    {
                        if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected="selected">'.$ruleset.'</option>';
                        else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                    }

                    ?>

                </select>
                
            </div>
            <div class="right-container-library-add">

                <p class="title-config">Fraud vertice / Phrase identification</p><br>

                <!-- Fraud Triangle vertice -->

                <select class="select-option-styled-fraudvertice" name="fraudvertice-add" id="fraudvertice-add">
                    <option selected="selected">PRESSURE</option>
                    <option>OPPORTUNITY</option>
                    <option>RATIONALIZATION</option>
                </select>

                <input type="text" name="phrase-identification-add" id="phrase-identification-add" autocomplete="off" placeholder="enter here the phrase identification" class="input-value-text-id-add">
                    
            </div>

            <div class="right-container-library-language">

                <p class="title-config">Library language</p><br>
                <select class="select-option-styled-language-add" name="library-add-language" id="library-add-language">

                    <?php

                        if ($configFile["wc_language"] == "es" || $configFile["wc_language"] == "hu") 
                        {
                            echo '<option value="fta_text_rule_spanish" selected="selected">Spanish</option>';
                            echo '<option value="fta_text_rule_english">English</option>';
                        }
                        else if ($configFile["wc_language"] == "en")
                        {
                            echo '<option value="fta_text_rule_spanish">Spanish</option>';
                            echo '<option value="fta_text_rule_english" selected="selected">English</option>';
                        }

                    ?>

                </select>
            </div>

            <div class="regexp-container">
                <p class="title-config">Phrase regular expression</p>
                <div class="input-regexp-left">/</div>
                <input type="text" name="regexpression-add" id="regexpression-add" autocomplete="off" placeholder="enter here the regular expression in PCRE format" class="input-value-text-regexp-add">
                <div class="input-regexp-right">/</div>        

                <div class="btn-group btn-group-toggle" data-toggle="buttons" style="width: 30px; height: 32px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                    <label class="flag-enable-disable-button btn btn-default" id="add-flag" style="width: 30px; height: 30px; padding: 7px 5px 0px 5px; outline: 0 !important; -webkit-box-shadow: none !important; box-shadow: none !important;">
                        <input type="checkbox" name="addflag" onchange="checkboxAddFlag()" id="checkbox-addflag" value="addflag"><span id="flag-changer" class="fa fa-flag-o font-icon-color-gray" style="font-size: 14px;"></span>
                    </label>
                </div>
               
                <button type="submit" class="rule-button-add btn-default" style="font-family: 'FFont', sans-serif; font-size: 11px !important;" id="add-rule" name="action" value="addrule">Add rule</button>
            </div>

        </div>

        <div class="master-container-library">
            <div class="left-container-library">              
                
                <p class="title-config-bold"><i class="fa fa-minus-circle" aria-hidden="true"></i>&nbsp;Modify or detele from</p><br>

                <!-- Rule department -->

                <select class="select-option-styled-rulesworkshop" name="ruleset-delmodify" id="ruleset-delmodify">

                    <?php

                    $configFile = parse_ini_file("../config.ini");
                    $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);

                    foreach ($jsonFT['dictionary'] as $ruleset => $value)
                    {
                        if ($ruleset == "BASELINE") echo '<option value="'.$ruleset.'" selected="selected">'.$ruleset.'</option>';
                        else echo '<option value="'.$ruleset.'">'.$ruleset.'</option>';
                    }

                    ?>

                </select>
                
            </div>
            <div class="right-container-library-mod">
                   
                <p class="title-config">Fraud vertice / Phrase identification</p><br>

                <!-- Fraud Triangle vertice -->

                <select class="select-option-styled-fraudvertice" name="fraudvertice-delmodify" id="fraudvertice-delmodify">
                    <option selected="selected">PRESSURE</option>
                    <option>OPPORTUNITY</option>
                    <option>RATIONALIZATION</option>
                </select>

                <input type="text" name="phrase-identification-delmodify" id="phrase-identification-delmodify" autocomplete="off" placeholder="enter here the phrase identification" class="input-value-text-id-delmodify"> 
                    
            </div>

            <div class="right-container-library-language">

                <p class="title-config">Library language</p><br>
                <select class="select-option-styled-language-search" name="library-search-language" id="library-search-language">

                    <?php

                        if ($configFile["wc_language"] == "es" || $configFile["wc_language"] == "hu") 
                        {
                            echo '<option value="fta_text_rule_spanish" selected="selected">Spanish</option>';
                            echo '<option value="fta_text_rule_english">English</option>';
                        }
                        else if ($configFile["wc_language"] == "en")
                        {
                            echo '<option value="fta_text_rule_spanish">Spanish</option>';
                            echo '<option value="fta_text_rule_english" selected="selected">English</option>';
                        }

                    ?>

                </select>
            </div>

            <div class="regexp-container">
                <p class="title-config">Phrase regular expression</p>
                <div class="input-regexp-left">/</div>
                <input type="text" name="regexpression-delmodify" id="regexpression-delmodify" autocomplete="off" placeholder="you will see here the regular expression" class="input-value-text-regexp-delmodify">
                <div class="input-regexp-right">/</div>
                <button type="button" class="rule-button-searchdelmodify btn-default" style="font-family: 'FFont', sans-serif; font-size: 11px !important;" id="search-rule">SCH</button>
                <button type="submit" class="rule-button-searchdelmodify btn-default" style="font-family: 'FFont', sans-serif; font-size: 11px !important;" id="delete-rule" name="action" value="deleterule">DEL</button>
                <button type="submit" class="rule-button-searchdelmodify btn-default" style="font-family: 'FFont', sans-serif; font-size: 11px !important;" id="modify-rule" name="action" value="modifyrule">MOD</button>
            </div>

        </div>

        <div class="container-status-library">

            <?php

                /* Online phrase library upgrade process */

                $localLibrarySpanish = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
                $localLibraryEnglish = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);

                $phraseNameSpanish = explode("/", $configFile['fta_text_rule_spanish']);
                $phraseNameSelectionSpanish = $phraseNameSpanish[7];
                $phraseNameEnglish = explode("/", $configFile['fta_text_rule_english']);
                $phraseNameSelectionEnglish = $phraseNameEnglish[7];

                preg_match('/version: (.*),/', $localLibrarySpanish["_comment"], $localPhraseLibraryVersionSpanish);
                preg_match('/version: (.*),/', $localLibraryEnglish["_comment"], $localPhraseLibraryVersionEnglish);

                $configFile = parse_ini_file("../config.ini");
                $serialNumber = $configFile['pl_serial'];

                /* Query license version */

                $serverAddress = "https://licensing.thefraudexplorer.com/validateSerial.php";

                $postRequest = array(
                    'serial' => $serialNumber,
                    'capabilities' => "false",
                    'retrieve' => "false"
                );

                $payload = json_encode($postRequest);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $serverAddress);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

                $headers = [
                    'Content-Type: application/json',
                ];

                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $server_output = curl_exec($ch);
                curl_close($ch);
                $replyJSON = json_decode($server_output, true);
                $remotePhraseLibraryVersionEnglish = $replyJSON['englishVersion'];
                $remotePhraseLibraryVersionSpanish = $replyJSON['spanishVersion'];

                echo '<p class="warning"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;&nbsp;';

                if (is_internet() == true)
                {
                    if (isset($localPhraseLibraryVersionSpanish) && isset($remotePhraseLibraryVersionSpanish) && isset($localPhraseLibraryVersionEnglish) && isset($remotePhraseLibraryVersionEnglish))
                    {
                        if (($localPhraseLibraryVersionSpanish[1] != $remotePhraseLibraryVersionSpanish) || ($localPhraseLibraryVersionEnglish[1] != $remotePhraseLibraryVersionEnglish))
                        {
                            echo 'There are different versions of the phrase libraries at the official repo. ES [loc'. $localPhraseLibraryVersionSpanish[1].'-rem'.$remotePhraseLibraryVersionSpanish.'] - EN [loc'.$localPhraseLibraryVersionEnglish[1].'-rem'.$remotePhraseLibraryVersionEnglish.']';
                        }
                        else echo 'Your phrase libraries database are up to date, you don\'t need to upgrade your libraries now but pay future attention';
                    }
                    else echo 'Warning, the phrase library database modification could cause unwanted results due to a bad regular expression writing';
                }
                else echo 'You don\'t have internet connection at this time, please stablish permissions to reach the phrase library license server';

                echo '</p>';

            ?>

        </div>

        <div class="modal-footer window-footer-library">
            
            <?php    
            
                if (is_internet() == false)
                {
                    echo '<a id="upgrade-library-nointernet" class="btn btn-danger disabled" data-dismiss="modal" style="outline: 0 !important;">Synchronize libraries</a>';
                }
                else echo '<a id="upgrade-library" class="btn btn-danger" data-dismiss="modal" style="outline: 0 !important;">Synchronize libraries</a>';
            
                echo '<a id="download-rules" class="btn btn-success" style="outline: 0 !important;">Download libraries</a>';

            ?>
        
        </div>
    </form>
</div>

<?php

    function escapeJsonString( $value ) 
    {
        $escapers =     array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }

    $fta_lang = $configFile['fta_lang_selection'];

    if ($fta_lang == "fta_text_rule_multilanguage") 
    {
        $numberOfLibraries = 2;
        $jsonFT[1] = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
        $jsonFT[2] = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);

        $pureJSONforJSSpanish = str_replace("\u0022","\\\\\"", json_encode($jsonFT[1], JSON_HEX_APOS|JSON_HEX_QUOT)); 
        $pureJSONforJSSpanish = escapeJsonString($pureJSONforJSSpanish);

        $pureJSONforJSEnglish = str_replace("\u0022","\\\\\"", json_encode($jsonFT[2], JSON_HEX_APOS|JSON_HEX_QUOT)); 
        $pureJSONforJSEnglish = escapeJsonString($pureJSONforJSEnglish);
    }
    else 
    {
        $numberOfLibraries = 1;
        $jsonFT[1] = json_decode(file_get_contents($configFile[$fta_lang]), true);
        $pureJSONforJS = str_replace("\u0022","\\\\\"", json_encode($jsonFT[1], JSON_HEX_APOS|JSON_HEX_QUOT)); 
        $pureJSONforJS = escapeJsonString($pureJSONforJS);
    }

?>

<!-- Buttons Add, Delete, Modify -->

<script>

$("#add-rule").click(function(e) {

    e.preventDefault();

    var regexpadd = $('#regexpression-add').val();
    var phraseidentification = $('#phrase-identification-add').val();
    var allvalues = new Array(regexpadd, phraseidentification);

    if (!regexpadd || !phraseidentification)
    {
        var regexpaddfield = "#regexpression-add,";
        var phraseidentificationfield = "#phrase-identification-add,";
        var finalfield = "";

        if (allvalues[0] == "") finalfield = regexpaddfield;
        if (allvalues[1] == "") finalfield = finalfield + phraseidentificationfield;
 
        finalfield = finalfield.replace(/(,$)/g, "");

        setTimeout("$('"+finalfield+"').addClass('blink-check');", 100);
        setTimeout("$('"+finalfield+"').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        var numLibraries = '<?php if(isset($numberOfLibraries)) echo $numberOfLibraries; else echo " "; ?>'

        if (numLibraries == 1)
        {
            var data = '<?php if (isset($pureJSONforJS)) echo $pureJSONforJS; else echo " "; ?>'
            data = JSON.parse(data);
            var search = document.getElementById('phrase-identification-add').value
            var ruleset = document.getElementById('ruleset-add').value
            var vertice = document.getElementById('fraudvertice-add').value.toLowerCase();
            var searchPath = data["dictionary"][ruleset][vertice][search];

            if (typeof(searchPath) != "undefined") 
            {
                var finalRegexpString = "the regular expression already exists";
                $("#regexpression-add").val(finalRegexpString);

                setTimeout("$('#regexpression-add').addClass('blink-check');", 100);
                setTimeout("$('#regexpression-add').removeClass('blink-check');", 1000);

                return;
            }
        }
        else
        {
            var dataSpanish = '<?php if (isset($pureJSONforJSSpanish)) echo $pureJSONforJSSpanish; else echo " "; ?>'
            var dataEnglish = '<?php if (isset($pureJSONforJSEnglish)) echo $pureJSONforJSEnglish; else echo " "; ?>'

            dataSpanish = JSON.parse(dataSpanish);
            dataEnglish = JSON.parse(dataEnglish);

            var search = document.getElementById('phrase-identification-add').value
            var ruleset = document.getElementById('ruleset-add').value
            var vertice = document.getElementById('fraudvertice-add').value.toLowerCase();
        
            var searchPathSpanish = dataSpanish["dictionary"][ruleset][vertice][search];
            var searchPathEnglish = dataEnglish["dictionary"][ruleset][vertice][search];

            var finalRegexpString = null;

            if (typeof(searchPathSpanish) != "undefined") 
            {
                finalRegexpString = "the regular expression already exists";
                $("#regexpression-add").val(finalRegexpString);

                setTimeout("$('#regexpression-add').addClass('blink-check');", 100);
                setTimeout("$('#regexpression-add').removeClass('blink-check');", 1000);

                return;
            }
           
            if (typeof(searchPathEnglish) != "undefined") 
            {
                finalRegexpString = "the regular expression already exists";
                $("#regexpression-add").val(finalRegexpString);

                setTimeout("$('#regexpression-add').addClass('blink-check');", 100);
                setTimeout("$('#regexpression-add').removeClass('blink-check');", 1000);

                return;
            }
        }

        $("#formRules").submit(function(event) {
            $(this).append('<input type="hidden" name="action" value="addrule" /> ');
            return true;
        });

        $('#formRules').submit();
    }

});

$("#delete-rule").click(function(e) {

    e.preventDefault();

    var regexpdel = $('#regexpression-delmodify').val();
    var phraseidentification = $('#phrase-identification-delmodify').val();
    var allvalues = new Array(regexpdel, phraseidentification);

    if (!regexpdel || !phraseidentification || regexpdel == "no regular expression found")
    {
        var regexpdelfield = "#regexpression-delmodify,";
        var phraseidentificationfield = "#phrase-identification-delmodify,";
        var finalfield = "";

        if (allvalues[0] == "" || allvalues[0] == "no regular expression found") finalfield = regexpdelfield;
        if (allvalues[1] == "") finalfield = finalfield + phraseidentificationfield;
    
        finalfield = finalfield.replace(/(,$)/g, "");

        setTimeout("$('"+finalfield+"').addClass('blink-check');", 100);
        setTimeout("$('"+finalfield+"').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        $("#formRules").submit(function(event) {
            $(this).append('<input type="hidden" name="action" value="deleterule" /> ');
            return true;
        });

        $('#formRules').submit();
    }

});

$("#modify-rule").click(function(e) {

    e.preventDefault();

    var regexpmod = $('#regexpression-delmodify').val();
    var phraseidentification = $('#phrase-identification-delmodify').val();
    var allvalues = new Array(regexpmod, phraseidentification);

    if (!regexpmod || !phraseidentification || regexpmod == "no regular expression found")
    {
        var regexpmodfield = "#regexpression-delmodify,";
        var phraseidentificationfield = "#phrase-identification-delmodify,";
        var finalfield = "";

        if (allvalues[0] == "" || allvalues[0] == "no regular expression found") finalfield = regexpmodfield;
        if (allvalues[1] == "") finalfield = finalfield + phraseidentificationfield;

        finalfield = finalfield.replace(/(,$)/g, "");

        setTimeout("$('"+finalfield+"').addClass('blink-check');", 100);
        setTimeout("$('"+finalfield+"').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        $("#formRules").submit(function(event) {
            $(this).append('<input type="hidden" name="action" value="modifyrule" /> ');
            return true;
        });

        $('#formRules').submit();
    }

});

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

<!-- Upgrade phrase library -->

<script>
    $(document).ready(function(){
    $('#upgrade-library').click(function(){
    $.ajax({
        url: '../lbs/upgradeLibrary.php',
        type: 'post',
        success: function(response){
            window.location = response;
        }
    });
    });
    });
</script>

<!-- Search regular expression -->

<script>

  $(function() {
    $("#search-rule").on("click", function() {

        var phraseidentification = $('#phrase-identification-delmodify').val();

        if (!phraseidentification)
        {
            setTimeout("$('#phrase-identification-delmodify').addClass('blink-check');", 100);
            setTimeout("$('#phrase-identification-delmodify').removeClass('blink-check');", 1000);

            return;
        }
        else
        {
            var numLibraries = '<?php if(isset($numberOfLibraries)) echo $numberOfLibraries; else echo " "; ?>'

            if (numLibraries == 1)
            {
                var data = '<?php if (isset($pureJSONforJS)) echo $pureJSONforJS; else echo " "; ?>'
                data = JSON.parse(data);

                var search = document.getElementById('phrase-identification-delmodify').value
                var searchCustom = "c:"+search;
                var searchFlag = search+":*";
                var searchFlagCustom = "c:"+search+":*";

                var ruleset = document.getElementById('ruleset-delmodify').value
                var vertice = document.getElementById('fraudvertice-delmodify').value.toLowerCase();
                var searchPath = data["dictionary"][ruleset][vertice][search];
                var searchPathCustom = data["dictionary"][ruleset][vertice][searchCustom];
                var searchPathFlag = data["dictionary"][ruleset][vertice][searchFlag];
                var searchPathFlagCustom = data["dictionary"][ruleset][vertice][searchFlagCustom];

                if (typeof(searchPath) != "undefined") var finalRegexpString = searchPath.replace(/\//g, "");
                if (typeof(searchPathCustom) != "undefined") var finalRegexpString = searchPathCustom.replace(/\//g, "");
                if (typeof(searchPathFlag) != "undefined") var finalRegexpString = searchPathFlag.replace(/\//g, "");
                if (typeof(searchPathFlagCustom) != "undefined") var finalRegexpString = searchPathFlagCustom.replace(/\//g, "");
                else var finalRegexpString = "no regular expression found";
            }
            else
            {
                var dataSpanish = '<?php if (isset($pureJSONforJSSpanish)) echo $pureJSONforJSSpanish; else echo " "; ?>'
                var dataEnglish = '<?php if (isset($pureJSONforJSEnglish)) echo $pureJSONforJSEnglish; else echo " "; ?>'

                dataSpanish = JSON.parse(dataSpanish);
                dataEnglish = JSON.parse(dataEnglish);
                matched = false;

                var search = document.getElementById('phrase-identification-delmodify').value
                var searchCustom = "c:"+search;
                var searchFlag = search+":*";
                var searchFlagCustom = "c:"+search+":*";
                var ruleset = document.getElementById('ruleset-delmodify').value
                var vertice = document.getElementById('fraudvertice-delmodify').value.toLowerCase();
        
                /* Spanish */

                var searchPathSpanish = dataSpanish["dictionary"][ruleset][vertice][search];
                var searchPathSpanishCustom = dataSpanish["dictionary"][ruleset][vertice][searchCustom];
                var searchPathSpanishFlag = dataSpanish["dictionary"][ruleset][vertice][searchFlag];
                var searchPathSpanishFlagCustom = dataSpanish["dictionary"][ruleset][vertice][searchFlagCustom];

                /* English */

                var searchPathEnglish = dataEnglish["dictionary"][ruleset][vertice][search];
                var searchPathEnglishCustom = dataEnglish["dictionary"][ruleset][vertice][searchCustom];
                var searchPathEnglishFlag = dataEnglish["dictionary"][ruleset][vertice][searchFlag];
                var searchPathEnglishFlagCustom = dataEnglish["dictionary"][ruleset][vertice][searchFlagCustom];

                var finalRegexpString = null;

                if (typeof(searchPathSpanish) != "undefined")
                {
                    matched = true;
                    finalRegexpString = searchPathSpanish.replace(/\//g, "");

                    $(".select-option-styled-language-search option:selected").removeAttr("selected");
                    $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                    $('#library-search-language').val('fta_text_rule_spanish');
                    $('#library-search-language').niceSelect('update');
                }
                else if (typeof(searchPathSpanishCustom) != "undefined")
                {
                    matched = true;
                    finalRegexpString = searchPathSpanishCustom.replace(/\//g, "");

                    $(".select-option-styled-language-search option:selected").removeAttr("selected");
                    $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                    $('#library-search-language').val('fta_text_rule_spanish');
                    $('#library-search-language').niceSelect('update');
                }
                else if (typeof(searchPathSpanishFlag) != "undefined")
                {
                    matched = true;
                    finalRegexpString = searchPathSpanishFlag.replace(/\//g, "");

                    $(".select-option-styled-language-search option:selected").removeAttr("selected");
                    $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                    $('#library-search-language').val('fta_text_rule_spanish');
                    $('#library-search-language').niceSelect('update');
                }
                else if (typeof(searchPathSpanishFlagCustom) != "undefined")
                {
                    matched = true;
                    finalRegexpString = searchPathSpanishFlagCustom.replace(/\//g, "");

                    $(".select-option-styled-language-search option:selected").removeAttr("selected");
                    $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                    $('#library-search-language').val('fta_text_rule_spanish');
                    $('#library-search-language').niceSelect('update');
                }
                else finalRegexpString = "no regular expression found";

                if (matched == false)
                {
                    if (typeof(searchPathEnglish) != "undefined")
                    {
                        finalRegexpString = searchPathEnglish.replace(/\//g, "");
                    
                        $(".select-option-styled-language-search option:selected").removeAttr("selected");
                        $(".select-option-styled-language-search option[value=fta_text_rule_english]").attr('selected', 'selected');
                        $('#library-search-language').val('fta_text_rule_english');
                        $('#library-search-language').niceSelect('update');
                    }
                    else if (typeof(searchPathEnglishCustom) != "undefined")
                    {
                        finalRegexpString = searchPathEnglishCustom.replace(/\//g, "");
                    
                        $(".select-option-styled-language-search option:selected").removeAttr("selected");
                        $(".select-option-styled-language-search option[value=fta_text_rule_english]").attr('selected', 'selected');
                        $('#library-search-language').val('fta_text_rule_english');
                        $('#library-search-language').niceSelect('update');
                    }
                    else if (typeof(searchPathEnglishFlag) != "undefined")
                    {
                        matched = true;
                        finalRegexpString = searchPathEnglishFlag.replace(/\//g, "");

                        $(".select-option-styled-language-search option:selected").removeAttr("selected");
                        $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                        $('#library-search-language').val('fta_text_rule_spanish');
                        $('#library-search-language').niceSelect('update');
                    }
                    else if (typeof(searchPathEnglishFlagCustom) != "undefined")
                    {
                        matched = true;
                        finalRegexpString = searchPathEnglishFlagCustom.replace(/\//g, "");

                        $(".select-option-styled-language-search option:selected").removeAttr("selected");
                        $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                        $('#library-search-language').val('fta_text_rule_spanish');
                        $('#library-search-language').niceSelect('update');
                    }
                    else finalRegexpString = "no regular expression found";
                }
            }
        
            $("#regexpression-delmodify").val(finalRegexpString);

        }
    });
  });

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Flag button -->

<script>

function checkboxAddFlag()
{
    var checkbox = document.getElementById('checkbox-addflag');
    var checkboxGeneral = document.getElementById('add-flag');

    if(checkbox.checked === true)
    {
        $('#flag-changer').removeClass('fa-flag-o');
        $('#flag-changer').addClass('fa-flag');
        checkboxGeneral.style.background = "white";
        $('#add-flag').css('border', '1px solid #B1B3B1');
    }
    else
    {
        $('#flag-changer').removeClass('fa-flag');
        $('#flag-changer').addClass('fa-flag-o');
        checkboxGeneral.style.background = "white";
        $('#add-flag').css('border', '1px solid #B1B3B1');
    }
}

</script>