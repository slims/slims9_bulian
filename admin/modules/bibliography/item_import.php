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
use SLiMS\Filesystems\Storage;
use SLiMS\Csv\Writer;
use SLiMS\Csv\Row;

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
require __DIR__ . '/biblio_utils.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// Redirect content
if (isset($_SESSION['csv']['name']) && !isset($_POST['process'])) redirect()->simbioAJAX(MWB . 'bibliography/import_preview.php');

if (isset($_GET['action']) && $_GET['action'] === 'download_sample')
{
  // Create Csv instance
  $csv = new Writer;
  $csv->add(new Row([
    'item_code','call_number','coll_type_name','inventory_code',
    'received_date','supplier_name','order_no','location_name',
    'order_date','item_status_name','site','source','invoice',
    'price','price_currency','invoice_date','input_date','last_update','title'
  ]));

  // Download CSV
  $csv->download('biblio_item_sample_import');
}

// max chars in line for file operations
$max_chars = 1024*100;

if (isset($_POST['doImport'])) {
     // create upload object
    $files_disk = Storage::files();

    
    // check for form validity
    if (!isset($_POST['process'])) {
        if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
            utility::jsToastr('Item Import', __('Required fields (*)  must be filled correctly!'), 'error');
            exit;
        }

        // get system temporary directory location
        $_SESSION['csv'] = [];
        $_SESSION['csv']['name'] = md5($_FILES['importFile']['name'] . date('this'));
        
        if (!$files_disk->isExists('temp')) $files_disk->makeDirectory('temp');

        if ($files_disk->isExists('temp' . DS . $_SESSION['csv']['name'])) {
        $files_disk->delete('temp' . DS . $_SESSION['csv']['name']);
        }
        
        // set csv format
        $_SESSION['csv']['format'] = [
        'recordNum' => intval($_POST['recordNum']),
        'fieldEnc' => trim($_POST['fieldEnc']),
        'fieldSep' => trim($_POST['fieldSep']),
        'recordOffset' => intval($_POST['recordOffset'])
        ];

        $_SESSION['csv']['section'] = 'item';
        $_SESSION['csv']['action'] = $_SERVER['PHP_SELF'];

        // create upload object
        $csv_upload = $files_disk->upload('importFile', function($files) use($sysconf) {
        // Extension check
        $files->isExtensionAllowed(['.csv']);

        // File size check
        $files->isLimitExceeded($sysconf['max_upload']*1024);

        // destroy it if failed
        if (!empty($files->getError())) $files->destroyIfFailed();

        })->as('temp' . DS . $_SESSION['csv']['name']);
        
        if (!$csv_upload->getUploadStatus())
        {
            toastr(__('Upload failed! File type not allowed or the size is more than').($sysconf['max_upload']/1024).' MB')->error(__('Import Tool'));
            exit;
        }

        // Redirect content
        redirect()->simbioAJAX(MWB . 'bibliography/import_preview.php');
    } else {
        $start_time = time();
        // set PHP time limit
        set_time_limit(0);
        // set ob implicit flush
        ob_implicit_flush();
        $row_count = 0;
        // check for import setting
        $record_num = intval($_SESSION['csv']['format']['recordNum']);
        $field_enc = trim($_SESSION['csv']['format']['fieldEnc']);
        $field_sep = trim($_SESSION['csv']['format']['fieldSep']);
        $record_offset = intval($_SESSION['csv']['format']['recordOffset']);
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
        $file = $files_disk->readStream('temp' . DS . $_SESSION['csv']['name'] . '.csv');
        $fileNumber = $files_disk->readStream('temp' . DS . $_SESSION['csv']['name'] . '.csv');
        $n = 0;

        // get total line
        $lineNumber = 0;
        while (!feof($fileNumber)) {
            $line = fgets($fileNumber);
            if (empty($line)) continue;
            $lineNumber++;
            ob_flush();
            flush();
        }

        try {
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
                        $source = $field[11]?'\''.$field[11].'\'':'0';
                        $invoice = $field[12]?'\''.$field[12].'\'':'NULL';
                        $price = $field[13]?'\''.$field[13].'\'':'NULL';
                        $price_currency = $field[14]?'\''.$field[14].'\'':'NULL';
                        $invoice_date = $field[15]?'\''.$field[15].'\'':'NULL';
                        $input_date = $field[16]?'\''.$field[16].'\'':'\''.date('Y-m-d H:i:s').'\'';
                        $last_update = $field[17]?'\''.$field[17].'\'':'\''.date('Y-m-d H:i:s').'\'';
                        $title = $field[18];
    
                        // first field is header
                        if (isset($_POST['header']) && $n < 1) {
                          $n++;
                          continue;
                        } else {
    
                            // get biblio_id
                            $b_q = $dbs->query(sprintf("select biblio_id from biblio where title = '%s'", $title));
                            if($b_q->num_rows < 1) continue;
                            $b_d = $b_q->fetch_row();
                            $biblio_id = $b_d[0];
        
                            // sql insert string
                            if (!isItemExists($item_code)) {
                                // echo '<script>console.log(\'Insert\')</script>';
                                $sql_str = "INSERT INTO item (biblio_id, item_code, call_number, coll_type_id,
                                    inventory_code, received_date, supplier_id,
                                    order_no, location_id, order_date, item_status_id, site,
                                    source, invoice, price, price_currency, invoice_date,
                                    input_date, last_update)
                                        VALUES ($biblio_id, $item_code, $call_number, $coll_type,
                                        $inventory_code, $received_date, $supplier,
                                        $order_no, $location, $order_date, $item_status, $site,
                                        $source, $invoice, $price, $price_currency, $invoice_date,
                                        $input_date, $last_update)";
                            } else {
                                // echo '<script>console.log(\'Update\')</script>'; 
                                $sql_str = "UPDATE item SET call_number=$call_number, coll_type_id=$coll_type,
                                        inventory_code=$inventory_code, received_date=$received_date, supplier_id=$supplier,
                                        order_no=$order_no, location_id=$location, order_date=$order_date, item_status_id=$item_status, site=$site,
                                        source=$source, invoice=$invoice, price=$price, price_currency=$price_currency, invoice_date=$invoice_date,
                                        input_date=$input_date, last_update=$last_update WHERE item_code LIKE $item_code";
                            }
    
                            // send query
                            $dbs->query($sql_str);
                            if ($dbs->affected_rows > 0) { $updated_row++; }
                        }
                    }

                    $row_count++;
                    importProgress(round($row_count/$lineNumber * 100));
                    sleep(1);
                }
            }
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            toastr($errorMessage)->error();
            exit(<<<HTML
            <script>
            parent.\$('.infoBox').html('{$errorMessage}')
            parent.\$('.infoBox').addClass('errorBox');
            parent.\$('.infoBox').removeClass('infoBox');
            </script>
            HTML);
        }

        // close file handle
        fclose($file);

        // set information variable before reset csv session
        $redirectTo = $_SESSION['csv']['action'];
        $fileName = $_SESSION['csv']['name'];

        // delete temp file
        $files_disk->delete('temp' . DS . $fileName . '.csv');

        // Reset session
        unset($_SESSION['csv']);

        $end_time = time();
        $import_time_sec = $end_time-$start_time;
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'Importing '.$updated_row.' item records from file : '.$fileName);
        $label = str_replace(['{row_count}','{time_to_finish}'], [$row_count, $import_time_sec], __('Success imported <strong>{row_count}</strong> title in <strong>{time_to_finish}</strong> second'));
        exit(<<<HTML
        <script>
        parent.\$('.infoBox').html('{$label}')
        setTimeout(() => parent.\$('#mainContent').simbioAJAX('{$redirectTo}'), 2500)
        </script>
        HTML);
    }
}
?>
<div class="menuBox">
    <div class="menuBoxInner importIcon">
        <div class="per_title">
            <h2><?php echo __('Item Import tool'); ?></h2>
        </div>
        <div class="infoBox">
            <?php echo __('Import for item data from CSV file'); ?>
            &nbsp;<a href="<?= $_SERVER['PHP_SELF'] ?>?action=download_sample" class="s-btn btn btn-secondary notAJAX"><?= __('Download Sample') ?></a>
        </div>
    </div>
</div>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Process').'" class="btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input  = '<div class="container-fluid">';
$str_input .= '<div class="row">';
$str_input .= '<div class="custom-file col-6">';
$str_input .= simbio_form_element::textField('file', 'importFile','','class="custom-file-input"');
$str_input .= '<label class="custom-file-label" for="customFile">Choose file</label>';
$str_input .= '</div>';
$str_input .= '<div class="col">';
$str_input .= '<div class="mt-2">Maximum '.$sysconf['max_upload'].' KB</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$form->addAnything(__('File To Import'), $str_input);
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" maxlength="3" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;" class="form-control"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of Records To Import (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;" class="form-control"');
// header (column name)
$form->addCheckBox('header', __('The first row is the columns names'), array( array('1', __('Yes')) ), '');
// output the form
echo $form->printOut();
?>
<script>
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});
</script>