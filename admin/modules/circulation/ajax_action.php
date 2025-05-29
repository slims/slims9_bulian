<?php
/**
 * Circulation AJAX handler.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');

// quick return
if (isset($_POST['quickReturnID'])) {
    $_POST['quickReturnID'] = $dbs->escape_string(trim($_POST['quickReturnID']));
    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#circulationLayer\').simbioAJAX(\''.MWB.'circulation/circulation_action.php\', {method: \'post\', addData: \'quickReturnID='.trim($_POST['quickReturnID']).'\'});'."\n";
    echo 'parent.$(\'#quickReturnID\').val(\'\');'."\n";
    echo 'parent.$(\'#quickReturnID\').focus();'."\n";
    echo '</script>';
    exit();
}
