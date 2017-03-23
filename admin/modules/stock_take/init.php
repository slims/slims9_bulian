<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/* Stock Take */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}
if ($_SESSION['uid'] != '1') {
    die('<div class="errorBox">'.__('You must be an admin to run initialization process!').'</div>');
}

// check if there is any active stock take proccess
$stk_q = $dbs->query('SELECT * FROM stock_take WHERE is_active=1');
if ($stk_q->num_rows) {
    echo '<div class="errorBox">'.__('There is already stock taking proccess running!').'</div>';
} else {
    // add new stock take
    if (isset($_POST['saveData']) AND empty($_POST['name'])) {
        utility::jsAlert(__('Stock Take Name must be filled!'));
        exit();
    } else if (isset($_POST['saveData']) AND !empty($_POST['name'])) {
        $data['stock_take_name'] = trim($dbs->escape_string(strip_tags($_POST['name'])));
        $data['start_date'] = date('Y-m-d H:i:s');
        $data['init_user'] = $_SESSION['realname'];
        $data['is_active'] = 1;

        $sql_op = new simbio_dbop($dbs);
        if ($sql_op->insert('stock_take', $data)) {
            // get latest stock take id
            $stock_take_id = $sql_op->insert_id;
            // criteria
            $criteria = ' WHERE (ist.skip_stock_take<1 OR ist.skip_stock_take IS NULL)  ';
                // gmd
                if ($_POST['gmdID'] != '0') {
                    $criteria .= ' AND b.gmd_id='.intval($_POST['gmdID']).' ';
                }
                // collection type
                if ($_POST['collTypeID'] != '0') {
                    $criteria .= ' AND i.coll_type_id=\''.intval($_POST['collTypeID']).'\' ';
                }
                // location
                if ($_POST['location'] != '0') {
                    $criteria .= ' AND i.location_id=\''.$dbs->escape_string($_POST['location']).'\' ';
                }
                // site/placement
                if ($_POST['itemSite']) {
                    $criteria .= ' AND i.site LIKE \''.$dbs->escape_string($_POST['itemSite']).'\' ';
                }
                // classification
                if ($_POST['classification']) {
                    $criteria .= ' AND (';
                    // each class
                    $arr_class = explode(',', $_POST['classification']);
                    $class_criteria = '';
                    foreach ($arr_class as $each_class) {
                        // check each class if its containing wildcard
                        if (strpos($each_class, '*') !== false) {
                            $each_class = str_replace('*', '%', $dbs->escape_string($each_class));
                            $class_criteria .= ' b.classification LIKE \''.$each_class.'\' OR ';
                        } else {
                            $each_class = $dbs->escape_string($each_class);
                            $class_criteria .= ' b.classification=\''.$each_class.'\' OR ';
                        }
                    }
                    // remove last OR
                    $class_criteria = substr_replace($class_criteria, '', -3);
                    $criteria .= $class_criteria.' )';
                }
            // for debugging purpose only
            // emptying previous stock take item data
            $clean_q = $dbs->query('TRUNCATE TABLE stock_take_item');
            // copy all item data to stock take detail table
            $insert_q = $dbs->query("INSERT INTO stock_take_item (stock_take_id, item_id, item_code, title, gmd_name, classification, coll_type_name, call_number, location, status, checked_by, last_update)
                SELECT $stock_take_id, i.item_id, i.item_code, b.title, g.gmd_name, b.classification, ct.coll_type_name, i.call_number, loc.location_name, 'm', '".$_SESSION['realname']."', NULL FROM
                item AS i
                LEFT JOIN mst_item_status AS ist ON i.item_status_id=ist.item_status_id
                LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
                LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id
                LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
                LEFT JOIN mst_gmd AS g ON b.gmd_id=g.gmd_id
                $criteria");
            // get number of item on loan
            $item_loan_q = $dbs->query("SELECT COUNT(loan_id) FROM loan AS l WHERE is_lent=1 AND is_return=0");
            $item_loan_d = $item_loan_q->fetch_row();
            if (!$item_loan_d[0]) {
                $item_loan_d[0] = 0;
            }
            // update data for item being loan
            $update_q = $dbs->query("UPDATE stock_take_item SET status='l' WHERE item_code IN (SELECT item_code FROM loan AS l WHERE is_lent=1 AND is_return=0)");
            // total rows inserted
            $total_rows_q = $dbs->query("SELECT COUNT(item_code) FROM stock_take_item WHERE status='m'");
            $total_rows_d = $total_rows_q->fetch_row();
            if ($total_rows_d[0] > 0) {
                // update total_lost_item field value in stock_take table
                $update_total_q = $dbs->query('UPDATE stock_take SET total_item_stock_taked='.$total_rows_d[0].', total_item_loan='.$item_loan_d[0].', total_item_lost='.$total_rows_d[0].", stock_take_users='".$_SESSION['realname']."\n' WHERE stock_take_id=$stock_take_id");
                // write log
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'stock_take', $_SESSION['realname'].' initialize stock take ('.$data['stock_take_name'].') from address '.$_SERVER['REMOTE_ADDR']);
                utility::jsAlert(__('Stock Taking Initialized'));
                echo '<script type="text/javascript">parent.location.href = \''.SWB.'admin/index.php?mod=stock_take\';</script>';
            } else {
                // delete stock take data
                $dbs->query('DELETE FROM stock_take WHERE stock_take_id='.$stock_take_id);
                utility::jsAlert(__('Stock Taking FAILED to Initialized.\nNo item to stock take!'));
            }
            exit();
        }
    }

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?action=new', 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Initialize Stock Take').'" class="btn btn-default"';

    // form table attributes
    $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
    $form->table_content_attr = 'class="alterCell2"';

    /* Form Element(s) */
    // stock take name
    $form->addTextField('text', 'name', __('Stock Take Name').'*', '', 'style="width: 60%;"');
    // gmd
        // get gmd data related to this record from database
        $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
        $gmd_options[] = array('0', __('ALL'));
        while ($gmd_d = $gmd_q->fetch_row()) {
            $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
        }
    $form->addSelectList('gmdID', __('GMD'), $gmd_options);
    // collection type
        // get coll_type data related to this record from database
        $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
        $coll_type_options[] = array('0', __('ALL'));
        while ($coll_type_d = $coll_type_q->fetch_row()) {
            $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
        }
    $form->addSelectList('collTypeID', __('Collection Type'), $coll_type_options);
    // location
        // get language data related to this record from database
        $location_q = $dbs->query("SELECT location_id, location_name FROM mst_location");
        $location_options[] = array('0', __('ALL'));
        while ($location_d = $location_q->fetch_row()) {
            $location_options[] = array($location_d[0], $location_d[1]);
        }
    $form->addSelectList('location', __('Location'), $location_options);
    // item site
    $form->addTextField('text', 'itemSite', __('Shelf Location'), '', 'style="width: 20%;"');
    // classification;
    $str_input = simbio_form_element::textField('text', 'classification', '', 'style="width: 60%;"');
    $str_input .= '<br />'.__('Separate each class comma sign. Use * for wildcard');
    $form->addAnything(__('Classification'), $str_input);
    // print out the object
    ?>
    <fieldset class="menuBox">
    <div class="menuBoxInner printIcon">
	   <div class="per_title">
      <h2><?php echo __('Initialize Stock Take Process'); ?></h2>
     </div>
    </div>
    </fieldset>
    <?php
    echo $form->printOut();
}
