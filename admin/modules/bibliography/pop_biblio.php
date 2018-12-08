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


/* Biblio Item List */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if (isset($_GET['itemID'])) {
  $_POST['itemID'] = $_GET['itemID'];
}

if (isset($_GET['itemCollID'])) {
  $_POST['itemCollID'] = $_GET['itemCollID'];
}

$_GET['inPopUp'] = true;

ob_start();
require MDLBS.'bibliography/index.php';
?>
<script type="text/javascript">
$(document).ready(function() {
  $('#pageContent').trigger('simbioAJAXloaded');
});
</script>
<?php
$content = ob_get_clean();

// page title
$page_title = 'Bibliographic Data';

// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
