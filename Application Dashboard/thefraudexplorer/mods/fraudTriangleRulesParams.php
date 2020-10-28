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
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for append, delete or modify JSON entries
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
include "../lbs/openDBconn.php";

$libraryLanguageADDSelected = null;
if (isset($_POST['library-add-language'])) $libraryLanguageADDSelected = $_POST['library-add-language'];

$libraryLanguageSEARCHSelected = null;
if (isset($_POST['library-search-language'])) $libraryLanguageSEARCHSelected = $_POST['library-search-language'];

$actionTODO = filter($_POST['action']);

$rulesetSelectedADD = null;
$fraudVerticeSelectedADD = null;
$phraseIDSelectedADD = null;
$regularExpressionSelectedADD = null;
$rulesetSelectedDEL = null;
$fraudVerticeSelectedDEL = null;
$phraseIDSelectedDEL = null;
$regularExpressionSelectedDEL = null;
$rulesetSelectedMOD = null;
$fraudVerticeSelectedMOD = null;
$phraseIDSelectedMOD = null;
$regularExpressionSelectedMOD = null;

$proceedToSave = false;
$msg = "";

if ($actionTODO == "addrule")
{
    $fta_lang = $libraryLanguageADDSelected;
    $jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);

    if (isset($_POST['ruleset-add'])) $rulesetSelectedADD = $_POST['ruleset-add'];
    if (isset($_POST['fraudvertice-add'])) $fraudVerticeSelectedADD = $_POST['fraudvertice-add'];
    if (isset($_POST['phrase-identification-add'])) $phraseIDSelectedADD = "c:".$_POST['phrase-identification-add'];
    if (isset($_POST['regexpression-add'])) $regularExpressionSelectedADD = $_POST['regexpression-add'];
    
    if (isset($_POST['addflag'])) $addFlag = filter($_POST['addflag']);
    else $addFlag = NULL;

    /* Add rule */

    if ($phraseIDSelectedADD != null && $regularExpressionSelectedADD != null) 
    {
        if ($addFlag == "addflag") $phraseIDSelectedADD = $phraseIDSelectedADD . ":*";

        $jsonFT['dictionary'][$rulesetSelectedADD][strtolower($fraudVerticeSelectedADD)][$phraseIDSelectedADD] = "/".$regularExpressionSelectedADD."/";
        $proceedToSave = true;
        $msg = "Successfully added rule to library";
    }
}
else if ($actionTODO == "deleterule")
{
    $fta_lang = $libraryLanguageSEARCHSelected;
    $jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);

    if (isset($_POST['ruleset-delmodify'])) $rulesetSelectedDEL = $_POST['ruleset-delmodify'];
    if (isset($_POST['fraudvertice-delmodify'])) $fraudVerticeSelectedDEL = $_POST['fraudvertice-delmodify'];
    if (isset($_POST['phrase-identification-delmodify'])) $phraseIDSelectedDEL = $_POST['phrase-identification-delmodify'];
    if (isset($_POST['regexpression-delmodify'])) $regularExpressionSelectedDEL = $_POST['regexpression-delmodify'];

    /* Delete rule */

    if ($phraseIDSelectedDEL != null && $regularExpressionSelectedDEL != null) 
    {
        $phraseIDSelectedDELCustom = "c:".$phraseIDSelectedDEL;
        $phraseIDSelectedDELFlag = $phraseIDSelectedDEL.":*";
        $phraseIDSelectedDELFlagCustom = "c:".$phraseIDSelectedDEL.":*";

        $key = @$jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDEL];
        $keyCustom = @$jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELCustom];
        $keyFlag = @$jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELFlag];
        $keyFlagCustom = @$jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELFlagCustom];

        if (isset($key)) 
        {
            unset($jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDEL]);
            $msg = "Successfully removed rule from library";
        }
        else if (isset($keyCustom)) 
        {
            unset($jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELCustom]);
            $msg = "Successfully removed rule from library";
        }
        else if (isset($keyFlag)) 
        {
            unset($jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELFlag]);
            $msg = "Successfully removed rule from library";
        }
        else if (isset($keyFlagCustom)) 
        {
            unset($jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDELFlagCustom]);
            $msg = "Successfully removed rule from library";
        }
        else $msg = "Phrase rule does not exist";
        
        $proceedToSave = true;
    }
}
else if ($actionTODO == "modifyrule")
{
    $fta_lang = $libraryLanguageSEARCHSelected;
    $jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);

    if (isset($_POST['ruleset-delmodify'])) $rulesetSelectedMOD = $_POST['ruleset-delmodify'];
    if (isset($_POST['fraudvertice-delmodify'])) $fraudVerticeSelectedMOD = $_POST['fraudvertice-delmodify'];
    if (isset($_POST['phrase-identification-delmodify'])) $phraseIDSelectedMOD = $_POST['phrase-identification-delmodify'];
    if (isset($_POST['regexpression-delmodify'])) $regularExpressionSelectedMOD = $_POST['regexpression-delmodify'];

    /* Modify rule */

    if ($phraseIDSelectedMOD != null && $regularExpressionSelectedMOD != null)
    {
        $phraseIDSelectedMODCustom = "c:".$phraseIDSelectedMOD;
        $phraseIDSelectedMODFlag = $phraseIDSelectedMOD.":*";
        $phraseIDSelectedMODFlagCustom = "c:".$phraseIDSelectedMOD.":*";
        $key = @$jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMOD];
        $keyCustom = @$jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODCustom];
        $keyFlag = @$jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODFlag];
        $keyFlagCustom = @$jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODFlagCustom];

        if (isset($key)) 
        {
            $jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMOD] = "/".$regularExpressionSelectedMOD."/";
            $msg = "Successfully modified phrase rule";
        }
        else if (isset($keyCustom)) 
        {
            $jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODCustom] = "/".$regularExpressionSelectedMOD."/";
            $msg = "Successfully modified phrase rule";
        }
        else if (isset($keyFlag)) 
        {
            $jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODFlag] = "/".$regularExpressionSelectedMOD."/";
            $msg = "Successfully modified phrase rule";
        }
        else if (isset($keyFlagCustom)) 
        {
            $jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMODFlagCustom] = "/".$regularExpressionSelectedMOD."/";
            $msg = "Successfully modified phrase rule";
        }
        else $msg = "Phrase rule does not exist";

        $proceedToSave = true;
    }
}

/* Save Array to JSON file */

if ($proceedToSave == true)
{
    if ($actionTODO == "addrule")
    {
        $fta_lang = $libraryLanguageADDSelected;

        $jsonData = json_encode($jsonFT, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($configFile[$fta_lang], $jsonData);
    }
    else if ($actionTODO == "deleterule" || $actionTODO == "modifyrule")
    {
        $fta_lang = $libraryLanguageSEARCHSelected;

        $jsonData = json_encode($jsonFT, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($configFile[$fta_lang], $jsonData);
    }
}

$_SESSION['wm'] = encRijndael($msg);

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>