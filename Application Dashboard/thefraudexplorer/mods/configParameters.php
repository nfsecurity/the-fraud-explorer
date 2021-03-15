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
 * Description: Code for general setup
 */

include "../lbs/login/session.php";
include "../lbs/security.php";
include "../lbs/cronManager.php";

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
include "../lbs/elasticsearch.php";
include "../lbs/cryptography.php";
include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

$msg = "";

function notempty($var)
{
    return ($var === "0" || $var);
}

if (isset($_POST['key']))
{
    $keyPass = filter($_POST['key']);

    if (!empty($keyPass)) 
    {
        mysqli_query($connection, sprintf("UPDATE t_crypt SET password='%s'", $keyPass));

        auditTrail("setup", "successfully modified the endpoints password phrase");
        $msg = $msg . ", key phrase";
    }
}

if (isset($_POST['samplecalculation']))
{
    $setCalculation = filter($_POST['samplecalculation']);

    if($session->domain == "all")
    {
        $calculationQuery = mysqli_query($connection, "SELECT sample_data_calculation FROM t_config"); 
        $sampleQuery = mysqli_fetch_array($calculationQuery);
        $currentSampler = $sampleQuery[0]; 
    }
    else
    {
        $domainConfigTable = "t_config_".str_replace(".", "_", $session->domain);
        $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
        $calculationQuery = mysqli_query($connection, $queryCalc); 
        $sampleQuery = mysqli_fetch_array($calculationQuery); 
        $currentSampler = $sampleQuery[0];
    }

    if (!empty($setCalculation) && ($setCalculation != $currentSampler)) 
    {
        if ($session->domain == "all") mysqli_query($connection, sprintf("UPDATE t_config SET sample_data_calculation='%s'", $setCalculation));
        else 
        {
            $domainConfigTable = "t_config_".str_replace(".", "_", $session->domain);
            $queryConfigTable = "UPDATE ".$domainConfigTable." SET sample_data_calculation='".$setCalculation."'";
            
            mysqli_query($connection, $queryConfigTable);
        }

        auditTrail("setup", "successfully changed the demo sampler status feature");
        $msg = $msg . ", demo sampler";
    }
}

if (isset($_POST['password']))
{
    $originPasword = $_POST['password'];
    $username = "admin";

    if (!empty($originPasword)) 
    {
        $password = sha1(filter($_POST['password']));
        mysqli_query($connection, sprintf("UPDATE t_users SET password='%s' WHERE user='%s'", $password, $username));

        auditTrail("setup", "successfully modified the master admin password for the platform");
        $msg = $msg . ", admin password";
    }
}

if (isset($_POST['encryption']))
{
    $encryption = filter($_POST['encryption']);
    
    if (!empty($encryption)) 
    {
        mysqli_query($connection, sprintf("UPDATE t_crypt SET `key`='%s', `iv`='%s'", $encryption, $encryption));

        auditTrail("setup", "successfully modified the master cipher key for the platform");
        $msg = $msg . ", cipher key";
    }
}

if (isset($_POST['lowfrom']) && isset($_POST['lowto']) && isset($_POST['mediumfrom']) && isset($_POST['mediumto']) && isset($_POST['highfrom']) && isset($_POST['highto']) && isset($_POST['criticfrom']) && isset($_POST['criticto']))
{
    $lowFrom = filter($_POST['lowfrom']);
    $lowTo = filter($_POST['lowto']);
    $mediumFrom = filter($_POST['mediumfrom']);
    $mediumTo = filter($_POST['mediumto']);
    $highFrom = filter($_POST['highfrom']);
    $highTo = filter($_POST['highto']);
    $criticFrom = filter($_POST['criticfrom']);
    $criticTo = filter($_POST['criticto']);
                        
    $scoreQuery = mysqli_query($connection, "SELECT * FROM t_config");
    $scoreResult = mysqli_fetch_array($scoreQuery);

    $currentlowFrom = $scoreResult[0];
    $currentlowTo = $scoreResult[1];
    $currentmediumFrom = $scoreResult[2];
    $currentmediumTo = $scoreResult[3];
    $currenthighFrom = $scoreResult[4];
    $currenthighTo = $scoreResult[5];
    $currentcriticFrom = $scoreResult[6];
    $currentcriticTo = $scoreResult[7];

    if (notempty($lowFrom) && notempty($lowTo) && notempty($mediumFrom) && notempty($mediumTo) && notempty($highFrom) && notempty($highTo) && notempty($criticFrom) && notempty($criticTo)) 
    {
        if ($currentlowFrom != $lowFrom || $currentlowTo != $lowTo || $currentmediumFrom != $mediumFrom || $currentmediumTo != $mediumTo || $currenthighFrom != $highFrom || $currenthighTo != $highTo || $currentcriticFrom != $criticFrom || $currentcriticTo != $criticTo)
        {
            mysqli_query($connection, sprintf("UPDATE t_config SET score_ts_low_from='%s', score_ts_low_to='%s', score_ts_medium_from='%s', score_ts_medium_to='%s', score_ts_high_from='%s', score_ts_high_to='%s', score_ts_critic_from='%s', score_ts_critic_to='%s'", $lowFrom, $lowTo, $mediumFrom, $mediumTo, $highFrom, $highTo, $criticFrom, $criticTo));
    
            auditTrail("setup", "successfully modified the criticality level intervals");
            $msg = $msg . ", criticality levels";
        }
    }
}

if (isset($_POST['ftacron']))
{
    $ftacronSelected = filter($_POST['ftacron']);
    $cron_manager = new CronManager();
    $minutes = $cron_manager->cron_get_minutes("fta-ai-processor");

    if ($minutes == "false") $minutes = "disabled";

    $currentFtacron = $minutes;

    if (!empty($_POST['ftacron']) && ($currentFtacron != $ftacronSelected)) 
    {
        $cronJobMinutes = filter($_POST['ftacron']);
        $remove_cron_result = $cron_manager->remove_cronjob('fta-ai-processor');
        if($_POST['ftacron'] != "disabled") $cron_add_result = $cron_manager->add_cronjob('*/'.$cronJobMinutes.' * * * * cd /var/www/html/thefraudexplorer/core/ ; /usr/bin/php AIFraudTriangleProcessor.php', 'fta-ai-processor');
    
        auditTrail("setup", "successfully modified the FTA/AI daily schedule");
        $msg = $msg . ", scheduler";
    }
}

if (isset($_POST['librarylanguage']))
{
    $languageSelected = filter($_POST['librarylanguage']);
    $configFile = parse_ini_file("../config.ini");
    $currentLanguage = $configFile['wc_language'];

    if (!empty($_POST['librarylanguage']) && ($currentLanguage != $languageSelected))
    {
        $ftaLanguagetoSet = "fta_lang_selection = \\\"fta_text_rule";

        if ($languageSelected == "es") $ftaLanguagetoSet = $ftaLanguagetoSet . "_spanish\\\"";
        else if ($languageSelected == "en") $ftaLanguagetoSet = $ftaLanguagetoSet . "_english\\\"";
        else $ftaLanguagetoSet = $ftaLanguagetoSet . "_multilanguage\\\"";

        $wordCorrectiontoSet = "wc_language = \\\"" . $languageSelected . "\\\"";
     
        /* Change language in config.ini file */
 
        $ftaLang = "fta_lang_selection = \\\"" . $configFile['fta_lang_selection'] . "\\\"";
        $wcLang = "wc_language = \\\"" . $configFile['wc_language'] . "\\\"";

        $samplerLangSrc = explode("/", $configFile['es_sample_csv']);
        $samplerLang = $samplerLangSrc[6];
        $samplerLangToSet = "sampledata_".$languageSelected.".csv";

        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$wcLang.'/'.$wordCorrectiontoSet.'/g; s/'.$ftaLang.'/'.$ftaLanguagetoSet.'/g; s/'.$samplerLang.'/'.$samplerLangToSet.'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = exec($replaceParams);

        /* Change sampler language */
        
        $configFile = parse_ini_file("../config.ini");
        
        /* Delete Index and related data */
        
        deleteIndex($configFile['es_sample_alerter_index'], $configFile);
        mysqli_query($connection, sprintf("DELETE FROM t_inferences WHERE domain='thefraudexplorer.com'"));

        /* Insert sample data */

        insertSampleData($configFile);

        auditTrail("setup", "successfully changed the library language");
        $msg = $msg . ", library language";
    }
}

if (isset($_POST['spellchecker']))
{
    $spellSelected = filter($_POST['spellchecker']);
    $configFile = parse_ini_file("../config.ini");
    $currentSpell = $configFile['wc_enabled'];

    if (!empty($_POST['spellchecker']) && ($currentSpell != $spellSelected))
    {
        $spellCheckertoSet = "wc_enabled = \\\"";

        if ($spellSelected == "yes") $spellCheckertoSet = $spellCheckertoSet . "yes\\\"";
        else if ($spellSelected == "no") $spellCheckertoSet = $spellCheckertoSet . "no\\\"";
     
        /* Change spell checker in config.ini file */
 
        $wcSpell = "wc_enabled = \\\"" . $configFile['wc_enabled'] . "\\\"";
        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$wcSpell.'/'.$spellCheckertoSet.'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = exec($replaceParams);

        auditTrail("setup", "successfully changed the main spelling corrector status");
        $msg = $msg . ", spelling corrector";
    }
}

if (isset($_POST['defaultruleset']))
{
    $ruleSelected = filter($_POST['defaultruleset']);
    $configFile = parse_ini_file("../config.ini");
    $currentRule = $configFile['singup_ruleset'];

    if (!empty($_POST['defaultruleset']) && ($currentRule != $ruleSelected))
    {
        $ruletoSet = "singup_ruleset = \\\"" . $ruleSelected . "\\\"";
     
        /* Change default ruleset in config.ini file */
 
        $singupRule = "singup_ruleset = \\\"" . $configFile['singup_ruleset'] . "\\\"";
        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$singupRule.'/'.$ruletoSet.'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = exec($replaceParams);

        auditTrail("setup", "successfully changed the default singup for ruleset");
        $msg = $msg . ", default ruleset";
    }
}

if ($msg == "") 
{
    $msg = "none";
    $_SESSION['wm'] = encRijndael($msg);
}
else
{
    $msg = trim($msg, ",");
    $msg = ltrim($msg, " ");

    $_SESSION['wm'] = encRijndael("Success modification of ".$msg);
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>