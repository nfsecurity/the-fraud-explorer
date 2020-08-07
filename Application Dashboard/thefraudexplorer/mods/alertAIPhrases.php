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
 * Date: 2020-08
 * Revision: v1.4.7-aim
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

$alertid = filter($_GET['id']);
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
        color: #555;
        font-size:12px;
        padding: 7px 7px 7px 7px;
        background: #f7f7f7;
        overflow-y: scroll;
    }

    .matchedStyle
    {
        color: black;
        font-family: 'FFont-Bold', sans-serif;
        font-style: italic;
    }

</style>
    
<?php
   
    $sanitizedPhrases = decRijndael($alertPhrase['hits']['hits'][0]['_source']['stringHistory']);
    $phraseTyped = decRijndael($alertPhrase['hits']['hits'][0]['_source']['wordTyped']);
    $agentId = $alertPhrase['hits']['hits'][0]['_source']['agentId'];

    /* Phrase sanitization process */

    $sanitizedPhrases = phraseSanitization($sanitizedPhrases, $notwantedWords);

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
                        if (preg_match_all($termPhrase."i", $sanitizedPhrases, $matches))
                        {
                            $phrasesMatched[] = $matches;
                        }
                    }
                }
                $rule = $ruleset;
            }
        }

    /* Print sanitized phrase history */

    echo '<br>';
    echo '<div class="phrase-viewer" contenteditable=false>';
    echo '<p>'.$sanitizedPhrases.'</p>';
    echo '</div>'
        
?>

<script>

    <?php

    foreach ($phrasesMatched as $key => $value)
    {
        echo "var matchedPhrase = '" .$value[0][0]. "';";
        echo "$('p:contains('+matchedPhrase+')', document.body).each(function() { $(this).html($(this).html().replace(new RegExp(matchedPhrase, 'g'), '<span class=\"matchedStyle\">'+matchedPhrase+'</span>'));});";
    }

    ?>

</script>
