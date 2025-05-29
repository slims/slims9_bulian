<?php
/**
 * Overdue mail processing.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Membership
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
do_checkIP('smc-membership');
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
if (!$can_read) { die(); }

require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
require MDLBS.'membership/Membership.php';

if (is_null(config('mail'))) die('<div class="alert alert-warning">'.__('E-Mail configuration is not ready!').'</div>');

// get data
$memberID = $dbs->escape_string(trim($_POST['memberID']));
// create member Instance
$member = new Membersip($dbs, $memberID);
// send e-mail
$status = $member->sendOverdueNotice();
// get message
$alertType = $status['status'] == 'SENT' ? 'alert-success' : 'alert-danger';
echo '<div class="alert ' . $alertType . '">' . $status['message'] . '</div>';
