/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2018-12
 * Revision: v1.2.0
 *
 * Description: Code for Chart
 */

/* Code for include the chart-holder */

$(function(){
    $("#chartHolder").html("<div style=\"position: absolute; left: 50%; top: 50%; font-size: 11px; transform: translate(-50%, -50%); width: auto; eight: auto; text-align: center;\"><img src=\"../images/ajax-loader.gif\"/><br>Please wait</div>").load("analyticsHolder.php");
});

/* Code for html footer include */

$(function(){
    $("#includedGenericFooterContent").load("genericFooter.php"); 
});

/* Code for html top menu include */

$(function(){
    $("#includedTopMenu").load("topMenu.php");
});