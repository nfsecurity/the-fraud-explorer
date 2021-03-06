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
 * Description: Code for maintenance
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
require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

$_SESSION['processingStatus'] = "notstarted";

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-maintenance
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container-maintenance
    {
        margin: 20px;
    }

    .container-status
    {
        display: block;
    }

    .container-status::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .status-align-left
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:left;
        margin: 10px 0px 0px 0px;
        font-family: Verdana, sans-serif; font-size: 11px;
    }

    .status-align-right
    {
        display: inline;
        text-align: center;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:right;
        margin: 10px 0px 0px 0px;
        font-family: Verdana, sans-serif; font-size: 11px;
    }
    
    .select-option-styled
    {
        max-height: 30px !important;
        min-height: 30px !important;
        border: 1px solid #ccc !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        line-height: 11.6px !important;
        padding: 8px 0px 0px 10px !important;
        position: relative;
    }

    .select-option-styled .list
    {
        margin-left: 5px;
        overflow-y: scroll !important;
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19) !important;
        background: #f9f9f9 !important;
    }

    .age-text
    {
        font-family: 'FFont', sans-serif; font-size:11.5px;
    }

    .master-container
    {
        width: 100%; 
        height: 70px;
    }
    
    .left-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: left;
    }
    
    .right-container
    {
        width: calc(50% - 5px); 
        height: 100%; 
        display: inline; 
        float: right;
    }

    .btn-default, .btn-default:active, .btn-default:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

    .health-button
    {
        width: 100%;
        height: 34px;
        border: 1px solid #c1c1c1;
        border-radius: 5px;
        line-height: 32px;
        cursor: pointer;
        outline: 0 !important;
        margin-top: 13px;
    }

    .health-section
    {
        display: none;
        height: 228px;
    }

    .purge-section
    {
        height: 228px;
    }

    .left-health
    {
        height: 180px;
        width: 100%;
    }

    .right-health
    {
        width: 100%;
        height: 180px;
        background: #f9f9f9;
        border-radius: 5px;
    }

    .health-title
    {
        font-family: 'FFont', sans-serif; font-size: 12px;
        margin-bottom: 27px;
    }

    .resource-title
    {
        height: 35px; 
        font-family: 'FFont', sans-serif; font-size: 11px;
        display: inline-block; 
        vertical-align: middle; 
        margin-top: -10px; 
        margin-right: 10px;
    }

    .ram-container
    {
        display: inline-block; 
        border: 1px solid #dfdfdf;
        width: 235px; 
        height: 34px;
        border-radius: 8px;
    }

    .cpu-container
    {
        display: inline-block; 
        border: 1px solid #dfdfdf; 
        width: 235px; 
        height: 34px; 
        border-radius: 8px; 
        margin-top: 6px;
    }

    .disk-container
    {
        display: inline-block; 
        border: 1px solid #dfdfdf; 
        width: 235px; 
        height: 34px; 
        border-radius: 8px; 
        margin-top: 5px;
    }

    .total-container
    {
        display: inline-block; 
        border: 1px solid #dfdfdf; 
        width: 235px; 
        height: 34px; 
        border-radius: 8px; 
        margin-top: 5px;
    }

    .ram-status
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        display: inline; 
        position: absolute; 
        top: 138px; 
        left: 165px;
    }

    .cpu-status
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        display: inline; 
        position: absolute; 
        top: 187px; 
        left: 165px;
    }

    .disk-status
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        display: inline; 
        position: absolute; 
        top: 235px; 
        left: 165px;
    }

    .total-status
    {
        font-family: 'FFont', sans-serif; font-size: 11px;
        display: inline; 
        position: absolute; 
        top: 284px; 
        left: 165px;
    }

    .ram-bar, .cpu-bar, .disk-bar, .total-bar
    {
        height: 34px; 
        background: #c3e0d1; 
        float: left; 
        border-radius: 8px; 
        margin: -1px;
    }

    .es-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: -30px; 
        top: 25px; 
        background: #5c9678;
    }

    .ls-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: -1px; 
        top: 25px; 
        background: #5c9678;
    }

    .md-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: 30px; 
        top: 25px; 
        background: #5c9678;
    }

    .ai-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: -32px; 
        top: 55px; 
        background: #5c9678;
    }

    .ct-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: -3px; 
        top: 55px; 
        background: #5c9678;
    }

    .bk-icon
    {
        border-radius: 8px; 
        border: 2px solid #5c9678; 
        color: white; 
        font-family: 'FFont-Bold', sans-serif; 
        font-size:16px; 
        vertical-align: top; 
        display: inline-block; 
        line-height: 40px; 
        position: relative; 
        width: 50px; 
        height: 50px; 
        left: 28px; 
        top: 55px; 
        background: #5c9678;
    }

    .service-status
    {
        display: inline-block; 
        position:relative; 
        top: -25px; 
        font-family: 'FFont', sans-serif; 
        font-size:10px; 
        color: white
    }

    .btn-words-age, .btn-words-age:active, .btn-words-age:visited
    {
        font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;
        color: #757575 !important;
        display: inline;
        width: 132px;
        height: 30px;
        outline: none !important;
        float: right;
        text-align: left;
        padding-left: 9px;
    }

    input[name="wordsage"] 
    {
        position: relative;
        line-height: 11.6px !important;
        border: 1px solid #c9c9c9;
        padding: .2rem;
        padding-left: 10px;
        width: 132px;
        height: 30px;
        outline: 0 !important;
        border-radius: 5px;
        float: left;
    }

    input[name="wordsage"].mod::-webkit-outer-spin-button, input[name="wordsage"].mod::-webkit-inner-spin-button 
    {
        -webkit-appearance: none;
        background: #FFF url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAKUlEQVQYlWNgwAT/sYhhKPiPT+F/LJgEsHv37v+EMGkmkuImoh2NoQAANlcun/q4OoYAAAAASUVORK5CYII=) no-repeat center center;
        width: 15px;
        height: 28px;
        border-left: 1px solid #BBB;
        opacity: .5; 
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
    }

    input[name="wordsage"].mod::-webkit-inner-spin-button:hover, input[name="wordsage"].mod::-webkit-inner-spin-button:active
    {
        box-shadow: 0 0 2px #0CF;
        opacity: .8;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Maintenance & Health</h4>
</div>

<div class="div-container-maintenance">
    <form id="formPurge" name="formPurge" method="post" action="mods/maintenanceParameters">

        <div id="purge-section" class="purge-section">

            <div class="master-container">
                <div class="left-container">              
                    
                    <p class="title-config">Words data retention policy (days)</p><br>
                    <input class="mod age-text" type="number" name="wordsage" id="wordsage" min="0" max="180" value="<?php echo $configFile['store_words_days']; ?>" required>
                    <button type="button" class="btn btn-default btn-words-age" id="btnsetwordsage" style="font-family: 'FFont', 'Awesome-Font', sans-serif; font-size: 11.6px !important;" data-loading-text="<i class='fa fa-refresh fa-spin fa-fw'></i>&nbsp;Modifying age ...">Set words age now !</button>
                    
                </div>

                <div class="right-container">
                    
                    <p class="title-config">Purge old endpoint events</p><br>
                    <select class="select-option-styled wide" name="deletealerts" id="deletealerts">
                        <option value="1month">Preserve last month</option>
                        <option value="3month">Preserve last 3 months</option>
                        <option value="6month">Preserve last 6 months</option>
                        <option value="12month">Preserve last year</option>
                        <option value="preserveall" selected="selected">Preserve all</option>
                    </select>            
                        
                </div>
            </div>

            <div class="container-status">
                <div class="status-align-left">
                    
                    <?php
                    
                    $urlSize="http://localhost:9200/logstash-thefraudexplorer-text-*/_stats";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $urlSize);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $resultSize=curl_exec($ch);
                    curl_close($ch);

                    $resultSize = json_decode($resultSize, true);

                    if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                    {
                        $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024/1024;
                        $dataCount = $resultSize['_all']['total']['docs']['count'];

                        echo "You have ".number_format($dataCount)." words in ".round($dataSize, 1)." GB";
                    }
                    else
                    {
                        echo "You don't have any data yet";
                    }

                    ?>
                    
                </div>

                <div class="status-align-right">
                
                    <?php
                    
                    $urlSize="http://localhost:9200/logstash-alerter-*/_stats";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $urlSize);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $resultSize=curl_exec($ch);
                    curl_close($ch);

                    $resultSize = json_decode($resultSize, true);

                    if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                    {
                        $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                        $dataCount = $resultSize['_all']['total']['docs']['count'];
                    
                        echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                    }
                    else
                    {
                        echo "You don't have any data yet";
                    }
                    
                    ?>
                    
                </div>
            </div>
            
            <div class="master-container">
                <div class="left-container">              
                    
                    <p class="title-config">Delete old endpoint sessions</p><br>
                    <select class="select-option-styled wide" name="deadsessions" id="deadsessions">
                        <option value="1month">Purge dead sessions (30 days long)</option>
                        <option value="preserveall" selected="selected">Preserve all</option>
                    </select>            
                    
                </div>

                <div class="right-container">
                    
                    <p class="title-config">Delete old events status records</p><br>
                    <select class="select-option-styled wide" name="alertstatus" id="alertstatus">
                        <option value="1month">Preserve last month</option>
                        <option value="preserveall" selected="selected">Preserve all</option>
                    </select>            
                        
                </div>
            </div>

            <div class="container-status">
                <div class="status-align-left">
                    
                    <?php
                    
                    $queryDeadEndpoints = "SELECT COUNT(*) AS total FROM t_agents WHERE heartbeat < (CURRENT_DATE - INTERVAL 30 DAY)";
                    $countDead = mysqli_fetch_assoc(mysqli_query($connection, $queryDeadEndpoints));
                    
                    echo $countDead['total']." sessions in dead status (30 days)";
                    
                    ?>
                    
                </div>

                <div class="status-align-right">
                
                    <?php
                    
                    $urlSize="http://localhost:9200/tfe-alerter-status/_stats";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $urlSize);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                    $resultSize=curl_exec($ch);
                    curl_close($ch);

                    $resultSize = json_decode($resultSize, true);

                    if (isset($resultSize['_all']['total']['store']['size_in_bytes']))
                    {
                        $dataSize = $resultSize['_all']['total']['store']['size_in_bytes']/1024/1024;
                        $dataCount = $resultSize['_all']['total']['docs']['count'];
                    
                        echo "You have ".number_format($dataCount)." regs in ".round($dataSize, 1)." MB";
                    }
                    else
                    {
                        echo "You don't have any data yet";
                    }
                    
                    ?>
                    
                </div>
            </div>

        </div>

        <div id="health-section" class="health-section">

        <?php

            function get_server_memory_usage()
            {
                $free = shell_exec('free');
                $free = (string)trim($free);
                $free_arr = explode("\n", $free);
                $mem = explode(" ", $free_arr[1]);
                $mem = array_filter($mem);
                $mem = array_merge($mem);
                $memory_usage = $mem[2]/$mem[1]*100;

                return $memory_usage;
            }

            function get_server_cpu_usage()
            {
                $cpuUsage = shell_exec('/usr/bin/sudo /usr/bin/top -bn1 | grep "Cpu(s)" | sed "s/.*, *\([0-9.]*\)%* id.*/\1/" | awk \'{print 100 - $1}\'');

                return $cpuUsage;
            }

            function get_server_free_space()
            {
                $total = disk_total_space("/");
                $free = disk_free_space("/");
                $used = $total - $free;

                return $used * 100 / $total;
            }

            function get_server_elasticsearch_status()
            {
                $status = shell_exec('/usr/bin/sudo /usr/bin/curl localhost:9200/_cluster/health');

                if ((strpos($status, 'green') !== false) || (strpos($status, 'yellow') !== false)) return "good";
                else return "failed";
            }

            function get_server_logstash_status()
            {
                $status = shell_exec('/usr/bin/sudo /usr/sbin/service logstash status');

                if (strpos($status, 'active (running)') !== false) return "good";
                else return "failed";
            }

            function get_server_mariadb_status()
            {
                $status = shell_exec('/usr/bin/sudo /usr/sbin/service mariadb status');

                if (strpos($status, 'active (running)') !== false) return "good";
                else return "failed";
            }

            function get_server_aifta_status()
            {
                $today = date("Y-m-d");
                $eventData = extractLastEventFromAlerterStatus();
                $latestAlerterEvent = json_decode(json_encode($eventData), true);
                $lastEventDate = date('Y-m-d', strtotime($latestAlerterEvent['hits']['hits'][0]['_source']['@timestamp']));
                $daysDiff = (strtotime($today) - strtotime($lastEventDate)) / (60 * 60 * 24);

                if ($daysDiff > 0) return "failed";

                $sLock = '/var/www/html/thefraudexplorer/core/FTA.lock';

                if (file_exists($sLock)) 
                {
                    $status = shell_exec('/usr/bin/pgrep -f AIFraudTriangleProcessor.php');

                    if ($status == "" || $status == " " || $status == "null" || $status == NULL) return "failed";
                    else return "good";
                }

                else return "good";
            }

            function get_server_crontab_status()
            {
                $status = shell_exec('/usr/bin/sudo /usr/bin/crontab -l');

                 if (strpos($status, 'fta-ai-processor') !== false) return "good";
                 else return "failed";
            }

            function get_server_backup_status()
            {
                $prefix = "backup-";
                $currentDate = date("M-d-y");
                $dateBefore = date("M-d-y", strtotime($currentDate . ' -1 day'));
                $post = ".zip";
                $finalDate = $prefix.$dateBefore.$post;
                $backupFile = "/backup/".$finalDate;

                if (file_exists($backupFile)) return "good";
                else return "failed";
            }

            $ram = (int)get_server_memory_usage();
            $cpu = (int)get_server_cpu_usage();
            $disk = (int)get_server_free_space();

            if ($cpu <= 80) $cpu = $cpu + 10;

        ?>

            <div class="left-container">
                <div class="left-health">

                    <p class="health-title">Physical resources usage</p>

                    <div class="resource-title">RAM</div>
                    <div class="ram-container">
                        <div class="ram-status"><?php echo $ram."%"; ?></div>
                        <div class="ram-bar" style="width: <?php echo ($ram+1)."%"; ?>;"></div>
                    </div>
                    <div class="resource-title">CPU</div>
                    <div class="cpu-container">
                        <div class="cpu-status"><?php echo $cpu."%"; ?></div>
                        <div class="cpu-bar" style="width: <?php echo ($cpu+1)."%"; ?>;"></div>
                    </div>
                    <div class="resource-title">DSK</div>
                    <div class="disk-container">
                        <div class="disk-status"><?php echo $disk."%"; ?></div>
                        <div class="disk-bar" style="width: <?php echo ($disk+1)."%"; ?>;"></div>
                    </div>
                    <div class="resource-title">TOT</div>
                    <div class="total-container">
                        <div class="total-status"><?php echo (int)(($disk+$cpu+$ram)/3)."%"; ?></div>
                        <div class="total-bar" style="width: <?php echo (int)((($disk+$cpu+$ram)/3)+1)."%"; ?>;"></div>
                    </div>
                    
                </div>
            </div>

            <div class="right-container">

                <p class="health-title">Fraud Triangle services status</p>

                <div class="right-health">

                    <div class="es-icon">
                        ES<br>
                        <p class="service-status"><?php echo get_server_elasticsearch_status(); ?></p>
                    </div>
                    <div class="ls-icon">
                        LS<br>
                        <p class="service-status"><?php echo get_server_logstash_status(); ?></p>
                    </div>
                    <div class="md-icon">
                        MD<br>
                        <p class="service-status"><?php echo get_server_mariadb_status(); ?></p>
                    </div>
                    <br>
                    <div class="ai-icon">
                        AI<br>
                        <p class="service-status"><?php echo get_server_aifta_status(); ?></p>
                    </div>
                    <div class="ct-icon">
                        CT<br>
                        <p class="service-status"><?php echo get_server_crontab_status(); ?></p>
                    </div>
                    <div class="bk-icon">
                        BK<br>
                        <p class="service-status"><?php echo get_server_backup_status(); ?></p>
                    </div>

                </div>
            </div>

        </div>
        
        <div id="healthButton" class="btn-default health-button">Check The Fraud Explorer health system status</div>

        <div class="modal-footer window-footer-maintenance">
            <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            
            <?php    
            
            if ($session->username != "admin") echo '<button type="submit" class="btn btn-danger setup disabled" value="Purge data now" style="outline: 0 !important;">';
            else echo '<button type="submit" id="btn-purge" class="btn btn-danger setup" data-loading-text="<i class=\'fa fa-refresh fa-spin fa-fw\'></i>&nbsp;Purging, please wait" style="outline: 0 !important;">Purge data now</button>';

            ?>
        
        </div>
    </form>
</div>

<!-- Nice selects -->

<script>
    $(document).ready(function() {
        $('select').niceSelect();
    });
</script>

<!-- Button Purge -->

<script>

var $btn;

$("#btn-purge").click(function() {
    $btn = $(this);
    $btn.button('loading');
    setTimeout('getstatus()', 1000);
});

function getstatus()
{
    $.ajax({
        url: "../helpers/processingStatus.php",
        type: "POST",
        dataType: 'json',
        success: function(data) {
            $('#statusmessage').html(data.message);
            if(data.status=="pending")
              setTimeout('getstatus()', 1000);
            else
                $btn.button('reset');
        }
    });
}

</script>

<!-- Health button -->

<script>

var tab = 0;

$("#healthButton").click(function() {

    if (tab % 2 == 0)
    {
        $("#purge-section").hide();
        $("#health-section").show();
        $("#healthButton").html("Show system maintenance & data purge options");
    }
    else
    {
        $("#health-section").hide();
        $("#purge-section").show();
        $("#healthButton").html("Check The Fraud Explorer health system status");
    }

    tab++;

});

</script>

<!-- Button set words age -->

<script>

$(document).ready(function () {
        $("#btnsetwordsage").click(function () {
            var $btn = $(this);
            var $wordsAge = $("#wordsage").val();
            $btn.button('loading');
            $.ajax({
                    type: "POST",
                    url: "../mods/setWordsAge.php",
                    data: {
                        wordsage : $wordsAge
                    }
                })
                .done(function () {
                    $btn.button('reset');
                });
        });
    });

</script>