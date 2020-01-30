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
 * Date: 2020-02
 * Revision: v1.4.2-aim
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

$alertid = filter($_GET['alertID']);
$alertPhrase = getAlertIdData($alertid, $ESalerterIndex, "AlertEvent");

?>

<style>
    
    @font-face 
    {
        font-family: 'FFont';
        src: url('../fonts/Open_Sans/OpenSans-Regular.ttf');
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
        overflow-y: scroll;
    }

</style>
    
<?php
    
    $notwantedWords = array("rwin", "lwin", "decimal", "next", "snapshot");
    $sanitizedPhrases = decRijndael($alertPhrase['hits']['hits'][0]['_source']['stringHistory']);
    
    foreach($notwantedWords as $notWanted) $sanitizedPhrases = str_replace($notWanted, '', $sanitizedPhrases);

    echo '<br>';
    echo '<div class="phrase-viewer" contenteditable=false>';
    echo $sanitizedPhrases;
    echo '</div>'
        
?>