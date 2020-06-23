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
 * Description: Code for Fraud Simulator
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

require '../vendor/autoload.php';
include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/elasticsearch.php";

?>

<style>
    
    @font-face 
    {
        font-family: 'FFont';
        src: url('../fonts/Open_Sans/OpenSans-Regular.ttf');
    }

    .window-footer-event
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container-event
    {
        margin: 20px;
    }
    
    .phrase-speak-area
    {
        border: 1px solid #e2e2e2;
        line-height: 20px;
        width: 100%;
        height: 173px;
        border-radius: 4px;
        text-align: justify;
        font-family: 'FFont', sans-serif; 
        font-size:12px;
        padding: 7px 7px 7px 7px;
        background: #f7f7f7;
        overflow-y: scroll;
    }

    .matchedStyle-event
    {
        color: black;
        font-family: 'FFont-Bold', sans-serif;
        font-style: italic;
        cursor: pointer;
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .font-aw-color-phrases, .mic-color
    {
        color: #555;
    }

    .container-simulator-headers
    {
        display: block;
    }

    .container-simulator-headers::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .align-left-headers-simulator
    {
        display: inline;
        text-align: center;
        width: 49.2%;
        float: left;
        margin: 10px 0px 0px 0px;
    }

    .align-right-headers-simulator
    {
        display: inline;
        text-align: center;
        width: 49.2%;
        float: right;
        margin: 10px 0px 0px 0px;
    }

    .align-left-footers-simulator
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float: left;
        margin: 10px 0px 0px 0px;
    }

    .align-right-footers-simulator
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float: right;
        margin: 10px 0px 0px 0px;
    }

    .left-header-title-simulator
    {
        font-family: 'FFont-Bold', sans-serif; 
        font-size: 12px;
        padding-left: 2px;
        width: 49.2%;
        float: left;
        display: inline;
        text-align: left;
    }

    .right-header-title-simulator
    {
        font-family: 'FFont-Bold', sans-serif; 
        font-size: 12px;   
        padding-left: 2px;
        width: 49.2%;
        float: right;
        display: inline;
        text-align: left;
    }

    .select-option-styled-rules-simulator, .select-option-styled-application
    {
        width: 100%;
        height: 30px;
        line-height: 30px;
        position: relative;
    }

    .select-option-styled-rules-simulator .list, .select-option-styled-application .list
    {
        width: 100%;
        max-height: 200px;
        border: 1px solid #e2e5e6;
        margin-left: 5px;
        background: #f9f9f9;
        overflow-y: scroll;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
    }

    .btn-mic-start
    {
        position: absolute;
        left: 20;
        bottom: 20;
        outline: 0 !important;
        height: 34px;
    }

    .btn-mic-stop
    {
        position: absolute;
        left: 66;
        bottom: 20;
        outline: 0 !important;
        height: 34px;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Fraud simulator</h4>
</div>

<div class="div-container-event">

    <form id="simulatorForm">

        <p class="left-header-title-simulator">Eleanor Rails belongs to</p>
        <p class="right-header-title-simulator">She are typing/speaking in</p>

        <div class="container-simulator-headers">
                
            <div class="align-left-headers-simulator">     

                <select class="select-option-styled-rules-simulator" name="rulesetSimulator" id="ruleset">

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
    
            <div class="align-right-headers-simulator">

                <select class="select-option-styled-application" name="applicationSimulator" id="application">
                    <option value="Google Chrome - Twitter web">Google Chrome - Twitter web</option>
                    <option value="Microsoft Word - Letter.docx">Microsoft Word - Letter.docx</option>
                    <option value="Microsoft Teams - Chat with Kira">Microsoft Teams - Chat with Kira</option>
                    <option value="VoIP CISCO Phone - Call with 3331">VoIP CISCO Phone - Call with 3331</option>
                    <option value="WhatsApp - Chat with Sensei">WhatsApp - Chat with Sensei</option>
                    <option value="Google Chrome - GMAIL Inbox">Google Chrome - GMAIL Inbox</option>
                    <option value="Outlook - Compose New mail" selected="selected">Outlook - Compose New mail</option>
                    <option value="Google Chrome - Google Hangouts">Google Chrome - Google Hangouts</option>
                    <option value="Microsoft Excel - Project.xlsx">Microsoft Excel - Project.xlsx</option>
                    <option value="SAP - Business One GUI">SAP - Business One GUI</option>
                </select>

            </div>
        </div>

        <br>

        <!-- Text speak area -->

        <div id="simulatorParagraph" name="simulatorPhrases" class="phrase-speak-area" contenteditable=true>
            Hola buenos dias, espero todo ande muy bien, escribo para contarte que estamos algo estresados en el area porque imaginate
            que un proveedor le hizo una propuesta de trabajo a uno de nuestros colaboradores y eso definitivamente representa una violacion a 
            nuestro codigo de etica relacionada con conflictos de interes. Aqui todos dicen que cerremos la boca pero yo te estoy contando, saludos.
        </div>

        <br>

        <!-- Fraud simulator results -->

        <p class="left-header-title-simulator">Fraud Triangle probability</p>
        <p class="right-header-title-simulator">Number of pressure events</p>

        <div class="container-simulator-headers">
                
            <div class="align-left-footers-simulator">      
                <p id="deductionPercentage">0% of fraud probability</p>      
            </div>
    
            <div class="align-right-footers-simulator">
                <p id="pressureCount">0 matched phrases</p>
            </div>
        </div>

        <br>

        <p class="left-header-title-simulator">Number of opportunity events</p>
        <p class="right-header-title-simulator">Number of rationalization events</p>

        <div class="container-simulator-headers">
                
            <div class="align-left-footers-simulator">      
                <p id="opportunityCount">0 matched phrases</p>      
            </div>
    
            <div class="align-right-footers-simulator">
                <p id="rationalizationCount">0 matched phrases</p>
            </div>
        </div>

        <br>

        <div class="modal-footer window-footer-event">
            <br><button type="submit" name="putEvent" class="btn btn-danger" style="outline: 0 !important;" value="putEvent">Put event</button>
            <button type="submit" name="runCheck" id="btnRunCheck" class="btn btn-success" style="outline: 0 !important;" value="runCheck">Run check</button>               
        </div>

    </form>

    <button class="btn btn-default btn-mic-start" id="btnMicStart"><span class="fa fa-microphone fa-lg mic-color"></span></button>   
    <button class="btn btn-default btn-mic-stop" id="btnMicStop"><span class="fa fa-microphone-slash fa-lg mic-color"></span></button> 
   
</div>

<!-- Speech to text -->

<script>
   
   $(function () {
        try {
            var recognition = new webkitSpeechRecognition();
        } catch (e) {
            var recognition = Object;
        }
  
        recognition.continuous = true;
        recognition.interimResults = true;
        
        recognition.onresult = function (event) {
            var txtRec = '';

            for (var i = event.resultIndex; i < event.results.length; ++i) {
                txtRec += event.results[i][0].transcript;
            }
        
            $('#simulatorParagraph').text(txtRec);
        };
    
        $('#btnMicStart').click(function () {
            $('#simulatorParagraph').focus();
            recognition.start();
        });

        $('#btnMicStop').click(function () {
            recognition.stop();
        });
    });

</script>

<!-- AJAX simulator form -->

<script>

$('#simulatorForm button').click(function(e) {

    e.preventDefault();
    var form = new FormData(document.getElementById("simulatorForm"));

    if ($(this).attr("value") == "putEvent") form.append('action', 'putEvent');
    else 
    {
        $('#btnRunCheck').html('<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Triangulating&nbsp;');
        form.append('action', 'runCheck');
    }

    var rawPhrases = $('#simulatorParagraph').text(); 
    rawPhrases = rawPhrases.replace(/(\r\n|\n|\r)/gm, " ");
    rawPhrases = rawPhrases.replace(/\s+/g, " ");
    rawPhrases = rawPhrases.trim();
    $('#simulatorParagraph').text(rawPhrases);

    form.append('simulatorPhrases', rawPhrases);

    $.ajax({
        type: 'POST',
        url: 'mods/simulatorProcess.php',
        data: form,
        success:function(data){

            if (data == "eventputted")
            {
                $(function () {
                    $('#fraud-simulator').modal('toggle');
                });
            }
            else if (data == "nodata")
            {
                /* Fraud triangle vertice counts */

                $('#pressureCount').text("0 matched phrases");
                $('#opportunityCount').text("0 matched phrases");
                $('#rationalizationCount').text("0 matched phrases");
                $('#deductionPercentage').text("0% of fraud probability");

                 /* Testore triangulating button */ 

                $('#btnRunCheck').html('Run check');
            }
            else
            {
                var resultObject = eval(data);
                var pressureCount = 0;
                var opportunityCount = 0; 
                var rationalizationCount = 0;

                for(var i = 0; i < resultObject.length; i++) 
                {
                    var phrases = resultObject[i];

                    var matchedPhrase = resultObject[i];

                    if ('pressure' in phrases) 
                    {
                        var matchedPhrase = phrases['pressure'];

                        $('#simulatorParagraph:contains('+matchedPhrase+')', document.body).each(function() { 
                            $(this).html($(this).html().replace(new RegExp(matchedPhrase, 'g'), '<span class=\"matchedStyle-event tooltip-simulator\" title=\"<div class=tooltip-container><div class=tooltip-row><div class=tooltip-item>Phrase match</div><div class=tooltip-value>'+matchedPhrase+'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud vertice</div><div class=tooltip-value>Pressure</div></div></div>\">'+matchedPhrase+'</span>'));
                        });

                        pressureCount++;
                    }

                    if ('opportunity' in phrases) 
                    {
                        var matchedPhrase = phrases['opportunity'];

                        $('#simulatorParagraph:contains('+matchedPhrase+')', document.body).each(function() { 
                            $(this).html($(this).html().replace(new RegExp(matchedPhrase, 'g'), '<span class=\"matchedStyle-event tooltip-simulator\" title=\"<div class=tooltip-container><div class=tooltip-row><div class=tooltip-item>Phrase match</div><div class=tooltip-value>'+matchedPhrase+'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud vertice</div><div class=tooltip-value>Opportunity</div></div></div>\">'+matchedPhrase+'</span>'));
                        });

                        opportunityCount++;
                    }

                    if ('rationalization' in phrases) 
                    {
                        var matchedPhrase = phrases['rationalization'];

                        $('#simulatorParagraph:contains('+matchedPhrase+')', document.body).each(function() { 
                            $(this).html($(this).html().replace(new RegExp(matchedPhrase, 'g'), '<span class=\"matchedStyle-event tooltip-simulator\" title=\"<div class=tooltip-container><div class=tooltip-row><div class=tooltip-item>Phrase match</div><div class=tooltip-value>'+matchedPhrase+'</div></div><div class=tooltip-row><div class=tooltip-item>Fraud vertice</div><div class=tooltip-value>Rationalization</div></div></div>\">'+matchedPhrase+'</span>'));
                        });

                        rationalizationCount++;
                    }
                }

                /* Fraud triangle vertice counts */

                $('#pressureCount').text(pressureCount + " matched phrases");
                $('#opportunityCount').text(opportunityCount + " matched phrases");
                $('#rationalizationCount').text(rationalizationCount + " matched phrases");
                $('#deductionPercentage').text("0% of fraud probability");

                /* AI deductions */

                if (pressureCount != 0 && opportunityCount != 0 && rationalizationCount != 0) $('#deductionPercentage').text("100% of fraud probability");
                else if (pressureCount != 0 && opportunityCount != 0) $('#deductionPercentage').text("70% of fraud probability");
                else if (pressureCount != 0 && rationalizationCount != 0) $('#deductionPercentage').text("80% of fraud probability");
                else if (opportunityCount != 0 && rationalizationCount != 0) $('#deductionPercentage').text("50% of fraud probability");

                /* Testore triangulating button */ 

                $('#btnRunCheck').html('Run check');

                /* Tooltipster */

                $('.tooltip-simulator').tooltipster({
                    theme: 'tooltipster-custom',
                    contentAsHTML: true,
                    side: 'top',
                    delay: 0,
                    animationDuration: 0
                });
            }

        },
        error:function (data) {
            alert('error');
        },
        async:true,
        processData: false,
        contentType: false,
    });

});

</script>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>