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

/* LOAN HISTORY LIST IFRAME CONTENT */

// key to authenticate
define('INDEX_AUTH', '1');
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

if (!isset($_SESSION['memberID'])) { die(); }

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// page title
$page_title = 'Member Loan List';

// start the output buffering
ob_start();
// check if there is member ID
if (isset($_SESSION['memberID']) AND !empty($_SESSION['memberID'])) {
    /* LOAN HISTORY LIST */
    $memberID = trim($_SESSION['memberID']);
    // table spec
    $table_spec = 'loan AS l
        LEFT JOIN item AS i ON l.item_code=i.item_code
        LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn(
        'l.item_code AS \''.__('Item Code').'\'',
        'b.title AS \''.__('Title').'\'',
        'l.loan_date AS \''.__('Loan Date').'\'',
        'IF(is_return = 0, \'<i>'.__('Not Returned Yet').'</i>\', return_date) AS \''.__('Returned Date').'\'');
    $datagrid->setSQLorder("l.loan_date DESC");

    $criteria = 'l.member_id=\''.$dbs->escape_string($memberID).'\' ';
    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keyword = $dbs->escape_string($_GET['keywords']);
        $criteria .= " AND (l.item_code LIKE '%$keyword%' OR b.title LIKE '%$keyword%')";
    }
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    $datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
    // special properties
    $datagrid->using_AJAX = false;
    $datagrid->column_width = array(1 => '70%');
    $datagrid->disableSort('Return Date');

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
}

// get the buffered content
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
