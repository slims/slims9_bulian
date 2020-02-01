<?php
/**
 * Copyright (C) 2018  Eddy Subratha (eddy.subratha@gmail.com)
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

/* Barcode reader based on QuaggaJS */

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

	case 'stockopname' :
		$script = '
					Quagga.onDetected(function (result) {
						var code = result.codeResult.code;
						$("#barcodeAudio")[0].play();
						parent.$("#itemCode").focus().val(code);
						parent.$("#checkItem").click();
					});';

	break;
}
ob_start();

require SB.'/admin/'.$sysconf['admin_template']['dir'].'/barcodescanner.tpl.php';
$content = ob_get_clean();
echo $content;