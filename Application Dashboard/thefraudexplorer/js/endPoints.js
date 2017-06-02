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

/* Ajax for reset XML file */

$(function() 
  {
    $('a[class="reset-xml-button"]').click(function(){  
        $.ajax({
            url: "eraseCommands.php",
            type: "POST",
            data: "",	
            success: function(){},
            error:function(){}   
        });
    });
});

/* Code for refresh main Table using AJAX */

$(function(){
    $("#tableHolder").html("<div style=\"position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); width: auto; eight: auto; text-align: center;\"><img src=\"../images/ajax-loader.gif\"/><br>Please wait</div>"
                          ).load("endHolder.php"); 
});

/* Code for refresh XML Table using AJAX */

$(document).ready(function(){
    refreshXML();
});

function refreshXML()
{
    $('#tableHolderXML').load('getXMLfile.php', function(){
        setTimeout(refreshXML, 2000);
    });
}

/* Code for html footer include */

$(function(){
    $("#includedFooterContent").load("mainFooter.php"); 
});

/* Code for html top menu include */

$(function(){
    $("#includedTopMenu").load("topMenu.php");
});