<?php

 /*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Agent specific functions
 */ 

function queryOrDie($query)
{
 	$query = mysql_query($query);
 	if (! $query) exit(mysql_error());
 	return $query;
}

function isConnected($t1, $t2)
{
	$dateUpper=strtotime($t2);
        $dateLower=strtotime($t1);
        $differenceMns = (int)(($dateUpper - $dateLower)/60);
        return $differenceMns<70;
}

function getTextSist($system)
{ 
 	if($system=='5.1') return ' Windows XP';
 	if($system=='6.1') return ' Windows 7'; 
 	if($system=='6.2' || $system=='6.3') return ' Windows 8';
	if($system=='10.0') return ' Windows 10';
 	else return ' Windows Vista'; 
} 

?>
