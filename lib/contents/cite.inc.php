<?php
/**
*
* Citation page
* Copyright (C) 2015 Arie Nugraha (dicarve@yahoo.com)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*
*/

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

$cite_style = 'apa';
$biblio_id = 0;
if (isset($_GET['id'])) {
  $biblio_id = (integer)$_GET['id'];
}
/*
if (isset($_GET['style'])) {
  $cite_style = strtolower(trim($_GET['style']));
}
$cite_style_template = SB.'template'.DS.'citation'.DS.$cite_style.'_style_template.php';
if (!file_exists($cite_style_template)) {
  exit(__('Sorry, no cite template available.'));
}
*/
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');


require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
$dir = new simbio_directory(SB.'template'.DS.$sysconf['template']['theme'].DS.'citation');
$style_files = $dir->getFileList();

// create Biblio
$biblio = new Biblio($dbs, $biblio_id);
$biblio_detail = $biblio->detail();
// var_dump($biblio_detail);
extract($biblio_detail);

// foreach ($style_files as $file) {
//   if (false === stripos($file, '_style_template.php')) {
//     continue;
//   } else {
    include_once SB.'template'.DS.$sysconf['template']['theme'].DS.'citation'.DS.'apa_style_template.php'; 
    include_once SB.'template'.DS.$sysconf['template']['theme'].DS.'citation'.DS.'mla_style_template.php'; 
//   }
// }

// main content
$main_content = ob_get_clean();
// page title
echo '<link href="' . SWB . 'css/bootstrap.min.css" rel="stylesheet"/>';
echo '<link href="' . SWB . 'template/default/assets/plugin/font-awesome/css/fontawesome-all.min.css" rel="stylesheet"/>';
echo '<div class="mx-3">';
echo $main_content;
echo '</div>';
echo '<p class="spacer">&nbsp;</p>';
exit();
