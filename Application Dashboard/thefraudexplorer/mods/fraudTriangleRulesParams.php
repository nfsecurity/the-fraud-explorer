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
include "../lbs/openDBconn.php";

$fta_lang = $configFile['fta_lang_selection'];
$jsonFT = json_decode(file_get_contents($configFile[$fta_lang]), true);
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

if ($actionTODO == "addrule")
{
    if (isset($_POST['ruleset-add'])) $rulesetSelectedADD = $_POST['ruleset-add'];
    if (isset($_POST['fraudvertice-add'])) $fraudVerticeSelectedADD = $_POST['fraudvertice-add'];
    if (isset($_POST['phrase-identification-add'])) $phraseIDSelectedADD = $_POST['phrase-identification-add'];
    if (isset($_POST['regexpression-add'])) $regularExpressionSelectedADD = $_POST['regexpression-add'];

    /* Add rule */

    if ($phraseIDSelectedADD != null && $regularExpressionSelectedADD != null) 
    {
        $jsonFT['dictionary'][$rulesetSelectedADD][strtolower($fraudVerticeSelectedADD)][$phraseIDSelectedADD] = "/".$regularExpressionSelectedADD."/";
        $proceedToSave = true;
    }
}
else if ($actionTODO == "deleterule")
{
    if (isset($_POST['ruleset-delmodify'])) $rulesetSelectedDEL = $_POST['ruleset-delmodify'];
    if (isset($_POST['fraudvertice-delmodify'])) $fraudVerticeSelectedDEL = $_POST['fraudvertice-delmodify'];
    if (isset($_POST['phrase-identification-delmodify'])) $phraseIDSelectedDEL = $_POST['phrase-identification-delmodify'];
    if (isset($_POST['regexpression-delmodify'])) $regularExpressionSelectedDEL = $_POST['regexpression-delmodify'];

    /* Delete rule */

    if ($phraseIDSelectedDEL != null && $regularExpressionSelectedDEL != null) 
    {
        unset($jsonFT['dictionary'][$rulesetSelectedDEL][strtolower($fraudVerticeSelectedDEL)][$phraseIDSelectedDEL]);
        $proceedToSave = true;
    }
}
else if ($actionTODO == "modifyrule")
{
    if (isset($_POST['ruleset-delmodify'])) $rulesetSelectedMOD = $_POST['ruleset-delmodify'];
    if (isset($_POST['fraudvertice-delmodify'])) $fraudVerticeSelectedMOD = $_POST['fraudvertice-delmodify'];
    if (isset($_POST['phrase-identification-delmodify'])) $phraseIDSelectedMOD = $_POST['phrase-identification-delmodify'];
    if (isset($_POST['regexpression-delmodify'])) $regularExpressionSelectedMOD = $_POST['regexpression-delmodify'];

    /* Modify rule */

    if ($phraseIDSelectedMOD != null && $regularExpressionSelectedMOD != null)
    {
        $jsonFT['dictionary'][$rulesetSelectedMOD][strtolower($fraudVerticeSelectedMOD)][$phraseIDSelectedMOD] = "/".$regularExpressionSelectedMOD."/";
        $proceedToSave = true;
    }
}

/* Save Array to JSON file */

if ($proceedToSave == true)
{
    $jsonData = json_encode($jsonFT, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    file_put_contents($configFile[$fta_lang], $jsonData);
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>