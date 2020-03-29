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

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

function notempty($var)
{
    return ($var === "0" || $var);
}

if (isset($_POST['key']))
{
    $keyPass = filter($_POST['key']);

    if (!empty($keyPass)) mysqli_query($connection, sprintf("UPDATE t_crypt SET password='%s'", $keyPass));
}

if (isset($_POST['samplecalculation']))
{
    $setCalculation = filter($_POST['samplecalculation']);

    if (!empty($setCalculation)) 
    {
        if ($session->domain == "all") mysqli_query($connection, sprintf("UPDATE t_config SET sample_data_calculation='%s'", $setCalculation));
        else 
        {
            $domainConfigTable = "t_config_".str_replace(".", "_", $session->domain);
            $queryConfigTable = "UPDATE ".$domainConfigTable." SET sample_data_calculation='".$setCalculation."'";
            
            mysqli_query($connection, $queryConfigTable);
        }
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
    }
}

if (isset($_POST['encryption']))
{
    $encryption = filter($_POST['encryption']);
    
    if (!empty($encryption)) mysqli_query($connection, sprintf("UPDATE t_crypt SET `key`='%s', `iv`='%s'", $encryption, $encryption));
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

    if (notempty($lowFrom) && notempty($lowTo) && notempty($mediumFrom) && notempty($mediumTo) && notempty($highFrom) && notempty($highTo) && notempty($criticFrom) && notempty($criticTo)) 
    {
        mysqli_query($connection, sprintf("UPDATE t_config SET score_ts_low_from='%s', score_ts_low_to='%s', score_ts_medium_from='%s', score_ts_medium_to='%s', score_ts_high_from='%s', score_ts_high_to='%s', score_ts_critic_from='%s', score_ts_critic_to='%s'", $lowFrom, $lowTo, $mediumFrom, $mediumTo, $highFrom, $highTo, $criticFrom, $criticTo));
    }
}

if (isset($_POST['ftacron']))
{
    if (!empty($_POST['ftacron'])) 
    {
        $cronJobMinutes = filter($_POST['ftacron']);
        $cron_manager = new CronManager();
        $remove_cron_result = $cron_manager->remove_cronjob('fta-ai-processor');
        if($_POST['ftacron'] != "disabled") $cron_add_result = $cron_manager->add_cronjob('*/'.$cronJobMinutes.' * * * * cd /var/www/html/thefraudexplorer/core/ ; /usr/bin/php AIFraudTriangleProcessor.php', 'fta-ai-processor');
    }
}

if (isset($_POST['librarylanguage']))
{
    if (!empty($_POST['librarylanguage']))
    {
        $languageSelected = filter($_POST['librarylanguage']);
        $ftaLanguagetoSet = "fta_lang_selection = \\\"fta_text_rule";

        if ($languageSelected == "es") $ftaLanguagetoSet = $ftaLanguagetoSet . "_spanish\\\"";
        else if ($languageSelected == "en") $ftaLanguagetoSet = $ftaLanguagetoSet . "_english\\\"";
        $wordCorrectiontoSet = "wc_language = \\\"" . $languageSelected . "\\\"";
     
        /* Change language in config.ini file */
 
        $configFile = parse_ini_file("../config.ini");
        $ftaLang = "fta_lang_selection = \\\"" . $configFile['fta_lang_selection'] . "\\\"";
        $wcLang = "wc_language = \\\"" . $configFile['wc_language'] . "\\\"";

        $replaceParams = '/usr/bin/sudo /usr/bin/sed "s/'.$wcLang.'/'.$wordCorrectiontoSet.'/g; s/'.$ftaLang.'/'.$ftaLanguagetoSet.'/g" --in-place '.$documentRoot.'config.ini';
        $commandReplacements = shell_exec($replaceParams);
    }
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>