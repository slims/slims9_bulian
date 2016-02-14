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

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if (!isset($_SESSION['memberID'])) { die(); }

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_UTILS/simbio_date.inc.php';

// page title
$page_title = 'Member Loan List';

ob_start();
?>
<script type="text/javascript">
/**
 * Change date text to input text
 */
$(document).ready(function() {
    $('.dateChange').click(function(evt) {
        evt.preventDefault();
        var dateText = $(this);
        var loanID = dateText.attr('data');
        // check if it is due or loan date
        var dateToChange = 'due';
        var inputDateClass = 'dateChangeInput dueInput';
        if (dateText.hasClass('loan')) {
            dateToChange = 'loan';
            inputDateClass = 'dateChangeInput loanInput';
        }
        var dateContent = dateText.text().trim();
        var dateInputField = $('<input type="text" value="' + dateContent + '" class="' + inputDateClass + '" maxlength="10" size="10" />');
        dateText.before(dateInputField).hide();
        dateInputField.focus().blur(function() {
                var dateInputField = $(this);
                changeLoanDate(loanID, dateToChange, dateInputField, dateInputField.val());
            } ).keyup(function(evt) {
                    if (evt.keyCode == 13) {
                        changeLoanDate(loanID, dateToChange, dateInputField, dateInputField.val());
                    }
                });
    });
});

/**
 * Function to send AJAX request to change loan and due date
 */
var changeLoanDate = function(intLoanID, strDateToChange, dateElement, strDate)
{
    var dateData = {newLoanDate: strDate, loanSessionID: intLoanID};
    var dateText = $('.'+strDateToChange+'[data='+intLoanID+']');
    if (strDateToChange == 'due') { dateData = {newDueDate: strDate, loanSessionID: intLoanID}; }
    jQuery.ajax({url: '<?php echo MWB.'circulation/loan_date_AJAX_change.php'; ?>', type: 'POST',
        data: dateData,
        dataType: 'json',
        success: function(ajaxRespond) {
                if (!ajaxRespond) {
                    return;
                }
                // evaluate json respons
                var sessionDate = ajaxRespond;
                // update date element
                dateText.html(sessionDate.newDate);
            }
        });
    // remove input date
    dateElement.remove();
    dateText.show();
}
</script>
<?php
$js = ob_get_clean();

// start the output buffering
ob_start();
// check if there is member ID
if (isset($_SESSION['memberID'])) {
    $memberID = trim($_SESSION['memberID']);
    ?>
    <!--item loan form-->
    <div class="loanItemCodeInput">
        <form name="itemLoan" id="loanForm" action="circulation_action.php" method="post" style="display: inline;">
            <?php echo __('Insert Item Code/Barcode'); ?> :
            <input type="text" id="tempLoanID" name="tempLoanID" />
            <input type="submit" value="<?php echo __('Loan'); ?>" class="btn btn-warning button" />
        </form>
    </div>
    <script type="text/javascript">$('#tempLoanID').focus();</script>
    <!--item loan form end-->
    <?php
    // make a list of temporary loan if there is any
    if (count($_SESSION['temp_loan']) > 0) {
        // create table object
        $temp_loan_list = new simbio_table();
        $temp_loan_list->table_attr = "align='center' style='width: 100%;' cellpadding='3' cellspacing='0'";
        $temp_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $temp_loan_list->highlight_row = true;
        // table header
        $headers = array(__('Remove'),  __('Item Code'), __('Title'), __('Loan Date'), __('Due Date'));
        $temp_loan_list->setHeader($headers);
        // row number init
        $row = 1;
        foreach ($_SESSION['temp_loan'] as $_loan_ID => $temp_loan_list_d) {
            // alternate the row color
            $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

            // remove link
            $remove_link = '<a href="circulation_action.php?removeID='.$temp_loan_list_d['item_code'].'" title="Remove this item" class="trashLink">&nbsp;</a>';

            // check if manually changes loan and due date allowed
            if ($sysconf['allow_loan_date_change']) {
                $loan_date = '<a href="#" title="'.__('Click To Change Loan Date').'" class="dateChange loan notAJAX" data="'.$_loan_ID.'" id="loanDate'.$row.'">'.$temp_loan_list_d['loan_date'].'</a>';
                $due_date = '<a href="#" title="'.__('Click To Change Due Date').'" class="dateChange due notAJAX" data="'.$_loan_ID.'" id="dueDate'.$row.'">'.$temp_loan_list_d['due_date'].'</a>';
            } else {
                $loan_date = $temp_loan_list_d['loan_date'];
                $due_date = $temp_loan_list_d['due_date'];
            }

            // row colums array
            $fields = array(
                $remove_link, $temp_loan_list_d['item_code'],
                $temp_loan_list_d['title'], $loan_date, $due_date);

            // append data to table row
            $temp_loan_list->appendTableRow($fields);
            // set the HTML attributes
            $temp_loan_list->setCellAttr($row, null, 'class="'.$row_class.'"');
            $temp_loan_list->setCellAttr($row, 0, 'valign="top" align="center" style="width: 5%;"');
            $temp_loan_list->setCellAttr($row, 1, 'valign="top" style="width: 10%;"');
            $temp_loan_list->setCellAttr($row, 2, 'valign="top" style="width: 60%;"');

            $row++;
        }

        echo $temp_loan_list->printTable();
    }

}

// get the buffered content
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
