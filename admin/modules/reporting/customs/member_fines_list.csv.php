<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-08 15:41:21
 * @modify date 2022-11-08 16:16:09
 * @license GPLv3
 * @desc [description]
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}


foreach ($_SESSION['csvData'] as $memberId => $data) {
    $finesStatement = \SLiMS\DB::getInstance()
                        ->prepare('SELECT f.debet,f.credit, f.description, f.fines_date FROM fines AS f WHERE f.member_id= ? ' . $data['dateCriteria']);
    $finesStatement->execute([$memberId]);
}