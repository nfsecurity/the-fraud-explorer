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

        $msg = $msg . ", admin password";
    }
}

if (isset($_POST['encryption']))
{
    $encryption = filter($_POST['encryption']);
    
    if (!empty($encryption)) 
    {
        mysqli_query($connection, sprintf("UPDATE t_crypt SET `key`='%s', `iv`='%s'", $encryption, $encryption));

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

        $msg = $msg . ", library language";
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