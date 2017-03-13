<?php

/*
 * The Fraud Explorer
 * http://www.thefraudexplorer.com/
 *
 * Copyright (c) 2017 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * http://www.thefraudexplorer.com/License
 *
 * Date: 2017-04
 * Revision: v0.9.9-beta
 *
 * Description: Code for cryptography
 */

include "/var/www/html/thefraudexplorer/lbs/open-db-connection.php";

function encRijndael($unencrypted)
{
 $result_key=mysql_query("SELECT * FROM t_crypt");
 $row_key = mysql_fetch_array($result_key);
 $key = $row_key[0];
 $iv = $row_key[1];
 $iv_utf = mb_convert_encoding($iv, 'UTF-8');
 $toreturn = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $unencrypted, MCRYPT_MODE_CBC, $iv_utf);
 $toreturn = base64_encode($toreturn);
 return $toreturn;
}

function decRijndael($encrypted)
{
 $result_key=mysql_query("SELECT * FROM t_crypt");
 $row_key = mysql_fetch_array($result_key);
 $key = $row_key[0];
 $iv = $row_key[1];
 $iv_utf = mb_convert_encoding($iv, 'UTF-8');
 $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode(str_replace("_","/",str_replace("-","+",$encrypted))), MCRYPT_MODE_CBC, $iv_utf);
 $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
 return $toreturn;
}

function decRijndaelWOSC($encrypted)
{
 $result_key=mysql_query("SELECT * FROM t_crypt");
 $row_key = mysql_fetch_array($result_key);
 $key = $row_key[0];
 $iv = $row_key[1];
 $iv_utf = mb_convert_encoding($iv, 'UTF-8');
 $toreturn = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($encrypted), MCRYPT_MODE_CBC, $iv_utf);
 $toreturn = filter_var($toreturn, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
 return $toreturn;
}

?>
