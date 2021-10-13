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

/* Frequency Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

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
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $frequency = trim(strip_tags($_POST['frequencyName']));
    // check form validity
    if (empty($frequency)) {
        utility::jsToastr( __('Frequency'),__('Required fields (*)  must be filled correctly!'),'error');
        exit();
    } else {
        $data['frequency'] = $dbs->escape_string($frequency);
        $data['language_prefix'] = $dbs->escape_string(strip_tags(trim($_POST['languagePrefix'])));
        $data['time_increment'] = $dbs->escape_string(strip_tags(trim($_POST['timeIncrement'])));
        $data['time_unit'] = $dbs->escape_string(strip_tags(trim($_POST['timeUnit'])));
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
            // update the data
            $update = $sql_op->update('mst_frequency', $data, 'frequency_id='.$updateRecordID);
            if ($update) {
                utility::jsToastr( __('Frequency'),__('Frequency Data Successfully Updated'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { utility::jsToastr( __('Frequency'),__('Frequency Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('mst_frequency', $data)) {
                utility::jsToastr( __('Frequency'),__('New Frequency Data Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr( __('Frequency'),__('Frequency Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
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
        // check if this label data still in use biblio
        $_sql_freq_biblio_q = 'SELECT mf.frequency, COUNT(mf.frequency_id) FROM biblio AS b
        LEFT JOIN mst_frequency AS mf ON mf.frequency_id=b.frequency_id
        WHERE mf.frequency_id='.$itemID.' GROUP BY mf.frequency';
        $freq_biblio_q = $dbs->query($_sql_freq_biblio_q);
        $freq_biblio_d = $freq_biblio_q->fetch_row();
        if ($freq_biblio_d[1] < 1) {
            if (!$sql_op->delete('mst_frequency', 'frequency_id='.$itemID)) {
                $error_num++;
            }
        }else{
            $still_have_biblio[] = sprintf(__('Frequency %s still in use %d biblio(s)')."<br/>",substr($freq_biblio_d[0], 0, 45),$freq_biblio_d[1]);
            $error_num++;                       
        }
    }

    if ($still_have_biblio) {
        $titles = '';
        foreach ($still_have_biblio as $title) {
            $titles .= $title . "\n";
        }
        utility::jsToastr( __('Frequency'), __('Below data can not be deleted:') . "<br/>" . $titles, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . $_POST['lastQueryStr'] . '\'});</script>';
        exit();
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsToastr( __('Frequency'),__('All Data Successfully Deleted'),'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsToastr( __('Frequency'),__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'warning');
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
	    <h2><?php echo __('Frequency'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/frequency.php" class="btn btn-default"><?php echo __('Frequency Available'); ?></a>
      <a href="<?php echo MWB; ?>master_file/frequency.php?action=detail" class="btn btn-default"><?php echo __('Add New Frequency'); ?></a>
    </div>
    <form name="search" action="<?php echo MWB; ?>master_file/frequency.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_frequency WHERE frequency_id='.$itemID);
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
        $form->record_title = $rec_d['frequency'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // frequency name
    $form->addTextField('text', 'frequencyName', __('Frequency').'*', $rec_d['frequency']??'', 'style="width: 60%;" class="form-control"');
    // frequency language
        // get language data related to this record from database
        $lang_q = $dbs->query('SELECT language_id, language_name FROM mst_language');
        $lang_options = array();
        while ($lang_d = $lang_q->fetch_row()) {
            $lang_options[] = array($lang_d[0], $lang_d[1]);
        }
    $form->addSelectList('languagePrefix', __('Language'), $lang_options, $rec_d['language_prefix']??'','class="form-control col-3"');
    // frequency time increment
    $form->addTextField('text', 'timeIncrement', __('Time Increment').'*', $rec_d['time_increment']??'', 'style="width: 10%;" class="form-control"');
    // frequency time unit
    $unit_options[] = array('day', __('Day'));
    $unit_options[] = array('week', __('Week'));
    $unit_options[] = array('month', __('Month'));
    $unit_options[] = array('year', __('Year'));
    $form->addSelectList('timeUnit', __('Time Unit'), $unit_options, $rec_d['time_unit']??'','class="form-control col-3"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit Frequency data').' : <b>'.$rec_d['frequency'].'</b>  <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* GMD LIST */
    // table spec
    $table_spec = 'mst_frequency AS f
        LEFT JOIN mst_language AS l ON f.language_prefix=l.language_id';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('f.frequency_id',
            'f.frequency AS \''.__('Frequency').'\'',
            'l.language_name AS \''.__('Language').'\'',
            'f.time_increment AS \''.__('Time Increment').'\'',
            'f.time_unit AS \''.__('Time Unit').'\'',
            'f.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('f.frequency AS \''.__('Frequency').'\'',
            'l.language_name AS \''.__('Language').'\'',
            'f.time_increment AS \''.__('Time Increment').'\'',
            'f.time_unit AS \''.__('Time Unit').'\'',
            'f.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('frequency ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $datagrid->setSQLCriteria("g.frequency_name LIKE '%$keywords%'");
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
