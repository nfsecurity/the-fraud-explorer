/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPLv3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for AJAX in XML Table Holder
 */

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
