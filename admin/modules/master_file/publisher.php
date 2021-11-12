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
 * Some patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
 */

/* Publisher Management section */

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
    $publisherName = trim(strip_tags($_POST['publisherName']));
    // check form validity
    if (empty($publisherName)) {
        utility::jsToastr(__('Publisher'),__('Publisher Name can\'t be empty'),'error'); //mfc
        exit();
    } else {
        $data['publisher_name'] = $dbs->escape_string($publisherName);
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
            $update = $sql_op->update('mst_publisher', $data, 'publisher_id='.$updateRecordID);
            if ($update) {
                utility::jsToastr(__('Publisher'),__('Publisher Data Successfully Updated'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { utility::jsToastr(__('Publisher'),__('PUBLISHER Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('mst_publisher', $data);
            if ($insert) {
                utility::jsToastr(__('Publisher'),__('New Publisher Data Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr(__('Publisher'),__('Publisher Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
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
        // check if this place data still in use biblio
        $_sql_publish_biblio_q = sprintf('SELECT mp.publisher_name, COUNT(mp.publisher_id) FROM biblio AS b
        LEFT JOIN mst_publisher AS mp ON b.publisher_id=mp.publisher_id
        WHERE mp.publisher_id = \'%d\' GROUP BY mp.publisher_name', $itemID);
        $publish_biblio_q = $dbs->query($_sql_publish_biblio_q);
        $publish_biblio_d = $publish_biblio_q->fetch_row();
        if ($publish_biblio_d[1] < 1) {  

            if (!$sql_op->delete('mst_publisher', 'publisher_id='.$itemID)) {
                $error_num++;
            }
        }else{
            $still_have_biblio[] = sprintf(__('%s still in use %d biblio').'<br/>',substr($publish_biblio_d[0], 0, 45),$publish_biblio_d[1]);
            $error_num++;            
        }
    }

    if ($still_have_biblio) {
        $titles = '';
        foreach ($still_have_biblio as $title) {
            $titles .= $title . "\n";
        }
        utility::jsToastr(__('Publisher'),__('Below data can not be deleted:') . "\n" . $titles, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . $_POST['lastQueryStr'] . '\'});</script>';
        exit();
    }  

    // error alerting
    if ($error_num == 0) {
        utility::jsToastr(__('Publisher'),__('All Data Successfully Deleted'),'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsToastr(__('Publisher'),__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'error');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Publisher'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/publisher.php" class="btn btn-default"><?php echo __('Publisher List'); ?></a>
      <a href="<?php echo MWB; ?>master_file/publisher.php?action=detail" class="btn btn-default"><?php echo __('Add New Publisher'); ?></a>
    </div>
    <form name="search" action="<?php echo MWB; ?>master_file/publisher.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
    $rec_q = $dbs->query('SELECT * FROM mst_publisher WHERE publisher_id='.$itemID);
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
        $form->record_title = $rec_d['publisher_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // publisher name
    $form->addTextField('text', 'publisherName', __('Publisher Name').'*', $rec_d['publisher_name']??'', 'style="width: 60%;" class="form-control"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit publisher data').' : <b>'.$rec_d['publisher_name'].'</b> <br />'.__('Last Update').' '.$rec_d['last_update'] //mfc
            .'</div>'."\n";
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* PUBLISHER LIST */
    // table spec
    #$sql_criteria = 'b.publisher_id > 1';
    if (isset($_GET['type']) && $_GET['type'] == 'orphaned') {
        $table_spec = 'mst_publisher AS p LEFT JOIN biblio AS b ON p.publisher_id = b.publisher_id';
        $sql_criteria = 'b.publisher_id IS NULL';
    } else {
        $table_spec = 'mst_publisher AS p';
    }

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('p.publisher_id',
            'p.publisher_name AS \''.__('Publisher Name').'\'',
            'p.last_update AS \''.__('Last Update').'\'');
    } else {
    	// TODO: publisher_place was dropped in stable7...?
        $datagrid->setSQLColumn('p.publisher_name AS \''.__('Publisher Name').'\'',
            'p.publisher_place AS \''.lang_mod_masterfile_publisher_form_field_place.'\'',
            'p.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('publisher_name ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keywords = utility::filterData('keywords', 'get', true, true, true);
        if (isset($sql_criteria)) {
		    $sql_criteria .= " AND p.publisher_name LIKE '%$keywords%'";
		} else {
            $sql_criteria = " p.publisher_name LIKE '%$keywords%'";
		}
    }

    if (isset($sql_criteria) AND $sql_criteria <> "") {
        $datagrid->setSQLCriteria($sql_criteria);
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variable
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        echo '<table cellpadding="3" cellspacing="0" class="infoBox">';
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<tr><th>'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</th></tr>';
        echo '</table>';
    }

    echo $datagrid_result;
}
/* main content end */
