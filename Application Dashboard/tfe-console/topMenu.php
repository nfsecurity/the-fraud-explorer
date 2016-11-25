<?php

include "inc/open-db-connection.php";
$count_all = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents"));
$count_online = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents WHERE status='active'"));
$count_offline = mysql_fetch_assoc(mysql_query("SELECT count(*) AS total FROM t_agents WHERE status='inactive'"));
include "inc/close-db-connection.php";

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
                <a href="setupRuleset">Ruleset</a>
        </li>
	<li class="li">
                <a href="mainConfig" data-toggle="modal" data-target="#confirm-config" href="#">Configuration</a>
        </li>
	<li class="li">
                <a href="eraseCommands">Queue reset</a>
        </li>
	<li class="li">
                <a href="dashBoard?agent=<?php echo base64_encode(base64_encode("all")); ?>">Universal command</a>
        </li>
	<li class="li">
                <a href="https://www.thefraudexplorer.com/#contact" target="_blank">Help</a>
        </li>
 	        <li style="float:right">
                <a class="active logout-button" href="logout">Logout</a>
        </li class="li">
	<li class="search search-input">
        	<form action="" method="get">
            		<input type="text" name="search_text" id="search-box" class="search_text" placeholder="Search ..."/>
            		<input type="button" name="search_button" id="search_button">
        	</form>
    	</li>
	<li class="li counters">
               	<button id="totals-menu">Total<br><?php echo str_pad($count_all['total'], 4, "0", STR_PAD_LEFT); ?></button>
        </li>
	<li class="li counters">
                <button id="totals-menu">Online<br><?php echo str_pad($count_online['total'], 4, "0", STR_PAD_LEFT); ?></button>
        </li>
	<li class="li counters">
                <button id="totals-menu">Offline<br><?php echo str_pad($count_offline['total'], 4, "0", STR_PAD_LEFT); ?></button>
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

