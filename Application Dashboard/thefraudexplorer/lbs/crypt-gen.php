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
 * Date: 2017-06
 * Revision: v1.0.1-beta
 *
 * Description: Code for cryptography
 */

function encRijndael($text)
{
    $key = "";
    $iv = "";

    $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $padding = $block - (strlen($text) % $block);
    $text .= str_repeat(chr($padding), $padding);
    $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $text, MCRYPT_MODE_CBC, $iv);

    return base64_encode($crypttext);
}

$text_to_encrypt="";
$encrypted_text=encRijndael($text_to_encrypt);
echo $encrypted_text;
echo "";

?>
