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

include "/var/www/html/thefraudexplorer/lbs/openDBconn.php";

function encRijndael($unencrypted)
{
    global $connection;

    $result_key = mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $unencrypted, MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = base64_encode($toreturn);

    return rawurlencode($toreturn);
}

function decRijndael($encrypted)
{
    global $connection;
    $encrypted = rawurldecode($encrypted);

    $result_key = mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(str_replace("_","/",str_replace("-","+",$encrypted))), MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    
    return $toreturn;
}

function encRijndaelRemote($unencrypted)
{
    global $connection;

    $result_key = mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $unencrypted, MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = base64_encode($toreturn);

    return $toreturn;
}

function decRijndaelRemote($encrypted)
{
    global $connection;

    $result_key = mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(str_replace("_","/",str_replace("-","+",$encrypted))), MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    return $toreturn;
}

?>