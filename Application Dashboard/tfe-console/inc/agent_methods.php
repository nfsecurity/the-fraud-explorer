<?php

 /*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2016 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2016-07
 * Revision: v0.9.7-beta
 *
 * Description: Agent specific functions
 */ 

function queryOrDie($query)
{
 	$query = mysql_query($query);
 	if (! $query) exit(mysql_error());
 	return $query;
}

function isConnected($update_date, $now)
{ 
 	$date_1 = strtotime($update_date); 
 	$date_2 = strtotime($now);
 	$dife= $date_2 - $date_1;
 	$minutesstr = ($dife/60); 
 	$minutes = (INT)($minutesstr); 
 	if ($minutes<0) $minutes = $minutes * -1;
 	return $minutes<1; 
} 

function getTextSist($system)
{ 
 	if($system=='5.1') return ' WiXP';
 	if($system=='6.1') return ' Win7'; 
 	if($system=='6.2' || $system=='6.3') return ' Win8';
	if($system=='10.0') return ' Wi10';
 	else return ' WinV'; 
} 

?>
