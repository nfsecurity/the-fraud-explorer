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
 * Description: Code for words universe
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
include "../lbs/cryptography.php";

$_SESSION['processingStatus'] = "notstarted";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config-regionalism, .window-footer-config-tone
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-regionalism, .div-container-tone
    {
        margin: 20px;
    }

    .tabs
    {
        margin: 20px;
        border-bottom: 1px solid #c9c9c9;
        font-family: 'FFont', sans-serif; font-size: 12px;
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

    .master-container-regionalism, .master-container-tone
    {
        width: 100%; 
    }

    .downloadfile
    {
        outline: 0 !important;
    }

    .regionalism-text, .tone-text
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .input-value-text-regionalism, .input-value-text-tone
    {
        width: 100%; 
        height: 75px; 
        padding: 10px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }
 
    textarea 
    {
        resize: none;
    }

    .select-option-styled-language-reg
    {
        width: 130px;
        height: 35px;
        margin-right: 5px;
        line-height: 33px;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .select-option-styled-language-reg .list
    {
        width: 130px;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        font-family: 'FFont', sans-serif; font-size: 12px;
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

    .tab-regionalism
    {
        display: inline-block;
        margin-bottom: -1px;
        text-align: center;
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
        width: 100px;
        cursor: pointer;
        line-height: 28px; 
        height: 28px; 
        border-top: 1px solid #c9c9c9;
        border-bottom: 1px solid white;
        border-left: 1px solid #c9c9c9; 
        border-right: 1px solid #c9c9c9; 
        border-radius: 5px 5px 0px 0px;
        z-index: 1000;
    }

    .tab-tone
    {
        display: inline-block;
        margin-bottom: -1px;
        margin-left: -4px;
        text-align: center; 
        width: 110px;
        cursor: pointer;
        line-height: 28px; 
        height: 28px; 
        border-top: 1px solid #c9c9c9;
        border-bottom: 1px solid white;
        border-left: 1px solid #c9c9c9; 
        border-right: 1px solid #c9c9c9; 
        border-radius: 5px 5px 0px 0px;
        z-index: 1000;
    }

    .tab-selected
    {
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
    }

    .tab-unselected
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

</style>

<?php

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$regionalismESFile = encRijndael("/var/www/html/thefraudexplorer/core/spell/customESdictionary.txt");
$regionalismENFile = encRijndael("/var/www/html/thefraudexplorer/core/spell/customENdictionary.txt");
$toneESFile = encRijndael("/var/www/html/thefraudexplorer/core/tone/negative_spanish.txt");
$toneENFile = encRijndael("/var/www/html/thefraudexplorer/core/tone/negative_english.txt");

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Words universe</h4>
</div>

<!-- Tabs -->

<div class="tabs">
    <div id="TabRegionalism" class="tab-regionalism">Regionalism</div>
    <div id="TabTone" class="tab-tone">Message tones</div>
</div>

<!-- Regionalism tab -->

<div class="div-container-regionalism" id="regionalismTab">

    <form id="formRegionalism" name="formRegionalism" method="post" action="mods/setWordsUniverse?se=reg">

    <div class="master-container-regionalism">

        <p class="regionalism-text">
        Please specify the words you need to process as "regionalism" by the internal spell correction system. These strings will be treated as a valid words and included in the standard spanish and english dictionaries for correction:<br><br>
        </p>

        <textarea name="regionalismwords" id="regionalismwords" placeholder="hip, pajamas, frenemy, bromance, ginormous" class="input-value-text-regionalism"></textarea>

    </div>

    <br>
    <a class="downloadfile" onclick="libraryRegionalism();" href="mods/downloadWords?le=BhH193lFloVgj1Jd">
        <button type="button" class="btn btn-default" style="width: 423px; outline: 0 !important;">
            Download selected regionalism language dictionary file
        </button>
    </a>

    <select class="select-option-styled-language-reg" name="library-language" id="library-language">

        <?php

            if ($configFile["wc_language"] == "es" || $configFile["wc_language"] == "hu") 
            {
                echo '<option value="'.$regionalismESFile.'" selected="selected">Spanish</option>';
                echo '<option value="'.$regionalismENFile.'">English</option>';
            }
            else if ($configFile["wc_language"] == "en")
            {
                echo '<option value="'.$regionalismESFile.'">Spanish</option>';
                echo '<option value="'.$regionalismENFile.'" selected="selected">English</option>';
            }

        ?>

    </select>

    <br>

    <br>
    <div class="modal-footer window-footer-config-tone">
        <br>
        
        <?php    
            
            if ($session->username != "admin")
            {
                echo '<button type="button" id="button-del-words-regionalism" class="btn btn-danger setup" name="removewords" style="outline: 0 !important;">Remove words</button>';
                echo '<button type="button" id="button-add-words-regionalism" class="btn btn-success setup" name="addwords" style="outline: 0 !important;">Add all words</button>';
            }
            else
            {
                echo '<button type="button" id="button-del-words-regionalism" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Deleting, please wait" name="removewords" style="outline: 0 !important;">Remove words</button>';
                echo '<button type="button" id="button-add-words-regionalism" class="btn btn-success setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Adding, please wait" name="addwords" style="outline: 0 !important;">Add all words</button>';
            } 

        ?>

    </div>

    </form>
</div>

<!-- Tone words tab -->

<div class="div-container-tone" id="toneTab" style="display: none;">

    <form id="formTone" name="formTone" method="post" action="mods/setWordsUniverse?se=ton">

    <div class="master-container-tone">

        <p class="tone-text">
        Please specify the words you need to process as "negative" by the internal message tone system. You can improve the fraud and corruption detection using the negative tone feature through the workflows module setting conditions:<br><br>
        </p>

        <textarea name="tonewords" id="tonewords" placeholder="violation, waste, toxic, ugly, offensive" class="input-value-text-tone"></textarea>

    </div>

    <br>
    <a class="downloadfile" onclick="libraryTone();" href="mods/downloadWords?le=BhH193lFloVgj1Jd">
        <button type="button" class="btn btn-default" style="width: 423px; outline: 0 !important;">
            Download selected tone language dictionary file
        </button>
    </a>

    <select class="select-option-styled-language-reg" name="library-tone" id="library-tone">

        <?php

            if ($configFile["wc_language"] == "es" || $configFile["wc_language"] == "hu") 
            {
                echo '<option value="'.$toneESFile.'" selected="selected">Spanish</option>';
                echo '<option value="'.$toneENFile.'">English</option>';
            }
            else if ($configFile["wc_language"] == "en")
            {
                echo '<option value="'.$toneESFile.'">Spanish</option>';
                echo '<option value="'.$toneENFile.'" selected="selected">English</option>';
            }

        ?>

    </select>

    <br>

    <br>
    <div class="modal-footer window-footer-config-regionalism">
        <br>
        
        <?php    
            
            if ($session->username != "admin") 
            {
                echo '<button type="button" id="button-del-words-tone" class="btn btn-danger setup" name="removewords" style="outline: 0 !important;">Remove words</button>';
                echo '<button type="button" id="button-add-words-tone" class="btn btn-success setup" name="addwords" style="outline: 0 !important;">Add all words</button>';
            }
            else
            {
                echo '<button type="button" id="button-del-words-tone" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Deleting, please wait" name="removewords" style="outline: 0 !important;">Remove words</button>';
                echo '<button type="button" id="button-add-words-tone" class="btn btn-success setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Adding, please wait" name="addwords" style="outline: 0 !important;">Add all words</button>';
            } 

        ?>

    </div>

    </form>
</div>

<!-- Tabs clicks -->

<script>

    $("#TabRegionalism").click(function(event){
        $("#TabTone").removeClass("tab-selected");
        $("#TabTone").addClass("tab-unselected");

        $("#TabRegionalism").removeClass("tab-unselected");
        $("#TabRegionalism").addClass("tab-selected");

        $("#regionalismTab").show();
        $("#toneTab").hide();
    });

    $("#TabTone").click(function(event){
        $("#TabTone").removeClass("tab-unselected");
        $("#TabTone").addClass("tab-selected");

        $("#TabRegionalism").removeClass("tab-selected");
        $("#TabRegionalism").addClass("tab-unselected");

        $("#regionalismTab").hide();
        $("#toneTab").show();
    });

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Download regionalism and tone files -->

<script>

function libraryRegionalism()
{
    var selection = $("#library-language").val();
    var theLink = "mods/downloadWords?le=BhH193lFloVgj1Jd".replace('BhH193lFloVgj1Jd', selection);
    $('a').attr("href", theLink);
}

function libraryTone()
{
    var selection = $("#library-tone").val();
    var theLink = "mods/downloadWords?le=BhH193lFloVgj1Jd".replace('BhH193lFloVgj1Jd', selection);
    $('a').attr("href", theLink);
}

</script>

<!-- Buttons Deleting & Adding -->

<script>

var $btn;

/* Buttons regionalism */

$("#button-add-words-regionalism").click(function() {

    var phrasesContainer = $('#regionalismwords').val();

    if (!phrasesContainer)
    {
        setTimeout("$('#regionalismwords').addClass('blink-check');", 100);
        setTimeout("$('#regionalismwords').removeClass('blink-check');", 1000);

        return;
    }
    else
    {
        $("#formRegionalism").submit(function(event) {
            $(this).append('<input type="hidden" name="addwords" value="Add words" /> ');
            return true;
        });

        $('#formRegionalism').submit();
    }

    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

$("#button-del-words-regionalism").click(function() {

    var phrasesContainer = $('#regionalismwords').val();

    if (!phrasesContainer)
    {
        setTimeout("$('#regionalismwords').addClass('blink-check');", 100);
        setTimeout("$('#regionalismwords').removeClass('blink-check');", 1000);
        
        return;
    }
    else
    {
        $("#formRegionalism").submit(function(event) {
            $(this).append('<input type="hidden" name="removewords" value="Delete words" /> ');
            return true;
        });

        $('#formRegionalism').submit();
    }

    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

/* Buttons tone */

$("#button-add-words-tone").click(function() {

var phrasesContainer = $('#tonewords').val();

if (!phrasesContainer)
{
    setTimeout("$('#tonewords').addClass('blink-check');", 100);
    setTimeout("$('#tonewords').removeClass('blink-check');", 1000);

    return;
}
else
{
    $("#formTone").submit(function(event) {
        $(this).append('<input type="hidden" name="addwords" value="Add words" /> ');
        return true;
    });

    $('#formTone').submit();
}

$btn = $(this);
$btn.button('loading');
setTimeout('getstatus()', 1000);
});

$("#button-del-words-tone").click(function() {

var phrasesContainer = $('#tonewords').val();

if (!phrasesContainer)
{
    setTimeout("$('#tonewords').addClass('blink-check');", 100);
    setTimeout("$('#tonewords').removeClass('blink-check');", 1000);
    
    return;
}
else
{
    $("#formTone").submit(function(event) {
        $(this).append('<input type="hidden" name="removewords" value="Delete words" /> ');
        return true;
    });

    $('#formTone').submit();
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
