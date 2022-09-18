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

/* serial Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-serialcontrol');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require MDLBS.'serial_control/serial_base_lib.inc.php';

// privileges checking
$can_read = utility::havePrivilege('serial_control', 'r');
$can_write = utility::havePrivilege('serial_control', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// page title
$page_title = 'Kardex List';

$serialID = 0;
if (isset($_GET['serialID'])) {
    $serialID = (integer)$_GET['serialID'];
}
if (isset($_POST['serialID'])) {
    $serialID = (integer)$_POST['serialID'];
}

// start content buffering
ob_start();
$serial = new serial($dbs, $serialID);
if (isset($_POST['saveKardexes'])) {
    // save kardexes
    $serial->saveKardexes();
    toastr(__('Kardex data updated!'))->success();
} else if (isset($_POST['remove'])) {
    // remove kardex
    $removeID = (integer)$_POST['remove'];
    $removed = $serial->deleteKardex($removeID);
    if ($removed) {
        toastr(__('Kardex data deleted!'))->success();
    }
}
// view kardexes list
echo $serial->viewKardexes();
$content = ob_get_clean();

// js include
$js = '<script type="text/javascript" src="'.JWB.'calendar.js"></script>';
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
