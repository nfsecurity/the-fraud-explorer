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
 * Description: Code for showing the status of a executed command
 */

header("Cache-Control: no-store, no-cache, must-revalidate");

include "lbs/login/session.php";

if(!$session->logged_in)
{
    header ("Location: index");
    exit;
}  

include "lbs/global-vars.php";
include "lbs/cryptography.php";

function filter($variable)
{
    return addcslashes(mysql_real_escape_string($variable),',<>');
}

function clear_xml()
{
    $xml_for_delete = simplexml_load_file('update.xml');
    foreach ($xml_for_delete->version as $version) $numVersion = (int) $version['num'];
    $numVersion++;
    $xmlContent="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
    $xmlContent = $xmlContent . "</update>";
    $fp = fopen('update.xml',"w+");
    fputs($fp, $xmlContent);
    fclose($fp);
}

function clear_xml_updater($id)
{
    $xml_for_updater = simplexml_load_file('update.xml');
    foreach ($xml_for_updater->version as $version) $numVersion = (int) $version['num'];
    $numVersion++;
    $xmlContent ="<?xml version=\"1.0\"?>\r\n<update>\r\n<version num=\"" . $numVersion . "\" />\r\n";
    $xmlContent = $xmlContent . "<token type=\"". encRijndael("updater") . "\" arg=\"\" id=\"".$id."\" agt=\"". encRijndael("none") ."\"/> \r\n";
    $xmlContent = $xmlContent . "</update>";
    $fp = fopen('update.xml',"w+");
    fputs($fp, $xmlContent);
    fclose($fp);
}

/* List of available and permitted commands */

$cmds_srv = array("uninstall","update", "module", "killprocess");

$seconds_to_complete=3600;
if (!isset($_SESSION['seconds_waiting'])) $_SESSION['seconds_waiting']=0;
$agent = $_GET['agent'];
$agent_dec = base64_decode(base64_decode($_GET['agent']));

if (isset($_SESSION['id_command']))
{
    $_SESSION['new_command']=$_SESSION['id_command'];
    if ($_SESSION['new_command'] != $_SESSION['waiting_command']) $_SESSION['seconds_waiting']=0;
}
else
{
    echo "<b>STATUS:</b> Ready, enter a *valid* command to execute ...";
    unset($_SESSION['seconds_waiting']);
    exit;
}

if($_SESSION['id_command'] == 0 || $_SESSION['NRF'] == 1)
{
    if ($_SESSION['NRF'] == 1)
    {
        echo "<b>WARNING:</b> The command &lt;".$_SESSION['NRF_CMD']."&gt; was not recognized, please try again!";
    }
    else
    {
        echo "<b>STATUS:</b> Ready, enter a *valid* command to execute ...";
        unset($_SESSION['seconds_waiting']);
    }
}
else
{
    $id=$_SESSION['id_command'];
    if($id>0)
    {
        include "lbs/open-db-connection.php";

        $xml=simplexml_load_file('update.xml');
        $type = decRijndael($xml->token[0]['type']);

        $queryTables = mysql_list_tables("thefraudexplorer");
        $unionQuery = "";
        $agentPrefix = "t_".$agent_dec;

        while($row = mysql_fetch_row($queryTables)) if (strpos($row[0], $agentPrefix) !== false) $unionQuery = $unionQuery." SELECT * FROM ".$row[0]." UNION";

        $queryElements = explode( " ", $unionQuery);
        array_splice($queryElements, -1);
        $unionQuery = implode(" ", $queryElements);

        $result_a=mysql_query("SELECT finished, response, date FROM (".$unionQuery.") AS tbl WHERE id_uniq_command='".$id."' order by date desc limit 1");

        if($result_a != FALSE) $data = mysql_fetch_array($result_a);
        else $result_a = "";

        if ($agent_dec == "all")
        {
            if(!in_array($type,$cmds_srv))
            {
                $nrf_cmd=$type;
                $_SESSION['NRF_CMD']=(string)$nrf_cmd;
                echo "<b>WARNING:</b> The command &lt;".$_SESSION['NRF_CMD']."&gt; was not recognized, please try again!";          
                $_SESSION['NRF']=1;
                unset($_SESSION['id_command']);
            }
            else echo "<b>STATUS:</b> command sent to all online agents! with id ".$id.". Check each reply.";
        }
        else if(empty($data['finished']) && !empty($type))
        {
            if (!empty($type) && !in_array($type,$cmds_srv))
            {
                $nrf_cmd=$type;
                $_SESSION['NRF_CMD']=(string)$nrf_cmd;
                echo "<b>WARNING:</b> The command &lt;".$_SESSION['NRF_CMD']."&gt; was not recognized, please try again!";	    
                $_SESSION['NRF']=1;
                unset($_SESSION['id_command']);
            }
            else if (!empty($type) && ($_SESSION['seconds_waiting'] < $seconds_to_complete)) 
            {
                echo "<b>STATUS:</b> Wait for &lt;".$type ."&gt; with id ".$id." on ".$agent_dec." to complete  ..."; 
                $_SESSION['seconds_waiting']++;
                $_SESSION['waiting_command']=$_SESSION['id_command'];
            }
            else if (!empty($type))
            {
                echo "<b>STATUS:</b> Failed to execute command with id ".$id." on ".$agent_dec.". Please try again.";
                sleep(5);
                unset($_SESSION['id_command']);
                unset($_SESSION['seconds_waiting']);
                clear_xml();
            }
        }
        else if (!empty($type) && (in_array($type,$cmds_srv)))
        {
            echo "<b>STATUS:</b> Reply from agent!, &lt;".$type."&gt; with id ".$id." on " .$agent_dec. " : ".$data['response'];
            if ($type == "update") clear_xml_updater($_SESSION['id_command']);
            unset($_SESSION['seconds_waiting']);
        }
        include "lbs/close-db-connection.php";
    }
}

?>