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
 * Description: Code for cryptography
 */

function decRijndael($encrypted)
{
    $key = "";
    $iv = "";
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(str_replace("_","/",str_replace("-","+",$encrypted))), MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    return $toreturn;
}


$text_to_decrypt= $argv[1];
$decrypted_text=decRijndael($text_to_decrypt);
echo $decrypted_text . "\n";

?>
