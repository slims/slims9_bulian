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

/* Staffs/Application Users Management section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

function getUserType($obj_db, $array_data, $col) {
  global $sysconf;
  if (isset($sysconf['system_user_type'][$array_data[$col]])) {
    return $sysconf['system_user_type'][$array_data[$col]];
  }
}

// check if we want to change current user profile
$changecurrent = false;
if (isset($_GET['changecurrent'])) {
    $changecurrent = true;
}

if (!$changecurrent) {
    // only administrator have privileges add/edit users
    if ($_SESSION['uid'] != 1) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
}

/**
 * Verify Two-factor authentication (2FA)
 */
if (isset($_POST['secret_code']) && isset($_POST['verify_code'])) {
    $secret = utility::filterData('secret_code', 'post', true, true, true);
    $otp = OTPHP\TOTP::createFromSecret($secret);
    $isOk = $otp->verify(trim($_POST['verify_code']));
    if ($isOk) {
        // save to session for next purpose
        $_SESSION['2fa_secret'] = $secret;
        toastr(__('Code verified!'))->success();
    } else {
        unset($_SESSION['2fa_secret']);
        toastr(__('Invalid verification code!'))->error();
    }
    exit;
}

if (isset($_POST['updateRecordID']) && isset($_POST['disable2fa']) && isset($_GET['diable2fa'])) {
    $uid = (int)utility::filterData('updateRecordID', 'post', true, true, true);
    $arr = explode(':', $uid);
    if ($_SESSION['uid'] == 1 || $uid == $_SESSION['uid']) {
        $update = $dbs->query(sprintf("update user set 2fa = null where user_id = '%s'", $uid));
        if ($update) {
            echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.$.ajaxHistory[0].url);</script>';
            toastr(__('Two-factor authentication has been disabled.'))->success();
        } else {
            toastr(__('Upss... something wrong!'))->error();
        }
    } else {
        toastr(__('You don\'t have enough privileges to view this section'))->error();
    }
    exit;
}

/* REMOVE IMAGE */
if (isset($_POST['removeImage']) && isset($_POST['uimg']) && isset($_POST['img'])) {
  // validate post image
  $user_id = $_SESSION['uid'] > 1 ? $_SESSION['uid'] : utility::filterData('uimg', 'post', true, true, true);
  $image_name = utility::filterData('img', 'post', true, true, true);

  $query_image = $dbs->query("SELECT user_id FROM user WHERE user_id='{$user_id}' AND user_image='{$image_name}'");
  if ($query_image->num_rows > 0) {
    $_delete = $dbs->query(sprintf('UPDATE user SET user_image=NULL WHERE user_id=%d', $_POST['uimg']));
    if ($_delete) {
      // Change upict
      $_SESSION['upict'] = 'person.png';
      $postImage = stripslashes($_POST['img']);
      $postImage = str_replace('/', '', $postImage);
      @unlink(sprintf(IMGBS.'persons/%s', $postImage));
      exit('<script type="text/javascript">alert(\''.str_replace('{imageFilename}', $postImage, __('{imageFilename} successfully removed!')).'\'); $(\'#userImage, #imageFilename\').remove();</script>');
    }
  }
  exit();
}
/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $userName = $_SESSION['uid'] > 1 ? $_SESSION['uname'] : trim(strip_tags($_POST['userName']));
    $realName = trim(strip_tags($_POST['realName']));
    $passwd1 = trim($_POST['passwd1']);
    $passwd2 = trim($_POST['passwd2']);
    // check form validity
    if (empty($userName) OR empty($realName)) {
        toastr(__('User Name or Real Name can\'t be empty'))->error();
        exit();
    } else if (($userName == 'admin' OR $realName == 'Administrator') AND $_SESSION['uid'] != 1) {
        toastr(__('Login username or Real Name is probihited!'))->error();
        exit();
    } else if (($passwd1 AND $passwd2) AND ($passwd1 !== $passwd2)) {
        toastr(__('Password confirmation does not match. See if your Caps Lock key is on!'))->error();
        exit();
    } else if (!simbio_form_maker::isTokenValid()) {
        toastr(__('Invalid form submission token!'))->error();
        exit();
    } else {
        $data['username'] = $dbs->escape_string(trim($userName));
        $data['realname'] = $dbs->escape_string(trim($realName));
        $data['user_type'] = (integer)$_POST['userType'];
        $data['email'] = $dbs->escape_string(trim($_POST['eMail']));
        $social_media = array();
        foreach ($_POST['social'] as $id => $social) {
          $social_val = $dbs->escape_string(trim($social));
          if ($social_val != '') {
            $social_media[$id] = $social_val;
          }
        }
        if ($social_media) {
          $data['social_media'] = $dbs->escape_string(serialize($social_media));
        }
        if (isset($_POST['noChangeGroup'])) {
            // parsing groups data
            $groups = '';
            if (isset($_POST['groups']) AND !empty($_POST['groups'])) {
                $groups = serialize($_POST['groups']);
            } else {
                $groups = 'literal{NULL}';
            }
            $data['groups'] = trim($groups);
        }
        if (($passwd1 AND $passwd2) AND ($passwd1 === $passwd2)) {
            $data['passwd'] = password_hash($passwd2, PASSWORD_BCRYPT);
            // $data['passwd'] = 'literal{MD5(\''.$passwd2.'\')}';
        }
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        // save 2fa secret to database
        if (isset($_SESSION['2fa_secret'])) {
            $data['2fa'] = $_SESSION['2fa_secret'];
            unset($_SESSION['2fa_secret']);
        }

        // create upload object
        $upload = new simbio_file_upload();
        if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
          $upload->setAllowableFormat($sysconf['allowed_images']);
          $upload->setMaxSize($sysconf['max_image_upload']*1024); // approx. 100 kb
          $upload->setUploadDir(IMGBS.'persons');
          // give new name for upload file
          $new_filename = 'user_'.str_replace(array(',', '.', ' ', '-'), '_', strtolower($data['username']));
          $upload_status = $upload->doUpload('image', $new_filename);
          if ($upload_status == UPLOAD_SUCCESS) {
            $data['user_image'] = $dbs->escape_string($upload->new_filename);
          }
        } else if (!empty($_POST['base64picstring'])) {
            list($filedata, $filedom) = explode('#image/type#', $_POST['base64picstring']);
          $filedata = base64_decode($filedata);
          $fileinfo = getimagesizefromstring($filedata);
          $valid = strlen($filedata)/1024 < $sysconf['max_image_upload'];
          $valid = (!$fileinfo || $valid === false) ? false : in_array($fileinfo['mime'], $sysconf['allowed_images_mimetype']);
			    $new_filename = 'user_'.str_replace(array(',', '.', ' ', '-'), '_', strtolower($data['username'])).'.'.strtolower($filedom);

			    if ($valid AND file_put_contents(IMGBS.'persons/'.$new_filename, $filedata)) {
				    $data['user_image'] = $dbs->escape_string($new_filename);
				    if (!defined('UPLOAD_SUCCESS')) define('UPLOAD_SUCCESS', 1);
				    $upload_status = UPLOAD_SUCCESS;
			    }
		    }

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = $_SESSION['uid'] > 1 ? $_SESSION['uid'] : (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('user', $data, 'user_id='.$updateRecordID);
            if ($update) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' update user data ('.$data['realname'].') with username ('.$data['username'].')', 'User', 'Update');
                toastr(__('User Data Successfully Updated'))->success();
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // Change upict
                        $_SESSION['upict'] = $data['user_image'];
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', $_SESSION['realname'].' upload image file '.$upload->new_filename, 'User image', 'Upload');
                        toastr(__('Image Uploaded Successfully'))->success();
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')', 'User image', 'Fail');
                        toastr(__('Image FAILED to upload'))->error();
                    }
                }
                // echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.$.ajaxHistory[0].url);</script>';
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'\');</script>';
            } else { toastr(__('User Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('user', $data)) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' add new user ('.$data['realname'].') with username ('.$data['username'].')', 'User', 'Add');
                toastr(__('New User Data Successfully Saved'))->success();
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // Change upict
                        $_SESSION['upict'] = $data['user_image'];
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', $_SESSION['realname'].' upload image file '.$upload->new_filename, 'User image', 'Upload');
                        toastr(__('Image Uploaded Successfully'))->success();
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')', 'User image', 'Fail');
                        toastr(__('Image FAILED to upload'))->error();
                    }
                }
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { toastr(__('User Data FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error)->error(); }
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
        // get user data
        $user_q = $dbs->query('SELECT username, realname FROM user WHERE user_id='.$itemID);
        $user_d = $user_q->fetch_row();
        if (!$sql_op->delete('user', "user_id='$itemID'")) {
            $error_num++;
        } else {
            // write log
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' DELETE user ('.$user_d[1].') with username ('.$user_d[0].')', 'User', 'Delete');
        }
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

if (!$changecurrent) {
/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner userIcon">
	<div class="per_title">
	    <h2><?php echo __('Librarian & System Users'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>/system/app_user.php" class="btn btn-default"><?php echo __('User List'); ?></a>
      <a href="<?php echo MWB; ?>system/app_user.php?action=detail" class="btn btn-default"><?php echo __('Add New User'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>system/app_user.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
  </div>
</div>
</div>
<?php
/* search form end */
}

/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write) AND !$changecurrent) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    // try query
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    if ($changecurrent) {
        $itemID = (integer)$_SESSION['uid'];
    }
    $rec_q = $dbs->query('SELECT * FROM user WHERE user_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        if (!$changecurrent) {
            // form record id
            $form->record_id = $itemID;
        } else {
            $form->addHidden('updateRecordID', $itemID);
            $form->back_button = false;
        }
        // form record title
        $form->record_title = $rec_d['realname'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="btn btn-default"';
    }

    /* Form Element(s) */
    // user name
    if ($_SESSION['uid'] > 1) {
        $form->addAnything(__('Login Username'), '<strong>'.$rec_d['username'].'</strong>');
    } else {
        $form->addTextField('text', 'userName', __('Login Username').'*', $rec_d['username']??'', 'style="width: 50%;" class="form-control"');
    }

    // user real name
    $form->addTextField('text', 'realName', __('Real Name').'*', $rec_d['realname']??'', 'style="width: 50%;" class="form-control"');
    // user type
    $utype_options = array();
    foreach ($sysconf['system_user_type'] as $id => $name) {
      $utype_options[] = array($id, $name);
    }
    $form->addSelectList('userType', __('User Type').'*', $utype_options, $rec_d['user_type']??'','class="form-control col-3"');
    // user e-mail
    $form->addTextField('text', 'eMail', __('E-Mail'), $rec_d['email']??'', 'style="width: 50%;" class="form-control"');
    // social media link
    $str_input = '';
    $social_media = array();
    if (isset($rec_d['social_media'])) {
      $social_media = @unserialize($rec_d['social_media']);
    }
    $str_input = '<div class="row">';
    foreach ($sysconf['social'] as $id => $social) {
      $str_input .= '<div class="social-input col-4"><span class="social-form"><input type="text" name="social['.$id.']" value="'.(isset($social_media[$id])?$social_media[$id]:'').'" placeholder="'.$social.'" class="form-control" /></span></div>'."\n";
    }
    $str_input .= '</div>';
    $form->addAnything(__('Social Media'), $str_input);
    // user photo
    $str_input = '';
    if (isset($rec_d['user_image'])) {
      $str_input = '<div id="imageFilename"><a href="'.SWB.'images/persons/'.$rec_d['user_image'].'" class="openPopUp notAJAX"><strong>'.$rec_d['user_image'].'</strong></a> <a href="'.MWB.'system/app_user.php" postdata="removeImage=true&uimg='.$itemID.'&img='.$rec_d['user_image'].'" loadcontainer="imageFilename" class="makeHidden removeImage">'.__('REMOVE IMAGE').'</a></div>';
    }
    $str_input .= simbio_form_element::textField('file', 'image');
    $str_input .= ' '.__('Maximum').' '.$sysconf['max_image_upload'].' KB';
    if ($sysconf['webcam'] !== false) {
        $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';
        $str_input .= '<p>'.__('or take a photo').'</p>';
        $str_input .= '<div class="form-inline">';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button type="button" id="btn_load" class="btn btn-primary" onclick="loadcam(this)">'.__('Load Camera').'</button>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<select class="form-control" onchange="aspect(this)"><option value="1">1x1</option><option value="2" selected>2x3</option><option value="3">3x4</option></select>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<select class="form-control" id="cmb_format" onchange="if(pause){set();}"><option value="png">PNG</option><option value="jpg">JPEG</option></select>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button type="button" id="btn_pause" class="btn btn-primary" onclick="snapshot(this)" disabled>'.__('Capture').'</button>';
        $str_input .= '</div>';
        $str_input .= '<div class="form-group pr-2">';
        $str_input .= '<button type="button" id="btn_reset" class="btn btn-danger" onclick="resetvalue()">'.__('Reset').'</button>';
        $str_input .= '</div>';
        $str_input .= '</div>';
        $str_input .= '<div id="my_container" class="mt-2" style="width: 400px; height: 300px; border: 1px solid #f4f4f4; position: relative;">';
        $str_input .= '<video id="my_vid" autoplay width="400" height="300" style="float: left; position: absolute; left: 10;"></video>';
        $str_input .= '<canvas id="my_canvas" width="400" height="300" style="float: left; position: absolute; left: 10; visibility: hidden;"></canvas>';
        $str_input .= '<div id="my_frame" style="border: 1px solid #CCC; width: 160px; height: 240px; z-index: 2; margin: auto; position: absolute; top: 0; bottom: 0; left: 0; right: 0;"></div></div>';
        $str_input .= '<canvas id="my_preview" width="160" height="240" style="width: 160px; height: 240px; border: 1px solid #f4f4f4; display: none;"></canvas>';

    }

    $form->addAnything(__('User Photo'), $str_input);

    // user group
    // only appear by user who hold system module privileges
    if (!$changecurrent AND $can_read AND $can_write) {
        // add hidden element as a flag that we dont change group data
        $form->addHidden('noChangeGroup', '1');
        // user group
        $group_query = $dbs->query('SELECT group_id, group_name FROM
            user_group WHERE group_id != 1');
        // initiliaze group options
        $group_options = array();
        while ($group_data = $group_query->fetch_row()) {
            $group_options[] = array($group_data[0], $group_data[1]);
        }
        $form->addCheckBox('groups', __('Group(s)'), $group_options, unserialize($rec_d['groups']??''));
    }
    // user password
    $form->addTextField('password', 'passwd1', __('New Password').'*', '', 'style="width: 50%;" class="form-control"');
    // user password confirm
    $form->addTextField('password', 'passwd2', __('Confirm New Password').'*', '', 'style="width: 50%;" class="form-control"');

    // Two Factor Authentication
    if (!empty($rec_d) && $rec_d['user_id'] === $_SESSION['uid']) {
        $otp = OTPHP\TOTP::generate();
        $otp->setLabel(config('library_name'));
        $secret = $otp->getSecret();
        // generate qrcode
        $render = new BaconQrCode\Renderer\ImageRenderer(
            new BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
            new BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new BaconQrCode\Writer($render);
        $qrcode = $writer->writeString($otp->getProvisioningUri());
        $otp_html = '';
        if (($rec_d['2fa'] ?? false)) {
            $otp_html .= '<div class="alert alert-success d-flex justify-content-between"><span>🔐 ' . __('Two Factor Authentication enabled.') . '</span><div><button formaction="'.$_SERVER['PHP_SELF'].'?diable2fa=1&changecurrent=1" type="submit" name="disable2fa" value="1" class="btn btn-danger btn-sm">' . __('Disable It') . '</button></div></div>';
        }
        list($otp_app, $verification_code, $verify) = [
            str_replace('{link}', '<a href="https://play.google.com/store/apps/details?id=org.fedorahosted.freeotp" target="_blank">FreeOTP</a>', __('Scan this QRcode with your authenticator (e.g. Google Authenticator or {link}) <br> and enter verification code below to enable Two Factor Authentication.')),
            __('Verification code'),
            __('Verify')
        ];
        $otp_html .= <<<HTML
        <div style="display:flex; align-items:center">
            <div>{$qrcode}</div>
            <div>
                <div class="my-3">
                    {$otp_app}
                </div>
                <input form="formVerify2fa" type="hidden" name="secret_code" value="{$secret}">
                <div class="text-muted">{$verification_code}</div>
                <div class="input-group mb-3">
                    <input form="formVerify2fa" type="text" name="verify_code" class="form-control mr-0" placeholder="Enter code from authenticator" aria-label="Enter code from authenticator" aria-describedby="button-addon2">
                    <div class="input-group-append">
                        <button form="formVerify2fa" class="btn btn-outline-secondary" type="submit" id="button-addon2">{$verify}</button>
                    </div>
                </div>
            </div>
        </div>
        HTML;
        $form->addAnything(__('Enable Two Factor Authentication'), $otp_html);
    }

    // ability to disable Two Factor Authentication for administrator
    if ($_SESSION['uid'] == 1 && ($rec_d['2fa'] ?? false)) {
        $otp_html = '<button formaction="'.$_SERVER['PHP_SELF'].'?diable2fa=1" type="submit" name="disable2fa" value="1" class="btn btn-danger">'.__('Disable Two Factor Authentication').'</button>';
        $form->addAnything(__('Disable Two Factor Authentication'), $otp_html);
    }

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="per_title"><h2>'.__('Change User Profiles').'</h2></div>';
        echo '<div class="infoBox row"><div class="col-6">'.__('You are going to edit user profile'),' : <b>'.$rec_d['realname'].'</b> <br />'.__('Last Update').'&nbsp;'.$rec_d['last_update'].'
        <div>'.__('Leave Password field blank if you don\'t want to change the password').'</div></div>';
        if ($rec_d['user_image']) {
            if (file_exists(IMGBS.'persons/'.$rec_d['user_image'])) {
            echo '<div class="col-6"><div id="userImage" class="float-right"><img src="../images/persons/'.urlencode($rec_d['user_image']).'?'.date('this').'" class="w-100"/></div></div>';
            }
        }
        echo '</div>';
    }
    // print out the form object
    echo $form->printOut();
    echo '<form id="formVerify2fa" target="blindSubmit" method="post" action="'.$_SERVER['PHP_SELF'].'?changecurrent=true"><form>';
} else {
    // only administrator have privileges to view user list
    if (!($can_read AND $can_write) OR $_SESSION['uid'] != 1) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }

    /* USER LIST */
    // table spec
    $table_spec = 'user AS u';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('u.user_id',
            'u.realname AS \''.__('Real Name').'\'',
            'u.username AS \''.__('Login Username').'\'',
            'u.user_type AS \''.__('User Type').'\'',
            'u.last_login AS \''.__('Last Login').'\'',
            'u.last_update AS \''.__('Last Update').'\'');
        $col = 3;
    } else {
        $datagrid->setSQLColumn('u.realname AS \''.__('Real Name').'\'',
            'u.username AS \''.__('Real Name').'\'',
            'u.user_type AS \''.__('User Type').'\'',
            'u.last_login AS \''.__('Last Login').'\'',
            'u.last_update AS \''.__('Last Update').'\'');
        $col = 2;
    }
    $datagrid->modifyColumnContent($col, 'callback{getUserType}');
    $datagrid->setSQLorder('username ASC');

    // is there any search
    $criteria = 'u.user_id != 1 ';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $criteria .= " AND (u.username LIKE '%$keywords%' OR u.realname LIKE '%$keywords%')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
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
?>
