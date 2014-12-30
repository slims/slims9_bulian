<?php
/**
 * PhpThumb Library Example File
 *
 * This file contains example usage for the PHP Thumb Library
 *
 * PHP Version 5 with GD 2.0+
 * PhpThumb : PHP Thumb Library <http://phpthumb.gxdlabs.com>
 * Copyright (c) 2009, Ian Selby/Gen X Design
 *
 * Author(s): Ian Selby <ian@gen-x-design.com>
 *
 * Licensed under the MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Ian Selby <ian@gen-x-design.com>
 * @copyright Copyright (c) 2009 Gen X Design
 * @link http://phpthumb.gxdlabs.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 3.0
 * @package PhpThumb
 * @subpackage Examples
 * @filesource
 *
 * modified for SLiMS
 * by Indra Sutriadi Pipii <indra.sutriadi@gmail.com>
 * - three resize picture modes
 * - two crop picture modes
 * - reflection filter
 *
 */

require_once './ThumbLib.inc.php';

// common variable
$src = isset($_GET['src']) ? $_GET['src'] : './examples/test.jpg'; // image source
list($w, $h, $type, $attr) = getimagesize($src);
$action = isset($_GET['action']) ? $_GET['action'] : 'resize'; // default action is resize

// variable for resize function
$w = isset($_GET['w']) ? $_GET['w'] : $w; // width
$h = isset($_GET['h']) ? $_GET['h'] : $h; // height
$p = isset($_GET['p']) ? $_GET['p'] : 100; // percentage
$rmode = isset($_GET['rmode']) ? $_GET['rmode'] : false; // resize mode

// variable for crop function
$x0 = isset($_GET['x0']) ? $_GET['x0'] : 0; // axis coordinat begin
$x1 = isset($_GET['x1']) ? $_GET['x1'] : $w; // axis coordinat end
$y0 = isset($_GET['y0']) ? $_GET['y0'] : 0; // ordinat coordinat begin
$y1 = isset($_GET['y1']) ? $_GET['y1'] : $h; // ordinat coordinat end
$cmode = isset($_GET['cmode']) ? $_GET['cmode'] : false; // crop mode

// variable for rotate function
// temporary not implemented yet

// additional function
$reflection = ! isset($_GET['reflection']) ? false : true;
$rp = 20;

$thumb = PhpThumbFactory::create($src);
if ($action == 'resize')
{
	if ($rmode === false)
		$thumb->resize($w, $h);
	else if ($rmode === 'adaptive')
		$thumb->adaptiveResize($w, $h);
	else if ($rmode === 'percentage')
		$thumb->resizePercent($p);
}
else if ($action == 'crop')
{
	if ($cmode === false)
		$thumb->crop($x0, $y0, $x1, $y1);
	else if ($cmode === 'center')
		$thumb->cropFromCenter($x1-$x0, $y1-$y0);
}

if ($reflection !== false)
	$thumb->createReflection(50, 50, 80, true, '#a4a4a4');

$thumb->show();
