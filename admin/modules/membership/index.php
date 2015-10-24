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

/* Membership Management section */

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
  $_delete = $dbs->query(sprintf('UPDATE member SET member_image=NULL WHERE member_id=%d', $_POST['mimg']));
  if ($_delete) {
    @unlink(sprintf(IMGBS.'persons/%s',$_POST['img']));
    exit('<script type="text/javascript">alert(\''.$_POST['img'].' successfully removed!\'); $(\'#memberImage, #imageFilename\').remove();</script>');
  }
  exit();
}
/* member update process */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    // check form validity
    $memberID = trim($_POST['memberID']);
    $memberName = trim($_POST['memberName']);
    $mpasswd1 = trim($_POST['memberPasswd']);
    $mpasswd2 = trim($_POST['memberPasswd2']);
    if (empty($memberID) OR empty($memberName)) {
        utility::jsAlert(__('Member ID and Name can\'t be empty')); //mfc
        exit();
    } else if (($mpasswd1 AND $mpasswd2) AND ($mpasswd1 !== $mpasswd2)) {
        utility::jsAlert(__('Password confirmation does not match. See if your Caps Lock key is on!'));
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
                    // custom field
                    $cf_dbfield = $cfield['dbfield'];
                    if (isset($_POST[$cf_dbfield]) AND trim($_POST[$cf_dbfield]) != '') {
                        $data[$cf_dbfield] = trim($dbs->escape_string(strip_tags($_POST[$cf_dbfield], $sysconf['content']['allowable_tags'])));
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
        if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
          // create upload object
          $upload = new simbio_file_upload();
          $upload->setAllowableFormat($sysconf['allowed_images']);
          $upload->setMaxSize($sysconf['max_image_upload']*1024); // approx. 100 kb
          $upload->setUploadDir(IMGBS.'persons');
          // give new name for upload file
          $new_filename = 'member_'.$data['member_id'];
          $upload_status = $upload->doUpload('image', $new_filename);
          if ($upload_status == UPLOAD_SUCCESS) {
            $data['member_image'] = $dbs->escape_string($upload->new_filename);
          }
        } else if (!empty($_POST['base64picstring'])) {
			    list($filedata, $filedom) = explode('#image/type#', $_POST['base64picstring']);
          $filedata = base64_decode($filedata);
          $fileinfo = getimagesizefromstring($filedata);
          $valid = strlen($filedata)/1024 < $sysconf['max_image_upload'];
          $valid = (!$fileinfo || $valid === false) ? false : in_array($fileinfo['mime'], $sysconf['allowed_images_mimetype']);
			    $new_filename = 'member_'.$data['member_id'].'.'.strtolower($filedom);

			    if ($valid AND file_put_contents(IMGBS.'persons/'.$new_filename, $filedata)) {
				    $data['member_image'] = $dbs->escape_string($new_filename);
				    if (!defined('UPLOAD_SUCCESS')) define('UPLOAD_SUCCESS', 1);
				    $upload_status = UPLOAD_SUCCESS;
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
            // update the data
            $update = $sql_op->update('member', $data, "member_id='$updateRecordID'");
            if ($update) {
                // update other tables contain this member ID
                @$dbs->query('UPDATE loan SET member_id=\''.$data['member_id'].'\' WHERE member_id=\''.$old_member_ID.'\'');
                @$dbs->query('UPDATE fines SET member_id=\''.$data['member_id'].'\' WHERE member_id=\''.$old_member_ID.'\'');
                utility::jsAlert(__('Member Data Successfully Updated'));
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' upload image file '.$upload->new_filename);
                        utility::jsAlert(__('Image Uploaded Successfully'));
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')');
                        utility::jsAlert(__('Image FAILED to upload'));
                    }
                }
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' update member data ('.$memberName.') with ID ('.$memberID.')');
                if ($sysconf['webcam'] == 'html5') {
                  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php\');</script>';
                } else {
                  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php\');</script>';
                }
            } else { utility::jsAlert(__('Member Data FAILED to Save/Update. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            if (!$mpasswd1 AND !$mpasswd2) { $data['mpasswd'] = 'literal{NULL}'; }
            // insert the data
            $insert = $sql_op->insert('member', $data);
            if ($insert) {
                utility::jsAlert(__('New Member Data Successfully Saved'));
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' upload image file '.$upload->new_filename);
                        utility::jsAlert(__('Image Uploaded Successfully'));
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')');
                        utility::jsAlert(__('Image FAILED to upload'));
                    }
                }
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' add new member ('.$memberName.') with ID ('.$memberID.')');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsAlert(__('Member Data FAILED to Save/Update. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
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
        @$dbs->query('UPDATE member SET expire_date=\''.$expire_date.'\' WHERE member_id=\''.$memberID.'\'');
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' extends membership for member ('.$mtype_d[1].') with ID ('.$memberID.')');
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
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'membership', $_SESSION['realname'].' DELETE member data ('.$loan_d[1].') with ID ('.$loan_d[0].')');
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
        utility::jsAlert(__('Below member data can\'t be deleted because still have unreturned item(s)').' : '."\n".$mbr);
        exit();
    }
    // error alerting
    if ($error_num == 0) {
        utility::jsAlert(__('All Data Successfully Deleted'));
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsAlert(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'));
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner memberIcon">
	<div class="per_title">
    	<h2><?php echo __('Membership'); ?></h2>
    </div>
    <div class="sub_section">
	<div class="btn-group">
    <a href="<?php echo MWB; ?>membership/index.php" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('Member List'); ?></a>
    <a href="<?php echo MWB; ?>membership/index.php?expire=true" class="btn btn-default" style="color: #FF0000;"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('View Expired Member'); ?></a>
    <a href="<?php echo MWB; ?>membership/index.php?action=detail" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New Member'); ?></a>
	</div>
    <form name="search" action="<?php echo MWB; ?>membership/index.php" id="search" method="get" style="display: inline;"><?php echo __('Member Search'); ?> :
	    <input type="text" name="keywords" size="30" /><?php if (isset($_GET['expire'])) { echo '<input type="hidden" name="expire" value="true" />'; } ?>
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
    $itemID = $dbs->escape_string(trim(isset($_POST['itemID'])?$_POST['itemID']:''));
    $rec_q = $dbs->query("SELECT * FROM member WHERE member_id='$itemID'");
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
        $form->record_title = $rec_d['member_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="button"';
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
            $expired_message = '<b style="color: #FF0000;">('.__('Membership Already Expired').')</b>';
        }
    }

    // include custom fields file
    if (file_exists(MDLBS.'membership/member_custom_fields.inc.php')) {
        include MDLBS.'membership/member_custom_fields.inc.php';
    }

    // member code
    $str_input = simbio_form_element::textField('text', 'memberID', $rec_d['member_id'], 'id="memberID" onblur="ajaxCheckID(\''.SWB.'admin/AJAX_check_id.php\', \'member\', \'member_id\', \'msgBox\', \'memberID\')" style="width: 30%;"');
    $str_input .= ' &nbsp; <span id="msgBox">&nbsp;</span>';
    $form->addAnything(__('Member ID').'*', $str_input);
    // member name
    $form->addTextField('text', 'memberName', __('Member Name').'*', $rec_d['member_name'], 'style="width: 100%;"');
    // member birth date
    $form->addDateField('birthDate', __('Birth Date'), $rec_d['birth_date']);
    // member since date
    $form->addDateField('sinceDate', __('Member Since').'*', $form->edit_mode?$rec_d['member_since_date']:date('Y-m-d'));
    // member register date
    $form->addDateField('regDate', __('Register Date').'*', $form->edit_mode?$rec_d['register_date']:date('Y-m-d'));
    // member expire date
    if ($form->edit_mode) {
        $form->addDateField('expDate', __('Expiry Date').'*', $rec_d['expire_date']);
    } else {
        $chbox_array[] = array('1', __('Auto Set'));
        $str_input = '<div>'.simbio_form_element::checkBox('extend', $chbox_array, '1').'</div>';
        $str_input .= '<div>'.simbio_form_element::dateField('expDate', $rec_d['expire_date']).'</div>';
        $form->addAnything(__('Expiry Date').'*', $str_input);
    }
    // member institution
    $form->addTextField('text', 'instName', __('Institution'), $rec_d['inst_name'], 'style="width: 100%;"');
    // member type
        // get mtype data related to this record from database
        $mtype_query = $dbs->query("SELECT member_type_id, member_type_name FROM mst_member_type");
        $mtype_options = array();
        while ($mtype_data = $mtype_query->fetch_row()) {
            $mtype_options[] = array($mtype_data[0], $mtype_data[1]);
        }
    $form->addSelectList('memberTypeID', __('Membership Type').'*', $mtype_options, $rec_d['member_type_id']);
    // member gender
    $gender_chbox[0] = array('1', __('Male'));
    $gender_chbox[1] = array('0', __('Female'));
    $form->addRadio('gender', __('Sex'), $gender_chbox, !empty($rec_d['gender'])?$rec_d['gender']:'0');
    // member address
    $form->addTextField('textarea', 'memberAddress', __('Address'), $rec_d['member_address'], 'rows="2" style="width: 100%;"');
    // member postal
    $form->addTextField('text', 'memberPostal', __('Postal Code'), $rec_d['postal_code'], 'style="width: 60%;"');
    // member mail address
    $form->addTextField('textarea', 'memberMailAddress', __('Mail Address'), $rec_d['member_mail_address'], 'rows="2" style="width: 100%;"');
    // member phone
    $form->addTextField('text', 'memberPhone', __('Phone Number'), $rec_d['member_phone'], 'style="width: 60%;"');
    // member fax
    $form->addTextField('text', 'memberFax', __('Fax Number'), $rec_d['member_fax'], 'style="width: 60%;"');
    // member pin
    $form->addTextField('text', 'memberPIN', __('Personal ID Number'), $rec_d['pin'], 'style="width: 100%;"');
    // member notes
    $form->addTextField('textarea', 'memberNotes', __('Notes'), $rec_d['member_notes'], 'rows="2" style="width: 100%;"');

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
                $cf_data = (isset($cfield['data']) && $cfield['data'])?$cfield['data']:array();

                // custom field processing
                if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
                    $cf_max = isset($cfield['max'])?$cfield['max']:'200';
                    $cf_width = isset($cfield['width'])?$cfield['width']:'50';
                    $form->addTextField( ($cfield['type'] == 'longtext')?'textarea':'text', $cf_dbfield, $cf_label, isset($rec_d[$cf_dbfield])?$rec_d[$cf_dbfield]:$cf_default, 'style="width: '.$cf_width.'%;" maxlength="'.$cf_max.'"');
                } else if ($cfield['type'] == 'dropdown') {
                    $form->addSelectList($cf_dbfield, $cf_label, $cf_data, isset($rec_d[$cf_dbfield])?$rec_d[$cf_dbfield]:$cf_default);
                } else if ($cfield['type'] == 'checklist') {
                    $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, isset($rec_d[$cf_dbfield])?$rec_d[$cf_dbfield]:$cf_default);
                } else if ($cfield['type'] == 'choice') {
                    $form->addRadio($cf_dbfield, $cf_label, $cf_data, isset($rec_d[$cf_dbfield])?$rec_d[$cf_dbfield]:$cf_default);
                } else if ($cfield['type'] == 'date') {
                    $form->addDateField($cf_dbfield, $cf_label, isset($rec_d[$cf_dbfield])?$rec_d[$cf_dbfield]:$cf_default);
                }
            }
        }
    }

    // member is_pending
    $form->addCheckBox('isPending', __('Pending Membership'), array( array('1', __('Yes')) ), $rec_d['is_pending']);
    // member photo
    $str_input = '';
    if ($rec_d['member_image']) {
        $str_input = '<div id="imageFilename"><a href="'.SWB.'images/persons/'.$rec_d['member_image'].'" class="openPopUp notAJAX"><strong>'.$rec_d['member_image'].'</strong></a> <a href="'.MWB.'membership/index.php" postdata="removeImage=true&mimg='.$itemID.'&img='.$rec_d['member_image'].'" loadcontainer="imageFilename" class="makeHidden removeImage">'.__('REMOVE IMAGE').'</a></div>';
    }
    $str_input .= simbio_form_element::textField('file', 'image');
    $str_input .= ' '.__('Maximum').' '.$sysconf['max_image_upload'].' KB';
    if ($sysconf['webcam'] !== false) {
      $str_input .= '<p>'.__('or take a photo').'</p>';
      $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';

      if ($sysconf['webcam'] == 'flex') {
        $str_input .= '<object id="flash_video" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" height="280px" width="100%">';
        $str_input .= '<param name="src" value="'.SWB.'lib/flex/ShotSLiMSMemberPicture.swf"/>';
        $str_input .= '<embed name="src" src="'.SWB.'lib/flex/ShotSLiMSMemberPicture.swf" height="280px" width="100%"/>';
        $str_input .= '</object>';
      }
      elseif ($sysconf['webcam'] == 'html5') {
        $str_input .= '<button id="btn_load" class="button btn" onclick="loadcam(this)">'.__('Load Camera').'</button> | ';
        $str_input .= __('Ratio:').' <select onchange="aspect(this)"><option value="1">1x1</option><option value="2" selected>2x3</option><option value="3">3x4</option></select> | ';
        $str_input .= __('Format:').' <select id="cmb_format" onchange="if(pause){set();}"><option value="png">PNG</option><option value="jpg">JPEG</option></select> | ';
        $str_input .= '<button id="btn_pause" class="button btn" onclick="snapshot(this)" disabled>'.__('Capture').'</button> | ';
        $str_input .= '<button id="btn_reset" class="button btn" onclick="$(\'textarea#base64picstring\').val(\'\');">'.__('Reset').'</button>';
        $str_input .= '<div id="my_container" style="width: 400px; height: 300px; border: 1px solid #333; position: relative;">';
        $str_input .= '<video id="my_vid" autoplay width="400" height="300" style="border: 1px solid #333; float: left; position: absolute; left: 10;"></video>';
        $str_input .= '<canvas id="my_canvas" width="400" height="300" style="border: 1px solid #333; float: left; position: absolute; left: 10; visibility: hidden;"></canvas>';
        $str_input .= '<div id="my_frame" style="  border: 1px solid #CCC; width: 160px; height: 240px; z-index: 2; margin: auto; position: absolute; top: 0; bottom: 0; left: 0; right: 0;"></div></div>';
        $str_input .= '<canvas id="my_preview" width="160" height="240" style="width: 160px; height: 240px; border: 1px solid #444; display: none;"></canvas>';
      }
    }

    $form->addAnything(__('Photo'), $str_input);

    // member email
    $form->addTextField('text', 'memberEmail', __('E-mail'), $rec_d['member_email'], 'style="width: 40%;"');
    // member password
    $form->addTextField('password', 'memberPasswd', __('New Password'), null, 'style="width: 40%;"');
    // member password confirmation
    $form->addTextField('password', 'memberPasswd2', __('Confirm New Password'), null, 'style="width: 40%;"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'
            .'<div style="float: left; width: 80%;">'.__('You are going to edit member data').' : <b>'.$rec_d['member_name'].'</b> <br />'.__('Last Updated').' '.$rec_d['last_update'].' '.$expired_message
            .'<div>'.__('Leave Password field blank if you don\'t want to change the password').'</div>'
            .'</div>';
            if ($rec_d['member_image']) {
                if (file_exists(IMGBS.'persons/'.$rec_d['member_image'])) {
                    echo '<div id="memberImage" style="float: right;"><img src="'.SWB.'lib/minigalnano/createthumb.php?filename=../../images/persons/'.urlencode($rec_d['member_image']).'&amp;width=180&amp;timestamp='.date('his').'" alt="'.$rec_d['member_name'].'" /></div>';
                }
            }
        echo '</div>'."\n";
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* MEMBERSHIP LIST */
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
    } else {
        $datagrid->setSQLColumn('m.member_id AS \''.__('Member ID').'\'',
            'm.member_name AS \''.__('Member Name').'\'',
            'mt.member_type_name AS \''.__('Membership Type').'\'',
            'm.member_email AS \''.__('E-mail').'\'',
            'm.last_update AS \''.__('Last Updated').'\'');
    }
    $datagrid->setSQLorder('member_name ASC');

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
    $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if ((isset($_GET['keywords']) AND $_GET['keywords']) OR isset($_GET['expire'])) {
        echo '<div class="infoBox">';
        if (isset($_GET['expire'])) {
            echo '<b style="color: #FF0000;">'.__('Expired Member List').'</b><hr size="1" />';
            echo '<div><input type="button" value="'.__('Extend Selected Member(s)').'" onclick="javascript: if (confirm(\''.__('Are you sure to EXTEND membership for selected members?').'\')) { $(\'#mainContent\').simbioAJAX(\''.MWB.'membership/index.php?expire=1\', { method: \'post\', addData: $(\'#memberList\').serialize() + \'&batchExtend=true\' } ); }" class="button" /></div>';
            if (isset($_GET['numExtended']) AND $_GET['numExtended'] > 0) {
                echo '<div><strong>'.$_GET['numExtended'].'</strong> '.__('members extended!').'</div>'; //mfc
            }
        }
        if (isset($_GET['keywords']) AND $_GET['keywords']) {
            echo __('Found').' '.$datagrid->num_rows.' '.__('from your search with keyword').' : "'.$_GET['keywords'].'"'; //mfc
        }
        echo '</div>';
    }

    echo $datagrid_result;
}
/* main content end */
