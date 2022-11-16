<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
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

use SLiMS\Plugins;

/* circulation transaction process */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}
// key to get full database access
@define('DB_ACCESS', 'fa');

if (!defined('DIRECT_INCLUDE')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
require MDLBS.'membership/member_base_lib.inc.php';
require MDLBS.'circulation/circulation_base_lib.inc.php';

function visitOnLoan($member_id)
{
    global $dbs;
    $now = date('Y-m-d');
    // check if already checkin
    $query = $dbs->query('SELECT visitor_id FROM visitor_count WHERE member_id=\''.$member_id.'\' AND checkin_date LIKE \''.$now.'%\'');
    if ($query->num_rows < 1) {
        // get data
        $mquery = $dbs->query('SELECT member_name, inst_name FROM member WHERE member_id=\''.$member_id.'\'');
        $mdata = $mquery->fetch_row();
        $member_name = $dbs->escape_string($mdata[0]);
        $institution = $mdata[1];
        // insert visit
        $checkin_date  = date('Y-m-d H:i:s');
        $insert = $dbs->query("INSERT INTO visitor_count (member_id, member_name, institution, checkin_date) VALUES ('$member_id', '$member_name', '$institution', '$checkin_date')");
        if (!$insert) {
            toastr(__('ERROR! Can\'t insert visitor counter data'))->error();
            return false;
        }
    }

    return true;
}

// transaction is finished
if (isset($_POST['finish'])) {
    // create circulation object
    $memberID = $dbs->escape_string($_SESSION['memberID']);
    $circulation = new circulation($dbs, $memberID);
    // finish loan transaction    
    $flush = $circulation->finishLoanSession();
    if ($flush == TRANS_FLUSH_ERROR) {
        // write log
        utility::writeLogs($dbs, 'member', $memberID, 'circulation', 'ERROR : '.$dbs->escape_string($_SESSION['realname']).' FAILED finish circulation transaction with member ('.$memberID.')', 'Transaction', 'Failed');
        toastr(__('ERROR! Loan data can\'t be saved to database'))->error();
        // set error result
        if (ENVIRONMENT === 'development')
        {
            echo '<script>
                parent.$("#errordump").removeClass("d-none").addClass("d-block");
                parent.$("#errordump").html("' . $circulation->error . '");
            </script>';
        }
    } else {
        // insert visitor log
        visitOnLoan($memberID);
        // hook method on after successful transaction
        if (isset($_SESSION['receipt_record'])) {
            Plugins::getInstance()->execute(Plugins::CIRCULATION_AFTER_SUCCESSFUL_TRANSACTION, array ('data' => array_merge($_SESSION['receipt_record'], ['loggedin_user_id' => $memberID], ['loggedin_user_name' => $_SESSION['realname']])));
        }
        // write log
        utility::writeLogs($dbs, 'member', $memberID, 'circulation', $dbs->escape_string($_SESSION['realname']).' finish circulation transaction with member ('.$memberID.')', 'Transaction', 'finished');
        // send message
        echo '<script type="text/javascript">';
        if ($sysconf['transaction_finished_notification']) {
            toastr(__('Transaction finished'))->success();
        }
        // print receipt only if enabled and $_SESSION['receipt_record'] not empty
        if ($sysconf['circulation_receipt'] && isset($_SESSION['receipt_record'])) {
            // open receipt windows
            echo 'parent.openWin(\''.MWB.'circulation/pop_loan_receipt.php\', \'popReceipt\', 350, 500, true);';
        }
        echo 'parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'circulation/index.php\', {method: \'post\', addData: \'finishID='.$memberID.'\'});';
        echo '</script>';
    }
    exit();
}

// return and extend process
if (isset($_POST['process']) AND isset($_POST['loanID'])) {
    $loanID = intval($_POST['loanID']);
    // get loan data
    $loan_q = $dbs->query('SELECT item_code FROM loan WHERE loan_id='.$loanID);
    $loan_d = $loan_q->fetch_row();
    // create circulation object
    $circulation = new circulation($dbs, $dbs->escape_string($_SESSION['memberID']));
    $circulation->ignore_holidays_fine_calc = $sysconf['ignore_holidays_fine_calc'];
	$circulation->holiday_dayname = $_SESSION['holiday_dayname'];
	$circulation->holiday_date = $_SESSION['holiday_date'];
    if ($_POST['process'] == 'return') {
        $return_status = $circulation->returnItem($loanID);
        // write log
        utility::writeLogs($dbs, 'member', $dbs->escape_string($_SESSION['memberID']), 'circulation', $dbs->escape_string($_SESSION['realname']).' return item '.$loan_d[0].' for member ('.$dbs->escape_string($_SESSION['memberID']).')', 'Loan', 'Return');
        if ($circulation->loan_have_overdue) {
            toastr(__('Overdue fines inserted to fines database'))->success();
        }
        echo '<script type="text/javascript">';
        if ($return_status === ITEM_RESERVED) {
            echo 'location.href = \'loan_list.php?reserveAlert='.urlencode($loan_d[0]).'\';';
        } else { 
            echo 'location.href = \'loan_list.php\';'; 
        }
        echo '</script>';
    } else {
        // set holiday settings
        $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
        $circulation->holiday_date = $_SESSION['holiday_date'];
        $extend_status = $circulation->extendItemLoan($loanID);
        if ($extend_status === ITEM_RESERVED) {
            toastr(__('Item CANNOT BE Extended! This Item is being reserved by other member'))->warning();
            echo '<script type="text/javascript">';
            echo 'location.href = \'loan_list.php\';';
            echo '</script>';
        } else {
            // write log
            utility::writeLogs($dbs, 'member', $dbs->escape_string($_SESSION['memberID']), 'circulation', $dbs->escape_string($_SESSION['realname']).' extend loan for item '.$loan_d[0].' for member ('.$dbs->escape_string($_SESSION['memberID']).')', 'Loan', 'Extended');
            toastr(__('Loan Extended'))->success();
            if ($circulation->loan_have_overdue) {
                toastr(__('Overdue fines inserted to fines database'))->success();
            }
            echo '<script type="text/javascript">';
            echo 'location.href = \'loan_list.php\';';
            echo '</script>';
        }
    }
    exit();
}

// add temporary item to session
if (isset($_POST['tempLoanID'])) {
    // create circulation object
    $circulation = new circulation($dbs, $dbs->escape_string($_SESSION['memberID']));
    // set holiday settings
    $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
    $circulation->holiday_date = $_SESSION['holiday_date'];
    // add item to loan session
    $add = $circulation->addLoanSession(trim($_POST['tempLoanID']));
    if ($add == LOAN_LIMIT_REACHED) {
        echo '<html>';
        echo '<body>';
        if ($sysconf['loan_limit_override']) {
            // hidden form holding item code
            echo '<form method="post" name="overrideForm" action="'.MWB.'circulation/circulation_action.php"><input type="hidden" name="overrideID" value="'.$_POST['tempLoanID'].'" /></form>';
            echo '<script type="text/javascript">';
            echo 'var confOverride = confirm(\''.__('Loan Limit Reached!').'\' + "\n" + \''.__('Do You Want To Overide This?').'\');';
            echo 'if (confOverride) { ';
            echo 'document.overrideForm.submit();';
            echo '} else { self.location.href = \'loan.php\';}';
            echo '</script>';
        } else {
            toastr(__('Loan Limit Reached!'))->info();
            echo '<script type="text/javascript">';
            echo 'location.href = \'loan.php\';';
            echo '</script>';
        }
        echo '</body>';
        echo '</html>';
        exit();
    } else if ($add == ITEM_RESERVED) {
        // hidden form holding item code
        echo '<html>';
        echo '<body>';
        echo '<form method="post" name="overrideForm" action="'.MWB.'circulation/circulation_action.php">';
        echo '<input type="hidden" name="overrideID" value="'.$_POST['tempLoanID'].'" /></form>';
        echo '<script type="text/javascript">';
        echo 'var confOverride = confirm(\''.__('WARNING! This Item is reserved by another member').'\' + "\n" + \''.__('Do You Want To Overide This?').'\');';
        echo 'if (confOverride) { ';
        echo 'document.overrideForm.submit();';
        echo '} else { self.location.href = \'loan.php\';}';
        echo '</script>';
        echo '</body>';
        echo '</html>';
        exit();
    } else if ($add == ITEM_NOT_FOUND) {
        toastr(__('This Item is not registered in database'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    } else if ($add == ITEM_UNAVAILABLE) {
        toastr(__('This Item is currently not available'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    } else if ($add == LOAN_NOT_PERMITTED) {
        toastr(__('Loan NOT PERMITTED! Membership already EXPIRED!'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    } else if ($add == LOAN_NOT_PERMITTED_PENDING) {
        toastr(__('Loan NOT PERMITTED! Membership under PENDING State!'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    } else if ($add == ITEM_LOAN_FORBID) {
        toastr(__('Loan Forbidden for this Item!'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    } else {
        utility::writeLogs($dbs, 'member', $dbs->escape_string($_SESSION['memberID']), 'circulation', $dbs->escape_string($_SESSION['realname']).' insert new loan ('.$_POST['tempLoanID'].') for member ('.$dbs->escape_string($_SESSION['memberID']).')', 'Loan', 'Add');
        echo '<script type="text/javascript">';
        echo 'location.href = \'loan.php\';';
        echo '</script>';
    }
    exit();
}

// loan limit override
if (isset($_POST['overrideID']) AND !empty($_POST['overrideID'])) {
    // define constant
    define('IGNORE_LOAN_RULES', 1);
    // create circulation object
    $circulation = new circulation($dbs, $dbs->escape_string($_SESSION['memberID']));
    // set holiday settings
    $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
    $circulation->holiday_date = $_SESSION['holiday_date'];
    // add item to loan session
    $add = $circulation->addLoanSession($_POST['overrideID']);
    echo '<script type="text/javascript">';
    echo 'location.href = \'loan.php\';';
    echo '</script>';
    exit();
}

// remove temporary item session
if (isset($_GET['removeID'])) {
    // create circulation object
    $circulation = new circulation($dbs, $dbs->escape_string($_SESSION['memberID']));
    // remove item from loan session
    $circulation->removeLoanSession($_GET['removeID']);
    $msg = str_replace('{removeID}', $_GET['removeID'], __('Item {removeID} removed from session')); //mfc
    toastr($msg)->success();
    echo '<script type="text/javascript">';
    echo 'location.href = \'loan.php\';';
    echo '</script>';
    exit();
}

// quick return proccess
if (isset($_POST['quickReturnID']) AND $_POST['quickReturnID']) {
    // get loan data
    $loan_info_q = $dbs->query("SELECT l.*,m.member_id,m.member_name,b.title, b.classification, mt.member_type_name FROM loan AS l
        LEFT JOIN item AS i ON i.item_code=l.item_code
        LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
        LEFT JOIN member AS m ON l.member_id=m.member_id
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        WHERE l.item_code='".$dbs->escape_string($_POST['quickReturnID'])."' AND is_lent=1 AND is_return=0");
    if ($loan_info_q->num_rows < 1) {
        echo '<div class="errorBox">'.__('This is item already returned or not exists in loan database').'</div>';
    } else {
        $return_date = date('Y-m-d');
        // get data
        $loan_d = $loan_info_q->fetch_assoc();
        // create circulation object
        $circulation = new circulation($dbs, $loan_d['member_id']);

        /* modified by Indra Sutriadi */
        $circulation->ignore_holidays_fine_calc = $sysconf['ignore_holidays_fine_calc'];
        $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
        $circulation->holiday_date = $_SESSION['holiday_date'];
        /* end of modification */

        // check for overdue
        $overdue = $circulation->countOverdueValue($loan_d['loan_id'], $return_date);
        // check overdue
        if ($overdue) {
            $msg = str_replace('{overdueDays}', $overdue['days'],__('OVERDUED for {overdueDays} days(s) with fines value of')); //mfc
            $loan_d['title'] .= '<div style="color: red; font-weight: bold;">'.$msg.$overdue['value'].'</div>';
        }
        // return item
        $return_status = $circulation->returnItem($loan_d['loan_id']);
        if ($return_status === ITEM_RESERVED) {
            // get reservation data
            $reserve_q = $dbs->query('SELECT r.member_id, m.member_name
                FROM reserve AS r
                LEFT JOIN member AS m ON r.member_id=m.member_id
                WHERE item_code=\''.$loan_d['item_code'].'\' ORDER BY reserve_date ASC LIMIT 1');
            $reserve_d = $reserve_q->fetch_row();
            $member = $reserve_d[1].' ('.$reserve_d[0].')';
            $reserve_msg = str_replace(array('{itemCode}', '{member}'), array($loan_d['item_code'], $member), __('Item {itemCode} is being reserved by member {member}')); //mfc
            $loan_d['title'] .= '<div>'.$reserve_msg.'</div>';
        }
        // write log
        utility::writeLogs($dbs, 'member', $loan_d['member_id'], 'circulation', $dbs->escape_string($_SESSION['realname']).' return item ('.$_POST['quickReturnID'].') with title ('.$loan_d['title'].') with Quick Return method', 'Quick Loan', 'Returned');
        # Support for circulation_after_successful_transaction hook in 
        # quick return (circulation)
        $return_data = array();
        $return_data['memberID'] = $loan_d['member_id'];
        $return_data['memberName'] = $loan_d['member_name'];
        $return_data['memberType'] = $loan_d['member_type_name'];
        $return_data['date'] = date("Y-m-d h:i:s");
        $return_data['return'][0] = array ();
        $return_data['return'][0] = $loan_d;
        $return_data['return'][0]['itemCode'] = $_POST['quickReturnID'];
        $return_data['return'][0]['title'] = $loan_d['title'];
        $return_data['return'][0]['returnDate'] = date("Y-m-d");
        $return_data['return'][0]['overdues'] = $overdue;
        $return_data['return'][0]['loan_id'] = $loan_d['loan_id'];
        $return_data['return'][0]['is_return'] = (INT)$loan_d['is_return'] + 1;
        Plugins::getInstance()->execute(Plugins::CIRCULATION_AFTER_SUCCESSFUL_TRANSACTION, array ('data' => $return_data));
        // show loan information
        include SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
        // create table object
        $table = new simbio_table();
        $table->table_attr = 'class="border" style="width: 100%; margin-bottom: 5px;" cellpadding="5" cellspacing="0"';
        // append data to table row
        $table->appendTableRow(array(str_replace(array('{itemCode}', '{returnDate}'), array($_POST['quickReturnID'], $return_date), __('Item {itemCode} successfully returned on&nbsp;{returnDate}')))); //mfc
        $table->appendTableRow(array(__('Title'), $loan_d['title']));
        $table->appendTableRow(array(__('Member Name'), $loan_d['member_name'], __('Member ID'), $loan_d['member_id']));
        $table->appendTableRow(array(__('Loan Date'), $loan_d['loan_date'], __('Due Date'), $loan_d['due_date']));
        // set the cell attributes
        $table->setCellAttr(1, 0, 'class="dataListHeader" style="color: #fff; font-weight: bold;" colspan="4"');
        $table->setCellAttr(2, 0, 'class="alterCell"');
        $table->setCellAttr(2, 1, 'class="alterCell2" colspan="3"');
        $table->setCellAttr(3, 0, 'class="alterCell" width="15%"');
        $table->setCellAttr(3, 1, 'class="alterCell2" width="35%"');
        $table->setCellAttr(3, 2, 'class="alterCell" width="15%"');
        $table->setCellAttr(3, 3, 'class="alterCell2" width="35%"');
        $table->setCellAttr(4, 0, 'class="alterCell" width="15%"');
        $table->setCellAttr(4, 1, 'class="alterCell2" width="35%"');
        $table->setCellAttr(4, 2, 'class="alterCell" width="15%"');
        $table->setCellAttr(4, 3, 'class="alterCell2" width="35%"');
        // print out the table
        echo $table->printTable();
    }
    exit();
}

// add reservation
if (isset($_POST['reserveItemID'])) {
    $item_id = $dbs->escape_string(trim($_POST['reserveItemID']));
    if (!$item_id) {
        toastr(__('NO DATA Selected to reserve!'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'reserve_list.php\';';
        echo '</script>';
        die();
    }
    // get reservation limit from member type
    $reserve_limit_q = $dbs->query('SELECT reserve_limit FROM mst_member_type WHERE member_type_id='.(integer)$_SESSION['memberTypeID']);
    $reserve_limit_d = $reserve_limit_q->fetch_row();
    // get current reservation data for this member
    $current_reserve_q = $dbs->query('SELECT COUNT(reserve_id) FROM reserve WHERE member_id=\''.$dbs->escape_string(trim($_SESSION['memberID'])).'\'');
    $current_reserve_d = $current_reserve_q->fetch_row();
    if ($current_reserve_d[0] >= $reserve_limit_d[0]) {
        toastr(__('Can not add more reservation. Maximum limit reached'))->error();
        echo '<script type="text/javascript">';
        echo 'location.href = \'reserve_list.php\';';
        echo '</script>';
        die();
    }

    // get biblio data for this item
    $biblio_q = $dbs->query('SELECT i.biblio_id, ist.rules FROM biblio AS b
        LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
        LEFT JOIN mst_item_status AS ist ON i.item_status_id=ist.item_status_id
        WHERE i.item_code=\''.$dbs->escape_string($item_id).'\'');
    $biblio_d = $biblio_q->fetch_row();
    // check if this item is forbidden
    if (!empty($biblio_d[1])) {
        $arr_rules = @unserialize($biblio_d[1]);
        if ($arr_rules) {
            if (in_array(NO_LOAN_TRANSACTION, $arr_rules)) {
                toastr(__('Can\'t reserve this Item. Loan Forbidden!'))->error();
                echo '<script type="text/javascript">';
                echo 'location.href = \'reserve_list.php\';';
                echo '</script>';
                die();
            }
        }
    }
    // get the availability status
    $avail_q = $dbs->query('SELECT COUNT(l.loan_id) FROM loan AS l
        WHERE l.item_code=\''.$item_id.'\' AND l.is_lent=1 AND l.is_return=0 AND l.member_id!=\''.$dbs->escape_string($_SESSION['memberID']).'\'');
    $avail_d = $avail_q->fetch_row();
    if ($avail_d[0] > 0) {
        // write log
        utility::writeLogs($dbs, 'member', $dbs->escape_string($_SESSION['memberID']), 'circulation', $dbs->escape_string($_SESSION['realname']).' reserve item '.$item_id.' for member ('.$dbs->escape_string($_SESSION['memberID']).')', 'Reservation', 'Add');
        // add reservation to database
        $reserve_date = date('Y-m-d H:i:s');
        $dbs->query('INSERT INTO reserve(member_id, biblio_id, item_code, reserve_date) VALUES (\''.$dbs->escape_string($_SESSION['memberID']).'\', \''.$biblio_d[0].'\', \''.$item_id.'\', \''.$reserve_date.'\')');
        toastr(__('Reservation added'))->success();
        echo '<script type="text/javascript">';
        echo 'location.href = \'reserve_list.php\';';
        echo '</script>';
    } else {
        toastr(__('Item for this title is already available or already on hold by this member!'))->info();
        echo '<script type="text/javascript">';
        echo 'location.href = \'reserve_list.php\';';
        echo '</script>';
    }
    exit();
}

// remove reservation item
if (isset($_POST['reserveID']) AND !empty($_POST['reserveID'])) {
    $reserveID = intval($_POST['reserveID']);
    // get reserve data
    $reserve_q = $dbs->query('SELECT item_code FROM reserve WHERE reserve_id='.$reserveID);
    $reserve_d = $reserve_q->fetch_row();
    // delete reservation record from database
    $dbs->query('DELETE FROM reserve WHERE reserve_id='.$reserveID);
    // write log
    utility::writeLogs($dbs, 'member', $dbs->escape_string($_SESSION['memberID']), 'circulation', $dbs->escape_string($_SESSION['realname']).' remove reservation for item '.$reserve_d[0].' for member ('.$dbs->escape_string($_SESSION['memberID']).')', 'Reservation', 'Delete');
    toastr(__('Reservation removed'))->success();
    echo '<script type="text/javascript">';
    echo 'location.href = \'reserve_list.php\';';
    echo '</script>';
    exit();
}

// removing fines
if (isset($_POST['removeFines'])) {
    foreach ($_POST['removeFines'] as $fines_id) {
        $fines_id = intval($fines_id);
        // change loan data
        $dbs->query("DELETE FROM fines WHERE fines_id=$fines_id");
    }
    toastr(__('Fines data removed'))->success();
    echo '<script type="text/javascript">';
    echo 'location.href = \'fines_list.php\';';
    echo '</script>';
    exit();
}

// transaction is started
if (isset($_POST['memberID']) OR isset($_SESSION['memberID'])) {
    // create member object
    // if there is already member ID session
    if (isset($_SESSION['memberID'])) {
        $memberID = trim($_SESSION['memberID']);
    } else {
        // new transaction proccess
        // clear previous sessions
        $_SESSION['temp_loan'] = array();
        $memberID = trim(preg_replace('@\s*(<.+)$@i', '', $_POST['memberID']));
        // write log
        utility::writeLogs($dbs, 'member', $memberID, 'circulation', $dbs->escape_string($_SESSION['realname']).' start transaction with member ('.$memberID.')', 'Loan', 'Started');
    }
    $member = new member($dbs, $memberID);
    if (!$member->valid()) {
        echo '<div class="errorBox">'.__('Member ID').' '.$memberID.' '.__(' not valid (unregistered in database)').'</div>'; //mfc
    } else {
        // get member information
        $member_type_d = $member->getMemberTypeProp();
        // member type ID
        $_SESSION['memberTypeID'] = $dbs->escape_string($member->member_type_id);
        // save member ID to the sessions
        $_SESSION['memberID'] = $dbs->escape_string($member->member_id);
        // create renewed/reborrow session array
        $_SESSION['reborrowed'] = array();
        // check membership expire
        $_SESSION['is_expire'] = $member->isExpired();
        // check if membership is blacklisted
        $_SESSION['is_pending'] = $member->isPending();
        // print record
        $_SESSION['receipt_record'] = array();
        // set HTML buttons disable flag
        $disabled = '';
        $add_style = '';
        // check for expire date and pending state
        if ($_SESSION['is_expire'] OR $_SESSION['is_pending']) {
            $disabled = ' disabled ';
            $add_style = ' disabled';
        }
        // show the member information
        echo '<div class="per_title">';
        echo '<h2>'.__('Circulation').'</h2>';
        echo '</div>';
        echo '<table width="100%" class="s-member__account" cellpadding="5" cellspacing="0">'."\n";
        echo '<tr>'."\n";
        echo '<td colspan="5">';
        // hidden form for transaction finish
        echo '<form id="finishForm" method="post" target="blindSubmit" action="'.MWB.'circulation/circulation_action.php">
        <input type="button" class="btn btn-danger" id="circFinish" accesskey="T" value="'.__('Finish Transaction').' (Esc)" onclick="confSubmit(\'finishForm\', \''.__('Are you sure want to finish current transaction?').'\', '.config('enable_chbox_confirm', '1').')" /><input type="hidden" name="finish" value="true" /></form>';
        echo '</td>';
        echo '</tr>'."\n";
        echo '<tr>'."\n";
        echo '<td class="alterCell" width="15%"><strong>'.__('Member Name').'</strong></td><td class="alterCell2" width="30%">'.$member->member_name.'</td>';
        echo '<td class="alterCell" width="15%"><strong>'.__('Member ID').'</strong></td><td class="alterCell2" width="30%">'.$member->member_id.'</td>';
        // member photo
        if ($member->member_image) {
          if (file_exists(IMGBS.'persons/'.$member->member_image)) {
            echo '<div class="s-member__photo">';
            echo '<img src="'.SWB.'lib/minigalnano/createthumb.php?filename=images/persons/'.urlencode($member->member_image).'&amp;width=100" />';
            echo '</div>';
          }
        }
        echo '</tr>'."\n";
        echo '<tr>'."\n";
        echo '<td class="alterCell" width="15%"><strong>'.__('Member Email').'</strong></td><td class="alterCell2" width="30%">'.$member->member_email.'</td>';
        echo '<td class="alterCell" width="15%"><strong>'.__('Member Type').'</strong></td><td class="alterCell2" width="30%">'.$member->member_type_name.'</td>';
        echo '</tr>'."\n";
        echo '<tr>'."\n";
        echo '<td class="alterCell" width="15%"><strong>'.__('Register Date').'</strong></td><td class="alterCell2" width="30%">'.$member->register_date.'</td>';
        // give notification about expired membership and pending
        $expire_msg = '';
        if ($_SESSION['is_expire']) {
            $expire_msg .= '<span class="error">('.__('Membership Already Expired').')</span>';
        }
        echo '<td class="alterCell" width="15%"><strong>'.__('Expiry Date').'</strong></td><td class="alterCell2" width="30%">'.$member->expire_date.' '.$expire_msg.'</td>';
        echo '</tr>'."\n";
        // member notes and pending information
        if (!empty($member->member_notes) OR $_SESSION['is_pending']) {
          echo '<tr>'."\n";
          echo '<td class="alterCell" width="15%"><strong>'.__('Notes').'</strong></td><td class="alterCell2" colspan="4">';
          if ($member->member_notes) {
              echo '<div class=\'member_notes\'>'.$member->member_notes.'</div>';
          }
          if ($_SESSION['is_pending']) {
              echo '<div class="error">('.__('Membership currently in pending state, loan transaction is locked.').')</div>';
          }
          echo '</td>';
          echo '</tr>'."\n";
        }
        echo '</table>'."\n";
        // tab and iframe

        $fines_alert = FALSE;
        $total_unpaid_fines = 0;
        $_unpaid_fines = $dbs->query('SELECT 1 FROM fines WHERE member_id=\''.$dbs->escape_string($_SESSION['memberID']).'\' AND debet > credit LIMIT 1');
        if($_unpaid_fines->fetch_row()) {
            $fines_alert = TRUE;
        }

        $count_q = $dbs->query("SELECT COUNT(loan_id) FROM loan WHERE is_return = 0 AND is_lent = 1 AND member_id = '".$dbs->escape_string($_SESSION['memberID'])."'");
        $count_d = $count_q->fetch_row();
        if ($count_d[0] > 0) {
          $active_loan = '';
          $active_loan_list = 'active';
          $iframe_src = 'modules/circulation/loan_list.php';
        } else {
          $active_loan = 'active';
          $active_loan_list = '';
          $iframe_src = 'modules/circulation/loan.php';
        }

		echo '<div class="nav nav-tabs" id="transaction" role="tablist">';
        echo '<div id="errordump" class="w-100 d-none p-3 mb-2 bg-danger text-white font-weight-bold"></div>';
        echo '<a class="nav-item nav-link '.$active_loan.' notAJAX" id="circLoan" href="'.MWB.'circulation/loan.php" target="listsFrame">'.__('Loans').' (F2)</a>';
        echo '<a class="nav-item nav-link '.$active_loan_list.' notAJAX" id="circInLoan" href="'.MWB.'circulation/loan_list.php" target="listsFrame">'.__('Current Loans').' (F3)</a>';
        if ($member_type_d['enable_reserve']) {
          echo '<a class="nav-item nav-link notAJAX" id="circReserve" href="'.MWB.'circulation/reserve_list.php" target="listsFrame">'.__('Reserve').' (F4)</a>';
        }
        if ($fines_alert) {
            echo '<a class="nav-item nav-link notAJAX" id="circFine" href="'.MWB.'circulation/fines_list.php" target="listsFrame"><strong class="text-danger">'.__('Fines').' (F9)</strong></a>';
        } else {
            echo '<a class="nav-item nav-link notAJAX" id="circFine" href="'.MWB.'circulation/fines_list.php" target="listsFrame">'.__('Fines').' (F9)</a>';            
        }
        echo '<a class="nav-item nav-link notAJAX" id="circHistory" href="'.MWB.'circulation/member_loan_hist.php" target="listsFrame">'.__('Loan History').' (F10)</a>'."\n";
        echo '</div>';
        echo '<iframe src="'.$iframe_src.'" id="listsFrame" name="listsFrame" class="s-iframe expandable"></iframe>'."\n";
    }

    // Include Barcode Scanner
    if($sysconf['barcode_reader']) {
        ob_start();
        require SB.'admin/'.$sysconf['admin_template']['dir'].'/barcodescannermodal.tpl.php';
        $barcode = ob_get_clean();
        echo $barcode;
        echo '<script>parent.$(".modal-backdrop").remove(); </script>';
    } 
    
    ?>

    <script type="text/javascript">
    /**
     * increase the height of the iframe so that it uses all remaining space
     */
    function resizeIframe() {
        // remove the event handler if the iframe doesn't exist anymore
        if($('#listsFrame').length === 0) {
            $(window).off('resize', resizeIframe);
            return;
        }
        var buffer = 20; //scroll bar buffer
        var height = $(window).height(); // height of the whole page
        height -= $('#listsFrame').offset().top; // minus everything that is above of the iframe
        height -= $('.s-footer').innerHeight(); // minus everything that is on the below the iframe
        height -= buffer; // minus some buffer for scrollbars and such
        height = (height < 0) ? 0 : height; // in case we dont have enough space, the min-height=370px from css kicks in
        $('#listsFrame').css('height', height + 'px');
    }

    // call it once now
    resizeIframe();
    // and again if the window is resized
    $(window).on('resize', resizeIframe);

    $('#transaction .nav-link').click(function(){
        $(this).each(function(){
            $('a').removeClass('active');
        });
        $(this).addClass('active');
    });

    $(document.body).bind('keyup', this, function(e){
        // alert(e.keyCode);
        // ESC
        if(e.keyCode == 27) {
            $('#circFinish').click();
        }
        // F2
        if(e.keyCode == 113) {
            $('#circLoan').click();
            $('#listsFrame').attr('src', $('#circLoan').attr('href'));
        }
        // F3
        if(e.keyCode == 114) {
            $('#circInLoan').click();
            $('#listsFrame').attr('src', $('#circInLoan').attr('href'));
        }
        // F4
        if(e.keyCode == 115) {
            $('#circReserve').click();
            $('#listsFrame').attr('src', $('#circReserve').attr('href'));
        }
        // F9
        if(e.keyCode == 120) {
            $('#circFine').click();
            $('#listsFrame').attr('src', $('#circFine').attr('href'));
        }
        // F10
        if(e.keyCode == 121) {
            $('#circHistory').click();
            $('#listsFrame').attr('src', $('#circHistory').attr('href'));
        }
    });
    </script>
    <?php
    exit();
}
