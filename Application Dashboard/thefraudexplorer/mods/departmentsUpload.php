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
 * Date: 2019-01
 * Revision: v1.2.2-ai
 *
 * Description: Code for set departments
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

$target_dir = "../core/departments/";
$target_file = $target_dir . basename($_FILES["departmentsToUpload"]["name"]);
$fileType = pathinfo($target_file, PATHINFO_EXTENSION);

if ($fileType != "csv") exit;
else
{
    $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

    if(in_array($_FILES['departmentsToUpload']['type'], $mimes))
    {
        move_uploaded_file($_FILES["departmentsToUpload"]["tmp_name"], $target_file);

        if($_FILES["departmentsToUpload"]["size"] > 0)
		{  
            $file = fopen($target_file, "r");

             /* Ruleset existence */

             $rulesetLanguage = $configFile['fta_lang_selection'];
             $jsonFT = json_decode(file_get_contents($configFile[$rulesetLanguage]), true);
             $rulesetInventory = Array();
             $rulesetCount = 0;

             foreach ($jsonFT['dictionary'] as $ruleset => $value)
             {
                 $rulesetInventory[$rulesetCount] = $ruleset;
                 $rulesetCount++;
             }
              
	        while (($getData = fgetcsv($file, 100000, ",")) !== FALSE)
	        {
                $endpointName = $getData[2];

                if($getData[1] == "all")
                {
                    if(in_array($getData[3], $rulesetInventory)) $sql = "UPDATE t_agents SET name = '".$endpointName."', ruleset = '".$getData[3]."' WHERE agent like '".$getData[0]."\_%'";
                    else $sql = "UPDATE t_agents SET name = '".$endpointName."', ruleset = 'BASELINE' WHERE agent like '".$getData[0]."\_%'";
                }
                else
                {
                    if(in_array($getData[3], $rulesetInventory)) $sql = "UPDATE t_agents SET name = '".$endpointName."', ruleset = '".$getData[3]."' WHERE agent like '".$getData[0]."\_%' AND domain = '".$getData[1]."'";
                    else $sql = "UPDATE t_agents SET name = '".$endpointName."', ruleset = 'BASELINE' WHERE agent like '".$getData[0]."\_%'  AND domain = '".$getData[1]."'";
                }

                $result = mysql_query($sql);
            }
			fclose($file);	
		}
    } 
    else exit;
}

/* Referer Return */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>