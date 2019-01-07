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
 * Date: 2019-02
 * Revision: v1.3.1-ai
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

require '../vendor/autoload.php';
include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/elasticsearch.php";

$configFile = parse_ini_file("../config.ini");
$ESalerterIndex = $configFile['es_alerter_index'];

$documentId = filter($_GET['id']);
$indexId = filter($_GET['idx']);
$eventPhrase = getAlertIdData($documentId, $ESalerterIndex, "AlertEvent");
$regExp = filter($_GET['regexp']);

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

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }
    
    .phrase-viewer
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
        margin-bottom: 20px;
        overflow-y: scroll;
    }

    .footer-statistics
    {
        background-color: #e8e9e8;
        border-radius: 5px 5px 5px 5px;
        padding: 8px 8px 8px 8px;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Expression analysis</h4>
</div>

<div class="div-container">
    
    <!-- Editable content if admin -->
    
    <?php
    
    $notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot");
    $sanitizedPhrases = decRijndael($eventPhrase['hits']['hits'][0]['_source']['stringHistory']);
    
    foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);

    if($session->username == "admin")
    {
        
        echo '<div id="reviewPhrasesDivArea" class="phrase-viewer" contenteditable=true>';
        echo $sanitizedPhrases;
    }
    else
    {
        echo '<div class="phrase-viewer" contenteditable=false>';
        echo $sanitizedPhrases;
    }
        
    ?>
        
    </div>

    <?php

    $regularExpression = (strlen(base64_decode($regExp)) > 40) ? substr(base64_decode($regExp), 0, 40) . ' ...' : base64_decode($regExp);

    echo '<div class="footer-statistics"><span class="fa fa-exclamation-triangle font-aw-color">&nbsp;&nbsp;</span>Triggered by <i><b>"'.$regularExpression.'"</b></i> regular expression</div>'; 

    ?>
    
    <div class="modal-footer window-footer-config">
        
        <?php echo '<form id="formReview" name="formReview" method="post" action="mods/reviewPhrases?id='.$documentId.'&idx='.$indexId.'" onsubmit="return getContent()">'; ?>
    
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