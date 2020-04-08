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
 * Description: Code for regionalism words
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
include "../lbs/cryptography.php";

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
    }

    .div-container-regionalism
    {
        margin: 20px;
    }
    
    .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .master-container-regionalism
    {
        width: 100%; 
    }

    .latest-regionalism
    {
        font-family: 'FFont', sans-serif; font-size: 10px;
    }

    .downloadfile
    {
        outline: 0 !important;
    }

    .regionalism-text
    {
        text-align: justify;
        font-family: 'FFont', sans-serif; font-size: 12px;
    }

    .input-value-text-regionalism
    {
        width: 100%; 
        height: 75px; 
        padding: 10px; 
        border: solid 1px #c9c9c9; 
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }
 
    textarea 
    {
        resize: none;
    }   

</style>

<?php

$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");
$regionalismESFile = shell_exec("sudo find /var/www/html/thefraudexplorer/core/spell/*ES*.txt -printf '%T++%s+%p\n' | sort -r | head -n 1");
$regionalismENFile = shell_exec("sudo find /var/www/html/thefraudexplorer/core/spell/*EN*.txt -printf '%T++%s+%p\n' | sort -r | head -n 1");

if ($configFile["wc_language"] == "es") $regionalismFile = explode('+', trim($regionalismESFile));
else $regionalismFile = explode('+', trim($regionalismENFile));

$datetime = DateTime::createFromFormat('Y-m-d', $regionalismFile[0]);
$regionalismHour = explode(':', trim($regionalismFile[1]));
$size = $regionalismFile[2]/1024;

?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Regionalism words</h4>
</div>

<div class="div-container-regionalism">

    <form id="formRegionalism" name="formRegionalism" method="post" action="mods/setRegionalism">

    <div class="master-container-regionalism">

        <p class="regionalism-text">
        Please specify the words you need to process as "regionalism" by the internal spell correction system. This words as treated as a valid words and will be included in the standard spanish dictionary for correction:<br><br>
        </p>

        <textarea name="regionalismwords" id="regionalismwords" placeholder="hip, pajamas, frenemy, bromance, ginormous" class="input-value-text-regionalism"></textarea>

    </div>

    <br>
    <a class="downloadfile" href="mods/downloadRegionalism?le=<?php echo encRijndael($regionalismFile[3]); ?>">
    <button type="button" class="btn btn-default" style="width: 100%; outline: 0 !important;">
        Download entire regionalism dictionary file<br>
        <p class="latest-regionalism">

            <?php 

                echo $datetime->format('F d, Y') . ", at " . $regionalismHour[0] . ":" . $regionalismHour[1] . " with " . number_format(round($size)) . " Kb of size"; 
            
            ?>

        </p>
    </button>
    </a>
    <br>

    <br>
    <div class="modal-footer window-footer-config">
        <br>
        
        <?php    
            
            if ($session->username != "admin") 
            {
                echo '<input type="submit" class="btn btn-danger setup" value="Remove words" name="removewords" style="outline: 0 !important;">';
                echo '<input type="submit" class="btn btn-success setup" value="Add words" name="addwords" style="outline: 0 !important;">';
            }
            else
            {
                echo '<input type="submit" class="btn btn-danger setup" value="Remove words" name="removewords" style="outline: 0 !important;">';
                echo '<input type="submit" class="btn btn-success setup" value="Add all words" name="addwords" style="outline: 0 !important;">';
            } 

        ?>

    </div>

    </form>
</div>