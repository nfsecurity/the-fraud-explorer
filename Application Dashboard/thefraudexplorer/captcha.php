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
 * Description: Code for captcha on login 
 */

$width=100;
$height=55;

$image=imagecreatetruecolor($width,$height);
$black=imagecolorallocate($image,0,0,0);
$gray=imagecolorallocate($image,100,100,100);
$rgb[0] = rand(0,255);
$rgb[1] = rand(0,255);
$rgb[2] = rand(0,255);
$RandomColor=imagecolorallocate($image,$rgb[0],$rgb[1],$rgb[2]);
$RandomColorInverted=imagecolorallocate($image,255-$rgb[0],255-$rgb[1],255-$rgb[2]);

imagefill($image,0,0,$RandomColor);

imageline($image,0,0,$width,0,$black);
imageline($image,0,0,0,$height,$black);
imageline($image,$width-1,$height-1,0,$height-1,$black);
imageline($image,$width-1,$height-1,$width-1,0,$black);

imageline($image,25,0,25,$height,$gray);
imageline($image,50,0,50,$height,$gray);
imageline($image,75,0,75,$height,$gray);
imageline($image,0,13,$width,13,$gray);
imageline($image,0,26,$width,26,$gray);
imageline($image,0,39,$width,39,$gray);

$random=substr(str_replace("0","",str_replace("O","",strtoupper(md5(rand(9999,99999))))),0,5);

$ttf = "fonts/gunplay.ttf";
imagefttext($image,22,rand(-10,15),12,37,$RandomColorInverted,$ttf,$random);

/* Insert in database current captcha value */

error_reporting(0);
include "lbs/open-db-connection.php";
$result=mysql_query("DELETE from t_captcha",$connection);
$result=mysql_query("INSERT INTO t_captcha (captcha) VALUES ('" . $random . "')",$connection);
include "lbs/close-db-connection.php";

for ($i=0;$i<=700;$i++)
{
    $randx=rand(0,100);
    $randy=rand(0,55);
    imagesetpixel($image,$randx,$randy,$RandomColorInverted);
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

?>