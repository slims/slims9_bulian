<?php
/*
Heavily modified for SLiMS by Hendro Wicaksono (hendrowicaksono@yahoo.com)
(Senayan Library Management System), http://slims.web.id / http://senayan.diknas.go.id
It is derived from:
---------------------
MINIGAL NANO
- A PHP/HTML/CSS based image gallery script
This script and included files are subject to licensing from Creative Commons (http://creativecommons.org/licenses/by-sa/2.5/)
You may use, edit and redistribute this script, as long as you pay tribute to the original author by NOT removing the linkback to www.minigal.dk ("Powered by MiniGal Nano x.x.x")
MiniGal Nano is created by Thomas Rybak
Copyright 2010 by Thomas Rybak
Support: www.minigal.dk
Community: www.minigal.dk/forum
Please enjoy this free script!
USAGE EXAMPLE:
File: createthumb.php
Example: <img src="createthumb.php?filename=photo.jpg&amp;width=100&amp;height=100">
----------------------
Updated Example: $size is not used. Only width and height.
*/
//	error_reporting(E_ALL);

// Define variables
$target = "";
$xoord = 0;
$yoord = 0;
$default_res_width = 42;
$cache['enable'] = false;
$cache['folder'] = '../../caches/image/'; # try absolutely in case of error about safe mode in PHP
$cache['exist'] = false;
$cache['handle'] = '';
$imagefilename = urldecode(trim($_GET['filename']));

function genContentType($imagefilename)
{
  if (preg_match("/.jpg$|.jpeg$/i", $imagefilename)) header('Content-type: image/jpeg');
  if (preg_match("/.gif$/i", $imagefilename)) header('Content-type: image/gif');
  if (preg_match("/.png$/i", $imagefilename)) header('Content-type: image/png');
}

# Only accept JPG, PNG, GIF
if (!((preg_match("/.jpg$|.jpeg$/i", $imagefilename)) OR (preg_match("/.gif$/i", $imagefilename)) OR (preg_match("/.png$/i", $imagefilename)))) {
  header('Content-type: image/png');
  readfile('wrongcontenttype.png');
  exit;
}

// Display error image if file isn't found
if (!is_file($imagefilename)) {
  header('Content-type: image/png');
  readfile('filenotfound.png');
  exit;
}

// Display error image if file exists, but can't be opened
if (substr(decoct(fileperms($imagefilename)), -1, strlen(fileperms($imagefilename))) < 4 OR substr(decoct(fileperms($imagefilename)), -3,1) < 4) {
  header('Content-type: image/png');
  readfile('filecantbeopened.png');
  exit;
}

$imgsize = GetImageSize($imagefilename);
$width = $imgsize[0];
$height = $imgsize[1];

if ((isset($_GET['width'])) AND (trim($_GET['width']) != '')) {
  $res_width = $_GET['width'];
} else {
  $res_width = $default_res_width;
}

if ((isset($_GET['height'])) AND (trim($_GET['height']) != '')) {
  $res_height = $_GET['height'];
} else {
  $res_height = ($res_width/$width) * $height;
}

$cache['prefix'] = '_slims_img_cache_'.$res_width.'_x_'.$res_height.'_';
$cache['file'] = $cache['folder'].$cache['prefix'].basename($imagefilename);

if (file_exists($cache['file'])) {
  $cache['exist'] = true;
  genContentType($imagefilename);
  readfile($cache['file']);
  exit;
} else {
  $cache['exist'] = false;
}

genContentType($imagefilename);

$target = imagecreatetruecolor($res_width,$res_height);
if (preg_match("/.jpg$/i", $imagefilename)) $source = imagecreatefromjpeg($imagefilename);
if (preg_match("/.gif$/i", $imagefilename)) $source = imagecreatefromgif($imagefilename);
if (preg_match("/.png$/i", $imagefilename)) $source = imagecreatefrompng($imagefilename);
imagecopyresampled($target,$source,0,0,$xoord,$yoord,$res_width,$res_height,$width,$height);
imagedestroy($source);

if ($cache['exist'] == false) {
  if (preg_match("/.jpg$/i", $imagefilename)) {
    imagejpeg($target,null,90);
    imagejpeg($target,$cache['file'],90);
  }
  if (preg_match("/.gif$/i", $imagefilename)) {
    imagegif($target,null);
    imagegif($target,$cache['file']);
  }
  if (preg_match("/.png$/i", $imagefilename)) {
    imagepng($target,null,9);
    imagepng($target,$cache['file'],9);
  }
  imagedestroy($target);
}         
exit;
