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

/*
A Handler script for Item data AJAX Lookup
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
