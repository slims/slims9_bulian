<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/* Stock Take */

// key to authenticate
define('INDEX_AUTH', '1');

/* Stock Take Log Viewer */

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}
/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner stockTakeIcon">
<div class="menuBoxInner errorIcon">
  <div class="per_title">
    <h2><?php echo __('Stock Take Log'); ?></h2>
  </div>
  <div class="sub_section">
    <form name="search" action="<?php echo MWB; ?>stock_take/st_log.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
  </div>
</div>
</div>
<?php
/* search form end */
/* STOCK TAKE LOGS LIST */
// table spec
$table_spec = 'system_log AS sl LEFT JOIN user u ON u.user_id=sl.id';

// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn(
    'sl.log_date AS \''.__('Time').'\'',
    'u.realname AS \''.__('Username').'\'',
    'sl.log_msg AS \''.__('Message').'\'');
$datagrid->setSQLorder("sl.log_date DESC");

$criteria = 'sl.log_location=\'stock_take\' AND sl.log_msg !=\'\'';
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keyword = $dbs->escape_string(trim($_GET['keywords']));
    $words = explode(' ', $keyword);
    if (count($words) > 1) {
        $concat_sql = ' (';
        foreach ($words as $word) {
            $concat_sql .= " (sl.log_date LIKE '%$word%' OR sl.log_msg LIKE '%$word%') AND";
        }
        // remove the last AND
        $concat_sql = substr_replace($concat_sql, '', -3);
        $concat_sql .= ') ';
        $datagrid->setSQLCriteria($criteria.' AND '.$concat_sql);
    } else {
        $datagrid->setSQLCriteria($criteria." AND sl.log_date LIKE '%$keyword%' OR sl.log_msg LIKE '%$keyword%'");
    }
}
$datagrid->setSQLCriteria($criteria);

// set table and table header attributes
$datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->delete_URL = $_SERVER['PHP_SELF'];
$datagrid->column_width = array('15%','15%', '70%');
$datagrid->disableSort('Message');

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 50, false);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
    echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
}

echo $datagrid_result;
/* main content end */
