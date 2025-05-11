<?php
/**
 * SENAYAN admin application bootstrap files
 *
 * Copyright (C) Ari Nugraha (dicarve@gmail.com)
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
#use SLiMS\AdvancedLogging;
use SLiMS\AlLibrarian;
use SLiMS\DB;

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

/**
 * Just info for who want to
 * upgrade SLiMS if didn't have access
 * to last slims source code.
 */
if ($_SESSION['uid'] == 1 && config('init_info') === null) {
    DB::query('insert into `setting` set `setting_name` = ?, `setting_value` = ?', [
        'init_info',
        serialize([
            'version' => SENAYAN_VERSION,
            'tag' => SENAYAN_VERSION_TAG,
            'admin_url' => dirname($_SERVER['PHP_SELF'])
        ])
    ])->run();
}

// page title
$page_title = $sysconf['library_name'].' | '.__('Senayan Library Management System');
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

// get default current module menu 
$firstMenu = $module->getFirstMenu($current_module);
if ($current_module AND $can_read) {
    if (!isset($firstMenu[1]))
    {
        // set unprivileged module warning
        $module->unprivileged();
    }
    else
    {
        # ADV LOG SYSTEM - STIIL EXPERIMENTAL
        $log = new AlLibrarian('1101', array("username" => $_SESSION['uname'], "uid" => $_SESSION['uid'], "realname" => $_SESSION['realname'], "module" => $current_module));
        // get content of module default content with AJAX
        $defaultUrl = $firstMenu[1];
        $sysconf['page_footer'] .= "\n"
            .'<script type="text/javascript">'
            .'jQuery(document).ready(function() { jQuery(\'#mainContent\').simbioAJAX(\''.$defaultUrl.'\', {method: \'get\'}); });'
            .'</script>';
    }
} else {
    include 'default/home.php';
    // for debugs purpose only
    // include 'modules/bibliography/index.php';
}
// page content
$main_content = ob_get_clean();

utility::loadUserTemplate($dbs,$_SESSION['uid']);

// print out the template
require $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/index_template.inc.php';