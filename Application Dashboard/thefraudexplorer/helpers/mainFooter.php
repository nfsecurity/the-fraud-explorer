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
 * Description: Code for Footer
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

require '../vendor/autoload.php';
include "../lbs/elasticsearch.php";

$configFile = parse_ini_file("../config.ini");
$currentversion = $configFile['sw_version'];

/* Query if there are audit events for the user */

$client = Elasticsearch\ClientBuilder::create()->build();
$ESAuditIndex = $configFile['es_audit_trail_index'];
$view = $session->username;
$showAuditLink = false;

if (indexExist($ESAuditIndex, $configFile) == true) $eventCount = countAuditTrailEvents($view."*", $ESAuditIndex, "AuditEvent");

if (isset($eventCount['count']) && $eventCount['count'] != 0) $showAuditLink = true;

?>

<!-- Styles -->

<link rel="stylesheet" type="text/css" href="../css/footer.css?<?php echo filemtime('../css/footer.css') ?>">
<link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css" />

<style>

    .font-icon-color-footer 
    { 
        color: #FFFFFF; 
    }

    .software-version, .logging, .simulator, .audit
    {
        display: inline-block;
        color: white;
    }

    .software-version a, .software-version a:link, .software-version a:hover, .software-version a:visited, .logging a, .logging a:link, .logging a:hover, .logging a:visited, .simulator a, .simulator a:link, .simulator a:hover, .simulator a:visited, .audit a, .audit a:link, .audit a:hover, .audit a:visited
    {
        color: white;
    }

    .svg-container
    {
        display: inline-block;
    }

    .software-name
    {
        display: inline;
        margin-left: 5px;
    }

</style>

<div id="footer">
    <div class="footer-components">
        <p class="main-text">&nbsp;</p>
        <div class="logo-container">
            <div class="svg-container"><img src="images/fta.svg" style="width: 16px; margin-top: -1px;"></div>
            <div class="software-name">The Fraud Explorer &reg; Opensource Fraud Triangle Analytics</div>
        </div>
        <div class="helpers-container">
            <span class="fa fa-globe fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><a href="#" onclick="startTour()" style="color: white;">Take tour</a>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-file-text fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><div class="simulator"><a href="../mods/fraudSimulator" data-toggle="modal" class="fraud-simulator-button" data-target="#fraud-simulator" id="elm-fraud-simulator">Semantic simulator</a></div>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-lock fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><div class="simulator"><a href="../mods/libraryLicense" data-toggle="modal" class="library-license-button" data-target="#library-license" id="elm-library-license">Library license</a></div>&nbsp;&nbsp;&nbsp;&nbsp;            
            <span class="fa fa-medkit fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><div class="logging"><a href="../mods/fraudTriangleLogging" data-toggle="modal" class="logging-button" data-target="#logging" href="#" id="elm-logging">Logging</a></div>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-coffee fa-lg font-icon-color-footer">&nbsp;&nbsp;</span><div class="audit"><a href="../mods/viewAudit" data-toggle="modal" class="audit-button" data-target="<?php if ($showAuditLink == true) echo '#audit'; else echo '#noaudit'; ?>" href="#" id="elm-audit">Audit</a></div>&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-address-card fa-lg font-icon-color-footer">&nbsp;&nbsp;</span>Profile [<?php echo $session->username ." - ".$session->domain; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
            <span class="fa fa-codepen fa-lg font-icon-color-footer" id="elm-software-update">&nbsp;&nbsp;</span><div class="software-version"><a href="../mods/swUpdate" data-toggle="modal" class="software-update-button" data-target="#software-update" href="#"><?php echo "Version v".$currentversion; ?></a></div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </div>
    </div>  
</div>

<!-- Modal for Library License -->

<div class="modal" id="library-license" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!-- Modal for Software Update -->

<div class="modal" id="software-update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!-- Modal for Fraud simulator -->

<div class="modal" id="fraud-simulator" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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

<!-- Modal for Logging -->

<div class="modal" id="logging" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center" style="width: 693px;">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Audit -->

<div class="modal" id="audit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="vertical-alignment-helper">
        <div class="modal-dialog vertical-align-center" style="width: 890px;">
            <div class="modal-content">
                <div class="modal-body">
                    <p class="debug-url window-debug"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script for Library License -->

<script>
    $('#library-license').on('show.bs.modal', function(e){
        $(this).find('.library-license-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Script for Software Update -->

<script>
    $('#software-update').on('show.bs.modal', function(e){
        $(this).find('.software-update-button').attr('href', $(e.relatedTarget).data('href'));
    });
</script>

<!-- Script for Fraud simulator -->

<script>
    $('#fraud-simulator').on('show.bs.modal', function(e){
        $(this).find('.fraud-simulator-button').attr('href', $(e.relatedTarget).data('href'));
    });

    $('#fraud-simulator').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Script for Logging -->

<script>
    $('#logging').on('show.bs.modal', function(e){
        $(this).find('.logging-button').attr('href', $(e.relatedTarget).data('href'));
    });

    $('#logging').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>

<!-- Script for Audit -->

<script>
    $('#audit').on('show.bs.modal', function(e){
        $(this).find('.audit-button').attr('href', $(e.relatedTarget).data('href'));
    });

    $('#audit').on('hidden.bs.modal', function () {
        $(this).removeData('bs.modal');
    });
</script>
