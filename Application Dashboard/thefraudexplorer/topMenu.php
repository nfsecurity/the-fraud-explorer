<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for top menu
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* SQL queries */

$queryCountTotalsSQL = "SELECT COUNT(*) AS total FROM (SELECT agent FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent FROM t_agents) AS agents GROUP BY agent) AS totals";
$queryCountActiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, heartbeat, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, status FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent) AS totals WHERE status='active'";
$queryCountInactiveSQL = "SELECT COUNT(*) AS total FROM (SELECT agent, heartbeat, status FROM (SELECT SUBSTRING_INDEX(agent, '_', 1) AS agent, heartbeat, status FROM t_agents GROUP BY agent ORDER BY heartbeat DESC) AS agents GROUP BY agent) AS totals WHERE status='inactive'";

include "lbs/open-db-connection.php";
$count_all = mysql_fetch_assoc(mysql_query($queryCountTotalsSQL));
$count_online = mysql_fetch_assoc(mysql_query($queryCountActiveSQL));
$count_offline = mysql_fetch_assoc(mysql_query($queryCountInactiveSQL));
include "lbs/close-db-connection.php";

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="css/topMenu.css">

<ul class="ul">
    <li class="li">
        <p class="fixed-space">&nbsp;</p>
        &nbsp;&nbsp;<img src=images/nftop.svg class="main-logo">
    </li>
    <li class="li">
        <a href="dashBoard">Dashboard</a>
    </li>
    <li class="li">
        <a href="endPoints">Endpoints</a>
    </li>
    <li class="li">
        <a href="analyticsData">Analytics</a>
    </li>
    <li class="li">
        <a href="setupRuleset" data-toggle="modal" data-target="#ruleset" href="#">Ruleset</a>
    </li>
    <li class="li">
        <a href="mainConfig" data-toggle="modal" data-target="#confirm-config" href="#">Configuration</a>
    </li>

    <?php
    
    if ($session->domain == "all")
    {
        echo '<li class="li">';
        echo '<a href="rolesConfig" data-toggle="modal" data-target="#roles" href="#">Roles</a>';
        echo '</li>';
    }
    
    ?>

    <li class="li">
        <a href="eraseCommands">Queue reset</a>
    </li>

    <?php
    
    if ($session->domain == "all")
    {
        echo '<li class="li">';
        echo '<a href="endPoints?agent='.base64_encode(base64_encode("all")).'&domain='.base64_encode(base64_encode("all")).'">Global command</a>';
        echo '</li>';
    }
    else
    {
        echo '<li class="li">';
        echo '<a href="endPoints?agent='.base64_encode(base64_encode("all")).'&domain='.base64_encode(base64_encode('.$session->domain.')).'">Global command</a>';
        echo '</li>';
    }
    
    ?>

    <li class="li">
        <a href="https://www.thefraudexplorer.com/#contact" target="_blank">Help</a>
    </li>
    <li style="float:right">
        <a class="active logout-button" href="logout">Logout</a>
    </li class="li">
    <li class="search search-input">
        <form action="" method="get">
            <input type="text" name="search_text" autocomplete="off" id="search-box" class="search_text" placeholder="Search ..."/>
            <input class="input-search" type="button" name="search_button" id="search_button">
        </form>
    </li>
    <li class="li counters">
        <button class="button-totals" id="totals-menu">Total<br><?php echo str_pad($count_all['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
    <li class="li counters">
        <button class="button-totals" id="totals-menu">Online<br><?php echo str_pad($count_online['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
    <li class="li counters">
        <button class="button-totals" id="totals-menu">Offline<br><?php echo str_pad($count_offline['total'], 4, "0", STR_PAD_LEFT); ?></button>
    </li>
</ul>
<br>

<!-- Modal for agent setup -->

<div class="modal fade-scale" id="confirm-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal fade-scale" id="ruleset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!-- Modal for Roles -->

<div class="modal fade-scale" id="roles" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<?php

if (isset($_SESSION['instance']) && ($_SESSION['instance'] != "endPoints" && $_SESSION['instance'] != "alertData"))
{
    echo '<script>';
    echo 'document.getElementById("search-box").disabled = true;';
    echo 'document.getElementById("search-box").style.backgroundColor = "#e2e2e2";';
    echo 'document.getElementById("search-box").value = "Disabled search ...";';
    echo '</script>';
}

?>