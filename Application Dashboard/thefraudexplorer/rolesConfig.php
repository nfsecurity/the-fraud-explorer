<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for role administration
 */

include "lbs/login/session.php";

if(!$session->logged_in)
{
        header ("Location: index");
        exit;
}

include "lbs/global-vars.php";
include "lbs/open-db-connection.php";

?>

<style>

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
}

.window-footer-config
{
    padding: 0px 0px 0px 0px;
}

.div-container
{
    margin: 20px;
}

.table-role
{
    font-family: 'FFont', sans-serif; font-size:10px;
    border: 1px solid #C9C9C9;
    width: 100%;
    border-spacing: 0px;
}

.table-thead-role
{
    display: block;
    font-family: 'FFont-Bold', sans-serif; font-size:12px;
    border-bottom: 0px solid gray;
    border-top: 0px solid gray;
    border-left: 0px solid gray;
    border-right: 0px solid gray;
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
    background: white;
    min-width: calc(555px / 2);
    width: calc(555px / 2);
    text-align: center;
    padding: 0px 0px 0px 5px;
    height: 30px;
}

.table-tbody-role
{
    display: block;
    border: 1px solid white;
    width: 100%;
    height: 60px !important;
    max-height: 60px !important;
    overflow-y: scroll;
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

.table-td-role
{
    border: 0px solid gray;
    width: calc(555px / 2);
    min-width: calc(555px / 2);
    height: 30px;
    min-height: 30px;
    padding: 0px 0px 0px 5px;
    text-align: left;
}

</style>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title window-title" id="myModalLabel">Role Administration</h4>
</div>

<div class="div-container">
    <form id="formConfig" name="formConfig" method="post" action="rolesParameters">

	<p class="title-config">Type the user name</p><br>
	<input class="input-value-text-config" type="text" name="username" id="username" autocomplete="off" placeholder=":username here" padding: 5px; border: solid 2px #c9c9c9;">

	<br><p class="title-config">Type password (only for new user option)</p><br>
        <input class="input-value-text-config" type="password" name="password" id="password" autocomplete="off" placeholder=":password here" padding: 5px; border: solid 2px #c9c9c9;">

	<br><p class="title-config">Specify the domain context</p><br>
        <input class="input-value-text-config" type="text" name="domain" id="domain" autocomplete="off" placeholder=":domain context here" padding: 5px; border: solid 2px #c9c9c9;">
	<br>

	<p class="title-config">List of current users and their domain context</p><br>
	<table class="table-role">
	<thead class="table-thead-role">
		<th class="table-th-role" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>User name</th>
		<th class="table-th-role" style="text-align: left;"><span class="fa fa-bookmark-o font-icon-color">&nbsp;&nbsp;</span>Domain context</th>
	</thead>
	<tbody class="table-tbody-role">

		<?php

			$userQuery = mysql_query("SELECT user, domain FROM t_users"); 

			while($userEntry = mysql_fetch_assoc($userQuery))
			{
				echo '<tr class="table-tr-role">';
				echo '<td class="table-td-role">'.$userEntry['user'].'</td>';
				echo '<td class="table-td-role">'.$userEntry['domain'].'</td>';
				echo '</tr>';
			}

		?>

	</tbody>
	</table>

	<br>
        <div class="modal-footer window-footer-config">
                <br>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
		<input type="submit" name="delete" class="btn btn-danger setup" value="Delete User">
                <input type="submit" name="createmodify" class="btn btn-success setup" value="Create/Modify User">
        </div>
    </form>
</div> 
