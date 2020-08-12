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
 * Date: 2020-08
 * Revision: v1.4.7-aim
 *
 * Description: Code for set regionalism and tone
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

if (isset($_GET['se'])) $objective = $_GET['se'];
else header('Location: ' . $_SERVER['HTTP_REFERER']);

/* If selected regionalism */

if ($objective == "reg")
{
    $words = filter($_POST["regionalismwords"]);
    $regionalismFile = decRijndael(filter($_POST["library-language"]));

    $words = str_replace(' ', '', $words);
    $regionalismWords = explode(",", $words);

    if (isset($_POST["addwords"])) 
    {
        foreach ($regionalismWords as $word)
        {
            file_put_contents($regionalismFile, $word.PHP_EOL, FILE_APPEND);
        }

        if(strpos($regionalismFile, "customESdictionary") !== false) $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=es create master /usr/lib64/aspell-0.60/es-custom.pws < /var/www/html/thefraudexplorer/core/spell/customESdictionary.txt';
        else $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=en create master /usr/lib64/aspell-0.60/en-custom.pws < /var/www/html/thefraudexplorer/core/spell/customENdictionary.txt';

        exec($runCustomDictionary, $output, $return);

        /* ASPELL multilanguage */

        $runMultiDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=en --master=en.multi dump master | aspell -l en expand | perl -e \'while(<>){ print join("\n", split), "\n";}\' > /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt ; /usr/bin/sudo /usr/bin/aspell --lang=es --master=es.multi dump master | aspell -l es expand | perl -e \'while(<>){ print join("\n", split), "\n";}\' >> /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt ; /usr/bin/sudo /usr/bin/aspell --lang=hu --encoding=utf-8 create master /usr/lib64/aspell-0.60/hu.rws < /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt';
        exec($runMultiDictionary, $output, $return);
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

        if(strpos($regionalismFile, "customESdictionary") !== false) $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=es create master /usr/lib64/aspell-0.60/es-custom.pws < /var/www/html/thefraudexplorer/core/spell/customESdictionary.txt';
        else $runCustomDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=en create master /usr/lib64/aspell-0.60/en-custom.pws < /var/www/html/thefraudexplorer/core/spell/customENdictionary.txt';

        exec($runCustomDictionary, $output, $return);

        /* ASPELL multilanguage */

        $runMultiDictionary = '/usr/bin/sudo /usr/bin/aspell --lang=en --master=en.multi dump master | aspell -l en expand | perl -e \'while(<>){ print join("\n", split), "\n";}\' > /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt ; /usr/bin/sudo /usr/bin/aspell --lang=es --master=es.multi dump master | aspell -l es expand | perl -e \'while(<>){ print join("\n", split), "\n";}\' >> /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt ; /usr/bin/sudo /bin/recode ISO-8859-1..UTF8 /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt ; /usr/bin/sudo /usr/bin/aspell --lang=hu --encoding=utf-8 create master /usr/lib64/aspell-0.60/hu.rws < /var/www/html/thefraudexplorer/core/spell/multilingualSEdictionary.txt';
        exec($runMultiDictionary, $output, $return);
    }
}

/* If selected tone */

else
{
    $words = filter($_POST["tonewords"]);
    $toneFile = decRijndael(filter($_POST["library-tone"]));

    $words = str_replace(' ', '', $words);
    $toneWords = explode(",", $words);

    if (isset($_POST["addwords"])) 
    {
        foreach ($toneWords as $word) file_put_contents($toneFile, $word.PHP_EOL, FILE_APPEND);
    }
    if (isset($_POST["removewords"]))
    {
        $sourceFile = fopen($toneFile, "r") or exit("Unable to openfile!");
        $t = "";

        while (!feof($sourceFile))
        {
            $k = fgets($sourceFile);
            
            foreach ($toneWords as $word)
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
        $destinationFile = fopen($toneFile, "w") or exit("Unable to open file!");
        fwrite($destinationFile, $t);
        fclose($destinationFile);
    }
}

/* Page return to origin */

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>

</body>
</html>