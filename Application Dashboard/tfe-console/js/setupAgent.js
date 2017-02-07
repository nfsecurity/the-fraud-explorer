/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-06-30 15:12:41 -0500 (Wed, 30 Jun 2016)
 * Revision: v0.9.6-beta
 *
 * Description: Code for AJAX
 */

/* Tooltipster */

$(document).ready(function() 
{
    $('.tooltip').tooltipster();
});

/* SVG change */

jQuery(document).ready(function() 
{
    jQuery('img.svg').each(function()
    {
        var $img = jQuery(this);
        var imgID = $img.attr('id');
        var imgClass = $img.attr('class');
        var imgURL = $img.attr('src');

        jQuery.get(imgURL, function(data) 
        {
            var $svg = jQuery(data).find('svg');

            if(typeof imgID !== 'undefined') 
            {
                $svg = $svg.attr('id', imgID);
            }
 
            if(typeof imgClass !== 'undefined') 
            {
                $svg = $svg.attr('class', imgClass+' replaced-svg');
            }

            $svg = $svg.removeAttr('xmlns:a');

            $img.replaceWith($svg);
        });
    });
});

/* Ajax for reset XML file */

$(function() 
{ 
    $('a[class="reset-xml-button"]').click(function()
    {
        $.ajax({
            url: "eraseCommands.php", 
            type: "POST",
            data: "", 
            success: function()
            {
                console.log("AJAX request was successfull");
            },
            error:function()
            {
                console.log("AJAX request was a failure");
            }   
        });
    });
});

/* Code for refresh XML Table using AJAX */

$.ajaxSetup ({
    cache: false
});

$(document).ready(function()
{
    refreshXML();
});

function refreshXML()
{
    $('#tableHolderXML').load('getXMLfile.php', function()
    {
        setTimeout(refreshXML, 2000);
    });
}

/* Code for html footer include */

$(function()
{
    $("#includedFooterContent").load("mainFooter.html"); 
});
