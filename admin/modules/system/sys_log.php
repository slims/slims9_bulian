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
    $logs = $dbs->query('SELECT log_date, log_location, log_msg FROM system_log ORDER BY log_date DESC');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="system_logs_'.date('Ymd').'.log"');
    echo 'SENAYAN system logs record'."\n";
    while ($logs_d = $logs->fetch_row()) {
        echo '['.$logs_d[0].']---'.$logs_d[1].'---'.$logs_d[2]."\n";
    }
    exit();
}

// log data clearance action
if (isset($_POST['clearLogs']) AND $can_write AND $_SESSION['uid'] == 1) {
    $dbs->query('TRUNCATE TABLE system_log');
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' is cleaning all logs '. 'Log record', 'Clear');
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
$table_spec = 'system_log AS sl';

// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn(
    'sl.log_date AS \''.__('Time').'\'',
    'sl.log_location AS \''.__('Location').'\'',
    'sl.log_msg AS \''.__('Message').'\'');
$datagrid->setSQLorder('sl.log_date DESC');

// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keyword = utility::filterData('keywords', 'get', true, true, true);
    $words = explode(' ', $keyword);
    if (count($words) > 1) {
        $concat_sql = ' (';
        foreach ($words as $word) {
            $concat_sql .= " (sl.log_date LIKE '%$word%' OR sl.log_msg LIKE '%$word%') AND";
        }
        // remove the last AND
        $concat_sql = substr_replace($concat_sql, '', -3);
        $concat_sql .= ') ';
        $datagrid->setSQLCriteria($concat_sql);
    } else {
        $datagrid->setSQLCriteria("sl.log_date LIKE '%$keyword%' OR sl.log_msg LIKE '%$keyword%'");
    }
}

// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->delete_URL = $_SERVER['PHP_SELF'];
$datagrid->column_width = array('18%', '10%', '72%');
$datagrid->disableSort('Message');

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 50, false);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
    echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
}

echo $datagrid_result;
/* main content end */
