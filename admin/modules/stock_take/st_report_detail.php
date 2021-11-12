<?php
/**
 * @CreatedBy          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date               : 2020-12-15  11:59:48
 * @FileName           : st_report_detail.php
 * @Project            : slims9_bulian
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

// key to authenticate
defined('INDEX_AUTH') OR define('INDEX_AUTH', '1');

// require config file and autoload
require_once __DIR__ . '/../../../sysconfig.inc.php';

// num to show per-page
$num_recs_show = 20;

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');
if (!$can_read) die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');

// make sure stock_take_item table not reinitialize yet
$id = (int)utility::filterData('id');
$stock_q = $dbs->query("SELECT stock_take_id FROM stock_take ORDER BY stock_take_id DESC LIMIT 1");
$stock_d = $stock_q->fetch_row();
if ($stock_d[0] != $id) die('<div class="errorBox">'.__('Sorry, data not available!').'</div>');

// required other library
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

if (!isset($_GET['reportView'])) {
    ob_start();
    $query_string = http_build_query(array_unique($_GET));
    // frame
    echo <<<HTML
    <div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
    <iframe name="reportView" id="reportView" src="{$_SERVER['PHP_SELF']}?{$query_string}&reportView=1" frameborder="0" style="width: 100%; height: 500px;"></iframe>
HTML;
    /* main content end */
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';

} else {
    // start buffering
    ob_start();
    // get filter
    $user = utility::filterData('by', 'get', true, true, true);

    $grid = new report_datagrid;
    // table specification
    $table_spec = 'stock_take_item';
    // set table attribute
    $grid->table_attr = 'class="s-table table table-sm table-bordered"';
    // set table column
    $grid->setSQLColumn(
        "item_code AS '".__('Item Code')."'",
        "title AS '".__('Title')."'",
        "last_update AS '".__('Time Checked')."'");
    // sql criteria
    $criteria = sprintf("checked_by='%s'", $user);
    $grid->setSQLCriteria($criteria);
    // order statement
    $grid->setSQLorder('last_update DESC');
    // show spreadsheet export button
    $grid->show_spreadsheet_export = true;
    $grid->spreadsheet_export_btn = '<a href="'.MWB.'reporting/spreadsheet.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a>';
    // put the result into variables
    echo $grid->createDataGrid($dbs, $table_spec, $num_recs_show);
    // add javascript for pagination
    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $grid->paging_set).'\');'."\n";
    echo '</script>';
    $xlsquery = 'SELECT item_code AS \''.__('Item Code').'\''.
        ', title AS \''.__('Title').'\''.
        ', last_update AS \''.__('Time Checked').'\' FROM '.$table_spec.' WHERE '.$criteria;

    unset($_SESSION['xlsdata']);
    $_SESSION['xlsquery'] = $xlsquery;
    $_SESSION['tblout'] = "stock_take_detail_for_" . str_replace(' ', '_', strtolower($user));
    // get content
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
