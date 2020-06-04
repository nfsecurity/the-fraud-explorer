/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-06
 * Revision: v1.4.5-aim
 *
 * Description: Code for AJAX
 */

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("helpers/mainFooter.php"); 
});

/* Code for event module holder */

$(function(){
    $("#tableHolder").html("<div id=\"wrapper\"><div class=\"spinner\"><div class=\"rect1\" style=\"margin-right: 3px;\"></div><div class=\"rect2\" style=\"margin-right: 3px;\"></div><div class=\"rect3\" style=\"margin-right: 3px;\"></div><div class=\"rect4\" style=\"margin-right: 3px;\"></div><div class=\"rect5\"></div></div></div>").load("mods/eventHolder.php"); 
});
