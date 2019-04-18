<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2019 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2019-05
 * Revision: v1.3.3-ai
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
        width:100%;
        height: 30px;
        padding: 5px;
        border: solid 1px #c9c9c9;
        outline: none;
        font-family: 'FFont', sans-serif; font-size:12px;
        border-radius: 5px;
    }

    .window-footer-config
    {
        padding: 0px 0px 0px 0px;
        margin: 15px 0px 0px 0px;
    }

    .div-container-roles
    {
        margin: 20px;
    }

    .table-role
    {
        font-family: 'FFont', sans-serif; font-size: 10px;
        border: 1px solid #C9C9C9;
        width: 100%;
        border-collapse: separate !important;
        border-radius: 5px 5px 5px 5px;
    }

    .table-role tbody 
    {
        background-image: none;
        background-color: white;
    }

    .table-thead-role
    {
        display: block;
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        border-radius: 5px 5px 0px 0px;
        width: 100%;
        height: 30px;
    }

    .table-th-role
    {
        font-family: 'FFont-Bold', sans-serif; font-size:12px;
        border-bottom: 0px solid gray;
        border-top: 0px solid gray;
        border-left: 0px solid gray;
        border-right: 0px solid gray;
        background-color: white;
        min-width: calc(555px / 2);
        width: calc(555px / 2);
        text-align: center;
        padding: 0px 0px 0px 5px;
        height: 30px;
        border-collapse: separate !important;
        border-radius: 5px 5px 0px 0px;
    }

    .table-tbody-role
    {
        display: block;
        width: 100%;
        height: 60px !important;
        max-height: 60px !important;
        overflow-y: scroll;
        border-collapse: separate !important;
        border-radius: 0px 0px 5px 5px;
    }

    .table-tr-role
    {
        border: 0px solid gray;
        height: 30px;
        min-height: 30px;
        background: white;
    }

    .table-tbody-role tr:nth-child(odd)
    {
        background-color: #EDEDED !important;
    }
    
    .table-tbody-role tr:nth-child(even)
    {
        background: #FFFFFF;
    }

    .table-td-role
    {
        border-right: 2px solid white;
        border-top: 0px solid white;
        border-left: 0px solid white;
        border-bottom: 0px solid white;
        width: calc(555px / 2);
        min-width: calc(555px / 2);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
    }
    
    .table-td-role-domain
    {
        border: 0px solid gray;
        width: calc(555px / 2);
        min-width: calc(555px / 2 - 7);
        height: 30px;
        min-height: 30px;
        padding: 0px 0px 0px 5px;
        text-align: left;
    }
    
     .font-icon-color-green
    {
        color: green;
    }
    
    .font-icon-gray 
    { 
        color: #B4BCC2;; 
    }
    
    .fa-padding 
    { 
        padding-right: 5px; 
    }

</style>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h4 class="modal-title window-title" id="myModalLabel">Role Administration</h4>
</div>

<div class="div-container-roles">
    <form id="formRoles" name="formRoles" method="post" action="mods/rolesParameters">
        <p class="title-config">Type the user name</p><br>
        <input class="input-value-text-config" type="text" name="username" id="username" autocomplete="off" placeholder=":username here">
        <br><p class="title-config">Type password (only for new user option)</p><br>
        <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="off" placeholder=":password here">
        <br><p class="title-config">Specify the domain context</p><br>
        <input class="input-value-text-config" type="text" name="domain" id="domain" autocomplete="off" placeholder=":domain context here">
        <br>
        <p class="title-config">List of current users and their domain context</p><br>

        <table class="table-role">
            <thead class="table-thead-role">
                <th class="table-th-role" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>User name</th>
                <th class="table-th-role" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color fa-padding"></span>Domain context</th>
            </thead>
            <tbody class="table-tbody-role ruleset-scroll">

                <?php

                $userQuery = mysqli_query($connection, "SELECT user, domain FROM t_users"); 

                while($userEntry = mysqli_fetch_assoc($userQuery))
                {
                    echo '<tr class="table-tr-role">';
                    echo '<td class="table-td-role"><span class="fa fa-user-circle font-icon-color-green fa-padding"></span>'.$userEntry['user'].'</td>';
                    echo '<td class="table-td-role-domain"><span class="fa fa-globe font-icon-gray fa-padding"></span>'.$userEntry['domain'].'</td>';
                    echo '</tr>';
                }

                ?>

            </tbody>
        </table>

        <div class="modal-footer window-footer-config">
            <br><button type="button" class="btn btn-default" data-dismiss="modal" style="outline: 0 !important;">Cancel</button>
            <input type="submit" name="delete" class="btn btn-danger setup" value="Delete User" style="outline: 0 !important;">
            <input type="submit" name="createmodify" class="btn btn-success setup" value="Create/Modify User" style="outline: 0 !important;">
        </div>
    </form>
</div> 
