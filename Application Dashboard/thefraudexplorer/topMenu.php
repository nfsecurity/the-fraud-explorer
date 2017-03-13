<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for top menu
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

include "lbs/open-db-connection.php";
$count_all = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents"));
$count_online = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents WHERE status='active'"));
$count_offline = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents WHERE status='inactive'"));
include "lbs/close-db-connection.php";

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="css/topMenu.css">

<ul class="ul">
	<li class="li">
		<p class="fixed-space">&nbsp;</p>
		&nbsp;&nbsp;<img src=images/nftop.png class="main-logo">
	</li>
	<li class="li">
		<a href="dashBoard">Dashboard</a>
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
                	echo '<a href="dashBoard?agent='.base64_encode(base64_encode("all")).'">Universal command</a>';
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

<div class="modal fade" id="confirm-config" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal fade" id="ruleset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<div class="modal fade" id="roles" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

