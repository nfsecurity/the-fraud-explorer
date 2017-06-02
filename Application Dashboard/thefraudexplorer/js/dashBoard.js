/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for AJAX
 */

/* Code for load main Dashboard */

$(function(){
    $("#mainDashHolder").html("<div style=\"position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: auto; eight: auto; text-align: center;\"><img src=\"../images/ajax-loader.gif\"/><br>Please wait</div>"
                             ).load("dashHolder.php"); 
});

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("mainFooter.php"); 
});

/* Code for html top menu include */

$(function(){
    $("#includedTopMenu").load("topMenu.php");
});