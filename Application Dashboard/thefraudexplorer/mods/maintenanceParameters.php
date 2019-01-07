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
 * Description: Code for general setup
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

/* Delete dead endpoint sessions*/

if (isset($_POST['deadsessions']))
{
    $setDeadSessions=filter($_POST['deadsessions']);
     
    if (!empty($setDeadSessions) && $setDeadSessions == "1month") mysqli_query($connection, "DELETE FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");
}

/* Delete old phrase indexes (logstash-theraudepxlorer-text-*) */

if (isset($_POST['deletephrases']))
{
    $curate30days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgePhrases30d.yml';
    $curate60days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgePhrases60d.yml';
    $curate90days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgePhrases90d.yml';
    $setDeletePhrases=filter($_POST['deletephrases']);
     
    if (!empty($setDeletePhrases) && $setDeletePhrases == "1month") $commandCurator = shell_exec($curate30days);
    else if (!empty($setDeletePhrases) && $setDeletePhrases == "2month") $commandCurator = shell_exec($curate60days);
    else if (!empty($setDeletePhrases) && $setDeletePhrases == "3month") $commandCurator = shell_exec($curate90days);
}

/* Delete old alert indexes (logstash-alerter-*) */

if (isset($_POST['deletealerts']))
{
    $curate30days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts30d.yml';
    $curate60days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts60d.yml';
    $curate90days = '/usr/bin/sudo /usr/bin/python '.$documentRoot.'lbs/curator/bin/curator --config '.$documentRoot.'lbs/curator/config/curator.yml '.$documentRoot.'lbs/curator/actions/purgeAlerts90d.yml';
    $setDeleteAlerts=filter($_POST['deletealerts']);
     
    if (!empty($setDeleteAlerts) && $setDeletePhrases == "1month") 
    {
        $commandCurator = shell_exec($curate30days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 30 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");
    }
    else if (!empty($setDeleteAlerts) && $setDeletePhrases == "2month") 
    {
        $commandCurator = shell_exec($curate60days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 60 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");
    }
    else if (!empty($setDeleteAlerts) && $setDeletePhrases == "3month") 
    {
        $commandCurator = shell_exec($curate90days);
        mysqli_query($connection, "DELETE FROM t_inferences WHERE date < (CURRENT_DATE - INTERVAL 90 DAY) AND domain NOT LIKE 'thefraudexplorer.com'");
    }
}

/* Delete old alert status indexes (logstash-alerter-*) */

if (isset($_POST['alertstatus']))
{
    $setDeleteAlertStatus=filter($_POST['alertstatus']);
    
    if (!empty($setDeleteAlertStatus) && $setDeleteAlertStatus == "1month")
    {
        $urlAlerts="http://localhost:9200/tfe-alerter-status/_delete_by_query";
        $params = '{ "query": { "range": { "@timestamp": { "lte": "now-1M" } } } }';
              
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $urlAlerts);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $resultAlerts=curl_exec($ch);
        curl_close($ch);
    }
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);
include "../lbs/closeDBconn.php";

?>

</body>
</html>