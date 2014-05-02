<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

/* Global application configuration */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
  // main system configuration
  require '../../../sysconfig.inc.php';
  // start the session
  require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

if (isset($_POST['updateSettings'])) {
    $setting_type = trim($_POST['settingType']);
    $setting_name = $setting_type.'_print_settings';
    // reset
    $dbs->query(sprintf("REPLACE INTO setting (setting_name, setting_value) VALUES ('%s', '%s')",
      $setting_name, $dbs->escape_string(serialize($_POST[$setting_type]))));
    // write log
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' change '.$setting_type.' print settings');
    utility::jsAlert(__('Settings saved'));
    echo '<script type="text/javascript"></script>';
}
/* Config Vars update process end */

$type = 'barcode';
if (isset($_GET['type'])) {
  $type = trim($_GET['type']);
}

if (!in_array($type, array('barcode', 'label', 'membercard'))) {
  $type = 'barcode';
}

// include printed settings configuration file
include SB.'admin'.DS.'admin_template'.DS.'printed_settings.inc.php';
// check for custom template settings
$custom_settings = SB.'admin'.DS.$sysconf['admin_template']['dir'].DS.$sysconf['template']['theme'].DS.'printed_settings.inc.php';
if (file_exists($custom_settings)) {
  include $custom_settings;
}

// create form instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="updateSettings" value="'.__('Save Settings').'" class="btn btn-primary"';

// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

// load print settings from database
loadPrintSettings($dbs, $type);

$form->addAnything(__('Print setting for'), ucwords($type));
foreach ($sysconf['print'][$type] as $setting_name => $val) {
  $setting_name_label = ucwords(str_ireplace('_', ' ', $setting_name));
  $form->addTextField('text', $type.'['.$setting_name.']', __($setting_name_label), $val, 'style="width: 90%;"');
}
$form->addHidden('settingType', $type);

// print out the object
echo $form->printOut();
/* main content end */
