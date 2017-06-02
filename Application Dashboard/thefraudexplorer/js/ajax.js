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
 * Description: Code for AJAX command console
 */

function ajaxObject()
{
    var xmlhttp=false;
    
    try 
    {
        xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
    } 
    catch (e) 
    {
        try 
        {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        } 
        catch (E) 
        {
            xmlhttp = false;
        }
    }

    if (!xmlhttp && typeof XMLHttpRequest!='undefined') 
    {
        xmlhttp = new XMLHttpRequest();
    }
    
    return xmlhttp;
}

function showQuery(data)
{
    divResults = document.getElementById('result');
    ajax=ajaxObject();
    ajax.open("GET", data);
    ajax.onreadystatechange=function() 
    {
        if (ajax.readyState==4) 
        {
            divResults.innerHTML = divResults.innerHTML + ajax.responseText;
        }
    }
    ajax.send(null)
}