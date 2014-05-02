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

/* Circulation AJAX Process */

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
    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#circulationLayer\').simbioAJAX(\''.MWB.'circulation/circulation_action.php\', {method: \'post\', addData: \'quickReturnID='.trim($_POST['quickReturnID']).'\'});'."\n";
    echo 'parent.$(\'#quickReturnID\').val(\'\');'."\n";
    echo 'parent.$(\'#quickReturnID\').focus();'."\n";
    echo '</script>';
    exit();
}
