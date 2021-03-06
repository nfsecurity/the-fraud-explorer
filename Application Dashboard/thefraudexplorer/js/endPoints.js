/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: Code for AJAX
 */

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("helpers/mainFooter.php"); 
});

/* Code for endpoints module holder */

$(function(){
    $("#tableHolder").html("<div id=\"wrapper\"><div class=\"spinner\"><div class=\"rect1\" style=\"margin-right: 3px;\"></div><div class=\"rect2\" style=\"margin-right: 3px;\"></div><div class=\"rect3\" style=\"margin-right: 3px;\"></div><div class=\"rect4\" style=\"margin-right: 3px;\"></div><div class=\"rect5\"></div></div></div>").load("mods/endHolder.php"); 
});
