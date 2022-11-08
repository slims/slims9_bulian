<?php
/**
 *
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

/* Staff Activity Report */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Staff Activity Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
  <!-- filter -->
  <div class="per_title">
    <h2><?php echo __('Staff Activity'); ?></h2>
  </div>
  <div class="infoBox">
      <?php echo __('Report Filter'); ?>
  </div>
  <div class="sub_section">
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
            <div class="form-group divRow">
                    <div class="divRowContent">
                        <div>
                            <label style="width: 195px;"><?php echo __('Activity Date From'); ?></label>
                            <label><?php echo __('Activity Date Until'); ?></label>
                        </div>
                        <div id="range">
                            <input type="text" name="startDate" value="2000-01-01">
                            <span><?= __('to') ?></span>
                            <input type="text" name="untilDate" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
            </div>
            <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
            <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
            <input type="hidden" name="reportView" value="true" />
        </form>
  </div>
  <script>
        $(document).ready(function(){
            const elem = document.getElementById('range');
            const dateRangePicker = new DateRangePicker(elem, {
                language: '<?= substr($sysconf['default_lang'], 0,2) ?>',
                format: 'yyyy-mm-dd',
                showOnFocus: false
            });
        })
  </script>
  <!-- filter end -->
  <div class="paging-area"><div class="pb-3 pr-3" id="pagingBox"></div></div>
  <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'user AS u';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('u.realname AS \''.__('Real Name').'\'',
        'u.username AS \''.__('Login Username').'\'',
        'u.user_id AS \''.__('Bibliography Data Entry').'\'',
        'u.user_id AS \''.__('Item Data Entry').'\'',
        'u.user_id AS \''.__('Member Data Entry').'\'',
        'u.user_id AS \''.__('Loans').'\'',
        'u.user_id AS \''.__('Returns').'\'',
        'u.user_id AS \''.__('Extends').'\'');
    $reportgrid->setSQLorder('realname ASC');

    // is there any search
    $criteria = 'user_id IS NOT NULL ';
    $reportgrid->setSQLCriteria($criteria);

    $start_date = '2000-01-01';
    if (isset($_GET['startDate'])) {
        $start_date = $_GET['startDate'];
    }
    $until_date = date('Y-m-d');
    if (isset($_GET['untilDate'])) {
        $until_date = $_GET['untilDate'];
    }
    // callbacks
    function showBiblioEntries($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'bibliography\' AND log_type=\'staff\'
            AND log_msg LIKE \'%insert bibliographic data%\' AND id=\''.$array_data['2'].'\' AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    function showItemEntries($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'bibliography\' AND log_type=\'staff\'
            AND log_msg LIKE \'%insert item data%\' AND id=\''.$array_data['3'].'\' AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    function showMemberEntries($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'membership\' AND log_type=\'staff\'
            AND log_msg LIKE \'%add new member%\' AND id=\''.$array_data['4'].'\' AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    function showCirculation_Loan($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_user = $obj_db->escape_string($array_data[0]);
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'circulation\' AND log_type=\'member\'
            AND (log_msg LIKE \''.$_user.'%new loan%\') AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    function showCirculation_Return($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_user = $obj_db->escape_string($array_data[0]);
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'circulation\' AND log_type=\'member\'
            AND (log_msg LIKE \''.$_user.'%return item%\' OR \''.$_user.'%Quick Return%\') AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    function showCirculation_Extends($obj_db, $array_data)
    {
        global $start_date, $until_date;
        $_user = $obj_db->escape_string($array_data[0]);
        $_count_q = $obj_db->query('SELECT COUNT(log_id) FROM system_log WHERE log_location=\'circulation\' AND log_type=\'member\'
            AND (log_msg LIKE \''.$_user.'%extend loan%\') AND TO_DAYS(log_date) BETWEEN TO_DAYS(\''.$start_date.'\') AND TO_DAYS(\''.$until_date.'\')');
        $_count_d = $_count_q->fetch_row();
        return $_count_d[0];
    }

    // columns modification settings
    $reportgrid->column_width = array(0 => '10%', 1 => '10%');
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->modifyColumnContent(2, 'callback{showBiblioEntries}');
    $reportgrid->modifyColumnContent(3, 'callback{showItemEntries}');
    $reportgrid->modifyColumnContent(4, 'callback{showMemberEntries}');
    $reportgrid->modifyColumnContent(5, 'callback{showCirculation_Loan}');
    $reportgrid->modifyColumnContent(6, 'callback{showCirculation_Return}');
    $reportgrid->modifyColumnContent(7, 'callback{showCirculation_Extends}');

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, 20);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
