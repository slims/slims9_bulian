<?php
/**
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

/* Stock Take Upload */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// check if there is any active stock take proccess
$stk_query = $dbs->query('SELECT * FROM stock_take WHERE is_active=1');
if ($stk_query->num_rows < 1) {
    echo '<div class="errorBox">'.__('NO stock taking proccess initialized yet!').'</div>';
    die();
}

// file upload
if (isset($_POST['stUpload']) && isset($_FILES['stFile'])) {
    require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';
    // create upload object
    $upload = new simbio_file_upload();
    $upload->setAllowableFormat(array('.txt'));
    $upload->setMaxSize($sysconf['max_upload']*1024);
    $upload->setUploadDir(UPLOAD);
    // upload the file and change all space characters to underscore
    $upload_status = $upload->doUpload('stFile');
    if ($upload_status == UPLOAD_SUCCESS) {
        // write log
        writeLog('staff', $_SESSION['uid'], 'stock_take', $_SESSION['realname'].' upload stock take file '.$upload->new_filename, 'Upload data', 'OK');
        // open file
        $stfile = @fopen(UPLOAD.$upload->new_filename, 'r');
        if (!$stfile) {
            echo '<script type="text/javascript">'."\n";
            echo 'parent.$(\'#stUploadMsg\').html(\'Failed to open stock take file '.$upload->new_filename.'. Please check permission for directory '.UPLOAD.'\')';
            echo '.toggleClass(\'errorBox\').css( {\'display\': \'block\'} );'."\n";
            echo '</script>';
            exit();
        }
        // start loop
        $i = 0;
        while (!feof($stfile)) {
            $curr_time = date('Y-m-d H:i:s');
            $item_code = fgets($stfile, 80);
            // strip any html tags
            $item_code = strip_tags(trim($item_code));
            if (!$item_code) {
                continue;
            }
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $item_code)) {
                continue;
            }

            // check item status first
            $item_code = $dbs->real_escape_string($item_code);
            $item_check = $dbs->query(sprintf( "SELECT * FROM stock_take_item WHERE item_code='%s'", $item_code ));
            $item_check_d = $item_check->fetch_assoc();
            if ($item_check->num_rows > 0) {
                if ($item_check_d['status'] == 'l') {
                    // record to log
                    writeLog('staff', $_SESSION['uid'], 'stock_take', 'Stock Take ERROR : Item '.$item_check_d['title'].' ('.$item_check_d['item_code'].') is currently ON LOAN (from uploaded file '.$upload->new_filename.')', 'Item', 'Error');
                    continue;
                } else if ($item_check_d['status'] == 'e') {
                    continue;
                } else {
                    $update = @$dbs->query("UPDATE LOW_PRIORITY stock_take_item SET status='e', checked_by='".$_SESSION['realname']."', last_update='".$curr_time."' WHERE item_code='$item_code'");
                    $update = @$dbs->query("UPDATE LOW_PRIORITY stock_take SET total_item_lost=total_item_lost-1 WHERE is_active=1");
                    $i++;
                }
            } else {
                // record to log
                writeLog('staff', $_SESSION['uid'], 'stock_take', 'Stock Take ERROR : Item Code '.$item_code.' doesnt exists in stock take data. Invalid Item Code OR Maybe out of Stock Take range (from uploaded file '.$upload->new_filename.')');
            }
        }
        fclose($stfile);
        // message
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#stUploadMsg\').html(\''.__('Succesfully upload stock take file').$upload->new_filename.', <b>'.$i.'</b>'.__(' item codes scanned!').'\')'; //mfc
        echo '.css( {\'display\': \'block\'} );'."\n";
        echo '</script>';
    } else {
        // write log
        writeLog('staff', $_SESSION['uid'], 'stock_take', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload stock take file '.$upload->new_filename.', with error ('.$upload->error.')', 'Upload data', 'Fail');
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#stUploadMsg\').html(\'Failed to upload stock take file! <div>Error : '.$upload->error.'</div>\')';
        echo '.toggleClass(\'errorBox\').css( {\'display\': \'block\'} );'."\n";
        echo '</script>';
    }
    exit();
}

?>
<div class="menuBox">
<div class="menuBoxInner stockTakeIcon">
  <div class="per_title">
    <h2><?php echo __('Stock Take Upload'); ?></h2>
  </div>
  <div class="infoBox"><?php echo __('Upload a plain text file (.txt) containing list of Item Code to stock take. Each Item Code separated by line.'); ?></div>
  <div class="sub_section">
    <form name="uploadForm" class="notAJAX" method="post" enctype="multipart/form-data" action="<?php echo MWB.'stock_take/st_upload.php'; ?>" target="uploadAction" class="form-inline">
    <div class="container-fluid">
        <div class="row">
            <div class="custom-file col-6">
                <input type="file" name="stFile" id="stFile" value="" class="custom-file-input">
                <label class="custom-file-label" for="customFile"><?= __('Choose file')?></label></div>
                <div class="col"><div class="mt-2"><?= __(sprintf('Maximum %s KB',$sysconf['max_upload']))?></div></div>
            </div>
        </div>
    <div style="margin: 3px;"><input type="submit" name="stUpload" id="stUpload" value="<?php echo __('Upload File'); ?>" class="btn btn-default" />
    <iframe name="uploadAction" style="width: 0; height: 0; visibility: hidden;"></iframe>
    </div>
    </form>
  </div>
</div>
</div>
<div id="stUploadMsg" class="infoBox" style="display: none;">&nbsp;</div>
