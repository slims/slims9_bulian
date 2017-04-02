<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
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

/* Item Import section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// max chars in line for file operations
$max_chars = 1024*100;

if (isset($_POST['doImport'])) {
    // check for form validity
    if (!$_FILES['importFile']['name']) {
        utility::jsAlert(__('Please select the file to import!'));
        exit();
    } else if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
        utility::jsAlert(__('Required fields (*)  must be filled correctly!'));
        exit();
    } else {
        $start_time = time();
        // set PHP time limit
        set_time_limit(0);
        // set ob implicit flush
        ob_implicit_flush();
        // create upload object
        $upload = new simbio_file_upload();
        // get system temporary directory location
        $temp_dir = sys_get_temp_dir();
        $uploaded_file = $temp_dir.DS.$_FILES['importFile']['name'];
        unlink($uploaded_file);
        // set max size
        $max_size = $sysconf['max_upload']*1024;
        $upload->setAllowableFormat(array('.csv'));
        $upload->setMaxSize($max_size);
        $upload->setUploadDir($temp_dir);
        $upload_status = $upload->doUpload('importFile');
        if ($upload_status != UPLOAD_SUCCESS) {
            utility::jsAlert(__('Upload failed! File type not allowed or the size is more than').($sysconf['max_upload']/1024).' MB'); //mfc
            exit();
        }
        // uploaded file path
        $uploaded_file = $temp_dir.DS.$_FILES['importFile']['name'];
        $row_count = 0;
        // check for import setting
        $record_num = intval($_POST['recordNum']);
        $field_enc = trim($_POST['fieldEnc']);
        $field_sep = trim($_POST['fieldSep']);
        $record_offset = intval($_POST['recordOffset']);
        $record_offset = $record_offset-1;
        // get current datetime
        $curr_datetime = date('Y-m-d H:i:s');
        $curr_datetime = '\''.$curr_datetime.'\'';
        // foreign key id cache
        $ct_id_cache = array();
        $loc_id_cache = array();
        $stat_id_cache = array();
        $spl_id_cache = array();
        // read file line by line
        $updated_row = 0;
        $file = fopen($uploaded_file, 'r');
        while (!feof($file)) {
            // record count
            if ($record_num > 0 AND $row_count == $record_num) {
                break;
            }
            // go to offset
            if ($row_count < $record_offset) {
                // pass and continue to next loop
                $field = fgetcsv($file, 1024, $field_sep, $field_enc);
                $row_count++;
                continue;
            } else {
                // get an array of field
                $field = fgetcsv($file, $max_chars, $field_sep, $field_enc);
                if ($field) {
                    // strip escape chars from all fields
                    foreach ($field as $idx => $value) {
                        $field[$idx] = str_replace('\\', '', trim($value));
                        $field[$idx] = $dbs->escape_string($field[$idx]);
                    }
                    // strip leading field encloser if any
                    $item_code = '\''.$field[0].'\'';
                    $call_number = $field[1]?'\''.$field[1].'\'':'NULL';
                    $coll_type = (integer)utility::getID($dbs, 'mst_coll_type', 'coll_type_id', 'coll_type_name', $field[2], $ct_id_cache);
                    $inventory_code = $field[3]?'\''.$field[3].'\'':'NULL';
                    $received_date = $field[4]?'\''.$field[4].'\'':'NULL';
                    $supplier = (integer)utility::getID($dbs, 'mst_supplier', 'supplier_id', 'supplier_name', $field[5], $spl_id_cache);
                    $order_no = $field[6]?'\''.$field[6].'\'':'NULL';
                    $location = utility::getID($dbs, 'mst_location', 'location_id', 'location_name', $field[7], $loc_id_cache);
                    $location = $location?'\''.$location.'\'':'NULL';
                    $order_date = $field[8]?'\''.$field[8].'\'':'NULL';
                    $item_status = utility::getID($dbs, 'mst_item_status', 'item_status_id', 'item_status_name', $field[9], $stat_id_cache);
                    $item_status = $item_status?'\''.$item_status.'\'':'NULL';
                    $site = $field[10]?'\''.$field[10].'\'':'NULL';
                    $source = $field[11]?'\''.$field[11].'\'':'NULL';
                    $invoice = $field[12]?'\''.$field[12].'\'':'NULL';
                    $price = $field[13]?'\''.$field[13].'\'':'NULL';
                    $price_currency = $field[14]?'\''.$field[14].'\'':'NULL';
                    $invoice_date = $field[15]?'\''.$field[15].'\'':'NULL';
                    $input_date = '\''.$field[16].'\'';
                    $last_update = '\''.$field[17].'\'';

                    // sql insert string
                    $sql_str = "INSERT INTO item (item_code, call_number, coll_type_id,
                        inventory_code, received_date, supplier_id,
                        order_no, location_id, order_date, item_status_id, site,
                        source, invoice, price, price_currency, invoice_date,
                        input_date, last_update)
                            VALUES ($item_code, $call_number, $coll_type,
                            $inventory_code, $received_date, $supplier,
                            $order_no, $location, $order_date, $item_status, $site,
                            $source, $invoice, $price, $price_currency, $invoice_date,
                            $input_date, $last_update)";

                    // send query
                    // die($sql_str);
                    $dbs->query($sql_str);
                    // case duplicate do update
                    if ($dbs->errno && $dbs->errno == 1062) {
                        $sql_str = "UPDATE item SET call_number=$call_number, coll_type_id=$coll_type,
                                inventory_code=$inventory_code, received_date=$received_date, supplier_id=$supplier,
                                order_no=$order_no, location_id=$location, order_date=$order_date, item_status_id=$item_status, site=$site,
                                source=$source, invoice=$invoice, price=$price, price_currency=$price_currency, invoice_date=$invoice_date,
                                input_date=$input_date, last_update=$last_update WHERE item_code LIKE $item_code";
                        // update data
                        $dbs->query($sql_str);
                        if ($dbs->affected_rows > 0) { $updated_row++; }
                    } else {
                            if ($dbs->affected_rows > 0) { $updated_row++; }
                    }
                }
                $row_count++;
            }
        }
        // close file handle
        fclose($file);
        $end_time = time();
        $import_time_sec = $end_time-$start_time;
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'Importing '.$updated_row.' item records from file : '.$_FILES['importFile']['name']);
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#importInfo\').html(\'<strong>'.$updated_row.'</strong> records updated successfully to item database, from record <strong>'.$_POST['recordOffset'].' in '.$import_time_sec.' second(s)</strong>\');'."\n";
        echo 'parent.$(\'#importInfo\').css( {\'display\': \'block\'} );'."\n";
        echo '</script>';
        exit();
    }
}
?>
<fieldset class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    	<h2><?php echo __('Item Import tool'); ?></h2>
	</div>
	<div class="infoBox">
    <?php echo __('Import for item data from CSV file'); ?>
	</div>
</div>
</fieldset>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Import Now').'" class="btn btn-default"';
// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input = simbio_form_element::textField('file', 'importFile');
$str_input .= ' Maximum '.$sysconf['max_upload'].' KB';
$form->addAnything(__('File To Import'), $str_input);
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" maxlength="3"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of Records To Import (0 for all records)'), '0', 'style="width: 10%;"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;"');
// output the form
echo $form->printOut();
