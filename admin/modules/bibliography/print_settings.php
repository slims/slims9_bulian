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

//Modified by Eddy Subratha
//Measure for each configuration

// label print settings
/* measurement in cm */
$measure['print']['label']['page_margin']         = __('(px)');
$measure['print']['label']['items_per_row']       = __('(default is 3)');
$measure['print']['label']['items_margin']        = __('(cm)');
$measure['print']['label']['box_width']           = __('(cm)');
$measure['print']['label']['box_height']          = __('(cm)');
$measure['print']['label']['include_header_text'] = __('(0=No or 1=Yes)'); 
$measure['print']['label']['header_text']         = __('(empty if you want to use Library Name)');
$measure['print']['label']['fonts']               = __('(name of the font used)');
$measure['print']['label']['font_size']           = __('(pt)');
$measure['print']['label']['border_size']         = __('(px)');

// item barcode print settings
/* measurement in cm */
$measure['print']['barcode']['barcode_page_margin']         = __('(cm)');
$measure['print']['barcode']['barcode_items_per_row']       = __('(cm)');
$measure['print']['barcode']['barcode_items_margin']        = __('(cm)');
$measure['print']['barcode']['barcode_box_width']           = __('(cm)');
$measure['print']['barcode']['barcode_box_height']          = __('(cm)');
$measure['print']['barcode']['barcode_include_header_text'] = __('(0=No or 1=Yes)'); 
$measure['print']['barcode']['barcode_cut_title']           = __('(0=No or 1=Yes)');
$measure['print']['barcode']['barcode_header_text']         = __('(empty if you want to use Library Name)');
$measure['print']['barcode']['barcode_fonts']               = __('(name of the font used)');
$measure['print']['barcode']['barcode_font_size']           = __('(pt)');
$measure['print']['barcode']['barcode_scale']               = __('(percent relative to box width and height)');
$measure['print']['barcode']['barcode_border_size']         = __('(px)');

// barcode generator print settings
$measure['print']['barcodegen']['box_width']                = __('(cm)');
$measure['print']['barcodegen']['page_margin']              = __('(decimal value. default is 0.2)');
$measure['print']['barcodegen']['items_margin']             = __('(decimal value. default is 0.05)');
$measure['print']['barcodegen']['include_border']           = __('(0=No or 1=Yes)');
$measure['print']['barcodegen']['items_per_row']            = __('(default is 3)');

/* Receipt Printing */
$measure['print']['receipt']['receipt_width']               = __('(cm)');
$measure['print']['receipt']['receipt_font']                = __('font name. default is Courier');
$measure['print']['receipt']['receipt_color']               = __('(hexa color code)');
$measure['print']['receipt']['receipt_margin']              = __('px');
$measure['print']['receipt']['receipt_padding']             = __('px');
$measure['print']['receipt']['receipt_border']              = __('px style hexa');
$measure['print']['receipt']['receipt_fontSize']            = __('pt');
$measure['print']['receipt']['receipt_header_fontSize']     = __('pt');
$measure['print']['receipt']['receipt_titleLength']         = __('(number)');

// member card print settings
/* measurement in cm */
$measure['print']['membercard']['page_margin']              = __('(decimal)');
$measure['print']['membercard']['items_margin']             = __('(decimal)');
$measure['print']['membercard']['items_per_row']            = __('(number)'); //

/* measurement in cm*/
$measure['print']['membercard']['factor']                   = __('(cm)');

// Items Settings
// change to 0 if dont want to use selected items
$measure['print']['membercard']['include_id_label']         = __('(0=No or 1=Yes)'); // id 
$measure['print']['membercard']['include_name_label']       = __('(0=No or 1=Yes)'); // name
$measure['print']['membercard']['include_pin_label']        = __('(0=No or 1=Yes)'); // identify
$measure['print']['membercard']['include_inst_label']       = __('(0=No or 1=Yes)'); // institution
$measure['print']['membercard']['include_email_label']      = __('(0=No or 1=Yes)'); // mail address
$measure['print']['membercard']['include_address_label']    = __('(0=No or 1=Yes)'); // home or office address
$measure['print']['membercard']['include_barcode_label']    = __('(0=No or 1=Yes)'); // barcode
$measure['print']['membercard']['include_expired_label']    = __('(0=No or 1=Yes)'); // expired date

// Cardbox Settings
$measure['print']['membercard']['box_width']                = __('(cm)');
$measure['print']['membercard']['box_height']               = __('(cm)');
$measure['print']['membercard']['front_side_image']         = __('(filename)');
$measure['print']['membercard']['back_side_image']          = __('(filename)');

// Logo Setting
$measure['print']['membercard']['logo']                     = __('(filename)');
$measure['print']['membercard']['front_logo_width']         = __('(px)');
$measure['print']['membercard']['front_logo_height']        = __('(px)');
$measure['print']['membercard']['front_logo_left']          = __('(px)');
$measure['print']['membercard']['front_logo_top']           = __('(px)');
$measure['print']['membercard']['back_logo_width']          = __('(px)');
$measure['print']['membercard']['back_logo_height']         = __('(px)');
$measure['print']['membercard']['back_logo_left']           = __('(px)');
$measure['print']['membercard']['back_logo_top']            = __('(px)');

// Photo Settings
$measure['print']['membercard']['photo_left']               = __('(px)');
$measure['print']['membercard']['photo_top']                = __('(px)');
$measure['print']['membercard']['photo_width']              = __('(cm)');
$measure['print']['membercard']['photo_height']             = __('(cm)');

// Header Settings
$measure['print']['membercard']['front_header1_text']       = __('(text - add <br> tag for line break)'); // use <br /> tag to make another line
$measure['print']['membercard']['front_header1_font_size']  = __('(pt)');
$measure['print']['membercard']['front_header2_text']       = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['front_header2_font_size']  = __('(pt)');
$measure['print']['membercard']['back_header1_text']        = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['back_header1_font_size']   = __('(pt)');
$measure['print']['membercard']['back_header2_text']        = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['back_header2_font_size']   = __('(pt)');
$measure['print']['membercard']['header_color']             = __('(hexa color)'); //e.g. :#0066FF, green, etc.

//biodata settings
$measure['print']['membercard']['bio_font_size']            = __('(pt)');
$measure['print']['membercard']['bio_font_weight']          = __('(normal or bold)');
$measure['print']['membercard']['bio_label_width']          = __('(px)');

// Stamp Settings
$measure['print']['membercard']['city']                     = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['title']                    = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['officials']                = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['officials_id']             = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['stamp_file']               = __('(filename)'); // stamp image, use transparent image
$measure['print']['membercard']['signature_file']           = __('(filename)'); // sign picture, use transparent image
$measure['print']['membercard']['stamp_left']               = __('(px)');
$measure['print']['membercard']['stamp_top']                = __('(px)');
$measure['print']['membercard']['stamp_width']              = __('(px)');
$measure['print']['membercard']['stamp_height']             = __('(px)');

//expired
$measure['print']['membercard']['exp_left']                 = __('(px)');
$measure['print']['membercard']['exp_top']                  = __('(px)');
$measure['print']['membercard']['exp_width']                = __('(px)');
$measure['print']['membercard']['exp_height']               = __('(px)');

// Barcode Setting
$measure['print']['membercard']['barcode_scale']            = __('1-100 percent'); // barcode scale in percent relative to box width and height
$measure['print']['membercard']['barcode_left']             = __('(px)');
$measure['print']['membercard']['barcode_top']              = __('(px)');
$measure['print']['membercard']['barcode_width']            = __('(px)');
$measure['print']['membercard']['barcode_height']           = __('(px)');

// Rules
$measure['print']['membercard']['rules']                    = __('(list of rules)');  
$measure['print']['membercard']['rules_font_size']          = __('(pt)');

// address
$measure['print']['membercard']['address']                  = __('(text - add <br> tag for line break)');
$measure['print']['membercard']['address_font_size']        = __('(pt)');
$measure['print']['membercard']['address_left']             = __('(px)');
$measure['print']['membercard']['address_top']              = __('(px)');

$form->addAnything(__('Print setting for'), ucwords($type));
foreach ($sysconf['print'][$type] as $setting_name => $val) {
  $setting_name_label = ucwords(str_ireplace('_', ' ', $setting_name));
  $form->addTextField('text', $type.'['.$setting_name.']', __($setting_name_label).'<br/><small><em>'.$measure['print'][$type][$setting_name].'</em></small>', $val, 'style="width: 75%;"');
}
$form->addHidden('settingType', $type);

// print out the object
echo $form->printOut();
/* main content end */
