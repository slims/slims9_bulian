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

/* Document Label Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

//  main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $labelName = trim(strip_tags($_POST['labelName']));
    $labelDesc = trim(strip_tags($_POST['labelDesc']));
    // check form validity
    if (empty($labelDesc) OR empty($labelName)) {
        utility::jsAlert('Label Name OR Label Description must be filled!');
        exit();
    } else {
        $data['label_desc'] = $dbs->escape_string($labelDesc);
        $data['label_name'] = 'label-'.strtolower(str_ireplace(array(' ', 'label-', '_'), array('-', '', '-'), $dbs->escape_string($labelName)));
        // image uploading
        if (!empty($_FILES['labelImage']) AND $_FILES['labelImage']['size']) {
            // create upload object
            $image_upload = new simbio_file_upload();
            $image_upload->setAllowableFormat($sysconf['allowed_images']);
            $image_upload->setMaxSize($sysconf['max_image_upload']*1024);
            $image_upload->setUploadDir(IMAGES_BASE_DIR.'labels');
            // upload
            $img_upload_status = $image_upload->doUpload('labelImage', $data['label_name']);
            if ($img_upload_status == UPLOAD_SUCCESS) {
              $data['label_image'] = $dbs->escape_string($image_upload->new_filename.'.png');
              // resize the image
              if (function_exists('imagecopyresampled')) {
                // we use phpthumb class to resize image
                include LIB.'phpthumb/ThumbLib.inc.php';
                // create phpthumb object
                $src = IMAGES_BASE_DIR.'labels/'.$image_upload->new_filename;
                $phpthumb = PhpThumbFactory::create($src);
                $w = $h = 24;
                $phpthumb->resize($w, $h);
                $phpthumb->save(IMAGES_BASE_DIR.'labels/'.$data['label_name'].'.png', 'PNG');
              }
              // write log
              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' upload label image file '.$image_upload->new_filename);
              utility::jsAlert('Label image file successfully uploaded');
            } else {
              // write log
              utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload label image file '.$image_upload->new_filename.', with error ('.$image_upload->error.')');
              utility::jsAlert('FAILED to upload label image! Please see System Log for more detailed information');
            }
        }
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
            // update the data
            $update = $sql_op->update('mst_label', $data, 'label_id='.$updateRecordID);
            if ($update) {
                utility::jsAlert(__('Label Data Successfully Updated'));
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { utility::jsAlert(__('Label Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('mst_label', $data)) {
                utility::jsAlert(__('New Label Data Successfully Saved'));
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsAlert(__('Label Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        if (!$sql_op->delete('mst_label', 'label_id='.$itemID)) {
            $error_num++;
        }
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsAlert(__('All Data Successfully Deleted'));
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsAlert(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'));
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Label'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/label.php" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('Label List'); ?></a>
      <a href="<?php echo MWB; ?>master_file/label.php?action=detail" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New Label'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>master_file/label.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="button" />
    </form>
  </div>
</div>
</fieldset>
<?php
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_label WHERE label_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="button"';

    // form table attributes
    $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = str_ireplace('label-', '', $rec_d['label_name']);
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="button"';
    }

    /* Form Element(s) */
    // label name
    $form->addTextField('text', 'labelName', __('Label Name').'*', $rec_d['label_name'], 'style="width: 60%;" maxlength="20"');
    // label image
    if (empty($rec_d['label_image'])) {
        $str_input = simbio_form_element::textField('file', 'labelImage');
        $str_input .= ' Maximum '.$sysconf['max_image_upload'].' KB. All image will be automatically resized.';
        $form->addAnything(__('File Attachment'), $str_input);
    } else {
        $str_input = '<div><img src="'.SWB.IMAGES_DIR.'/labels/'.$rec_d['label_image'].'" align="middle" /> <strong>'.$rec_d['label_image'].'</strong></div>';
        $str_input .= simbio_form_element::textField('file', 'labelImage');
        $str_input .= ' Maximum '.$sysconf['max_image_upload'].' KB. All image will be automatically resized.';
        $form->addAnything(__('File Attachment'), $str_input);
    }
    // label desc
    $form->addTextField('text', 'labelDesc', __('Label Description'), $rec_d['label_desc'], 'style="width: 100%;" maxlength="50"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit Label data').' : <b>'.$rec_d['label_name'].' - '.$rec_d['label_desc'].'</b>  <br />'.__('Last Update').$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* GMD LIST */
    // table spec
    $table_spec = 'mst_label AS lb';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('lb.label_id',
            'lb.label_desc AS \''.__('Label Description').'\'',
            'lb.label_name AS \''.__('Label Name').'\'',
            'lb.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('lb.label_desc AS \''.__('Label Description').'\'',
            'lb.label_name AS \''.__('Label Name').'\'',
            'lb.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('label_name ASC');

    // is there any search
    $criteria = 'lb.label_id IS NOT NULL';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $criteria = " AND (lb.label_name LIKE '%$keywords%' OR lb.label_desc LIKE '%$keywords%')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
