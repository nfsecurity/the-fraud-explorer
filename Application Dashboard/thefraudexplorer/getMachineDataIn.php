<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v1.0.0-beta
 *
 * Description: Code for get data from the agent
 */

include "lbs/global-vars.php";
include $documentRoot."lbs/open-db-connection.php";
include $documentRoot."lbs/cryptography.php";
include "lbs/security.php";

function queryOrDie($query)
{
    $query = mysql_query($query);
    if (! $query) exit(mysql_error());
    return $query;
}

function minute_difference($update_date)
{
    $actual_date = date("Y-m-d H:i:s",time());
    $update_date2 = strtotime($update_date);
    $actual_date2 = strtotime($actual_date);
    $dife = $actual_date2 - $update_date2;
    $minutesstr = ($dife/60);
    $minutes = (INT)($minutesstr);
    $minutes = $minutes+60;
    return $minutes;
} 

$macAgent = decRijndael(filter($_GET['m']));
$id_uniq_command = decRijndael(filter($_GET['id']));
$finished = filter($_GET['end']);
$command = filter($_GET['c']);
$content = decRijndael(filter($_GET['response']));
$table='t_'.$macAgent;

$result_a=mysql_query("SELECT count(*) FROM ".$table." WHERE id_uniq_command=" .$id_uniq_command." AND finished=false order by date desc limit 1");
$row_a = mysql_fetch_array($result_a);

/* If the agent exists or not */

if($row_a[0]>0)
{
    $result_b=mysql_query("SELECT * FROM ".$table." WHERE id_uniq_command=" .$id_uniq_command);
    $row_b = mysql_fetch_array($result_b);

    if($finished==0)
    {
        $result=mysql_query("Update ".$table." set date=now(), response='".$row_b["response"].$content."' where id_uniq_command=".$id_uniq_command);
    }
    else
    {
        $result=mysql_query("Update ".$table." set date=now(), response='".$row_b["response"].$content."', finished=true where id_uniq_command=".$id_uniq_command);
    }
}
else
{
    if($finished==0)
    {
        $query="INSERT INTO ".$table." (command, response, finished, date, id_uniq_command, showed) VALUES ('" . $command . "','" . $content ."',false,now(),".$id_uniq_command.",false) ";
        queryOrDie($query);
    }
    else
    {
        $query="INSERT INTO " .$table. " (command, response, finished, date, id_uniq_command, showed) VALUES ('" .$command. "','" .$content . "',true,now()," .$id_uniq_command.",false) ";
        queryOrDie($query);
    }
}

include $documentRoot."lbs/close-db-connection.php";

?>