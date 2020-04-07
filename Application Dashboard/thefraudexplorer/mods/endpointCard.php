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
 * Description: Code for Endpoint Card
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
include "../lbs/cryptography.php";

$agentId = filter(decRijndael($_GET['id']));
$domain = filter(decRijndael($_GET['in']));
$agentId = explode("_", $agentId);
$agentId = $agentId[0];
$queryEndpoint = "SELECT agent, domain, ipaddress, heartbeat, system, version, name, ruleset FROM t_agents WHERE agent LIKE '%s_%%' AND domain='%s' ORDER BY heartbeat LIMIT 1";
$endpointQuery = mysqli_query($connection, sprintf($queryEndpoint, $agentId, $domain));
$queryResult = mysqli_fetch_array($endpointQuery);

?>

<style>

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container
    {
        margin: 20px;
    }

    .container-card
    {
        display: block;
    }

    .container-card::after 
    {
        display:block;
        content:"";
        clear:both;
    }

    .card-align-left
    {
        display: inline;
        text-align: left;
        background: white;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:left;
        margin: 10px 0px 0px 0px;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .card-align-right
    {
        display: inline;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        background: #f2f2f2;
        border-radius: 5px;
        padding: 10px;
        width: 49.2%;
        height: 33px;
        float:right;
        margin: 10px 0px 0px 0px;
        font-family: 'FFont', sans-serif; font-size: 11px;
        text-overflow: ellipsis;
        padding-left: 32px;
        padding-right: 32px;
    }

    .font-icon-color-gray-low
    {
        color: #B4BCC2;
    }

    .awfont-padding-right
    {
        padding-right: 5px;
    }

    .icon-fa
    {
        display: inline-block;
        min-width: 27px;
        max-width: 27px;
        text-align: center;
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }
}

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Endpoint Card</h4>
</div>

<div class="div-container">

    <!-- Endpoint ID -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-address-book-o fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Endpoint internal identification";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["agent"];
                
            ?>
                
        </div>
    </div>

    <!-- Endpoint Ruleset -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-building-o fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Business department/area or ruleset";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["ruleset"];
                
            ?>
                
        </div>
    </div>

    <!-- Endpoint name -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-graduation-cap fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Employee asigned alias or name";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            if ($queryResult["name"] == NULL) echo "Not specified yet";
            else echo $queryResult["name"];
                
            ?>
                
        </div>
    </div>

    <!-- Company Domain -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-globe fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Company domain or workgroup";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["domain"];
                
            ?>
                
        </div>
    </div>

    <!-- IP Address -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-desktop fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Last IP address used to register";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["ipaddress"];
                
            ?>
                
        </div>
    </div>

    <!-- Heartbeat -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-calendar fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Last date where endpoint reported";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["heartbeat"];
                
            ?>
                
        </div>
    </div>

    <!-- Operating System -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-info-circle fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Operating system version";

            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["system"];
                
            ?>
                
        </div>
    </div>

    <!-- Agent Version -->

    <div class="container-card">
        <div class="card-align-left">
                
            <?php

            echo "<div class=\"icon-fa\"><span class=\"fa fa-cube fa-lg font-icon-color-gray-low awfont-padding-right\"></span></div>Agent software version installed";


            ?>
                
        </div>
        <div class="card-align-right">
               
            <?php
                        
            echo $queryResult["version"];
                
            ?>
                
        </div>
    </div>

    <div class="modal-footer window-footer-config">
        <br>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
        <button type="button" class="btn btn-success" data-dismiss="modal" style="outline: 0 !important;">It's okay</button>
    </div>

</div>