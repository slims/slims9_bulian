<?php
/**
 * Barcode reader based on QuaggaJS.
 * 
 * @author Original code Eddy Subratha (eddy.subratha@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2018
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

// start the session
require SB.'admin/default/session.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}
switch($_GET['mode']) {
	case 'circulation' :
		$script = '
					Quagga.onDetected(function (result) {
						var code = result.codeResult.code;
						$("#barcodeAudio")[0].play();
						parent.$("#listsFrame").contents().find("#tempLoanID").focus().val(code);
						parent.$("#listsFrame").contents().find("#loanForm").submit();
					});';
	break;

	case 'membership' :
		$script = '
					Quagga.onDetected(function (result) {
						var code = result.codeResult.code;
						$("#barcodeAudio")[0].play();
						parent.$("#memberID").focus().val(code);
						parent.$("#startCirc").submit();
					});';

	break;

	case 'quickreturn' :
		$script = '
					Quagga.onDetected(function (result) {
						var code = result.codeResult.code;
						$("#barcodeAudio")[0].play();
						parent.$("#quickReturnID").focus().val(code);
						parent.$("#quickReturnProcess").click();
					});';
	break;

}
ob_start();

require SB.'/admin/'.$sysconf['admin_template']['dir'].'/barcodescanner.tpl.php';
$content = ob_get_clean();
echo $content;