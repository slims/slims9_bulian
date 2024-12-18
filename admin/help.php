<?php
/**
 * Copyright (C) 2014  Eddy Subratha (eddy.subratha@slims.web.id)
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

// key to authenticate
define('INDEX_AUTH', '1');

// required file
require '../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
// start the session
require SB.'admin/default/session.inc.php';
// session checking
require SB.'admin/default/session_check.inc.php';
// markdown library
require LIB.'parsedown/Parsedown.php';

//Load Markdown File  
$parsedown = new Parsedown();


if(isset($_GET['url']) && !empty($_GET['url'])) {		
	$file_path = HELP.'/'.$sysconf['default_lang'].'/'.$_GET['url'];
	if(!file_exists($file_path)|| !preg_match("/^.*\.(md)$/i", $file_path)) {
		echo '<p>'.__('Sorry, help content is not available yet.').'</p>';
	} else {
		//Convert Markdown to HTML
		$markdown_text = file_get_contents($file_path); //bibliography/add-new-bibliography.md		
		echo Parsedown::instance()->setBreaksEnabled(true)->text($markdown_text); 
	}
} else {
		echo __('Cannot Access This File Directly');	
		exit;
}
  
?>
