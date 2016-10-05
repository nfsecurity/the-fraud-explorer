/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Code for Chart
 */

/* Tooltipster */

$(document).ready(function() 
{
    	$('.tooltip').tooltipster(
	{
		theme: 'tooltipster-light',
		contentAsHTML: true
	});	
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

