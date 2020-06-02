/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-05
 * Revision: v1.4.4-aim
 *
 * Description: Code for Chart
 */

 /* Code for html top menu include */

$(function(){
    $("#includedTopMenu").load("helpers/topMenu.php");
});

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("helpers/mainFooter.php"); 
});

/* Code for analytics module holder */

$(function(){
    $("#chartHolder").html("<div id=\"wrapper\"><div class=\"spinner\"><div class=\"rect1\" style=\"margin-right: 3px;\"></div><div class=\"rect2\" style=\"margin-right: 3px;\"></div><div class=\"rect3\" style=\"margin-right: 3px;\"></div><div class=\"rect4\" style=\"margin-right: 3px;\"></div><div class=\"rect5\"></div></div></div>").load("mods/analyticsHolder.php");
});