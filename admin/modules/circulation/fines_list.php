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

/* Fines Management section */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

if (!isset($_SESSION['memberID'])) { die(); }

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die();
}

// page title
$page_title = 'Member Loan List';

// start the output buffering
ob_start();
/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $debet = preg_replace('@[.,\-a-z ]@i', '', $_POST['debet']);
    $credit = preg_replace('@[.,\-a-z ]@i', '', $_POST['credit']);
    // check form validity
    if (empty($_POST['finesDesc']) OR empty($debet)) {
        utility::jsAlert(''.__('Fines Description and Debet value can\'t be empty').'');
    } else if ($credit > $debet) {
        utility::jsAlert(''.__('Value of Credit can not be higher that Debet Value').'');
    } else {
        $data['member_id'] = $_SESSION['memberID'];
        $data['fines_date'] = trim($dbs->escape_string(strip_tags($_POST['finesDate'])));
        $data['description'] = trim($dbs->escape_string(strip_tags($_POST['finesDesc'])));
        $data['debet'] = $debet;
        $data['credit'] = $credit;

        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('fines', $data, 'fines_id='.$updateRecordID);
            if ($update) {
                utility::jsAlert(__('Fines Data Successfully Updated'));
            } else { utility::jsAlert(__('Fines Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error); }
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('fines', $data);
            if ($insert) {
                utility::jsAlert(__('New Fines Data Successfully Saved'));
            } else { utility::jsAlert(__('Fines Data FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error); }
        }
    }
} else if ($_SESSION['uid'] == 1 && isset($_POST['itemID']) && !empty($_POST['itemID']) && isset($_POST['itemAction'])) {
    // only admin can delete
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
        if (!$sql_op->delete('fines', 'fines_id='.$itemID)) {
            $error_num++;
        }
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsAlert('Fines data succesfully deleted!');
    } else {
        utility::jsAlert('Fines data FAILED to delete!');
    }
}
/* RECORD OPERATION END */

/* header */
?>
<div class="sub_section">
<div class="btn-group">
    <a href="fines_list.php?action=detail" class="btn btn-default" style="color: #FF0000;"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New Fines'); ?></a>
    <a href="fines_list.php" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('Fines List'); ?></a>
    <a href="fines_list.php?balance=true" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('View Balanced Overdue'); ?></a>
</div>
</div>
<?php
/* search form end */
/* main content */
if ((isset($_GET['detail']) && isset($_GET['itemID'])) || (isset($_GET['action']) && $_GET['action'] == 'detail')) {
    /* RECORD FORM */
    $itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM fines WHERE fines_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="button"';

    // form table attributes
    $form->table_attr = 'align="center" id="dataList" style="width: 100%;" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // delete button only showed for admin user
        if ($_SESSION['uid'] != 1) {
            $form->delete_button = false;
        }
        // form record title
        $form->record_title = 'Fines Detail';
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="button"';
    }

    /* Form Element(s) */
    // fines dates
    $form->addDateField('finesDate', __('Fines Date'), $rec_d['fines_date']);
    // fines description
    $form->addTextField('text', 'finesDesc', __('Description/Name').'*', $rec_d['description'], 'style="width: 60%;"');
    // fines debet
    $form->addTextField('text', 'debet', __('Debit').'*', !empty($rec_d['debet'])?$rec_d['debet']:'0', 'style="width: 60%;"');
    // fines credit
    $form->addTextField('text', 'credit', __('Credit'), !empty($rec_d['credit'])?$rec_d['credit']:'0', 'style="width: 60%;"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit fines data').' : <b>'.$rec_d['description'].'</b></div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* FINES LIST */
    $memberID = trim($_SESSION['memberID']);
    // table spec
    $table_spec = 'fines AS f';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('f.fines_id AS \'EDIT\'',
        'f.description AS \''.__('Description/Name').'\'',
        'f.fines_date AS \''.__('Fines Date').'\'',
        'f.debet AS \''.__('Debit').'\'',
        'f.credit AS \''.__('Credit').'\'');
    $datagrid->setSQLorder("f.fines_date DESC");

    $criteria = 'f.member_id=\''.$dbs->escape_string($memberID).'\' ';
    // view balanced overdue
    if (isset($_GET['balance'])) {
        $criteria .= ' AND (f.debet=f.credit) ';
    } else {
        $criteria .= ' AND (f.debet!=f.credit) ';
    }
    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keyword = $dbs->escape_string($_GET['keywords']);
        $criteria .= " AND (f.description LIKE '%$keyword%' OR f.fines_date LIKE '%$keyword%')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" style="width: 100%;" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
    // special properties
    $datagrid->using_AJAX = false;
    // checkbox delete only showed for admin user
    if ($_SESSION['uid'] != 1) {
        $datagrid->chbox_property = false;
    }
    $datagrid->column_width = array(0 => '73%');

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, true);
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */

// get the buffered content
$content = ob_get_clean();
// js include
$js = '<script type="text/javascript" src="'.JWB.'calendar.js"></script>';
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
