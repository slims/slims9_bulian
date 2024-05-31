<?php
/**
 *
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

/* System Log Viewer */
use SLiMS\Log\Factory as Log;

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

// log data save action
if (isset($_POST['saveLogs']) AND $can_write AND $_SESSION['uid'] == 1) {
   Log::download();
}

// log data clearance action
if (isset($_POST['clearLogs']) AND $can_write AND $_SESSION['uid'] == 1) {
    Log::truncate();
    Log::write('staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' is cleaning all logs '. 'Log record', 'Clear');
    utility::jsToastr(__('System Log'), __('System Log data completely cleared!'), 'success');    
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'system/sys_log.php\');</script>';
    exit();
}

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner syslogIcon">
	<div class="per_title">
	  <h2><?php echo __('System Log'); ?></h2>
  </div>
	<div class="sub_section">
    <?php if ($_SESSION['uid'] == 1) { ?>
	  <div class="btn-group">
      <a href="#" onclick="confSubmit('clearLogsForm', '<?php echo __('Are you SURE to completely clear system log data? This action cannot be undo!'); ?>')" class="notAJAX btn btn-danger"> <?php echo __('CLEAR LOGS'); ?></a>
      <a href="#" onclick="confSubmit('saveLogsForm', '<?php echo __('Save Logs record to file?'); ?>')" class="notAJAX btn btn-default"><?php echo __('Save Logs To File'); ?></a>
	  </div>
    <?php } ?>
    <form name="search" action="<?php echo MWB; ?>system/sys_log.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
    <!-- LOG CLEARANCE FORM -->
    <?php if ($_SESSION['uid'] == 1) { ?>
    <form action="<?php echo MWB; ?>system/sys_log.php" id="clearLogsForm" target="blindSubmit" method="post" class="form-inline"><input type="hidden" name="clearLogs" value="true" /></form>
    <form action="<?php echo MWB; ?>system/sys_log.php" id="saveLogsForm" target="blindSubmit" method="post" class="form-inline"><input type="hidden" name="saveLogs" value="true" /></form>
    <?php } ?>
    <!-- LOG CLEARANCE FORM END -->
  </div>
</div>
</div>
<?php
/* search form end */
/* SYSTEM LOGS LIST */
// table spec

// create datagrid
echo Log::read(new simbio_datagrid());
/* main content end */
