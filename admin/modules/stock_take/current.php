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

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privileges checking
$can_read = utility::havePrivilege('stock_take', 'r');
$can_write = utility::havePrivilege('stock_take', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// show only current user stock take item flag
if (isset($_GET['listShow']) && $_GET['listShow'] == '1') {
    $show_only_current = 1;
}

// check if there is any active stock take proccess
$stk_query = $dbs->query('SELECT * FROM stock_take WHERE is_active=1');
if ($stk_query->num_rows < 1) {
    echo '<div class="errorBox">'.__('NO stock taking proccess initialized yet!').'</div>';
} else {
    // check view mode
    $view = 'e';
    if (isset($_GET['view']) AND $_GET['view']) {
        $view = trim($_GET['view']);
    }
?>
    <fieldset class="menuBox">
    <div class="menuBoxInner stockTakeIcon">
      <div class="per_title">
        <h2><?php
        if ($view != 'm') {
          echo __('Stock Take Proccess');
        } else {
          echo __('Current Missing/Lost Items');
        }
        ?></h2>
      </div>
      <div class="sub_section">
      <?php
      if ($view != 'm') {
        echo '<form name="stockTakeForm" class="notAJAX" action="'.MWB.'stock_take/stock_take_action.php" target="stockTakeAction" method="post" style="display: inline;">
          <div><div style="width: 140px; float: left;">'.__('Item Code').':</div><input type="text" id="itemCode" name="itemCode" size="30" autofocus /> <input type="submit" value="'.__('Change Status').'" class="btn btn-default" /></div>
          <div style="margin-top: 3px;"><div style="width: 140px; float: left;">'.__('List stocktakes by').':</div>
          <input type="radio" id="listShow" name="listShow" value="1" onclick="$(\'mainContent\').simbioAJAX(\''.MWB.'stock_take/current.php?listShow=1\')" '.( isset($show_only_current)?'checked="checked"':'' ).' /> '.__('Current User Only').'
          <input type="radio" id="listShow2" name="listShow" value="0" onclick="$(\'mainContent\').simbioAJAX(\''.MWB.'stock_take/current.php?listShow=0\')" '.( isset($show_only_current)?'':'checked="checked"' ).' /> '.__('All User').'
          <iframe name="stockTakeAction" style="width: 0; height: 0; visibility: hidden;"></iframe></div>
          </form>';
      }
      ?>
      <form name="search" id="search" action="<?php echo MWB; ?>stock_take/current.php" method="get" style="display: inline;">
      <div style="margin-top: 3px;"><div style="width: 90px; float: left;"><?php echo __('Search'); ?> : </div><input type="text" name="keywords" size="30" /> <input type="hidden" name="view" value="<?php echo $view; ?>" /> <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" /></div>
      </form>
      </div>
    </div>
    </fieldset>
    <!-- give focus to itemCode text field -->
    <script type="text/javascript">
    //Form.Element.focus('itemCode');
    $('#itemCode').focus();
    </script>
    <div id="stError" class="errorBox" style="display: none;">&nbsp;</div>
<?php
    /* CURRENT STOCK TAKE ITEM LIST */
    // table spec
    $table_spec = 'stock_take_item AS sti';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('item_code AS \''.__('Item Code').'\'',
        'title AS \''.__('Title').'\'',
        'call_number AS \''.__('Call Number').'\'',
        'coll_type_name AS \''.__('Collection Type').'\'',
        'classification AS \''.__('Classification').'\'',
        'IF(sti.status=\'e\', \''.__('Exists').'\', IF(sti.status=\'l\', \''.__('On Loan').'\', \''.__('Missing').'\')) AS \'Status\'');
    $datagrid->setSQLorder("last_update DESC");

    $criteria = 'item_id <> 0 ';
    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keyword = $dbs->escape_string(trim($_GET['keywords']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' (';
            foreach ($words as $word) {
                $concat_sql .= " (title LIKE '%$word%' OR item_code LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $criteria .= ' AND '.$concat_sql." AND status='".$view."'";
        } else {
            $criteria .= " AND (title LIKE '%$keyword%' OR item_code LIKE '%$keyword%') AND status='".$view."'";
        }
    } else {
        $criteria .= " AND status='".$view."'";
    }
    if (isset($show_only_current)) {
        $criteria .= ' AND checked_by=\''.$_SESSION['realname'].'\'';
    }

    // set criteria
    $datagrid->setSQLCriteria($criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->delete_URL = $_SERVER['PHP_SELF'];
    $datagrid->column_width = array('10%', '60%', '10%', '10%', '10%');
    $datagrid->disableSort('Current Status');

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
    }

    echo $datagrid_result;
    /* main content end */
}
