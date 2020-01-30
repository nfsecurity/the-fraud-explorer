/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for AJAX
 */

/* Code for load Endpoints Holder */

$(function(){
    $("#tableHolder").html("<div style=\"position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: auto; eight: auto; text-align: center;\"><img src=\"../images/ajax-loader.gif\"/><br>Please wait</div>").load("mods/endHolder.php"); 
});

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("helpers/mainFooter.php"); 
});

/* Code for html top menu include */

$(function(){
    $("#includedTopMenu").load("helpers/topMenu.php?origin=endpoints");
});