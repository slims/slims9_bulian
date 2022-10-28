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

use SLiMS\DB;

/* User Group Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// only administrator have privileges to modify user groups
if ($_SESSION['uid'] != 1) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $groupName = trim(strip_tags($_POST['groupName']));
    // check form validity
    if (empty($groupName)) {
        utility::jsToastr(__('User Group'), __('Group name can\'t be empty'), 'error');
    } else {
        $data['group_name'] = $dbs->escape_string($groupName);
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('user_group', $data, 'group_id='.$updateRecordID);
            if ($update) {
                DB::getInstance()->prepare("DELETE FROM group_access WHERE group_id=?")->execute([$updateRecordID]);
                // set group privileges
                if (isset($_POST['read'])) {
                    $group_access = DB::getInstance()->prepare("INSERT INTO group_access VALUES (?,?,?,?,?)");
                    foreach ($_POST['read'] as $module) {
                        // check write privileges
                        $is_write = 0;
                        if (isset($_POST['write'])) {
                            foreach ($_POST['write'] as $module_write) {
                                if ($module_write == $module) {
                                    $is_write = 1;
                                }
                            }
                        }

                        // menus
                        $menus = null;
                        if (isset($_POST['menus']))
                            if (isset($_POST['menus'][(int)$module])) $menus = json_encode($_POST['menus'][(int)$module]);

                        $group_access->execute([$updateRecordID, $module, $menus, 1, $is_write]);
                    }
                }
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' update group data ('.$groupName.')', 'User group', 'Update');
                utility::jsToastr(__('User Group'), __('Group Data Successfully Updated'), 'success');

                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.$.ajaxHistory[0].url);</script>';
            } else { utility::jsToastr(__('User Group'), __('Group Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('user_group', $data);
            if ($insert) {
                $group_id = $dbs->insert_id;
                // set group privileges
                if (isset($_POST['read'])) {
                    $group_access = DB::getInstance()->prepare("INSERT INTO group_access VALUES (?,?,?,?,?)");
                    foreach ($_POST['read'] as $module) {

                        // check write privileges
                        $is_write = 0;
                        if (isset($_POST['write'])) {
                            foreach ($_POST['write'] as $module_write) {
                                if ($module_write == $module) {
                                    $is_write = 1;
                                }
                            }
                        }

                        // menus
                        $menus = null;
                        if (isset($_POST['menus']))
                            if (isset($_POST['menus'][(int)$module])) $menus = json_encode($_POST['menus'][(int)$module]);

                        
                        $group_access->execute([$group_id, $module, $menus, 1, $is_write]);
                    }
                }

                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' add new group ('.$groupName.')', 'User group', 'Add');
                utility::jsToastr(__('User Group'), __('New Group Data Successfully Saved'), 'success');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr(__('User Group'), __('Group Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error');}
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
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
        // get group data
        $group_q = $dbs->query('SELECT group_name FROM user_group WHERE group_id='.$itemID);
        $group_d = $group_q->fetch_row();
        if (!$sql_op->delete('user_group', 'group_id='.$itemID)) {
            $error_num++;
        } else {
            // also delete all records related to this data
            // delete group privileges
            $dbs->query('DELETE FROM group_access WHERE group_id='.$itemID);
            // write log
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' DELETE group ('.$group_d[0].')', 'User group', 'Delete');
        }
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsToastr(__('User Group'), __('All Data Successfully Deleted'), 'success');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsToastr(__('User Group'), __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'), 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner userGroupIcon">
	<div class="per_title">
	  <h2><?php echo __('User Group'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>system/user_group.php" class="btn btn-default"><?php echo __('Group List'); ?></a>
      <a href="<?php echo MWB; ?>system/user_group.php?action=detail" class="btn btn-default"><?php echo __('Add New Group'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>system/user_group.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
  </div>
</div>
</div>
<?php
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM user_group WHERE group_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['group_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // group
    $form->addTextField('text', 'groupName', __('Group Name').'*', $rec_d['group_name']??'', 'style="width: 60%;" class="form-control"');
    // privileges
        // get group access data
        $priv_data = array();
        $rec_q = $dbs->query('SELECT * FROM group_access WHERE group_id='.(!empty($rec_d['group_id'])?$rec_d['group_id']:0));
        while ($access_data = $rec_q->fetch_assoc()) {
            $priv_data[$access_data['module_id']]['r'] = $access_data['r'];
            $priv_data[$access_data['module_id']]['w'] = $access_data['w'];
            $priv_data[$access_data['module_id']]['menus'] = json_decode($access_data['menus'] ?? '{}', true);
        }
    $priv_table = '';
    include 'module_priv_form_adv.inc.php';
    $form->addAnything(__('Privileges'), $priv_table);

    // edit mode messagge
    if ($form->edit_mode) {
        // print out the object
        echo '<div class="infoBox">'.__('You are going to edit Group data').' : <b>'.$rec_d['group_name'].'</b>  <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'; //mfc
    }
    echo $form->printOut();
} else {
    /* GROUP LIST */
    // table spec
    $table_spec = 'user_group AS ug';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('ug.group_id',
        'ug.group_name AS \''.__('Group Name').'\'',
        'ug.last_update AS \''.__('Last Update').'\'');
    $datagrid->setSQLorder('group_name ASC');

    // is there any search
    $criteria = 'ug.group_id != 1';
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $criteria .= " AND ug.group_name LIKE '%$keywords%'";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, true);
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
