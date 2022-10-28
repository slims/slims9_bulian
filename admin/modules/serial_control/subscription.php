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

/* serial Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-serialcontrol');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require MDLBS.'serial_control/serial_base_lib.inc.php';

// privileges checking
$can_read = utility::havePrivilege('serial_control', 'r');
$can_write = utility::havePrivilege('serial_control', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// page title
$page_title = 'Subscription List';

$biblioID = 0;
if (isset($_GET['biblioID'])) {
    $biblioID = (integer)$_GET['biblioID'];
}
if (isset($_POST['biblioID'])) {
    $biblioID = (integer)$_POST['biblioID'];
}

/* RECORD OPERATION */
if (isset($_POST['saveData'])) {
    $dateStart = trim($dbs->escape_string(strip_tags($_POST['dateStart'])));
    // $dateEnd = trim($dbs->escape_string(strip_tags($_POST['dateEnd'])));
    $period = trim($dbs->escape_string(strip_tags($_POST['period'])));
    // check form validity
    if (!$period OR !$dateStart) {
        toastr(__('Error inserting subscription data, Subscription Date must be filled!'))->error();
    } else {
        $data['biblio_id'] = $biblioID;
        $data['date_start'] = $dateStart;
        // $data['date_end'] = $dateEnd;
        $data['period'] = $period;
        $data['notes'] = trim($_POST['notes'])==''?'literal{NULL}':trim($dbs->escape_string(strip_tags($_POST['notes'])));
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('serial', $data, 'serial_id='.$updateRecordID);
            if ($update) {
                toastr(__('Subscription Data Successfully Updated'))->success();
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'serial_control', $_SESSION['realname'].' update subcription('.$updateRecordID.') '.$period, 'Subcription', 'Update');
            } else { toastr(__('Subscription Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error)->error(); }
            echo '<script type="text/javascript">self.location.href = \''.MWB.'serial_control/subscription.php?biblioID='.$biblioID.'\';</script>';
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('serial', $data);
            $serial_id = $sql_op->insert_id;
            if ($insert) {
                $exemplar = (integer)$_POST['exemplar'];
                // generate kardex entry
                $serial = new serial($dbs, $serial_id);
                $serial->generateKardexes($exemplar, true);
                // alert
                toastr(__('New Subscription Data Successfully Saved'))->success();
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'serial_control', $_SESSION['realname'].' add new subcription('.$sql_op->insert_id.') '.$period, 'Subscription', 'Add');
            } else { toastr(__('Subscription Data FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error)->error(); }
            echo '<script type="text/javascript">self.location.href = \''.MWB.'serial_control/subscription.php?biblioID='.$biblioID.'\';</script>';
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

    // get biblio ID for this subcription
    $biblio_q = $dbs->query('SELECT biblio_id FROM serial WHERE serial_id='.( isset($_POST['itemID'][0])?$_POST['itemID'][0]:'0' ).' LIMIT 1');
    $biblio_d = $biblio_q->fetch_row();
    $biblioID = $biblio_d[0];
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        if (!$sql_op->delete('serial', 'serial_id='.$itemID)) {
            $error_num++;
        } else {
            // also delete kardex data
            $sql_op->delete('kardex', 'serial_id='.$itemID);
        }
    }

    // error alerting
    if ($error_num == 0) {
        toastr(__('Subscription data successfully deleted'))->success();
    } else {
        toastr(__('Subscription data FAILED to deleted!'))->error();
    }
}
/* RECORD OPERATION END */

// start the output buffering
ob_start();
/* main content */
if ($can_write AND ( isset($_POST['detail']) OR (isset($_GET['detail']) AND $_GET['detail'] == 'true') )) {
    /* RECORD FORM */
    $biblioID = (integer)isset($_GET['biblioID'])?$_GET['biblioID']:0;
    $itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM serial WHERE biblio_id='.$biblioID.' AND serial_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-primary"';

    // form table attributes
    $form->table_attr = 'class="s-table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['period'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // serial date start
    $form->addDateField('dateStart', __('Subscription Start').'*', $rec_d['date_start']??'','class="form-control"');
    if (!$form->edit_mode) {
        // serial exemplar
        $form->addTextField('text', 'exemplar', __('Total Exemplar Expected').'*', '1', 'class="form-control col-2"');
    }
    // serial periode name
    $form->addTextField('text', 'period', __('Period Name').'*', $rec_d['period']??'', 'class="form-control');
    // serial notes
    $form->addTextField('textarea', 'notes', __('Subscription Notes'), $rec_d['notes']??'', 'class="form-control" rows="3"');
    // serial gmd
        // get gmd data related to this record from database
        $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
        $gmd_options = array();
        while ($gmd_d = $gmd_q->fetch_row()) {
            $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
        }
    $form->addSelectList('gmdID', __('GMD'), $gmd_options, $rec_d['gmd_id']??'', 'class="form-control col-3"');
    // serial biblio ID
    $form->addHidden('biblioID', $biblioID);

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit Subscription data').' : <b>'.$rec_d['period'].'</b><div><i>'.$rec_d['notes'].'</i></div></div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* SUBSCRIPTION LIST */
    function serialTitle($obj_db, $array_data)
    {
        $_output = '';
        $_output .= '<strong>'.$array_data[1].'</strong>';
        $_output .= '<div><a href="'.MWB.'serial_control/kardex.php?serialID='.$array_data[0].'" title="'.__('View/Edit Kardex Detail').'" class="s-btn btn btn-success btn-sm">'.__('View/Edit Kardex Detail').'</a></div>';
        return $_output;
    }

    // table spec
    $table_spec = 'serial AS s';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('s.serial_id',
        's.period AS \''.__('Period Name').'\'',
        's.date_start AS \''.__('Subscription Start').'\'',
        's.notes AS \''.__('Subscription Notes').'\'');
    if ($can_read AND $can_write) {
        $datagrid->modifyColumnContent(1, 'callback{serialTitle}');
    } else {
        $datagrid->invisible_fields = array(0);
        $datagrid->modifyColumnContent(1, 'callback{serialTitle}');
    }
    $datagrid->setSQLorder('s.date_start DESC');

    $criteria = 's.biblio_id='.$biblioID;
    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keyword = $dbs->escape_string($_GET['keywords']);
        $criteria .= " AND (s.period LIKE '%$keyword%' OR s.notes LIKE '%$keyword%')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" style="width: 100%;" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    $datagrid->using_AJAX = false;
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
    // special properties
    $datagrid->column_width = array(0 => '45%');

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
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
$js = '<script type="text/javascript" src="'.JWB.'calendar.js?"></script>';
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
