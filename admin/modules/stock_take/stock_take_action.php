<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// if transaction is started
if (isset($_POST['itemCode'])) {
    echo '<!DOCTYPE html>';
    echo '<html><body>';
    // update item data
    $item_code = utility::filterData('itemCode', 'post', true, true, true);
    if (!$item_code) {
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#stError\').html(\''.__('Please enter a valid item code/barcode. You enter a BLANK code!').'\')';
        echo '.css( {\'display\': \'block\'} );'."\n";
        echo 'parent.$(\'#itemCode\').val(\'\').focus();'."\n";
        echo '</script>';
        echo '</body></html>';
        exit();
    }
    // check item status first
    $item_check = $dbs->query("SELECT * FROM stock_take_item WHERE item_code='$item_code'");
    $item_check_d = $item_check->fetch_assoc();
    if ($item_check->num_rows > 0) {
        if ($item_check_d['status'] == 'l') {
            // record to log
            utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'stock_take', 'Stock Take ERROR : '.sprintf(__('Item %s (%s) is currently ON LOAN'),$item_check_d['title'],$item_check_d['item_code']), 'Error', 'On Loan');
            echo '<script type="text/javascript">'."\n";
            echo 'parent.$(\'#stError\').html(\''.sprintf(__('Item %s is currently ON LOAN'), $item_code).'\')';
            echo '.css( {\'display\': \'block\'} );'."\n";
            echo 'parent.$(\'#itemCode\').val(\'\').focus();'."\n";
            echo '</script>';
        } else if ($item_check_d['status'] == 'e') {
            echo '<script type="text/javascript">'."\n";
            echo 'parent.$(\'#stError\').html(\''.sprintf(__('Item %s is already SCANNED!'), $item_code).'\')';
            echo '.css( {\'display\': \'block\'} );'."\n";
            echo 'parent.$(\'#itemCode\').val(\'\').focus();'."\n";
            echo '</script>';
        } else {
            $listShow = 0;
            if (isset($_POST['listShow']) && $_POST['listShow'] == '1') {
                $listShow = 1;
            }
            // current time
            $curr_time = date('Y-m-d H:i:s');
            $update = $dbs->query("UPDATE stock_take_item SET status='e', checked_by='".$_SESSION['realname']."', last_update='".$curr_time."' WHERE item_code='$item_code'");
            $update = $dbs->query("UPDATE stock_take SET total_item_lost=total_item_lost-1, total_item_exists=total_item_exists+1 WHERE is_active=1");

            //update stocktake participants
            //get user participants
            $user = array();
            $user[$_SESSION['realname']] = $_SESSION['realname'];
            $stock_take_users = serialize($user);
            $_q = $dbs->query("SELECT stock_take_users FROM stock_take WHERE is_active=1");
            if($_q->num_rows > 0){
                $_d = $_q->fetch_row()[0];
                if($_d == NULL){
                    $update = $dbs->query("UPDATE stock_take SET stock_take_users='".$stock_take_users."' WHERE is_active=1");
                }else{
                    $user = unserialize($_d);
                    $user[$_SESSION['realname']] = $_SESSION['realname'];
                    $stock_take_users = serialize($user);
                    $update = $dbs->query("UPDATE stock_take SET stock_take_users='".$stock_take_users."' WHERE is_active=1");
                }
            }
            echo '<script type="text/javascript">'."\n";
            echo 'parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'stock_take/current.php?listShow='.$listShow.'\');'."\n";
            echo '</script>';
        }
    } else {

        // check for item status rules "Skipped By Stock Take"
        $status_q = $dbs->query(sprintf("SELECT item.item_code FROM item 
                     LEFT JOIN mst_item_status ON item.item_status_id=mst_item_status.item_status_id
                     WHERE item.item_code = '%s' AND mst_item_status.skip_stock_take=1", $item_code));
        if ($status_q->num_rows > 0) {
            $message = 'Stock Take ERROR : '.sprintf(__('Item Code (%s) have rule to skipped by stock take'),$item_code);
            $alert = sprintf(__('Item Code %s have rule to skipped by stock take'),$item_code);
        } else {
            $message = 'Stock Take ERROR : '.sprintf(__('Item Code (%s) doesnt exists in stock take data. Invalid Item Code OR Maybe out of Stock Take range'),$item_code);
            $alert = sprintf(__('Item Code %s doesnt exists in stock take data.\\nInvalid Item Code OR Maybe out of Stock Take range'),$item_code);
        }

        // record to log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'stock_take',$message, 'Error', 'No Item');
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#stError\').html(\''.$alert.'\')';
        echo '.css( {\'display\': \'block\'} );'."\n";
        echo 'parent.$(\'#itemCode\').val(\'\').focus();'."\n";
        echo '</script>';
    }
    echo '</body></html>';
}
