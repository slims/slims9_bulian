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

/* Loan Rules management section */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}
// key to get full database access
define('DB_ACCESS', 'fa');

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $data['member_type_id'] = $_POST['memberTypeID'];
    $data['coll_type_id'] = $_POST['collTypeID'];
    $data['gmd_id'] = $_POST['gmdID'];
    $data['loan_limit'] = trim($_POST['loanLimit']);
    $data['loan_periode'] = trim($_POST['loanPeriode']);
    $data['reborrow_limit'] = trim($_POST['reborrowLimit']);
    $data['fine_each_day'] = trim($_POST['fineEachDay']);
    $data['grace_periode'] = trim($_POST['gracePeriode']);
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
        $update = $sql_op->update('mst_loan_rules', $data, 'loan_rules_id='.$updateRecordID);
        if ($update) {
            toastr(__('Loan Rules Successfully Updated'))->success();
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
        } else { toastr(__('Loan Rules FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
        exit();
    } else {
        /* INSERT RECORD MODE */
        $insert = $sql_op->insert('mst_loan_rules', $data);
        if ($insert) {
            toastr(__('New Loan Rules Successfully Saved'))->success();
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
        } else { toastr(__('Loan Rules FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error)->error(); }
        exit();
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
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
        $lrStatus = circapi::is_any_active_loanrules($dbs, $itemID);
        if (!$lrStatus) {
            if (!$sql_op->delete('mst_loan_rules', 'loan_rules_id='.$itemID)) {
                $error_num++;
            }
        }
    }
    // error alerting
    if ($error_num == 0) {
        if (!$lrStatus) {
            toastr(__('All Data Successfully Deleted'))->success();
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
        } else {
            toastr(__('Sorry. There is active loan transaction(s) using this loan rules.'))->info();
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
        }
    } else {
        toastr(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'))->success();
        echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner loanRulesIcon">
	<div class="per_title">
	    <h2><?php echo __('Loan Rules'); ?></h2>
    </div>
    <div class="sub_section">
	    <div class="btn-group">
		    <a href="<?php echo MWB; ?>circulation/loan_rules.php" class="btn btn-default"><?php echo __('Loan Rules List'); ?></a>
		    <a href="<?php echo MWB; ?>circulation/loan_rules.php?action=detail" class="btn btn-default"><?php echo __('Add New Loan Rules'); ?></a>
	    </div>
        <form name="search" action="<?php echo MWB; ?>circulation/loan_rules.php" id="search" method="get" class="form-inline">
            <?php echo __('Search'); ?>
		    <input type="text" name="keywords" size="30" class="form-control col-3">
		    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default">
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
    // try query
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_loan_rules WHERE loan_rules_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-primary"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        // form record id
        $form->record_id = $itemID;
        // form record title
        $form->record_title = 'Loan Rules';
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // member type
        // get mtype data related to this record from database
        $mtype_query = $dbs->query('SELECT member_type_id, member_type_name FROM mst_member_type');
        $mtype_options = array();
        while ($mtype_data = $mtype_query->fetch_row()) {
            $mtype_options[] = array($mtype_data[0], $mtype_data[1]);
        }
    $form->addSelectList('memberTypeID', __('Member Type'), $mtype_options, $rec_d['member_type_id']??'', 'class="form-control col-5"');
    // collection type
        // get collection type data related to this record from database
        $ctype_query = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
        $ctype_options = array();
        while ($ctype_data = $ctype_query->fetch_row()) {
            $ctype_options[] = array($ctype_data[0], $ctype_data[1]);
        }
        $ctype_options[] = array('0', __('ALL'));
    $form->addSelectList('collTypeID', __('Collection Type'), $ctype_options, $rec_d['coll_type_id']??'', 'class="form-control col-5"');
    // gmd
        // get gmd data related to this record from database
        $gmd_query = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
        $gmd_options[] = array(0, __('ALL'));
        while ($gmd_data = $gmd_query->fetch_row()) {
            $gmd_options[] = array($gmd_data[0], $gmd_data[1]);
        }
    $form->addSelectList('gmdID', __('GMD'), $gmd_options, $rec_d['gmd_id']??'', 'class="form-control col-5"');
    // loan limit
    $form->addTextField('text', 'loanLimit', __('Loan Limit'), $rec_d['loan_limit']??'', 'class="form-control col-2"');
    // loan periode
    $form->addTextField('text', 'loanPeriode', __('Loan Period'), $rec_d['loan_periode']??'', 'class="form-control col-2"');
    // reborrow limit
    $form->addTextField('text', 'reborrowLimit', __('Reborrow Limit'), $rec_d['reborrow_limit']??'0', 'class="form-control col-2"');
    // fine each day
    $form->addTextField('text', 'fineEachDay', __('Fines Each Day'), $rec_d['fine_each_day']??'0','class="form-control col-2"');
    // overdue grace periode
    $form->addTextField('text', 'gracePeriode', __('Overdue Grace Periode'), $rec_d['grace_periode']??'0','class="form-control col-2"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit loan rules').' : <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'."\n"; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* LOAN RULES LIST */
    // table spec
    $table_spec = 'mst_loan_rules AS lr
        LEFT JOIN mst_member_type AS mt ON lr.member_type_id=mt.member_type_id
        LEFT JOIN mst_coll_type AS ct ON lr.coll_type_id=ct.coll_type_id
        LEFT JOIN mst_gmd AS g ON lr.gmd_id=g.gmd_id';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('lr.loan_rules_id',
            'mt.member_type_name AS \''.__('Member Type').'\'',
            'ct.coll_type_name AS \''.__('Collection Type').'\'',
            'g.gmd_name AS \''.__('GMD').'\'',
            'lr.loan_limit AS \''.__('Loan Limit').'\'',
            'lr.loan_periode AS \''.__('Loan Period').'\'',
            'lr.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('mt.member_type_name AS \''.__('Member Type').'\'',
            'ct.coll_type_name AS \''.__('Collection Type').'\'',
            'g.gmd_name AS \''.__('GMD').'\'',
            'lr.loan_limit AS \''.__('Loan Limit').'\'',
            'lr.loan_periode AS \''.__('Loan Period').'\'',
            'lr.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('mt.member_type_name ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = $dbs->escape_string($_GET['keywords']);
       $datagrid->setSQLCriteria("mt.member_type_name LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->icon_edit = $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlentities($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
