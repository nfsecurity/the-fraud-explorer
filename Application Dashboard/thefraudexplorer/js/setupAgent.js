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
 * Revision: v1.2.1
 *
 * Description: Code for AJAX
 */

/* Tooltipster */

$(document).ready(function() {
    $('.tooltip').tooltipster();
});

/* SVG change */

jQuery(document).ready(function() {
    jQuery('img.svg').each(function(){
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

/* Code for disable cache modal */

$.ajaxSetup ({
    cache: false
});

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("helpers/mainFooter.html"); 
});