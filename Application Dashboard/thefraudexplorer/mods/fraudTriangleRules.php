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
 * Date: 2020-05
 * Revision: v1.4.4-aim
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
        margin-right: -6px !important;
        margin-left: 0px !important;
        border-left: 0px;
        border-top: solid 1px #c9c9c9;
        border-bottom: solid 1px #c9c9c9;
        outline: none;
        font-family: Courier; font-size: 12px;
        border-radius: 0px 0px 0px 0px;
    }

    .input-value-text-regexp-delmodify
    {
        width: 513px;
        height: 30px; 
        padding: 5px;
        margin-right: -6px !important;
        margin-left: 0px !important;
        border-left: 0px;
        border-top: solid 1px #c9c9c9;
        border-bottom: solid 1px #c9c9c9;
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
        width: 137px;
        height: 30px;
        min-height: 30px;
        border-radius: 5px;
        outline: 0 !important;
        background: white;
        border: 1px solid #BFC0BF;
        font-family: Verdana, sans-serif; font-size: 11px !important;
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
        font-family: Verdana, sans-serif; font-size: 11px !important;
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
        border-radius: 5px 0px 0px 5px;
        margin-top: 1px;
        margin-left: 2px !important;
        font-family: Courier; font-size: 14px;
        border: 0px solid #c9c9c9;
        text-align: center;
        float: left;
    }

    .input-regexp-right
    {
        background: #c9c9c9;
        width: 20px;
        height: 31px;
        border-radius: 0px 5px 5px 0px;
        margin-left: 0px !important;
        font-family: Courier; font-size: 14px;
        border-top: 1px solid #f2f2f2;
        text-align: center;
        margin-bottom: 0px;
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
                <p class="title-config">Phrase regular expression</p><br>
                <button type="button" class="input-regexp-left">/</button>
                <input type="text" name="regexpression-add" id="regexpression-add" autocomplete="off" placeholder="enter here the regular expression in PCRE format" class="input-value-text-regexp-add">
                <button type="button" class="input-regexp-right">/</button>
                <button type="submit" class="rule-button-add" id="add-rule" name="action" value="addrule">Add rule</button>
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
                <p class="title-config">Phrase regular expression</p><br>
                <button type="button" class="input-regexp-left">/</button>
                <input type="text" name="regexpression-delmodify" id="regexpression-delmodify" autocomplete="off" placeholder="you will see here the regular expression" class="input-value-text-regexp-delmodify">
                <button type="button" class="input-regexp-right" style="margin-right: 2px !important;">/</button>
                <button type="button" class="rule-button-searchdelmodify" id="search-rule">SCH</button>
                <button type="submit" class="rule-button-searchdelmodify" id="delete-rule" name="action" value="deleterule">DEL</button>
                <button type="submit" class="rule-button-searchdelmodify" id="modify-rule" name="action" value="modifyrule">MOD</button>
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

                $remotePhraseLibraryURLSpanish = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/core/rules/".$phraseNameSelectionSpanish;
                $onlineLibrarySpanish = json_decode(file_get_contents($remotePhraseLibraryURLSpanish), true);
                $remotePhraseLibraryURLEnglish = "https://raw.githubusercontent.com/nfsecurity/the-fraud-explorer/master/Application%20Dashboard/thefraudexplorer/core/rules/".$phraseNameSelectionEnglish;
                $onlineLibraryEnglish = json_decode(file_get_contents($remotePhraseLibraryURLEnglish), true);

                preg_match('/version: (.*),/', $localLibrarySpanish["_comment"], $localPhraseLibraryVersionSpanish);
                preg_match('/version: (.*),/', $localLibraryEnglish["_comment"], $localPhraseLibraryVersionEnglish);

                preg_match('/version: (.*),/', $onlineLibrarySpanish["_comment"], $remotePhraseLibraryVersionSpanish);
                preg_match('/version: (.*),/', $onlineLibraryEnglish["_comment"], $remotePhraseLibraryVersionEnglish);

                echo '<p class="warning"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;&nbsp;';

                if (isset($localPhraseLibraryVersionSpanish) && isset($remotePhraseLibraryVersionSpanish) && isset($localPhraseLibraryVersionEnglish) && isset($remotePhraseLibraryVersionEnglish))
                {
                    if (($localPhraseLibraryVersionSpanish[1] != $remotePhraseLibraryVersionSpanish[1]) || ($localPhraseLibraryVersionEnglish[1] != $remotePhraseLibraryVersionEnglish[1]))
                    {
                        echo 'There are different versions of the phrase libraries at the official repo. ES [loc'. $localPhraseLibraryVersionSpanish[1].'-rem'.$remotePhraseLibraryVersionSpanish[1].'] - EN [loc'.$localPhraseLibraryVersionEnglish[1].'-rem'.$remotePhraseLibraryVersionEnglish[1].']';
                    }
                    else echo 'Your phrase libraries database are up to date, you don\'t need to upgrade your libraries now but pay future attention !';
                }
                else echo 'Warning, the phrase library database modification could cause unwanted results due to a bad regular expression writing !';

                echo '</p>';

            ?>

        </div>

        <div class="modal-footer window-footer-library">
            
            <?php    
            
            echo '<a id="upgrade-library" class="btn btn-danger" data-dismiss="modal" style="outline: 0 !important;">Synchronize libraries</a>';
            echo '<a id="download-rules" class="btn btn-success" style="outline: 0 !important;">Download libraries</a>';

            ?>
        
        </div>
    </form>
</div>

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

  $(function() {
    $("#search-rule").on("click", function() {

        var numLibraries = '<?php if(isset($numberOfLibraries)) echo $numberOfLibraries; else echo " "; ?>'

        if (numLibraries == 1)
        {
            var data = '<?php if (isset($pureJSONforJS)) echo $pureJSONforJS; else echo " "; ?>'
            data = JSON.parse(data);
            var search = document.getElementById('phrase-identification-delmodify').value
            var ruleset = document.getElementById('ruleset-delmodify').value
            var vertice = document.getElementById('fraudvertice-delmodify').value.toLowerCase();
            var searchPath = data["dictionary"][ruleset][vertice][search];

            if (typeof(searchPath) === "undefined") var finalRegexpString = "no regular expression found";
            else var finalRegexpString = searchPath.replace(/\//g, "");
        }
        else
        {
            var dataSpanish = '<?php if (isset($pureJSONforJSSpanish)) echo $pureJSONforJSSpanish; else echo " "; ?>'
            var dataEnglish = '<?php if (isset($pureJSONforJSEnglish)) echo $pureJSONforJSEnglish; else echo " "; ?>'

            dataSpanish = JSON.parse(dataSpanish);
            dataEnglish = JSON.parse(dataEnglish);

            var search = document.getElementById('phrase-identification-delmodify').value
            var ruleset = document.getElementById('ruleset-delmodify').value
            var vertice = document.getElementById('fraudvertice-delmodify').value.toLowerCase();
     
            var searchPathSpanish = dataSpanish["dictionary"][ruleset][vertice][search];
            var searchPathEnglish = dataEnglish["dictionary"][ruleset][vertice][search];

            var finalRegexpString = null;

            if (typeof(searchPathSpanish) === "undefined") finalRegexpString = "no regular expression found";
            else 
            {
                finalRegexpString = searchPathSpanish.replace(/\//g, "");

                $(".select-option-styled-language-search option:selected").removeAttr("selected");
                $(".select-option-styled-language-search option[value=fta_text_rule_spanish]").attr('selected', 'selected');
                $('#library-search-language').val('fta_text_rule_spanish');
                $('#library-search-language').niceSelect('update');

            }

            if (typeof(searchPathEnglish) === "undefined") inalRegexpString = "no regular expression found";
            else 
            {
                finalRegexpString = searchPathEnglish.replace(/\//g, "");
               
                $(".select-option-styled-language-search option:selected").removeAttr("selected");
                $(".select-option-styled-language-search option[value=fta_text_rule_english]").attr('selected', 'selected');
                $('#library-search-language').val('fta_text_rule_english');
                $('#library-search-language').niceSelect('update');
            }
        }
    
    $("#regexpression-delmodify").val(finalRegexpString);
    });
  });

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>