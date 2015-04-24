<?php
/*

 * Image-Creator / Sample
 * Part of PHP-Barcode 0.3pl1

 * (C) 2001,2002,2003,2004 by Folke Ashberg <folke@ashberg.de>

 * The newest version can be found at http://www.ashberg.de/bar

 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.

 */

/*
 * call
 * http://localhost/barcode.php?code=012345678901
 *   or
 * http://localhost/barcode.php?code=012345678901&encoding=EAN&scale=4&mode=png
 *
 */

/*
 * Security Patch by Indra Sutriadi (http://sutriadi.web.id)
*/

define('INDEX_AUTH', '1');

if (!defined('SB')) {
  require '../../sysconfig.inc.php';
}

function scinfo()
{
  $host = $_SERVER['HTTP_HOST'];
  $path = $_SERVER['SCRIPT_NAME'];
  $dir = explode('/', $path);
  $file = $dir[count($dir)-1];
  unset($dir[count($dir)-1]);
  $dir = implode('/', $dir);
  return array($host, $dir, $file);
}

function checkref($mode = 'module') {
  $ref = false;
  if (isset($_SERVER['HTTP_REFERER'])) {
  	$ref_url = $_SERVER['HTTP_REFERER'];
  	$ref_part = (object) parse_url($ref_url);
  	
	$ref_host = isset($ref_part->host) ? $ref_part->host : '';
  	$ref_host .= isset($ref_part->port) ? ':' . $ref_part->port : '';
  	$ref_ip = isset($ref_part->host) ? gethostbyname($ref_host) : '';
  	$ref_path = isset($ref_part->path) ? $ref_part->path : '/';
  	$ref_dir = explode('/', $ref_path);
  	unset($ref_dir[count($ref_dir)-1]);
  	$ref_dir = implode('/', $ref_dir);
  	$ref_admin = $ref_host . $ref_dir;
  	$ref_q = isset($ref_part->query) ? $ref_part->query : '';
  	$ref_req = $ref_admin . '?' . $ref_q;
  
  	list($dest_host, $dest_dir, $dest_file) = scinfo();
  	$dest_path = $_SERVER['SCRIPT_NAME'];
  	$dest_ip = gethostbyname($dest_host);
  	$dest_dir = explode('/', SWB);
  	unset($dest_dir[count($dest_dir)-3]);
  	unset($dest_dir[count($dest_dir)-2]);
  	unset($dest_dir[count($dest_dir)-1]);
  	$dest_dir = implode('/', $dest_dir);
  	$dest_admin = $dest_host . $dest_dir . 'admin';
  	$dest_plugin = $dest_admin . '/modules/plugins';
  	$dest_q = 'mod=plugins';
  	$dest_req = $dest_admin . '?' . $dest_q;
  	switch ($mode)
  	{
  		case "host":
  			if ($ref_host == $dest_host)
  				$ref = true;
  			break;
  		case "ip":
  			if ($ref_ip == $dest_ip)
  				$ref = true;
  			break;
  		case "admin":
  			$is_admin = explode($dest_admin, $ref_admin);
  			if (empty($is_admin[0]))
  				$ref = true;
  			break;
  		case "module":
  		default:
  			if ($ref_req == $dest_req)
  				$ref = true;
  	}
  	if ($ref_path == $dest_path)
  		$ref = true;
  }
  if ($ref !== true)
    die(sprintf('<div>%s %s!</div>', $ref_admin, $dest_admin));
  else
    return;
}

// checkref('admin');
$get = (object)$_GET;
$allowed_scale = array(1, 2, 3, 4, 5, 6);
if ( ! isset($get->scale) OR (isset($get->scale) AND ! in_array($get->scale, $allowed_scale)))
	$get->scale = 2;

// http vars
$code = isset($get->code) ? trim($get->code) : '1234567890';
if (get_magic_quotes_gpc())
  $code=stripslashes($code);

$encoding = isset($get->encoding) ? trim($get->encoding) : 'code128';
$scale = isset($get->scale) ? trim($get->scale) : '2';
$mode = isset($get->mode) ? trim($get->mode) : 'png';

// output the barcode
if ($sysconf['zend_barcode_engine'] === true) {
  // include Zend_Barcode library
  ini_set('include_path', LIB);
  require_once LIB . 'Zend/Barcode.php';
  
  $act = isset($get->act) ? trim($get->act) : 'save';
  $output = isset($get->output) ? trim($get->output) : 'image';
  $ext = $output == 'image' ? $mode : 'pdf';
  
  $file_name = '../../images/barcodes/' . $code . '.' . $ext;
  
  $options = array('text' => $code);
  //$options['barHeight'] = 50;
  //$options['barThickWidth'] = 3;
  //$options['barThinWidth'] = 1;
  $options['factor'] = $scale;
  //$options['foreColor'] = "#000000";
  //$options['backgroundColor'] = "#FFFFFF";
  //$options['reverseColor'] = FALSE;
  // $options['orientation'] = 0;
  $options['font'] = "./DejaVuSans.ttf";
  $options['fontSize'] = 8;
  // $options['withBorder'] = TRUE;
  // $options['withQuietZones'] = TRUE;
  //$options['drawText'] = TRUE;
  //$options['stretchText'] = FALSE;
  //$options['withChecksum'] = FALSE;
  //$options['withChecksumInText'] = FALSE;
  
  // output the barcode
  $renderer = Zend_Barcode:: factory(
      $encoding, $output, $options, array()
  );
  if ($act == 'save') {
    call_user_func('image'.$mode, $renderer->draw(), $file_name);
  } else {
    $renderer->render();
  }
}
exit;
