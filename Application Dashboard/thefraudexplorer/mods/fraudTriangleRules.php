<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2019-05
 * Revision: v1.3.3-ai
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
        width: calc(100% - 145px); 
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
        width: calc(100% - 184px);
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
        width: calc(100% - 184px); 
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

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .select-option-styled-rulesworkshop
    {
        width: 165px;
    }

    .select-option-styled-rulesworkshop .list
    {
        width: 165px;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .select-option-styled-fraudvertice
    {
        width: 140px;
        margin-right: 0px;
    }

    .select-option-styled-fraudvertice .list
    {
        width: 140px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .nice-select
    {
        height: 30px;
        border: 1px solid #ccc;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
        color: #757575;
        padding: 8px 0px 0px 10px;
        position: relative;
        line-height: 11.6px;
    }

    .nice-select .list 
    { 
        overflow-y: scroll;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px;
    }

    .master-container-library
    {
        width: 100%; 
        border-radius: 5px;
        background: #f2f2f2;
        padding: 0px 10px 15px 10px;
    }

    .container-status-library
    {
        width: 100%; 
        border-radius: 5px;
        background: #f2f2f2;
        padding: 0px 10px 15px 10px;
        height: 30px;
    }
    
    .left-container-library
    {
        width: calc(25% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
        text-align: left;
    }
    
    .right-container-library
    {
        width: calc(75% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
        text-align: left;
    }

    .rule-button-add
    {
        width: 137px;
        height: 30px;
        border-radius: 5px;
        outline: 0 !important;
    }

    .rule-button-delmodify
    {
        width: 67px;
        height: 30px;
        border-radius: 5px;
        outline: 0 !important;
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
            <div class="right-container-library">

                <p class="title-config">Fraud vertice / Phrase identification</p><br>

                <!-- Fraud Triangle vertice -->

                <select class="select-option-styled-fraudvertice" name="fraudvertice-add" id="fraudvertice-add">
                    <option selected="selected">PRESSURE</option>
                    <option>OPPORTUNITY</option>
                    <option>RATIONALIZATION</option>
                </select>

                <input type="text" name="phrase-identification-add" id="phrase-identification-add" autocomplete="off" placeholder="enter here the phrase identification" class="input-value-text-id-add">   
                    
            </div>

            <div class="regexp-container">
                <p class="title-config">Phrase regular expression</p><br>
                <button type="button" class="input-regexp-left">/</button>
                <input type="text" name="regexpression-add" id="regexpression-add" autocomplete="off" placeholder="enter here the regular expression in PCRE format" class="input-value-text-regexp-add">
                <button type="button" class="input-regexp-right">/</button>
                <button type="submit" class="rule-button-add" id="add-rule" name="action" value="addrule">Add rule</button>
            </div>

        </div>

        <br>

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
            <div class="right-container-library">
                   
                <p class="title-config">Fraud vertice / Phrase identification</p><br>

                <!-- Fraud Triangle vertice -->

                <select class="select-option-styled-fraudvertice" name="fraudvertice-delmodify" id="fraudvertice-delmodify">
                    <option selected="selected">PRESSURE</option>
                    <option>OPPORTUNITY</option>
                    <option>RATIONALIZATION</option>
                </select>

                <input type="text" name="phrase-identification-delmodify" id="phrase-identification-delmodify" autocomplete="off" placeholder="enter here the phrase identification" class="input-value-text-id-delmodify">
                <button type="button" class="rule-button-add" id="search-rule">Search rule</button>
                    
            </div>

            <div class="regexp-container">
                <p class="title-config">Phrase regular expression</p><br>
                <button type="button" class="input-regexp-left">/</button>
                <input type="text" name="regexpression-delmodify" id="regexpression-delmodify" autocomplete="off" placeholder="you will see here the regular expression" class="input-value-text-regexp-delmodify">
                <button type="button" class="input-regexp-right">/</button>
                <button type="submit" class="rule-button-delmodify" id="delete-rule" name="action" value="deleterule">Delete</button>
                <button type="submit" class="rule-button-delmodify" id="modify-rule" name="action" value="modifyrule">Modify</button>
            </div>

        </div>

        <br>

        <div class="container-status-library">
            <p class="warning"><i class="fa fa-info-circle fa-lg" aria-hidden="true"></i>&nbsp;Warning, the phrase library database modification could cause unwanted results due to a bad regular expression writing !</p>
        </div>

        <div class="modal-footer window-footer-config">
            <br>
            
            <?php    
            
            echo '<button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Return to analytics</button>';
            echo '<a id="download-rules" class="btn btn-success" style="outline: 0 !important;">Download rules</a>';

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
    $jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);
    $pureJSONforJS = str_replace("\u0022","\\\\\"", json_encode($jsonFT, JSON_HEX_APOS|JSON_HEX_QUOT)); 
    $pureJSONforJS = escapeJsonString($pureJSONforJS);

    ?>

  $(function() {
    $("#search-rule").on("click", function() {
        
        var data = '<?php echo $pureJSONforJS; ?>'
        data = JSON.parse(data);
        var search = document.getElementById('phrase-identification-delmodify').value
        var ruleset = document.getElementById('ruleset-delmodify').value
        var vertice = document.getElementById('fraudvertice-delmodify').value.toLowerCase();
        var searchPath = data["dictionary"][ruleset][vertice][search];

        if (typeof(searchPath) === "undefined") 
        {
            var finalRegexpString = "no regular expression found";
        }
        else
        {
            var finalRegexpString = searchPath.replace(/\//g, "");
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
