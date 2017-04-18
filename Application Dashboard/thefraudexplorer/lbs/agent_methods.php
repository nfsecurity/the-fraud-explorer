<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: support@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
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

function samplerStatus($sessionDomain)
{
    if ($sessionDomain != "all") 
    {
        $domainConfigTable = "t_config_".str_replace(".", "_", $sessionDomain);
        $queryCalc = "SELECT sample_data_calculation FROM ".$domainConfigTable;
        $calculationQuery = mysql_query($queryCalc); 
        $sampleQuery = mysql_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
    else
    {
        $calculationQuery = mysql_query("SELECT sample_data_calculation FROM t_config");
        $sampleQuery = mysql_fetch_array($calculationQuery);
        return $sampleQuery[0];
    }
}

?>