<?php
/**
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

/* Biblio Import section */
use SLiMS\Filesystems\Storage;
use SLiMS\Csv\Writer;
use SLiMS\Csv\Reader;
use SLiMS\Csv\Row;

/* Member Import section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';
require MDLBS . '/bibliography/biblio_utils.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('membership', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// Redirect content
if (isset($_SESSION['csv']['name']) && !isset($_POST['process'])) redirect()->simbioAJAX(MWB . 'bibliography/import_preview.php');

if (isset($_GET['action']) && $_GET['action'] === 'download_sample')
{
  // Create Csv instance
  $csv = new Writer;
  $csv->add(new Row([
    'member_id','member_name','gender','member_type_name',
    'member_email','member_address','postal_code',
    'inst_name','is_new','member_image','pin','member_phone',
    'member_fax','member_since_date','register_date','expire_date',
    'birth_date','member_notes','mpasswd'
  ]));

  // Download CSV
  $csv->download('member_sample_import');
}


// max chars in line for file operations
$max_chars = 4096;

if (isset($_POST['doImport'])) {
    // create upload object
    $files_disk = Storage::files();
    
    // check for form validity
    if (!isset($_POST['process'])) {

        if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
            utility::jsToastr(__('Import Tool'), __('Required fields (*)  must be filled correctly!'), 'error');
            exit();
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

        $_SESSION['csv']['section'] = 'membership';
        $_SESSION['csv']['action'] = $_SERVER['PHP_SELF'];
        $_SESSION['csv']['password'] = (int)($_POST['password'][0]??0);
        if (isset($_POST['header'])) $_SESSION['csv']['header'] = 1;

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
        // set PHP time limit
        set_time_limit(7200);
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
        $mtype_id_cache = array();
        // read file line by line
        $inserted_row = 0;
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

        // close file handle
        fclose($fileNumber);

        try {
            while (!feof($file)) {
                // record count
                if ($record_num > 0 AND $row_count == $record_num) {
                    break;
                }
                // go to offset
                if ($row_count < $record_offset) {
                    // pass and continue to next loop
                    $row = fgets($file, $max_chars);
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
                        $member_id = preg_replace('@^\\\s*'.$field_enc.'@i', '', $field[0]);
                        $member_id = '\''.$member_id.'\'';
                        $member_name = '\''.$field[1].'\'';
                        $gender = ( ! empty($field[2])) ? $field[2] : 0; // patched by Indra Sutriadi
                        $member_type_id = utility::getID($dbs, 'mst_member_type', 'member_type_id', 'member_type_name', $field[3], $mtype_id_cache);
                        $member_email = $field[4]?'\''.$field[4].'\'':'NULL';
                        $member_address = $field[5]?'\''.$field[5].'\'':'NULL';
                        $postal_code = $field[6]?'\''.$field[6].'\'':'NULL';
                        $inst_name = $field[7]?'\''.$field[7].'\'':'NULL';
                        $is_new = $field[8]?$field[8]:'0';
                        $member_image = $field[9]?'\''.$field[9].'\'':'NULL';
                        $pin = $field[10]?'\''.$field[10].'\'':'NULL';
                        $member_phone = $field[11]?'\''.$field[11].'\'':'NULL';
                        $member_fax = $field[12]?'\''.$field[12].'\'':'NULL';
                        $member_since_date = '\''.$field[13].'\'';
                        $register_date = '\''.$field[14].'\'';
                        $expire_date = '\''.$field[15].'\'';
                        $birth_date = $field[16]?'\''.$field[16].'\'':'NULL';
                        $member_notes = preg_replace('@\\\s*'.$field_enc.'$@i', '', $field[17]);
                        $member_notes = $member_notes?'\''.$member_notes.'\'':'NULL';
                        // Password column
                        $lastKey = array_key_last($field);
                        $withPassword = isset($_SESSION['csv']['password']) && $_SESSION['csv']['password'] == 1;
                        $isPasswordValid = isset($field[$lastKey]) && !empty($field[$lastKey]);
                        $mpasswd = $withPassword && $isPasswordValid ? '\'' . password_hash($field[$lastKey], PASSWORD_BCRYPT) . '\'' : 'NULL';

                        // sql insert string
                        $sql_str = "INSERT IGNORE INTO member
                            (member_id, member_name, gender,
                            member_type_id, member_email, member_address, postal_code,
                            inst_name, is_new, member_image, pin, member_phone,
                            member_fax, member_since_date, register_date,
                            expire_date, birth_date, member_notes,
                            input_date, last_update,mpasswd)
                                VALUES ($member_id, $member_name, $gender,
                                $member_type_id, $member_email, $member_address, $postal_code,
                                $inst_name, $is_new,
                                $member_image, $pin, $member_phone,
                                $member_fax, $member_since_date, $register_date,
                                $expire_date, $birth_date, $member_notes,
                                $curr_datetime, $curr_datetime, $mpasswd)";
    
                        // first field is header
                        if (isset($_SESSION['csv']['header']) && $n < 1) {
                          $n++;
                        } else {
                          // send query
                          @$dbs->query($sql_str);
                          
                          if (!$dbs->error) {
                            $inserted_row++;
                          } else {
                            throw new Exception($dbs->error . ' with query : ' . $sql_str);
                          }
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

        // set information variable before reset csv session
        $redirectTo = $_SESSION['csv']['action'];
        $fileName = $_SESSION['csv']['name'];

        // delete temp file
        $files_disk->delete('temp' . DS . $fileName . '.csv');

        // Reset session
        unset($_SESSION['csv']);

        $end_time = time();
        $import_time_sec = $end_time-$start_time;
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', 'Importing '.$inserted_row.' members data from file : '.$fileName, 'Import', 'Add');
        $label = str_replace(['{row_count}','{time_to_finish}'], [$inserted_row, $import_time_sec], __('Success imported <strong>{row_count}</strong> title in <strong>{time_to_finish}</strong> second'));
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
    	<h2><?php echo __('Import Data'); ?></h2>
    </div>
    <div class="sub_section">
	    <div class="btn-group">
            <a href="<?php echo MWB; ?>membership/index.php" class="btn btn-default"><?php echo __('Member List'); ?></a>
        </div>
    </div>
    <div class="infoBox">
    <?php echo __('Import for members data from CSV file'); ?>
    &nbsp;<a href="<?= $_SERVER['PHP_SELF'] ?>?action=download_sample" class="s-btn btn btn-secondary notAJAX"><?= __('Download Sample') ?></a>
	</div>
</div>
</div>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'', 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Process').'" class="s-btn btn btn-primary"';

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
$form->addAnything(__('File To Import').'*', $str_input);
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;" class="form-control"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of Records To Export (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;" class="form-control"');
// header (column name)
$form->addCheckBox('header', __('The first row is columns names'), array( array('1', __('Yes')) ), '');
// password (last column)
$form->addCheckBox('password', __('The last column is password'), array( array('1', __('Yes')) ), '');
// output the form
echo $form->printOut();
?>
<script>
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});
</script>