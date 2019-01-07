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
 * Date: 2019-02
 * Revision: v1.3.1-ai
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
                $endpointLogin = $getData[0];
                $endpointDomain = $getData[1];
                $endpointName = $getData[2];
                $endpointDepartment = $getData[3];
                $endpointGender = $getData[4];

                if ($endpointGender != "male" && $endpointGender != "female") $endpointGender = "male";

                if($endpointDomain == "all")
                {
                    if(in_array($getData[3], $rulesetInventory)) $sql = "UPDATE t_agents SET gender = '".$endpointGender."', name = '".$endpointName."', ruleset = '".$endpointDepartment."' WHERE agent like '".$endpointLogin."\_%'";
                    else $sql = "UPDATE t_agents SET gender = '".$endpointGender."', name = '".$endpointName."', ruleset = 'BASELINE' WHERE agent like '".$endpointLogin."\_%'";
                }
                else
                {
                    if(in_array($endpointDepartment, $rulesetInventory)) $sql = "UPDATE t_agents SET gender = '".$endpointGender."', name = '".$endpointName."', ruleset = '".$endpointDepartment."' WHERE agent like '".$endpointLogin."\_%' AND domain = '".$endpointDomain."'";
                    else $sql = "UPDATE t_agents SET gender = '".$endpointGender."', name = '".$endpointName."', ruleset = 'BASELINE' WHERE agent like '".$endpointLogin."\_%'  AND domain = '".$endpointDomain."'";
                }

                $result = mysqli_query($connection, $sql);
            }
			fclose($file);	
		}
    } 
    else exit;
}

/* Referer Return */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>