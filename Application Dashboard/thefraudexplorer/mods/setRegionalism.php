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
 * Date: 2020-04
 * Revision: v1.4.3-aim
 *
 * Description: Code for set regionalism
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

$words = filter($_POST["regionalismwords"]);
$words = str_replace(' ', '', $words);
$regionalismWords = explode(",", $words);
$configFile = parse_ini_file("/var/www/html/thefraudexplorer/config.ini");

if ($configFile["wc_language"] == "es") $regionalismFile = "../core/spell/customESdictionary.txt";
else $regionalismFile = "../core/spell/customENdictionary.txt";

if (isset($_POST["addwords"])) 
{
    foreach ($regionalismWords as $word)
    {
        file_put_contents($regionalismFile, $word.PHP_EOL, FILE_APPEND);
    }

    $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang='.$configFile["wc_language"].' create master /usr/lib64/aspell-0.60/'.$configFile["wc_language"].'-custom.pws < /var/www/html/thefraudexplorer/core/spell/custom'.strtoupper($configFile["wc_language"]).'dictionary.txt';
    exec($runCustomDictionary, $output, $return);
}
if (isset($_POST["removewords"]))
{
    $sourceFile = fopen($regionalismFile, "r") or exit("Unable to openfile!");
    $t = "";

    while (!feof($sourceFile))
    {
        $k = fgets($sourceFile);
        
        foreach ($regionalismWords as $word)
        {
            if ((preg_match("/".$word."/", $k))) 
            {
                $found = true;
                break;
            }
            else
            {
                $found = false;
            }
        }

        if ($found == false) $t = $t.$k;
    }
    
    fclose($sourceFile);
    $destinationFile = fopen($regionalismFile, "w") or exit("Unable to open file!");
    fwrite($destinationFile, $t);
    fclose($destinationFile);

    $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang='.$configFile["wc_language"].' create master /usr/lib64/aspell-0.60/'.$configFile["wc_language"].'-custom.pws < /var/www/html/thefraudexplorer/core/spell/custom'.strtoupper($configFile["wc_language"]).'dictionary.txt';
    exec($runCustomDictionary, $output, $return);
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>
