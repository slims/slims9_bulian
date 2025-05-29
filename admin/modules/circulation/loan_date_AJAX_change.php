<?php
/**
 * Handler script for AJAX manual loan date change.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
define('INDEX_AUTH', '1');

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// session checking
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// get ID of loan session
$loanSessionID = trim(strip_tags($_POST['loanSessionID']));
if (isset($_POST['newLoanDate']) && trim($_POST['newLoanDate']) != '') {
    $newLoanDate = trim($_POST['newLoanDate']);
    $newDates = array('newDate' => $newLoanDate);
    $_SESSION['temp_loan'][$loanSessionID]['loan_date'] = $newLoanDate;
}
if (isset($_POST['newDueDate']) && trim($_POST['newDueDate']) != '') {
    $newDueDate = trim($_POST['newDueDate']);
    $newDates = array('newDate' => $newDueDate);
    $_SESSION['temp_loan'][$loanSessionID]['due_date'] = $newDueDate;
}
// parse to json
echo json_encode($newDates);
