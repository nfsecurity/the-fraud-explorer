/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for Chart
 */

/* Code for include the chart-holder */

$(function()
{
    $("#chartHolder").html("<div style=\"position: absolute; left: 50%; top: 50%; font-size: 11px; transform: translate(-50%, -50%); width: auto; eight: auto; text-align: center;\"><img src=\"../images/ajax-loader.gif\"/><br>Please wait</div>"
    ).load("analyticsHolder.php");
});

/* Code for html footer include */

$(function()
{
    $("#includedGenericFooterContent").load("genericFooter.php"); 
});

/* Code for html top menu include */

$(function()
{
    $("#includedTopMenu").load("topMenu.php");
});

