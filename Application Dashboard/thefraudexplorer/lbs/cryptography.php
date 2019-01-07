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
 * Description: Code for cryptography
 */

include "/var/www/html/thefraudexplorer/lbs/openDBconn.php";

function encRijndael($unencrypted)
{
    global $connection;

    $result_key=mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $unencrypted, MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = base64_encode($toreturn);
    return $toreturn;
}

function decRijndael($encrypted)
{
    global $connection;

    $result_key=mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(str_replace("_","/",str_replace("-","+",$encrypted))), MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    return $toreturn;
}

function decRijndaelWOSC($encrypted)
{
    global $connection;

    $result_key=mysqli_query($connection, "SELECT * FROM t_crypt");
    $row_key = mysqli_fetch_array($result_key);
    $key = $row_key[0];
    $iv = $row_key[1];
    $iv_utf = mb_convert_encoding($iv, 'UTF-8');
    $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv_utf);
    $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    return $toreturn;
}

?>