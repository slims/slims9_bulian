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

/* Collection Type Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
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

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $collTypeName = trim(strip_tags($_POST['collTypeName']));
    // check form validity
    if (empty($collTypeName)) {
        utility::jsToastr(__('Collection Type'),__('Collection type name can\'t be empty'),'error');
        exit();
    } else {
        $data['coll_type_name'] = $dbs->escape_string($collTypeName);
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
            $update = $sql_op->update('mst_coll_type', $data, 'coll_type_id='.$updateRecordID);
            if ($update) {
                utility::jsToastr(__('Collection Type'),__('Colllection Type Data Successfully Updated'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { utility::jsToastr(__('Collection Type'),__('Colllection Type Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('mst_coll_type', $data);
            if ($insert) {
                utility::jsToastr(__('Collection Type'),__('New Colllection Type Data Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr(__('Collection Type'),__('Author Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
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
        // check if this item data still have an item
        $item_q = $dbs->query('SELECT ct.coll_type_name, COUNT(item_id) FROM item AS i
            LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
            WHERE i.coll_type_id='.$itemID.' GROUP BY i.coll_type_id');
        $item_d = $item_q->fetch_row();
        if ($item_d[1] < 1) {
            if (!$sql_op->delete('mst_coll_type', "coll_type_id=$itemID")) {
                $error_num++;
            }
        } else {
            $still_have_item[] = sprintf(__('Collection type %s still used by %s items'),$item_d[0],$item_d[1]);
            $error_num++;
        }
    }

    if ($still_have_item) {
        $undeleted_coll_types = '';
        foreach ($still_have_item as $coll_type) {
            $undeleted_coll_types .= $coll_type."\n";
        }
        utility::jsToastr(__('Collection Type'),__('Below data can not be deleted:').$undeleted_coll_types,'error');
        exit();
    }
    // error alerting
    if ($error_num == 0) {
        utility::jsToastr(__('Collection Type'),__('All Data Successfully Deleted'),'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsToastr(__('Collection Type'),__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'warning');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Collection Type'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/coll_type.php" class="btn btn-default"><?php echo __('Collection Type List'); ?></a>
      <a href="<?php echo MWB; ?>master_file/coll_type.php?action=detail" class="btn btn-default"><?php echo __('Add New Collection Type'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>master_file/coll_type.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query("SELECT * FROM mst_coll_type WHERE coll_type_id=$itemID");
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
        $form->record_title = $rec_d['coll_type_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // coll_type_name
    $form->addTextField('text', 'collTypeName', __('Collection Type').'*', $rec_d['coll_type_name']??'', 'style="width: 60%;" class="form-control"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit collection type data').' : <b>'.$rec_d['coll_type_name'].'</b>  <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* COLLECTION TYPE LIST */
    // table spec
    $table_spec = 'mst_coll_type AS ct';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('ct.coll_type_id', 'ct.coll_type_name AS \''.__('Collection Type').'\'', 'ct.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('ct.coll_type_name AS \''.__('Collection Type').'\'', 'ct.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('coll_type_name ASC');

    // change the record order
    if (isset($_GET['fld']) AND isset($_GET['dir'])) {
        $datagrid->setSQLorder("'".urldecode($_GET['fld'])."' ".$dbs->escape_string($_GET['dir']));
    }

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $datagrid->setSQLCriteria("ct.coll_type_name LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
