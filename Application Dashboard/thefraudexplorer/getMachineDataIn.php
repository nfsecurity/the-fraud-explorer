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
 * Date: 2020-02
 * Revision: v1.4.2-aim
 *
 * Description: Code for get data from the endpoint
 */

include "lbs/globalVars.php";
include $documentRoot."lbs/openDBconn.php";
include $documentRoot."lbs/cryptography.php";
include "lbs/security.php";

function queryOrDie($query)
{
    global $connection;

    $query = mysqli_query($connection, $query);
    if (! $query) exit(mysqli_error());
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

$endpointIdentification = decRijndaelRemote(filter($_GET['m']));
$id_uniq_command = decRijndaelRemote(filter($_GET['id']));
$finished = filter($_GET['end']);
$command = filter($_GET['c']);
$content = decRijndaelRemote(filter($_GET['response']));
$table = 't_'.$endpointIdentification;

$result_a = mysqli_query($connection, "SELECT count(*) FROM ".$table." WHERE id_uniq_command=" .$id_uniq_command." AND finished=false order by date desc limit 1");

if (is_bool($result_a) === true) exit;
else $row_a = mysqli_fetch_array($result_a);

/* If the endpoint exists or not */

if($row_a[0] > 0)
{
    $result_b = mysqli_query($connection, "SELECT * FROM ".$table." WHERE id_uniq_command=" .$id_uniq_command);
    $row_b = mysqli_fetch_array($result_b);

    if ($finished == 0)
    {
        $result = mysqli_query($connection, "Update ".$table." set date=now(), response='".$row_b["response"].$content."' where id_uniq_command=".$id_uniq_command);
    }
    else
    {
        $result = mysqli_query($connection, "Update ".$table." set date=now(), response='".$row_b["response"].$content."', finished=true where id_uniq_command=".$id_uniq_command);
    }
}
else
{
    if ($finished == 0)
    {
        $query = "INSERT INTO ".$table." (command, response, finished, date, id_uniq_command, showed) VALUES ('" . $command . "','" . $content ."',false,now(),".$id_uniq_command.",false) ";
        queryOrDie($query);
    }
    else
    {
        $query = "INSERT INTO " .$table. " (command, response, finished, date, id_uniq_command, showed) VALUES ('" .$command. "','" .$content . "',true,now()," .$id_uniq_command.",false) ";
        queryOrDie($query);
    }
}

include $documentRoot."lbs/closeDBconn.php";

?>