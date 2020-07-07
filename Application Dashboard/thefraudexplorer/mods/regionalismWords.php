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
 * Date: 2020-07
 * Revision: v1.4.6-aim
 *
 * Description: Code for regionalism words
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
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config-regionalism
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-regionalism
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

    .master-container-regionalism
    {
        width: 100%; 
    }

    .downloadfile
    {
        outline: 0 !important;
    }

    .regionalism-text
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .input-value-text-regionalism
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
        50% { border-color: #ff6666; } 
    }

    .blink-check
    {
        -webkit-animation: blink .1s step-end 6 alternate;
    }

</style>

<?php

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$regionalismESFile = encRijndael("/var/www/html/thefraudexplorer/core/spell/customESdictionary.txt");
$regionalismENFile = encRijndael("/var/www/html/thefraudexplorer/core/spell/customENdictionary.txt");

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Regionalism words</h4>
</div>

<div class="div-container-regionalism">

    <form id="formRegionalism" name="formRegionalism" method="post" action="mods/setRegionalism">

    <div class="master-container-regionalism">

        <p class="regionalism-text">
        Please specify the words you need to process as "regionalism" by the internal spell correction system. These strings will be treated as a valid words and included in the standard spanish and english dictionaries for correction:<br><br>
        </p>

        <textarea name="regionalismwords" id="regionalismwords" placeholder="hip, pajamas, frenemy, bromance, ginormous" class="input-value-text-regionalism"></textarea>

    </div>

    <br>
    <a class="downloadfile" onclick="libraryLanguage();" href="mods/downloadRegionalism?le=BhH193lFloVgj1Jd">
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
    <div class="modal-footer window-footer-config-regionalism">
        <br>
        
        <?php    
            
            if ($session->username != "admin") 
            {
                echo '<input type="button" class="btn btn-danger setup" value="Remove words" name="removewords" style="outline: 0 !important;">';
                echo '<input type="button" class="btn btn-success setup" value="Add words" name="addwords" style="outline: 0 !important;">';
            }
            else
            {
                echo '<button type="button" id="button-del-words" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Deleting, please wait" name="removewords" style="outline: 0 !important;">Remove words</button>';
                echo '<button type="button" id="button-add-words" class="btn btn-success setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Adding, please wait" name="addwords" style="outline: 0 !important;">Add all words</button>';
            } 

        ?>

    </div>

    </form>
</div>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Download regionalism file -->

<script>

function libraryLanguage()
{
    var selection = $("#library-language").val();
    var theLink = "mods/downloadRegionalism?le=BhH193lFloVgj1Jd".replace('BhH193lFloVgj1Jd', selection);
    $('a').attr("href", theLink);
}

</script>

<!-- Buttons Deleting & Adding -->

<script>

var $btn;

$("#button-add-words").click(function() {

    var phrasesContainer = $('#regionalismwords').val();

    if (!phrasesContainer)
    {
        $target = $('#regionalismwords');
        $target.removeClass('blink-check');
        setTimeout("$target.addClass('blink-check');", 100);
        
        return;
    }
    else
    {
        $('#formRegionalism').submit();
    }

    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

$("#button-del-words").click(function() {

    var phrasesContainer = $('#regionalismwords').val();

    if (!phrasesContainer)
    {
        $target = $('#regionalismwords');
        $target.removeClass('blink-check');
        setTimeout("$target.addClass('blink-check');", 100);
        
        return;
    }
    else
    {
        $('#formRegionalism').submit();
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