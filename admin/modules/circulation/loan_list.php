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

/* loan list iframe content */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}


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

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
require MDLBS.'membership/member_base_lib.inc.php';
require MDLBS.'circulation/circulation_base_lib.inc.php';

// page title
$page_title = 'Member Loan List';

// start the output buffering
ob_start();
?>
<!--loan specific javascript functions-->
<script type="text/javascript">
function confirmProcess(intLoanID, strItemCode, strProcess)
{
    if (strProcess == 'return') {
        var confirmBox = confirm('<?php echo __('Are you sure you want to return the item'); ?> ' + strItemCode);
    } else {
        var confirmBox = confirm('<?php echo __('Are you sure to extend loan for'); ?> ' + strItemCode); //mfc
    }

    if (confirmBox) {
        // fill the hidden form value
        document.loanHiddenForm.process.value = strProcess;
        document.loanHiddenForm.loanID.value = intLoanID;
        // submit hidden form
        document.loanHiddenForm.submit();
    }
}
</script>
<!--loan specific javascript functions end-->
<?php
// check if there is member ID
if (isset($_SESSION['memberID'])) {
    $memberID = trim($_SESSION['memberID']);
    $circulation = new circulation($dbs, $memberID);
    $loan_list_query = $dbs->query(sprintf("SELECT L.loan_id, b.title, ct.coll_type_name,
        i.item_code, L.loan_date, L.due_date, L.return_date, L.renewed,
        IF(lr.reborrow_limit IS NULL, IF(L.renewed>=mt.reborrow_limit, 1, 0), IF(L.renewed>=lr.reborrow_limit, 1, 0)) AS extend
        FROM loan AS L
        LEFT JOIN item AS i ON L.item_code=i.item_code
        LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
        LEFT JOIN member AS m ON L.member_id=m.member_id
        LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
        LEFT JOIN mst_loan_rules AS lr ON mt.member_type_id=lr.member_type_id AND i.coll_type_id = lr.coll_type_id
        LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
        WHERE L.is_lent=1 AND L.is_return=0 AND L.member_id='%s'", $memberID)); // query modified by Indra Sutriadi

    // create table object
    $loan_list = new simbio_table();
    $loan_list->table_attr = 'align="center" width="100%" cellpadding="3" cellspacing="0"';
    $loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    $loan_list->highlight_row = true;
    // table header
    $headers = array(__('Return'), __('Extend'), __('Item Code'), __('Title'), __('Col. Type'), __('Loan Date'), __('Due Date'));
    $loan_list->setHeader($headers);
    // row number init
    $row = 1;
    $is_overdue = false;
    /* modified by Indra Sutriadi */
    $circulation->ignore_holidays_fine_calc = $sysconf['ignore_holidays_fine_calc'];
    $circulation->holiday_dayname = $_SESSION['holiday_dayname'];
    $circulation->holiday_date = $_SESSION['holiday_date'];
    /* end of modification */
    $_total_temp_fines = 0; #newly added
    while ($loan_list_data = $loan_list_query->fetch_assoc()) {
        // alternate the row color
        $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

        // return link
        $return_link = '<a href="#" onclick="confirmProcess('.$loan_list_data['loan_id'].', \''.$loan_list_data['item_code'].'\', \'return\')" title="'.__('Return this item').'" class="returnLink">&nbsp;</a>';
        // extend link
        // check if membership already expired
        if ($_SESSION['is_expire']) {
            $extend_link = '<span class="noExtendLink" title="'.__('No Extend').'">&nbsp;</span>';
        } else {
            // check if this loan just already renewed
            if ($loan_list_data['return_date'] == date('Y-m-d') || in_array($loan_list_data['loan_id'], $_SESSION['reborrowed']) || $loan_list_data['extend'] == 1) {
                $extend_link = '<span class="noExtendLink" title="'.__('No Extend').'">&nbsp;</span>';
            } else {
                $extend_link = '<a href="#" onclick="confirmProcess('.$loan_list_data['loan_id'].', \''.$loan_list_data['item_code'].'\', \'extend\')" title="'.__('Extend loan for this item').'" class="extendLink">&nbsp;</a>';
            }
        }
        // renewed flag
        if ($loan_list_data['renewed'] > 0) {
            $loan_list_data['title'] = $loan_list_data['title'].' - <strong style="color: blue;">'.__('Extended').'</strong>';
        }
        // check for overdue
        $curr_date = date('Y-m-d');
        $overdue = $circulation->countOverdueValue($loan_list_data['loan_id'], $curr_date);
        if ($overdue) {
            $is_overdue = true;
            $loan_list_data['title'] .= '<div style="color: red; font-weight: bold;">'.__('OVERDUED for').' '.$overdue['days'].' '.__('days(s) with fines value').' '.$overdue['value'].'</div>'; //mfc
            $_total_temp_fines = $_total_temp_fines + $overdue['value']; #newly added
        }
        // row colums array
        $fields = array(
            $return_link,
            $extend_link,
            $loan_list_data['item_code'],
            $loan_list_data['title'],
            $loan_list_data['coll_type_name'],
            $loan_list_data['loan_date'],
            $loan_list_data['due_date']
            );

        // append data to table row
        $loan_list->appendTableRow($fields);
        // set the HTML attributes
        $loan_list->setCellAttr($row, null, "valign='top' class='$row_class'");
        $loan_list->setCellAttr($row, 0, "valign='top' align='center' class='$row_class' style='width: 5%;'");
        $loan_list->setCellAttr($row, 1, "valign='top' align='center' class='$row_class' style='width: 5%;'");
        $loan_list->setCellAttr($row, 2, "valign='top' class='$row_class' style='width: 10%;'");
        $loan_list->setCellAttr($row, 3, "valign='top' class='$row_class' style='width: 50%;'");
        $loan_list->setCellAttr($row, 4, "valign='top' class='$row_class' style='width: 15%;'");

        $row++;
    }
    // show reservation alert if any
    if (isset($_GET['reserveAlert']) AND !empty($_GET['reserveAlert'])) {
        $reservedItem = $dbs->escape_string(trim($_GET['reserveAlert']));
        // get reservation data
        $reserve_q = $dbs->query(sprintf('SELECT r.member_id, m.member_name
            FROM reserve AS r
            LEFT JOIN member AS m ON r.member_id=m.member_id
            WHERE item_code=\'%s\' ORDER BY reserve_date ASC LIMIT 1', $reservedItem));
        $reserve_d = $reserve_q->fetch_row();
        $member = $reserve_d[1].' ('.$reserve_d[0].')';
        $reserve_msg = str_replace(array('{itemCode}', '{member}'), array('<b>'.$reservedItem.'</b>', '<b>'.$member.'</b>'), __('Item {itemCode} is being reserved by member {member}'));
        echo '<div class="infoBox">'.$reserve_msg.'</div>';
    }

    // create e-mail lin if there is overdue
    if ($is_overdue) {
        echo '<div style="padding: 5px; background: #ccc;"><div id="emailStatus"></div><a class="sendEmail usingAJAX" href="'.MWB.'membership/overdue_mail.php'.'" postdata="memberID='.$memberID.'" loadcontainer="emailStatus">'.__('Send overdues notice e-mail').'</a> | <span style="color: red; font-weight: bold;">'.__('Total of temporary fines').': '.$_total_temp_fines.'.</span></div>'."\n";
    }
    echo $loan_list->printTable();
    // hidden form for return and extend process
    echo '<form name="loanHiddenForm" method="post" action="circulation_action.php"><input type="hidden" name="process" value="return" /><input type="hidden" name="loanID" value="" /></form>';
?>
<script type="text/javascript">
// registering event for send email button
$(document).ready(function() {
    $('a.usingAJAX').click(function(evt) {
        evt.preventDefault();
        var anchor = $(this);
        // get anchor href
        var url = anchor.attr('href');
        var postData = anchor.attr('postdata');
        var loadContainer = anchor.attr('loadcontainer');
        if (loadContainer) { container = jQuery('#'+loadContainer); }
        // set ajax
        if (postData) {
            container.simbioAJAX(url, {method: 'post', addData: postData});
        } else {
            container.simbioAJAX(url, {addData: {ajaxload: 1}});
        }
    });
});
</script>
<?php
}

// get the buffered content
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
