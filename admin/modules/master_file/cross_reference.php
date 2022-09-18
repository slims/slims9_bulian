<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified by Waris Agung Widodo (ido.alit@gmail.com)
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

/* Narrower Term for vocabolary control */

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
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
	$code = trim(strip_tags($_POST['rt-code']));
	$desc = trim(strip_tags($_POST['rt-desc']));
	if (empty($code) OR empty($desc)) {
		toastr(__('Cross Reference Code AND Description can\'t be empty'))->error();
        exit();
	}else{
		$data['rt_id'] = trim($dbs->escape_string($code));
		$data['rt_desc'] = trim($dbs->escape_string($desc));

		// create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
        	// filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update data
        	$update = $sql_op->update('mst_relation_term', $data, 'ID='.$updateRecordID);
            if ($update) {
                toastr(__('Cross Reference Data Successfully Updated'))->success();
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { toastr(__('Cross Reference Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
            exit();
        }else{
        	/* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('mst_relation_term', $data);
            if ($insert) {
                toastr(__('New Cross Reference Data Successfully Saved'))->success();
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { toastr(__('Cross Reference Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
            exit();
        }
	}
	exit();
}else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
	if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    // create sql op object
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
        if (!$sql_op->delete('mst_relation_term', 'ID='.$itemID)) {
            $error_num++;
        }
    }

    // error alerting
    if ($error_num == 0) {
        toastr(__('All Data Successfully Deleted'))->success();
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        toastr(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'))->error();
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
	    <h2><?php echo __('Cross Reference'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
		  <a href="<?php echo MWB; ?>master_file/cross_reference.php" class="btn btn-default"><?php echo __('Cross Reference List'); ?></a>
		  <a href="<?php echo MWB; ?>master_file/cross_reference.php?action=detail" class="btn btn-default"><?php echo __('Add New Term'); ?></a>
		  <a href="<?php echo MWB; ?>master_file/topic.php" class="btn btn-success"><?php echo __('Subject List'); ?></a>
	  </div>
	  <form name="search" action="<?php echo MWB; ?>master_file/cross_reference.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_relation_term WHERE ID='.$itemID);
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
        $form->record_title = $rec_d['rt_desc'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // code
    $form->addTextField('text', 'rt-code', __('Cross Reference Code').'*', $rec_d['rt_id']??'', 'class="form-control" style="width: 30%;"');
	// description
    $form->addTextField('text', 'rt-desc', __('Cross Reference Description').'*', $rec_d['rt_desc']??'', 'class="form-control" style="width: 60%;"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit Subject data').' : <b>'.$rec_d['rt_desc'].'</div>';
    }
    // print out the form object
    echo $form->printOut();
}else{
	/* term list */
	$datagrid = new simbio_datagrid();
	// table spec
	$table_spec = 'mst_relation_term AS rt';
	if ($can_read AND $can_write) {
		$datagrid->setSQLColumn(
			'rt.ID',
			'rt.rt_id AS \''.__('Cross Reference Code').'\'',
			'rt.rt_desc AS \''.__('Cross Reference Description').'\''
			);
	}else{
		$datagrid->setSQLColumn(
			'rt.rt_id AS \''.__('Cross Reference Code').'\'',
			'rt.rt_desc AS \''.__('Cross Reference').'\''
			);
	}

	$datagrid->setSQLorder('rt_id ASC');

	$sql_criteria = 'rt.ID >= 1';

	// is there any search
	if (isset($_GET['keywords']) AND $_GET['keywords']) {
		$keyword = $dbs->escape_string(trim($_GET['keywords']));
		$words = explode(' ', $keyword);
		if (count($words) > 1) {
			$concat_sql = ' AND (';
			foreach ($words as $word) {
				$concat_sql .= " (rt.rt_id LIKE '%$word%' OR rt.rt_desc LIKE '%$word%') AND";
			}
			$concat_sql = substr_replace($concat_sql, '', -3);
			$concat_sql .= ') ';
			$sql_criteria .= $concat_sql;
		}else{
			$sql_criteria .= " AND rt.rt_id LIKE '%$keyword%' OR rt_desc LIKE '%$keyword%' ";
		}
	}

	$datagrid->setSQLCriteria($sql_criteria);

	// set table and table header attributes
	$datagrid->table_attr = 'id="dataList" class="s-table table"';
	$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
	// set delete proccess URL
	$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

	// put the result into variables
	$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));

	echo $datagrid_result;
}

?>