<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2020 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for role administration
 */

include "../lbs/login/session.php";
include "../lbs/security.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/openDBconn.php";

?>

<style>

    .ruleset-scroll::-webkit-scrollbar-track 
    {
        border-radius: 0px 0px 5px 0px;
    }

    .title-config
    {
        font-family: 'FFont', sans-serif; font-size:12px;
        float: left;
        padding-bottom: 10px;
        padding-top: 10px;
    }

    .input-value-text-config
    {
        width: 100%;
        height: 30px;
        padding: 5px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: 'FFont', sans-serif; font-size: 12px;
        border-radius: 5px;
    }

    .window-footer-roles
    {
        padding: 15px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container-roles
    {
        margin: 20px;
    }

    .table-roles 
    {
        display: table;
        table-layout: fixed;
        border: 1px solid #C9C9C9;
        border-radius: 5px 5px 5px 5px;
        width: 100%;
        height: 93px;
    }

    .thead-roles 
    {
        display: table-header-group;
        font-family: 'FFont-Bold', sans-serif; font-size: 12px;
        text-align: left;
        color: black;
    }

    .tbody-roles 
    {
        display: table-row-group;
        overflow-y: scroll;
    }

    .tbody-wrapper
    {
        height: 60px; 
        width: 556px; 
        overflow: auto;
        border-radius: 0px 0px 5px 5px;
    }
    
    .tr-roles, .thead-roles 
    { 
        display: table-row; 
    }

    .td-roles 
    { 
        display: table-cell;
        font-family: Verdana, sans-serif; font-size: 11px;
        padding: 5px 5px 5px 8px;
        text-align: left;
        height: 30px;
    }

    .th-roles 
    { 
        padding: 8px 8px 8px 8px;
        display: table-cell;
    }

    .tr-roles:nth-of-type(odd)
    {
        background-color: #EDEDED !important;
    }
    
    .tr-roles:nth-of-type(even)
    {
        background: #FFFFFF;
    }
    
     .font-icon-color-green
    {
        color: #4B906F;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

    .btn-success, .btn-success:active, .btn-success:visited 
    {
        background-color: #4B906F !important;
        border: 1px solid #4B906F !important;
    }

    .btn-success:hover
    {
        background-color: #57a881 !important;
        border: 1px solid #57a881 !important;
    }

    .btn-success, .btn-success:active, .btn-success:visited, .btn-danger, .btn-danger:active, .btn-danger:visited
    {
        font-family: Verdana, sans-serif; font-size: 14px !important;
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Role Administration</h4>
</div>

<div class="div-container-roles">
    <form id="formRoles" name="formRoles" method="post" action="mods/rolesParameters">
        <p class="title-config">Type the user name</p><br>
        <input class="input-value-text-config" type="text" name="username" id="username" autocomplete="new-password" placeholder=":username here">
        <br><p class="title-config">Type password (only for new user option)</p><br>
        <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="new-password" placeholder=":password here">
        <br><p class="title-config">Specify the domain context</p><br>
        <input class="input-value-text-config" type="text" name="domain" id="domain" autocomplete="new-password" placeholder=":domain context here">
        <br>
        <p class="title-config">List of current users and their domain context</p>

        <div class="table-roles">
            <div class="thead-roles">
                <div class="thead-roles">
                    <div class="th-roles" style="width: 275px;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>User name</div>
                    <div class="th-roles" style="width: 280px;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Domain context</div>
                </div>
            </div>
            <div class="tbody-roles">

                <div class="tbody-wrapper">
                <?php

                    $userQuery = mysqli_query($connection, "SELECT user, domain FROM t_users"); 

                    while($userEntry = mysqli_fetch_assoc($userQuery))
                    {
                        echo '<div class="tr-roles">';
                        echo '<div class="td-roles" style="width: 280px;"><span class="fa fa-user-circle font-icon-color-green fa-padding"></span>'.$userEntry['user'].'</div>';
                        echo '<div class="td-roles" style="width: 275px;"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$userEntry['domain'].'</div>';
                        echo '</div>';
                    }

                ?>

                </div>

            </div>
        </div>

        <div class="modal-footer window-footer-roles">
            <input type="submit" name="delete" class="btn btn-danger setup" value="Delete profile" style="outline: 0 !important;">
            <input type="submit" name="createmodify" class="btn btn-success setup" value="Create/Modify profile" style="outline: 0 !important;">
        </div>
    </form>
</div> 