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
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for Phrase viewer
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

$configFile = parse_ini_file("../config.ini");
$ESalerterIndex = $configFile['es_alerter_index'];

$documentId = filter($_GET['id']);
$indexId = filter($_GET['ex']);
$eventPhrase = getAlertIdData($documentId, $ESalerterIndex, "AlertEvent");
$regExp = filter($_GET['xp']);
$phraseTyped = filter($_GET['se']);
$alertDate = filter($_GET['te']);
$alertType = filter($_GET['pe']);
$endPoint = filter($_GET['nt']);
$windowTitle = filter($_GET['le']);

?>

<script type="text/javascript">
    function getContent(){
        document.getElementById("reviewPhrasesTextArea").value = document.getElementById("reviewPhrasesDivArea").innerHTML;
    }
</script>

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
    
    .phrase-viewer-event
    {
        border: 1px solid #e2e2e2;
        line-height: 20px;
        width: 100%;
        height: 93px;
        border-radius: 4px;
        text-align: justify;
        font-family: 'FFont', sans-serif; 
        font-size:12px;
        padding: 7px 7px 7px 7px;
        background: #f7f7f7;
        overflow-y: scroll;
    }

    .phrase-viewer-resume
    {
        border: 0px solid #e2e2e2;
        line-height: 20px;
        width: 100%;
        border-radius: 4px;
        text-align: justify;
        font-family: 'FFont', sans-serif; 
        font-size: 12px;
        padding: 0px 0px 0px 0px;
        background: #FFFFFF;
        margin-bottom: 0px;
        overflow-y: none;
    }

    .footer-statistics-event
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
        margin: 15px 0px 15px 0px;
    }

    .matchedStyle-event
    {
        color: black;
        font-family: 'FFont-Bold', sans-serif;
        font-style: italic;
    }

    .matchedStyle-resume
    {
        font-family: 'FFont-Bold', sans-serif;
        font-style: italic;
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Expression analysis</h4>
</div>

<div class="div-container-event">
    
    <!-- Editable content if admin -->
    
    <?php
    
        $notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot");
        $sanitizedPhrases = decRijndael($eventPhrase['hits']['hits'][0]['_source']['stringHistory']);
        $agentId = $eventPhrase['hits']['hits'][0]['_source']['agentId'];
        
        foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);

        echo '<div class="phrase-viewer-resume font-aw-color" contenteditable=false>';
        echo 'At <span class="matchedStyle-resume font-aw-color">'.decRijndael($alertDate).'</span> the endpoint <span class="matchedStyle-resume font-aw-color">'.decRijndael($endPoint).'</span> under <span class="matchedStyle-resume font-aw-color">'.substr(decRijndael($windowTitle), 0, 60) . ' ...' . '</span> expressed a <span class="matchedStyle-resume font-aw-color">'.decRijndael($alertType).'</span> behavior as shown below:<br><br>';
        echo '</div>';

        if($session->username == "admin")
        {
            
            echo '<div id="reviewPhrasesDivArea" class="phrase-viewer-event" contenteditable=true>';
            echo '<p>'.$sanitizedPhrases.'</p>';
            echo '</div>';
        }
        else
        {
            echo '<div class="phrase-viewer-event" contenteditable=false>';
            echo '<p>'.$sanitizedPhrases.'</p>';
            echo '</div>';
        }

        $regularExpression = (strlen(decRijndael($regExp)) > 40) ? substr(decRijndael($regExp), 0, 40) . ' ...' : decRijndael($regExp);

        echo '<div class="footer-statistics-event"><span class="fa fa-exclamation-triangle font-aw-color">&nbsp;&nbsp;</span>Triggered by <i><b>"'.$regularExpression.'"</b></i> regular expression</div>'; 

        /* Traverse phrase library searching for matched phrases */

        $rulesetQuery = sprintf("SELECT ruleset FROM t_agents WHERE agent='%s'", $agentId);
        $rulesetExecution = mysqli_query($connection, $rulesetQuery);
        $rowRuleset = mysqli_fetch_assoc($rulesetExecution);
        $ruleset = $rowRuleset['ruleset'];

        $configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
        $fta_lang = $configFile['fta_lang_selection'];

        if ($fta_lang == "fta_text_rule_multilanguage") 
        {
            $numberOfLibraries = 2;
            $jsonFT[1] = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
            $jsonFT[2] = json_decode(file_get_contents($configFile['fta_text_rule_english']), true);
        }
        else 
        {
            $numberOfLibraries = 1;
            $jsonFT[1] = json_decode(file_get_contents($configFile[$fta_lang]), true);
        }

        for ($lib = 1; $lib<=$numberOfLibraries; $lib++)
        {        
            $fraudTriangleTerms = array('pressure', 'opportunity', 'rationalization');
            $rule = "BASELINE";

            if ($ruleset != "BASELINE") $steps = 2;
            else $steps = 1;

            for($i=1; $i<=$steps; $i++)
            {
                foreach ($fraudTriangleTerms as $term)
                {
                    foreach ($jsonFT[$lib]['dictionary'][$rule][$term] as $field => $termPhrase)
                    {
                        if (preg_match_all($termPhrase, $sanitizedPhrases, $matches))
                        {
                            $phrasesMatched[] = $matches;
                        }
                    }
                }
                $rule = $ruleset;
            }
        }

    ?>
    
    <div class="modal-footer window-footer-event">
        
        <?php echo '<form id="formReview" name="formReview" method="post" action="mods/reviewPhrases?id='.$documentId.'&ex='.rawurlencode($indexId).'" onsubmit="return getContent()">'; ?>
    
            <br>
            <textarea id="reviewPhrasesTextArea" name="reviewPhrasesTextArea" style="display:none"></textarea>
        
            <?php
        
            if($session->username == "admin") echo '<input type="submit" class="btn btn-danger" value="Review & Save" style="outline: 0 !important;">';
        
            ?>
            
            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">Accept</button>
        </form>
    </div>
   
</div>

<!-- Style matched expression -->

<script>

<?php

foreach ($phrasesMatched as $key => $value)
{
    echo "var matchedPhrase = '" .$value[0][0]. "';";
    echo "$('p:contains('+matchedPhrase+')', document.body).each(function() { $(this).html($(this).html().replace(new RegExp(matchedPhrase, 'g'), '<span class=\"matchedStyle-event\">'+matchedPhrase+'</span>'));});";
}

?>

</script>