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
include "../lbs/cryptography.php";
require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$ESAlerterIndex = $configFile['es_alerter_index'];
$msg = "";

/* Delete dead endpoint sessions */

if ((isset($_POST['deadsessions'])) && ($_POST['deadsessions'] != "preserveall"))
{
    $setDeadSessions = filter($_POST['deadsessions']);
     
    if (!empty($setDeadSessions) && $setDeadSessions == "1month") 
    {
        mysqli_query($connection, "DELETE FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");

        auditTrail("health", "successfully purged the dead data endpoints");
        $msg = $msg . ", dead endpoints";
    }
}

/* Delete old alert indexes (logstash-alerter-*) */

if ((isset($_POST['deletealerts'])) && ($_POST['deletealerts'] != "preserveall"))
{
    /* Search for alerter index */

    $curate30days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts30d.yml';
    $curate90days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts90d.yml';
    $curate180days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts180d.yml';
    $curate365days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts365d.yml';

    /* Search alerts in workflows */

    $alerts30days = getAllFraudTriangleMatchesMonthsBack($ESAlerterIndex, "1M");
    $alerts90days = getAllFraudTriangleMatchesMonthsBack($ESAlerterIndex, "3M");
    $alerts180days = getAllFraudTriangleMatchesMonthsBack($ESAlerterIndex, "6M");
    $alerts365days = getAllFraudTriangleMatchesMonthsBack($ESAlerterIndex, "12M");

    /* Proceed to the purge */

    $setDeleteAlerts = filter($_POST['deletealerts']);

    if (!empty($setDeleteAlerts) && $setDeletePhrases == "1month") 
    {
        $commandCurator = shell_exec($curate30days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");

        /* Workflows triggers */

        foreach ($alerts30days['hits']['hits'] as $result)
        {
            $regid = $result['_id'];
            $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$regid."%'";        
            $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);
        }

        auditTrail("health", "successfully purged the alerts data, keeping 1 month");
        $msg = $msg . ", 1 month old alerts";
    }
    else if (!empty($setDeleteAlerts) && $setDeletePhrases == "3month") 
    {
        $commandCurator = shell_exec($curate90days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 90 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");

        /* Workflows triggers */

        foreach ($alerts90days['hits']['hits'] as $result)
        {
            $regid = $result['_id'];
            $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$regid."%'";        
            $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);
        }

        auditTrail("health", "successfully purged the alerts data, keeping 3 months");
        $msg = $msg . ", 3 months old alerts";
    }
    else if (!empty($setDeleteAlerts) && $setDeletePhrases == "6month") 
    {
        $commandCurator = shell_exec($curate180days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 180 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");

        /* Workflows triggers */

        foreach ($alerts180days['hits']['hits'] as $result)
        {
            $regid = $result['_id'];
            $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$regid."%'";        
            $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);
        }

        auditTrail("health", "successfully purged the alerts data, keeping 6 months");
        $msg = $msg . ", 6 months old alerts";
    }
    else if (!empty($setDeleteAlerts) && $setDeletePhrases == "12month") 
    {
        $commandCurator = shell_exec($curate365days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 365 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");

        /* Workflows triggers */

        foreach ($alerts365days['hits']['hits'] as $result)
        {
            $regid = $result['_id'];
            $queryDeleteWFAlert = "DELETE FROM t_wtriggers WHERE ids LIKE '%".$regid."%'";        
            $resultQuery = mysqli_query($connection, $queryDeleteWFAlert);
        }

        auditTrail("health", "successfully purged the alerts data, keeping 12 months");
        $msg = $msg . ", 12 months old alerts";
    }
}

/* Delete old alert status indexes (logstash-alerter-*) */

if ((isset($_POST['alertstatus'])) && ($_POST['alertstatus'] != "preserveall"))
{
    $setDeleteAlertStatus = filter($_POST['alertstatus']);
    
    if (!empty($setDeleteAlertStatus) && $setDeleteAlertStatus == "1month")
    {
        $urlAlerts = "http://localhost:9200/tfe-alerter-status/_delete_by_query";
        $params = '{ "query": { "range": { "@timestamp": { "lte": "now-1M" } } } }';
              
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts = curl_exec($ch);
        curl_close($ch);

        auditTrail("health", "successfully purged the alert status, keeping 1 month");
        $msg = $msg . ", alert status";
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

    $_SESSION['wm'] = encRijndael("Success purge of ".$msg);
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>