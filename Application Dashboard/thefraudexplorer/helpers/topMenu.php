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
 * Description: Code for top menu
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

include "../lbs/openDBconn.php";
include "../lbs/endpointMethods.php";

/* Online and Offline Endpoints Query */

discoverOnline();

/* SQL queries */

if ($session->domain == "all")
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $queryCountTotalsSQL = "SELECT COUNT(*) AS total FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM t_agents) AS agents GROUP BY agent) AS totals";
        $queryCountActiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE status='active'";
        $queryCountInactiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE status='inactive'";
    }
    else
    {
        $queryCountTotalsSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain NOT LIKE 'thefraudexplorer.com'";
        $queryCountActiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain NOT LIKE 'thefraudexplorer.com' AND status='active'";
        $queryCountInactiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain NOT LIKE 'thefraudexplorer.com' AND status='inactive'";
    }
}
else
{
    if (samplerStatus($session->domain) == "enabled")
    {
        $queryCountTotalsSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com'";
        $queryCountActiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND status='active'";
        $queryCountInactiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' OR domain='thefraudexplorer.com' AND status='inactive'";
    }
    else
    {
        $queryCountTotalsSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com'";
        $queryCountActiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com' AND status='active'";
        $queryCountInactiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, domain, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, domain, status FROM t_agents) AS agents GROUP BY agent) AS totals WHERE domain='".$session->domain."' AND domain NOT LIKE 'thefraudexplorer.com' AND status='inactive'";
    }
}

include "../lbs/openDBconn.php";
$count_all = mysqli_fetch_assoc(mysqli_query($connection, $queryCountTotalsSQL));
$count_online = mysqli_fetch_assoc(mysqli_query($connection, $queryCountActiveSQL));
$count_offline = mysqli_fetch_assoc(mysqli_query($connection, $queryCountInactiveSQL));
include "../lbs/closeDBconn.php";

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="../css/topMenu.css?<?php echo filemtime('../css/topMenu.css') ?>">

<ul class="ul" id="elm-topmenu">
    <li class="li" style="padding-right: 44px;">
        <img src=../images/nftop.svg class="main-logo" alt="nftop">
    </li>
    <li class="li">
        <a href="dashBoard" id="elm-dashboard">Dashboard</a>
    </li>
    <li class="li">
        <a href="../eventData?nt=<?php include "../lbs/cryptography.php"; echo encRijndael("all"); ?>" id="elm-eventmodule">Events</a>
    </li>
    <li class="li">
        <a href="analyticsData" id="elm-analytics">Analytics</a>
    </li>
        <li class="li">
        <a href="../endPoints" id="elm-endpoints">Endpoints</a>
    </li>
    <li class="li">
        <a href="../mods/setupRuleset" data-toggle="modal" data-target="#ruleset" class="topmenu-ruleset" href="#" id="elm-ruleset">Rules</a>
    </li>

    <?php
    
    if (isset($_GET['or'])) $resourceOrigin=filter($_GET['or']);
    else $resourceOrigin = "other";
    
    if ($session->domain == "all")
    {
        echo '<li class="li">';
        echo '<a href="../mods/rolesConfig" data-toggle="modal" data-target="#roles" class="topmenu-roles" href="#" id="elm-roles">Roles</a>';
        echo '</li>';
    }
    
    ?>
    
    <li class="li">
        <a href="../mods/mainConfig" data-toggle="modal" data-target="#confirm-config" class="topmenu-setup" href="#" id="elm-configuration">Setup</a>
    </li>
    
    <li class="li">
        <a href="../mods/maintenancePurge" data-toggle="modal" data-target="#confirm-maintenance" class="topmenu-maintenance" href="#" id="elm-maintenance">Maintenance</a>
    </li>

    <li style="float:right">
        <a class="active logout-button" href="logout">Logout</a>
    </li>
    <li class="search search-input" id="elm-search">
        <input type="search" name="search_text" autocomplete="off" id="search-box" class="search_text" data-column="any" placeholder="Search ..."/>
        <input class="input-search" type="button" name="search_button" id="search_button">
    </li>
    <li class="li counters">
        <button class="button-totals" id="totals-menu">Total<br><?php echo str_pad($count_all['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
    <li class="li counters" id="elm-counters">
        <button class="button-totals" id="totals-menu">Online<br><?php echo str_pad($count_online['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
    <li class="li counters">
        <button class="button-totals" id="totals-menu">Offline<br><?php echo str_pad($count_offline['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
</ul>
<br>

<!-- Modal for main Configuration -->

<div class="modal" id="confirm-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Maintenance -->

<div class="modal" id="confirm-maintenance" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Ruleset -->

<div class="modal" id="ruleset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center" style="width: 690px;">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Roles -->

<div class="modal" id="roles" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search box status -->

<?php

if (isset($_SESSION['instance']) && ($_SESSION['instance'] != "endPoints" && $_SESSION['instance'] != "eventData"))
{
    echo '<script>';
    echo 'document.getElementById("search-box").disabled = true;';
    echo 'document.getElementById("search-box").style.backgroundColor = "#e2e2e2";';
    echo 'document.getElementById("search-box").value = "Disabled search ...";';
    echo '</script>';
}

?>

<!-- Mdule highlight -->

<?php

if (isset($_SESSION['instance']))
{
    if ($_SESSION['instance'] == "dashBoard")
    {
        echo '<script>';
        echo '$("#elm-dashboard").css({"font-family": "\'FFont-Bold\'", "color": "#4B906F"});';
        echo '</script>';
    }
    else if ($_SESSION['instance'] == "eventData")
    {
        echo '<script>';
        echo '$("#elm-eventmodule").css({"font-family": "\'FFont-Bold\'", "color": "#4B906F"});';
        echo '</script>';
    }
    else if ($_SESSION['instance'] == "endPoints")
    {
        echo '<script>';
        echo '$("#elm-endpoints").css({"font-family": "\'FFont-Bold\'", "color": "#4B906F"});';
        echo '</script>';
    }
    else if ($_SESSION['instance'] == "analyticsData")
    {
        echo '<script>';
        echo '$("#elm-analytics").css({"font-family": "\'FFont-Bold\'", "color": "#4B906F"});';
        echo '</script>';
    }
}

?>

<!-- Modal for setup dialog -->

<script>
    $('#confirm-config').on('show.bs.modal', function(e){
        $(".modal-body").html("");
        $(this).find('.topmenu-setup').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for maintenance -->

<script>
    $('#confirm-maintenance').on('show.bs.modal', function(e){
        $(".modal-body").html("");
        $(this).find('.topmenu-maintenance').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for ruleset -->

<script>
    $('#ruleset').on('show.bs.modal', function(e){
        $(".modal-body").html("");
        $(this).find('.topmenu-ruleset').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Modal for roles -->

<script>
    $('#roles').on('show.bs.modal', function(e){
        $(".modal-body").html("");
        $(this).find('.topmenu-roles').attr('href', $(e.relatedTarget).data('href'));
    });
</script>