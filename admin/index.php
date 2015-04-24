<?php
/**
 * SENAYAN admin application bootstrap files
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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
require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
require LIB.'module.inc.php';

// https connection (if enabled)
if ($sysconf['https_enable']) {
    simbio_security::doCheckHttps($sysconf['https_port']);
}

// page title
$page_title = $sysconf['library_name'].' :: Library Automation System';
// main menu
$module = new module();
$module->setModulesDir(MDLBS);
$main_menu = $module->generateModuleMenu($dbs);

$current_module = '';
// get module from URL
if (isset($_GET['mod']) AND !empty($_GET['mod'])) {
  $current_module = trim($_GET['mod']);
}
// read privileges
$can_read = utility::havePrivilege($current_module, 'r');

// submenu
$sub_menu = $module->generateSubMenu(($current_module AND $can_read)?$current_module:'');

// start the output buffering for main content
ob_start();
// info
$info = __('You are currently logged in as').' <strong>'.$_SESSION['realname'].'</strong>'; //mfc

if ($current_module AND $can_read) {
    // get content of module default content with AJAX
    $sysconf['page_footer'] .= "\n"
        .'<script type="text/javascript">'
        .'jQuery(document).ready(function() { jQuery(\'#mainContent\').simbioAJAX(\''.MWB.$current_module.'/index.php\', {method: \'get\'}); });'
        .'</script>';
} else {
    include 'default/home.php';
    // for debugs purpose only
    // include 'modules/bibliography/index.php';
}
// page content
$main_content = ob_get_clean();

// print out the template
require $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/index_template.inc.php';