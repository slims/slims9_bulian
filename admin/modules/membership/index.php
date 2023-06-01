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

use SLiMS\Plugins;

/* Membership Management section */
use SLiMS\Filesystems\Storage;

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
do_checkIP('smc-membership');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('membership', 'w');

if (!$can_read) {
    die('<div class="errorBox">You dont have enough privileges to view this section</div>');
}

// execute registered hook
Plugins::getInstance()->execute(Plugins::MEMBERSHIP_INIT);

/* Just In Case for PHP < 5.4 */
/* Taken From imageman (http://www.php.net/manual/en/function.getimagesizefromstring.php#113976) */
/* Make sure to set allow_url_fopen = on inside your php.ini */
if (version_compare(phpversion(), '5.4', '<')) 
{
    function getimagesizefromstring($string_data)
    {
        $uri = 'data://application/octet-stream;base64,'  . base64_encode($string_data);
        return getimagesize($uri);
    }
}

/* REMOVE IMAGE */
if (isset($_POST['removeImage']) && isset($_POST['mimg']) && isset($_POST['img'])) {
  // validate post image
  $member_id = utility::filterData('mimg', 'post', true, true, true);
  $image_name = utility::filterData('img', 'post', true, true, true);

  $query_image = $dbs->query("SELECT member_id FROM member WHERE member_id='{$member_id}' AND member_image='{$image_name}'");
  if (!empty($query_image->num_rows)) {
    $_delete = $dbs->query(sprintf("UPDATE member SET member_image=NULL WHERE member_id='%s'", $member_id));
    if ($_delete) {
      $postImage = stripslashes($_POST['img']);
      $postImage = str_replace('/', '', $postImage);
      @Storage::images()->delete(sprintf('persons/%s', $postImage));
      exit('<script type="text/javascript">alert(\''.str_replace('{imageFilename}', $postImage, __('{imageFilename} successfully removed!')).'\'); $(\'#memberImage, #imageFilename\').remove();</script>');
    }
  }
  exit();
}
/* member update process */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    // check form validity
    $memberID = trim($_POST['memberID']);
    $memberName = trim($_POST['memberName']);
    $birthDate = trim($_POST['birthDate']);
    $mpasswd1 = trim($_POST['memberPasswd']);
    $mpasswd2 = trim($_POST['memberPasswd2']);
    if (empty($memberID) OR empty($memberName) OR empty($birthDate)) {
        toastr(__('Member ID, Name and Birthday cannot be empty'))->error(); //mfc
        exit();
    } else if (($mpasswd1 OR $mpasswd2) AND ($mpasswd1 !== $mpasswd2)) {
        toastr(__('Password confirmation does not match. See if your Caps Lock key is on!'))->error();
        exit();
    } else {

        // include custom fields file
        if (file_exists(MDLBS.'membership/member_custom_fields.inc.php')) {
            include MDLBS.'membership/member_custom_fields.inc.php';
        }

        /**
         * Custom fields
         */
        if (isset($member_custom_fields)) {
          if (is_array($member_custom_fields) && $member_custom_fields) {
            foreach ($member_custom_fields as $fid => $cfield) {
              // custom field data
              $cf_dbfield = $cfield['dbfield'];
              if (isset($_POST[$cf_dbfield])) {
                if(is_array($_POST[$cf_dbfield])){ 
                  foreach ($_POST[$cf_dbfield] as $value) {
                    $arr[$value] = $value;
                  }
                  $custom_data[$cf_dbfield] = serialize($arr);
                }
                else{
                  $cf_val = $dbs->escape_string(strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']));
                  if($cfield['type'] == 'numeric' && (!is_numeric($cf_val) && $cf_val!='')){
                    toastr(sprintf(__('Field %s only number for allowed'),$cfield['label']))->error(__('Membership'));      
                    exit();        
                  }
                  elseif ($cfield['type'] == 'date' && $cf_val == '') {
                    toastr(sprintf(__('Field %s is date format, empty not allowed'),$cfield['label']))->error(__('Membership'));      
                    exit();
                  }
                  $custom_data[$cf_dbfield] = $cf_val;
                }
              }else{
                $custom_data[$cf_dbfield] = serialize(array());
              }
            }
          }
        }

        $data['member_id'] = $dbs->escape_string($memberID);
        $data['member_name'] = $dbs->escape_string($memberName);
        $data['member_type_id'] = (integer)$_POST['memberTypeID'];
        $data['inst_name'] = trim($dbs->escape_string(strip_tags($_POST['instName'])));
        $data['gender'] = trim($dbs->escape_string(strip_tags($_POST['gender'])));
        $data['birth_date'] = trim($dbs->escape_string(strip_tags($_POST['birthDate'])));
        $data['birth_date'] = $data['birth_date'] == '' ? null : $data['birth_date'];
        $data['register_date'] = trim($dbs->escape_string(strip_tags($_POST['regDate'])));
        // member since date
        $member_since = trim($dbs->escape_string(strip_tags($_POST['sinceDate'])));
        if (isset($_POST['updateRecordID'])) {
            $data['member_since_date'] = $member_since;
        } else {
            if ($member_since) {
                $data['member_since_date'] = $member_since;
            } else {
                $data['member_since_date'] = $data['register_date'];
            }
        }

        $data['expire_date'] = trim($dbs->escape_string(strip_tags($_POST['expDate'])));
        // extending membership
        if (isset($_POST['extend']) AND !empty($_POST['extend'])) {
            // get membership periode from database
            $mtype_query = $dbs->query("SELECT member_periode FROM mst_member_type WHERE member_type_id=".$data['member_type_id']);
            $mtype_data = $mtype_query->fetch_row();
            $data['register_date'] = date('Y-m-d');
            $data['expire_date'] = simbio_date::getNextDate($mtype_data[0], $data['register_date']);
        }
        $data['pin'] = trim($dbs->escape_string(strip_tags($_POST['memberPIN'])));
        $data['member_address'] = trim($dbs->escape_string(strip_tags($_POST['memberAddress'])));
        $data['member_mail_address'] = trim($dbs->escape_string(strip_tags($_POST['memberMailAddress'])));
        $data['member_phone'] = trim($dbs->escape_string(strip_tags($_POST['memberPhone'])));
        $data['member_fax'] = trim($dbs->escape_string(strip_tags($_POST['memberFax'])));
        $data['postal_code'] = trim($dbs->escape_string(strip_tags($_POST['memberPostal'])));
        $data['member_notes'] = trim($dbs->escape_string(strip_tags($_POST['memberNotes'])));
        $data['member_email'] = trim($dbs->escape_string(strip_tags($_POST['memberEmail'])));
        $data['is_pending'] = isset($_POST['isPending'])? intval($_POST['isPending']) : '0';
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        $imageDisk = Storage::images();
        if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
          // create upload object
          $upload = $imageDisk->upload('image', function($image) use($sysconf) {

            // Extension check
            $image->isExtensionAllowed($sysconf['allowed_images']);

            // File size check
            $image->isLimitExceeded($sysconf['max_image_upload']*1024);

            // destroy it if failed
            if (!empty($image->getError())) $image->destroyIfFailed();

          })->as('persons/' . 'member_'.$data['member_id']);

          if ($upload->getUploadStatus()) {
            $data['member_image'] = $dbs->escape_string($upload->getUploadedFileName());
          } else {
            // write log
            $data['member_image'] = NULL;
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'ERROR : ' . $_SESSION['realname'] . ' FAILED TO upload image file ' . $upload->getUploadedFileName() . ', with error (' . $upload->getError() . ')');
            utility::jsToastr('Membership', __('Image Uploaded Failed').'<br/>'.$upload->getError(), 'error');
          }
        } else if (!empty($_POST['base64picstring'])) {
			    list($filedata, $filedom) = explode('#image/type#', $_POST['base64picstring']);
          $filedata = base64_decode($filedata);
          $fileinfo = getimagesizefromstring($filedata);
          $valid = strlen($filedata)/1024 < $sysconf['max_image_upload'];
          $valid = (!$fileinfo || $valid === false) ? false : in_array($fileinfo['mime'], $sysconf['allowed_images_mimetype']);
			    $new_filename = 'member_'.$data['member_id'].'.'.strtolower($filedom);

			    if ($valid) {
            @$imageDisk->put('persons/'.$new_filename, $filedata);

            if ($imageDisk->isExists('persons/'.$new_filename))
            {
              $data['member_image'] = $dbs->escape_string($new_filename);
              if (!defined('UPLOAD_SUCCESS')) define('UPLOAD_SUCCESS', 1);
              $upload_status = UPLOAD_SUCCESS;
            }
			    }
		    }
        // password confirmation
        if (($mpasswd1 AND $mpasswd2) AND ($mpasswd1 === $mpasswd2)) {
          // $data['mpasswd'] = 'literal{MD5(\''.$mpasswd2.'\')}';
          $data['mpasswd'] = password_hash($mpasswd2, PASSWORD_BCRYPT);
        }

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
            $old_member_ID = $updateRecordID;

            // execute registered hook
            Plugins::getInstance()->execute(Plugins::MEMBERSHIP_BEFORE_UPDATE, ['data' => $data]);

            // update the data
            $update = $sql_op->update('member', $data, "member_id='$updateRecordID'");
            if ($update) {

                // execute registered hook
                Plugins::getInstance()->execute(Plugins::MEMBERSHIP_AFTER_UPDATE, ['data' => api::member_load($dbs, $updateRecordID)]);

                // update custom data
                if (isset($custom_data)) {
                  // check if custom data for this record exists
                  $_sql_check_custom_q = sprintf("SELECT member_id FROM member_custom WHERE member_id='%s'", $updateRecordID);
                  $check_custom_q = $dbs->query($_sql_check_custom_q);
                  if ($check_custom_q->num_rows) {
                    @$sql_op->update('member_custom', $custom_data, 'member_id=\''.$updateRecordID.'\'');
                  } else {
                    $custom_data['member_id'] = $updateRecordID;
                    @$sql_op->insert('member_custom', $custom_data);
                  }
                }
                // update other tables contain this member ID
                @$dbs->query('UPDATE loan SET member_id=\''.$data['member_id'].'\' WHERE member_id=\''.$old_member_ID.'\'');
                @$dbs->query('UPDATE fines SET member_id=\''.$data['member_id'].'\' WHERE member_id=\''.$old_member_ID.'\'');
                toastr(__('Member Data Successfully Updated'))->success();
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' upload image file '.$upload->new_filename, 'Photo', 'Update');
                        toastr(__('Image Uploaded Successfully'))->success();
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')', 'Photo', 'Fail');
                        toastr(__('Image FAILED to upload'))->error();
                    }
                }
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' update member data ('.$memberName.') with ID ('.$memberID.')', 'Update', 'OK');
                if ($sysconf['webcam'] == 'html5') {
                  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php\');</script>';
                } else {
                  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php\');</script>';
                }
            } else { toastr(__('Member Data FAILED to Save/Update. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            if (!$mpasswd1 AND !$mpasswd2) { $data['mpasswd'] = 'literal{NULL}'; }
            // insert the data
            $insert = $sql_op->insert('member', $data);
            if ($insert) {

                // insert custom data
                if ($custom_data) {
                  $custom_data['member_id'] = $data['member_id'];
                  @$sql_op->insert('member_custom', $custom_data);
                }

                Plugins::getInstance()->execute(Plugins::MEMBERSHIP_AFTER_SAVE, ['data' => api::member_load($dbs, $data['member_id'])]);

                toastr(__('New Member Data Successfully Saved'))->success();
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' upload image file '.$upload->new_filename, 'Photo', 'Add');
                        toastr(__('Image Uploaded Successfully'))->success();
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')', 'Photo', 'Fail');
                        toastr(__('Image FAILED to upload'))->error();
                    }
                }
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' add new member ('.$memberName.') with ID ('.$memberID.')', 'Add', 'OK');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { toastr(__('Member Data FAILED to Save/Update. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->danger(); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['batchExtend']) && $can_read && $can_write) {
    /* BATCH extend membership proccessing */
    $curr_date = date('Y-m-d');
    $num_extended = 0;
    foreach ($_POST['itemID'] as $itemID) {
        $memberID = $dbs->escape_string(trim($itemID));
        // get membership periode from database
        $mtype_q = $dbs->query('SELECT member_periode, m.member_name FROM member AS m
            LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
            WHERE m.member_id=\''.$memberID.'\'');
        $mtype_d = $mtype_q->fetch_row();
        $expire_date = simbio_date::getNextDate($mtype_d[0], $curr_date);
        @$dbs->query('UPDATE member SET register_date=\''.date("Y-m-d").'\',  expire_date=\''.$expire_date.'\', last_update=\''.date("y-m-d").'\' WHERE member_id=\''.$memberID.'\'');
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' extends membership for member ('.$mtype_d[1].') with ID ('.$memberID.')', 'Extend', 'OK');
        $num_extended++;
    }
    header('Location: '.MWB.'membership/index.php?expire=true&numExtended='.$num_extended);
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    $still_have_loan = array();
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($dbs->escape_string(trim($_POST['itemID'])));
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = $dbs->escape_string(trim($itemID));
        // check if the member still have loan
        $loan_q = $dbs->query('SELECT DISTINCT m.member_id, m.member_name, COUNT(l.loan_id) FROM member AS m
            LEFT JOIN loan AS l ON (m.member_id=l.member_id AND l.is_lent=1 AND l.is_return=0)
            WHERE m.member_id=\''.$itemID.'\' GROUP BY m.member_id');
        $loan_d = $loan_q->fetch_row();
        if ($loan_d[2] < 1) {
            if (!$sql_op->delete('member', "member_id='$itemID'")) {
                $error_num++;
            } else {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' DELETE member data ('.$loan_d[1].') with ID ('.$loan_d[0].')', 'Delete', 'OK');
            }
        } else {
            $still_have_loan[] = $loan_d[0].' - '.$loan_d[1];
            $error_num++;
        }
    }

    if ($still_have_loan) {
        $members = '';
        foreach ($still_have_loan as $mbr) {
            $members .= $mbr."\n";
        }
        toastr(__('Below member data can\'t be deleted because still have unreturned item(s)').' : '."\n".$mbr)->error();
        exit();
    }
    // error alerting
    if ($error_num == 0) {
        toastr(__('All Data Successfully Deleted'))->success();
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        toastr(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'))->error();
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
$page_title = __('Membership');
if(isset($_GET['expire'])) {
    $page_title = __('Expired Member List');
}
?>
<div class="menuBox">
<div class="menuBoxInner memberIcon">
	<div class="per_title">
    	<h2><?php echo $page_title; ?></h2>
    </div>
    <div class="sub_section">
	<div class="btn-group">
    <a href="<?php echo MWB; ?>membership/index.php" class="btn btn-default"><?php echo __('Member List'); ?></a>
    <a href="<?php echo MWB; ?>membership/index.php?action=detail" class="btn btn-default"><?php echo __('Add New Member'); ?></a>
    <a href="<?php echo MWB; ?>membership/index.php?expire=true" class="btn btn-danger"><?php echo __('View Expired Member'); ?></a>
	</div>
    <form name="search" action="<?php echo MWB; ?>membership/index.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
	    <input type="text" name="keywords" class="form-control col-md-3" /><?php if (isset($_GET['expire'])) { echo '<input type="hidden" name="expire" value="true" />'; } ?>
	    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
	</form>
	</div>
</div>
</div>
<?php
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    $itemID = $dbs->escape_string(trim(isset($_POST['itemID'])?$_POST['itemID']:'0'));
    $rec_d = [];
    if (!empty($itemID))
    {
      $rec_q = $dbs->query("SELECT * FROM member WHERE member_id='$itemID'");
      $rec_d = $rec_q->fetch_assoc();
    }

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if (!empty($itemID) && $rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['member_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';

        // custom field data query
        $_sql_rec_cust_q = sprintf("SELECT * FROM member_custom WHERE member_id='%s'", $itemID);
        $rec_cust_q = $dbs->query($_sql_rec_cust_q);
        $rec_cust_d = $rec_cust_q->fetch_assoc();

    }

    /* Form Element(s) */
    if ($form->edit_mode) {
        // check if member expired
        $curr_date = date('Y-m-d');
        $compared_date = simbio_date::compareDates($rec_d['expire_date'], $curr_date);
        $is_expired = ($compared_date == $curr_date);
        $expired_message = '';
        if ($is_expired) {
            // extend membership
            $chbox_array[] = array('1', __('Extend'));
            $form->addCheckBox('extend', __('Extend Membership'), $chbox_array);
            $expired_message = '<strong class="text-danger">('.__('Membership Already Expired').')</strong>';
        }
    }

    // include custom fields file
    if (file_exists(MDLBS.'membership/member_custom_fields.inc.php')) {
        include MDLBS.'membership/member_custom_fields.inc.php';
    }

    // member code
    $str_input  = '<div class="container-fluid">';
    $str_input .= '<div class="row">';
    $str_input .= simbio_form_element::textField('text', 'memberID', $rec_d['member_id']??'', 'id="memberID" onblur="ajaxCheckID(\''.SWB.'admin/AJAX_check_id.php\', \'member\', \'member_id\', \'msgBox\', \'memberID\')" class="form-control col-4"');
    $str_input .= '<div id="msgBox" class="col mt-2"></div>';
    $str_input .= '</div>';
    $str_input .= '</div>';
    $form->addAnything(__('Member ID').'*', $str_input);
    // member name
    $form->addTextField('text', 'memberName', __('Member Name').'*', $rec_d['member_name']??'', 'class="form-control" style="width: 50%;"');
    // member birth date
    $form->addDateField('birthDate', __('Birth Date').'*', $rec_d['birth_date']??'','class="form-control"');
    // member since date
    $form->addDateField('sinceDate', __('Member Since').'*', $rec_d['member_since_date']??date('Y-m-d'),'class="form-control"');
    // member register date
    $form->addDateField('regDate', __('Register Date').'*', $rec_d['register_date']??date('Y-m-d'),'class="form-control"');
    // member expire date
    if ($form->edit_mode) {
        $form->addDateField('expDate', __('Expiry Date').'*', $rec_d['expire_date']??'','class="form-control"');
    } else {
        $chbox_array[] = array('1', __('Auto Set'));
        $str_input = '<div>'.simbio_form_element::checkBox('extend', $chbox_array, '1').'</div>';
        $str_input .= '<div>'.simbio_form_element::dateField('expDate', $rec_d['expire_date']??'', 'class="form-control"').'</div>';
        $form->addAnything(__('Expiry Date').'*', $str_input);
    }
    // member institution
    $form->addTextField('text', 'instName', __('Institution'), $rec_d['inst_name']??'', 'class="form-control" style="width: 100%;"');
    // member type
        // get mtype data related to this record from database
        $mtype_query = $dbs->query("SELECT member_type_id, member_type_name FROM mst_member_type");
        $mtype_options = array();
        while ($mtype_data = $mtype_query->fetch_row()) {
            $mtype_options[] = array($mtype_data[0], $mtype_data[1]);
        }
    $form->addSelectList('memberTypeID', __('Membership Type').'*', $mtype_options, $rec_d['member_type_id']??'','class="form-control col-4"');
    // member gender
    $gender_chbox[0] = array('1', __('Male'));
    $gender_chbox[1] = array('0', __('Female'));
    $form->addRadio('gender', __('Sex'), $gender_chbox, !empty($rec_d['gender'])?$rec_d['gender']:'0');
    // member address
    $form->addTextField('textarea', 'memberAddress', __('Address'), $rec_d['member_address']??'', 'rows="2" class="form-control" style="width: 100%;"');
    // member postal
    $form->addTextField('text', 'memberPostal', __('Postal Code'), $rec_d['postal_code']??'', 'class="form-control" style="width: 50%;"');
    // member mail address
    $form->addTextField('textarea', 'memberMailAddress', __('Mail Address'), $rec_d['member_mail_address']??'', 'rows="2" class="form-control" style="width: 100%;"');
    // member phone
    $form->addTextField('text', 'memberPhone', __('Phone Number'), $rec_d['member_phone']??'', 'class="form-control" style="width: 50%;"');
    // member fax
    $form->addTextField('text', 'memberFax', __('Fax Number'), $rec_d['member_fax']??'', 'class="form-control" style="width: 50%;"');
    // member pin
    $form->addTextField('text', 'memberPIN', __('Personal ID Number'), $rec_d['pin']??'', 'class="form-control" style="width: 50%;"');
    // member notes
    $form->addTextField('textarea', 'memberNotes', __('Notes'), $rec_d['member_notes']??'', 'rows="2" class="form-control" style="width: 100%;"');

    /**
     * Custom fields
     */
    if (isset($member_custom_fields)) {
        if (is_array($member_custom_fields) && $member_custom_fields) {
            foreach ($member_custom_fields as $fid => $cfield) {

            // custom field properties
            $cf_dbfield = $cfield['dbfield'];
            $cf_label = $cfield['label'];
            $cf_default = $cfield['default'];
            $cf_class = $cfield['class']??'';
            $cf_data = (isset($cfield['data']) && $cfield['data'] )?unserialize($cfield['data']):array();

            // get data field record
            if(isset($rec_cust_d[$cf_dbfield]) && @unserialize($rec_cust_d[$cf_dbfield]) !== false){
              $rec_cust_d[$cf_dbfield] = unserialize($rec_cust_d[$cf_dbfield]);
            }

            // custom field processing
            if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
              $cf_max = isset($cfield['max'])?$cfield['max']:'200';
              $cf_width = isset($cfield['width'])?$cfield['width']:'50';
              $form->addTextField( ($cfield['type'] == 'longtext')?'textarea':'text', $cf_dbfield, $cf_label, $rec_cust_d[$cf_dbfield]??$cf_default, ' class="form-control '.$cf_class.'" style="width: '.$cf_width.'%;" maxlength="'.$cf_max.'"');
            } else if ($cfield['type'] == 'dropdown') {
              $form->addSelectList($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield]??$cf_default,' class="form-control '.$cf_class.'"');
            } else if ($cfield['type'] == 'checklist') {
              $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield]??$cf_default,' class="form-control '.$cf_class.'"');
            } else if ($cfield['type'] == 'choice') {
              $form->addRadio($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield]??$cf_default,' class="form-control '.$cf_class.'"');
            } else if ($cfield['type'] == 'date') {
              $form->addDateField($cf_dbfield, $cf_label, $rec_cust_d[$cf_dbfield]??$cf_default,' class="form-control '.$cf_class.'"');
            }
            unset($cf_data);
            }
        }
    }

    // member is_pending
    $form->addCheckBox('isPending', __('Pending Membership'), array( array('1', __('Yes')) ), $rec_d['is_pending']??'');
    // member photo
    $upper_dir  = '';
    $str_input  = '<div class="row">';
    $str_input .= '<div class="col-2">';
    $str_input .= '<div id="imageFilename" class="s-margin__bottom-1">';
    if (isset($rec_d['member_image']) && Storage::images()->isExists('persons/'.$rec_d['member_image'])) {
        $str_input .= '<a href="'.SWB.'images/persons/'.$rec_d['member_image'].'" class="openPopUp notAJAX" title="'.__('Click to enlarge preview').'" width="300" height="400">';
        // $str_input .= '<img src="'.$upper_dir.'../lib/minigalnano/createthumb.php?filename=images/persons/'.urlencode(($rec_d['member_image']??'photo.png')).'&width=130" class="img-fluid" alt="Image cover">';
        $str_input .= '<img src="'.SWB.'lib/minigalnano/createthumb.php?filename=images/persons/'.urlencode(($rec_d['member_image']??'photo.png')).'&width=148&v='.date('this').'" class="img-fluid rounded" alt="Image cover">';
        $str_input .= '</a>';
        $str_input .= '<a href="'.MWB.'membership/index.php" postdata="removeImage=true&mimg='.$itemID.'&img='.($rec_d['member_image']??'photo.png').'" loadcontainer="imageFilename" class="s-margin__bottom-1 s-btn btn btn-danger btn-block rounded-0 makeHidden removeImage">'.__('Remove Image').'</a>';
    }else{
        $str_input .= '<img src="'.SWB.'images/persons/person.png'.'?'.date('this').'" class="img-fluid rounded" alt="Image cover">';
    }
    $str_input .= '</div>';
    $str_input .= '</div>';
    $str_input .= '<div class="custom-file col-4">';
    $str_input .= simbio_form_element::textField('file', 'image', '', 'class="custom-file-input"');
    $str_input .= '<label class="custom-file-label" for="customFile">Choose file</label>';
    $str_input .= '</div>';
    $str_input .= ' <div class="mt-2 ml-2">Maximum '.$sysconf['max_image_upload'].' KB</div>';
    $str_input .= '</div>';
    // $str_input = '<div id="imageFilename"><a href="'.SWB.'images/persons/'.$rec_d['member_image'].'" class="openPopUp notAJAX"><strong>'.$rec_d['member_image'].'</strong></a> <a href="'.MWB.'membership/index.php" postdata="removeImage=true&mimg='.$itemID.'&img='.$rec_d['member_image'].'" loadcontainer="imageFilename" class="makeHidden removeImage">'.__('REMOVE IMAGE').'</a></div>';    
    // $str_input .= simbio_form_element::textField('file', 'image');
    // $str_input .= ' '.__('Maximum').' '.$sysconf['max_image_upload'].' KB';
    if ($sysconf['webcam'] !== false) {
      $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';

      if ($sysconf['webcam'] == 'flex') {
        $str_input .= '<object id="flash_video" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" height="280px" width="100%">';
        $str_input .= '<param name="src" value="'.SWB.'lib/flex/ShotSLiMSMemberPicture.swf"/>';
        $str_input .= '<embed name="src" src="'.SWB.'lib/flex/ShotSLiMSMemberPicture.swf" height="280px" width="100%"/>';
        $str_input .= '</object>';
      }
      elseif ($sysconf['webcam'] == 'html5') {
        $str_input .= '<div class="makeHidden_">';
        $str_input .= '<p>'.__('or take a photo').'</p>';
        $str_input .= '<div class="form-inline">';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button id="btn_load" type="button" class="btn btn-primary" onclick="loadcam(this)">'.__('Load Camera').'</button>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<select class="form-control" onchange="aspect(this)"><option value="1">1x1</option><option value="2" selected>2x3</option><option value="3">3x4</option></select>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<select class="form-control" id="cmb_format" onchange="if(pause){set();}"><option value="png">PNG</option><option value="jpg">JPEG</option></select>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button id="btn_pause" type="button" class="btn btn-primary" onclick="snapshot(this)" disabled>'.__('Capture').'</button>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button type="button" id="btn_reset" class="btn btn-danger" onclick="resetvalue()">'.__('Reset').'</button>';
        $str_input .= '</div>';
        $str_input .= '</div>';
        $str_input .= '<div id="my_container" class="makeHidden_ mt-2" style="width: 400px; height: 300px; border: 1px solid #f4f4f4; position: relative;">';
        $str_input .= '<video id="my_vid" autoplay width="400" height="300" style="float: left; position: absolute; left: 10;"></video>';
        $str_input .= '<canvas id="my_canvas" width="400" height="300" style="float: left; position: absolute; left: 10; visibility: hidden;"></canvas>';
        $str_input .= '<div id="my_frame" style="border: 1px solid #CCC; width: 160px; height: 240px; z-index: 2; margin: auto; position: absolute; top: 0; bottom: 0; left: 0; right: 0;"></div></div>';
        $str_input .= '<canvas id="my_preview" width="160" height="240" style="width: 160px; height: 240px; border: 1px solid #f4f4f4; display: none;"></canvas>';
        }
    }

    $form->addAnything(__('Photo'), $str_input);

    // hidden username and password fields so that the password manager of the browser will not fill in the username in the memberEmail and the password in the memberPasswd field
    $form->addTextField('text', 'dummyUserField', null, null, '');
    $form->addTextField('password', 'dummyPasswdField', null, null, '');
    echo '<style type="text/css">#simbioFormRowdummyPasswdField, #simbioFormRowdummyUserField {display: none}</style>';

    // member email
    $form->addTextField('text', 'memberEmail', __('E-mail'), $rec_d['member_email']??'', 'class="form-control" style="width: 40%;" class="form-control"');
    // member password
    $form->addTextField('password', 'memberPasswd', __('New Password'), null, 'class="form-control" style="width: 40%;" class="form-control" autocomplete="new-password"');
    // member password confirmation
    $form->addTextField('password', 'memberPasswd2', __('Confirm New Password'), null, 'class="form-control" style="width: 40%;" class="form-control" autocomplete="new-password"');

    // edit mode messagge
    if ($form->edit_mode) {
        if (isset($rec_d['member_image'])) {
            if (Storage::images()->isExists('persons/'.$rec_d['member_image'])) {
                echo '<div id="memberImage"><img src="'.SWB.'lib/minigalnano/createthumb.php?filename=images/persons/'.urlencode($rec_d['member_image']).'&width=120&v='.date('his').'" alt="'.$rec_d['member_name'].'" /></div>';
            }
        }
        echo '<div class="infoBox">
                <div>'.__('You are going to edit member data').' : <strong>'.$rec_d['member_name'].'</strong></div>
                <div>'.__('Last Updated').' '.date('d F Y h:i:s',strtotime($rec_d['last_update'])).' '.$expired_message.'</div>
                <div>'.__('Leave Password field blank if you don\'t want to change the password').'</div>';
        echo '</div>'."\n";
    }
    // print out the form object
    echo $form->printOut();
?>
<script type="text/javascript">
$(document).ready(function() {
    $('.removeImage').click(function (e) {
      if (confirm('Are you sure you want to permanently remove this image?')) {
        return true;
      } else {
        return false;
      }
    });
    $(document).on('change', '.custom-file-input', function () {
        let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
        $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
    });
});
</script>
<?php
} else {
    
    /* MEMBERSHIP LIST */
    function showMemberImage($obj_db, $array_data){
      global $sysconf;
      $imageDisk = Storage::images();
      $image = 'images/persons/photo.png';
      $_q = $obj_db->query('SELECT member_image,member_name,member_address,member_phone FROM member WHERE member_id = "'.$array_data[0].'"');
      if(isset($_q->num_rows)){
        $_d = $_q->fetch_row();
        if($_d[0] != NULL){     
          $image = $imageDisk->isExists('persons/'.$_d[0])?'images/persons/'.$_d[0]:'images/persons/photo.png';
        }
        $addr  = $_d[2]!=''?'<i class="fa fa-map-marker" aria-hidden="true"></i></i>&nbsp;'.$_d[2]:'';
        $phone = $_d[3]!=''?'<i class="fa fa-phone" aria-hidden="true"></i>&nbsp;'.$_d[3]:'';
      }

       $imageUrl = SWB . 'lib/minigalnano/createthumb.php?filename=' . $image . '&width=120';
       $_output = '<div class="media"> 
                    <a href="'.$imageUrl.'" class="openPopUp notAJAX" title="'.$_d[1].'" width="300" height="400" >
                    <img class="mr-3 rounded" src="'.$imageUrl.'" alt="cover image" width="60"></a>
                    <div class="media-body">
                      <div class="title">'.$array_data[2].'</div>
                      <div class="sub">'.$phone.'</div>
                      <div class="sub">'.$addr.'</div>
                    </div>
                  </div>';
       return $_output;
    }

    // table spec
    $table_spec = 'member AS m
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('m.member_id',
            'm.member_id AS \''.__('Member ID').'\'',
            'm.member_name AS \''.__('Member Name').'\'',
            'mt.member_type_name AS \''.__('Membership Type').'\'',
            'm.member_email AS \''.__('E-mail').'\'',
            'm.last_update AS \''.__('Last Updated').'\'');
            $datagrid->modifyColumnContent(2, 'callback{showMemberImage}');
    } else {
        $datagrid->setSQLColumn('m.member_id AS \''.__('Member ID').'\'',
            'm.member_name AS \''.__('Member Name').'\'',
            'mt.member_type_name AS \''.__('Membership Type').'\'',
            'm.member_email AS \''.__('E-mail').'\'',
            'm.last_update AS \''.__('Last Updated').'\'');
            $datagrid->modifyColumnContent(1, 'callback{showMemberImage}');
    }
    $datagrid->setSQLorder('m.last_update DESC');

    // is there any search
    $criteria = 'm.member_id IS NOT NULL ';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $criteria .= " AND (m.member_name LIKE '%$keywords%' OR m.member_id LIKE '%$keywords%') ";
    }
    if (isset($_GET['expire'])) {
        $criteria .= " AND TO_DAYS('".date('Y-m-d')."')>TO_DAYS(m.expire_date)";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
    $datagrid->table_name = 'memberList';
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if ((isset($_GET['keywords']) AND $_GET['keywords']) OR isset($_GET['expire'])) {
        if (isset($_GET['expire'])) {
            echo '<div class="infoBox">';
            echo '<input type="button" value="'.__('Extend Selected Member(s)').'" onclick="javascript: if (confirm(\''.__('Are you sure to EXTEND membership for selected members?').'\')) { $(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php?expire=1\', { method: \'post\', addData: $(\'#memberList\').serialize() + \'&batchExtend=true\' } ); }" class="s-btn btn btn-primary" />';
            echo '</div>';
            if (isset($_GET['numExtended']) AND $_GET['numExtended'] > 0) {
                echo '<div class="infoBox mt-1">';
                echo '<strong>'.$_GET['numExtended'].'</strong> '.__('members extended!'); //mfc
                echo '</div>';
            }
        }
        if (isset($_GET['keywords']) AND $_GET['keywords']) {
            echo __('Found').' '.$datagrid->num_rows.' '.__('from your search with keyword').' : "'.htmlentities($_GET['keywords']).'"'; //mfc
        }
        echo '</div>';
    }

    echo $datagrid_result;
}
/* main content end */
