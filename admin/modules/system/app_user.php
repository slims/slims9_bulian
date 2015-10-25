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

/* REMOVE IMAGE */
if (isset($_POST['removeImage']) && isset($_POST['uimg']) && isset($_POST['img'])) {
  $_delete = $dbs->query(sprintf('UPDATE user SET user_image=NULL WHERE user_id=%d', $_POST['uimg']));
  if ($_delete) {
    @unlink(sprintf(IMGBS.'persons/%s',$_POST['img']));
    exit('<script type="text/javascript">alert(\''.$_POST['img'].' successfully removed!\'); $(\'#userImage, #imageFilename\').remove();</script>');
  }
  exit();
}
/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $userName = trim(strip_tags($_POST['userName']));
    $realName = trim(strip_tags($_POST['realName']));
    $passwd1 = trim($_POST['passwd1']);
    $passwd2 = trim($_POST['passwd2']);
    // check form validity
    if (empty($userName) OR empty($realName)) {
        utility::jsAlert(__('User Name or Real Name can\'t be empty'));
        exit();
    } else if (($userName == 'admin' OR $realName == 'Administrator') AND $_SESSION['uid'] != 1) {
        utility::jsAlert(__('Login username or Real Name is probihited!'));
        exit();
    } else if (($passwd1 AND $passwd2) AND ($passwd1 !== $passwd2)) {
        utility::jsAlert(__('Password confirmation does not match. See if your Caps Lock key is on!'));
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

        if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
          // create upload object
          $upload = new simbio_file_upload();
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
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('user', $data, 'user_id='.$updateRecordID);
            if ($update) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' update user data ('.$data['realname'].') with username ('.$data['username'].')');
                utility::jsAlert(__('User Data Successfully Updated'));
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', $_SESSION['realname'].' upload image file '.$upload->new_filename);
                        utility::jsAlert(__('Image Uploaded Successfully'));
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')');
                        utility::jsAlert(__('Image FAILED to upload'));
                    }
                }
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.$.ajaxHistory[0].url);</script>';
            } else { utility::jsAlert(__('User Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('user', $data)) {
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' add new user ('.$data['realname'].') with username ('.$data['username'].')');
                utility::jsAlert(__('New User Data Successfully Saved'));
                // upload status alert
                if (isset($upload_status)) {
                    if ($upload_status == UPLOAD_SUCCESS) {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', $_SESSION['realname'].' upload image file '.$upload->new_filename);
                        utility::jsAlert(__('Image Uploaded Successfully'));
                    } else {
                        // write log
                        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system/user', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$upload->new_filename.', with error ('.$upload->error.')');
                        utility::jsAlert(__('Image FAILED to upload'));
                    }
                }
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsAlert(__('User Data FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error); }
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
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' DELETE user ('.$user_d[1].') with username ('.$user_d[0].')');
        }
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

if (!$changecurrent) {
/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner userIcon">
	<div class="per_title">
	    <h2><?php echo __('Librarian & System Users'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>/system/app_user.php" class="btn btn-default"><i class="glyphicon glyphicon-user"></i>&nbsp;<?php echo __('User List'); ?></a>
      <a href="<?php echo MWB; ?>system/app_user.php?action=detail" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New User'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>system/app_user.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
  </div>
</div>
</fieldset>
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
    $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
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
    $form->addTextField('text', 'userName', __('Login Username').'*', $rec_d['username'], 'style="width: 50%;"');
    // user real name
    $form->addTextField('text', 'realName', __('Real Name').'*', $rec_d['realname'], 'style="width: 50%;"');
    // user type
    $utype_options = array();
    foreach ($sysconf['system_user_type'] as $id => $name) {
      $utype_options[] = array($id, $name);
    }
    $form->addSelectList('userType', __('User Type').'*', $utype_options, $rec_d['user_type']);
    // user e-mail
    $form->addTextField('text', 'eMail', __('E-Mail'), $rec_d['email'], 'style="width: 50%;"');
    // social media link
    $str_input = '';
    $social_media = array();
    if ($rec_d['social_media']) {
      $social_media = @unserialize($rec_d['social_media']);
    }
    foreach ($sysconf['social'] as $id => $social) {
      $str_input .= '<div class="social-input"><span class="social-label">'.$social.'</span><span class="social-form"><input type="text" name="social['.$id.']" value="'.(isset($social_media[$id])?$social_media[$id]:'').'" placeholder="'.$social.'" /></span></div>'."\n";
    }
    $form->addAnything(__('Social Media'), $str_input);
    // user photo
    $str_input = '';
    if ($rec_d['user_image']) {
      $str_input = '<div id="imageFilename"><a href="'.SWB.'images/persons/'.$rec_d['user_image'].'" class="openPopUp notAJAX"><strong>'.$rec_d['user_image'].'</strong></a> <a href="'.MWB.'system/app_user.php" postdata="removeImage=true&uimg='.$itemID.'&img='.$rec_d['user_image'].'" loadcontainer="imageFilename" class="makeHidden removeImage">'.__('REMOVE IMAGE').'</a></div>';
    }
    $str_input .= simbio_form_element::textField('file', 'image');
    $str_input .= ' '.__('Maximum').' '.$sysconf['max_image_upload'].' KB';
    if ($sysconf['webcam'] !== false) {
      $str_input .= '<p>'.__('or take a photo').'</p>';
      $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';
      $str_input .= '<button id="btn_load" onclick="loadcam(this)">'.__('Load Camera').'</button> | ';
      $str_input .= __('Ratio:').' <select onchange="aspect(this)"><option value="1">1x1</option><option value="2" selected>2x3</option><option value="3">3x4</option></select> | ';
      $str_input .= __('Format:').' <select id="cmb_format" onchange="if(pause){set();}"><option value="png">PNG</option><option value="jpg">JPEG</option></select> | ';
      $str_input .= '<button id="btn_pause" onclick="snapshot(this)" disabled>'.__('Capture').'</button> | ';
      $str_input .= '<button id="btn_reset" onclick="$(\'textarea#base64picstring\').val(\'\');">'.__('Reset').'</button>';
      $str_input .= '<div id="my_container" style="width: 400px; height: 300px; border: 1px solid #333; position: relative;">';
      $str_input .= '<video id="my_vid" autoplay width="400" height="300" style="border: 1px solid #333; float: left; position: absolute; left: 10;"></video>';
      $str_input .= '<canvas id="my_canvas" width="400" height="300" style="border: 1px solid #333; float: left; position: absolute; left: 10; visibility: hidden;"></canvas>';
      $str_input .= '<div id="my_frame" style="  border: 1px solid #CCC; width: 160px; height: 240px; z-index: 2; margin: auto; position: absolute; top: 0; bottom: 0; left: 0; right: 0;"></div></div>';
      $str_input .= '<canvas id="my_preview" width="160" height="240" style="width: 160px; height: 240px; border: 1px solid #444; display: none;"></canvas>';
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
        $form->addCheckBox('groups', __('Group(s)'), $group_options, unserialize($rec_d['groups']));
    }
    // user password
    $form->addTextField('password', 'passwd1', __('New Password').'*', '', 'style="width: 50%;"');
    // user password confirm
    $form->addTextField('password', 'passwd2', __('Confirm New Password').'*', '', 'style="width: 50%;"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="per_title"><h2>'.__('Change User Profiles').'</h2></div>';
        echo '<div class="infoBox"><div style="float: left; width: 80%;">'.__('You are going to edit user profile'),' : <b>'.$rec_d['realname'].'</b> <br />'.__('Last Update').'&nbsp;'.$rec_d['last_update'].'
          <div>'.__('Leave Password field blank if you don\'t want to change the password').'</div></div>';
        if ($rec_d['user_image']) {
          if (file_exists(IMGBS.'persons/'.$rec_d['user_image'])) {
            echo '<div id="userImage" style="float: right;"><img src="'.SWB.'lib/minigalnano/createthumb.php?filename=../../images/persons/'.urlencode($rec_d['user_image']).'&amp;width=180&amp;timestamp='.date('his').'" style="border: 1px solid #999999" /></div>';
          }
        }
        echo '</div>';
    }
    // print out the form object
    echo $form->printOut();
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
?>
