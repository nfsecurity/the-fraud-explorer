<?php

/*
 * The Fraud Explorer
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
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

/* Prevent direct access to this URL */ 

if(!isset($_SERVER['HTTP_REFERER']))
{
    header( 'HTTP/1.0 403 Forbidden', TRUE, 403);
    exit;
}

include "../lbs/globalVars.php";
include "../lbs/cryptography.php";
include "../lbs/openDBconn.php";

$target_dir = "../core/departments/";
$target_file = $target_dir . basename($_FILES["departmentsToUpload"]["name"]);
$fileType = pathinfo($target_file, PATHINFO_EXTENSION);
$msg = "";

if ($fileType != "csv") 
{
    $msg = "Unable to load department file structure";
}
else
{
    $mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv');

    if(in_array($_FILES['departmentsToUpload']['type'], $mimes))
    {
        move_uploaded_file($_FILES["departmentsToUpload"]["tmp_name"], $target_file);

        if($_FILES["departmentsToUpload"]["size"] > 0)
		{  
            $file = fopen($target_file, "r");

            /* Rulesets */

            $jsonFT = json_decode(file_get_contents($configFile['fta_text_rule_spanish']), true);
            $rulesetInventory = Array();
            $rulesetCount = 0;

            foreach ($jsonFT['dictionary'] as $ruleset => $value)
            {
                $rulesetInventory[$rulesetCount] = $ruleset;
                $rulesetCount++;
            }
              
	        while (($getData = fgetcsv($file, 100000, ",")) !== FALSE)
	        {
                $endpointLogin = filter(trim($getData[0]));
                $endpointDomain = filter(trim($getData[1]));
                $endpointName = filter(trim($getData[2]));
                $endpointDepartment = filter(trim($getData[3]));
                $endpointGender = filter(trim($getData[4]));

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
            
            auditTrail("business", "successfully loaded business units structure to database");
            $msg = "Successfully loaded department structure";
		}
    } 
    else
    {
        auditTrail("business", "unable to load business units file structure to database");
        $msg = "Unable to load department file structure";
    }
}

$_SESSION['wm'] = encRijndael($msg);

/* Referer Return */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>