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

/* Reserve List */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Reservation List Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
    	<h2><?php echo __('Reservation'); ?></h2>
    </div>
    <div class="infoBox">
        <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="form-group divRow">
            <label><?php echo __('Member ID').'/'.__('Member Name'); ?></label>
            <?php echo simbio_form_element::textField('text', 'member', '', 'class="form-control col-4"'); ?>
        </div>
        <div class="form-group divRow">
            <label><?php echo __('Title/ISBN'); ?></label>
            <?php echo simbio_form_element::textField('text', 'title', '', 'class="form-control col-6"'); ?>
        </div>
        <div class="form-group divRow">
            <label><?php echo __('Item Code'); ?></label>
            <?php echo simbio_form_element::textField('text', 'itemCode', '', 'class="form-control col-3"'); ?>
        </div>
        <div class="form-group divRow">
            <label><?php echo __('Reserve Date From'); ?></label>
            <?php
            echo simbio_form_element::dateField('startDate', '2000-01-01','class="form-control"');
            ?>
        </div>
        <div class="form-group divRow">
            <label><?php echo __('Reserve Date Until'); ?></label>
            <?php
            echo simbio_form_element::dateField('untilDate', date('Y-m-d'),'class="form-control"');
            ?>
        </div>
    </div>
    <input type="button" name="moreFilter" class="s-btn btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" class="s-btn btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </form>
    </div>
    <!-- filter end -->
    <div class="paging-area"><div class="pb-3 pr-3" id="pagingBox"></div></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'reserve AS r
        LEFT JOIN biblio AS b ON r.biblio_id=b.biblio_id
        LEFT JOIN member AS m ON r.member_id=m.member_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn('r.item_code AS \''.__('Item Code').'\'',
        'b.title AS \''.__('Title').'\'',
        'm.member_name AS \''.__('Member Name').'\'',
        'm.member_id AS \''.__('Member ID').'\'',
        'r.reserve_date AS \''.__('Reserve Date').'\'');
    $reportgrid->setSQLorder('r.reserve_date DESC');

    // is there any search
    $criteria = 'r.reserve_id IS NOT NULL ';
    if (isset($_GET['title']) AND !empty($_GET['title'])) {
        $keyword = $dbs->escape_string(trim($_GET['title']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' AND (';
            foreach ($words as $word) {
                $concat_sql .= " (b.title LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $criteria .= $concat_sql;
        } else {
            $criteria .= ' AND (b.title LIKE \'%'.$keyword.'%\')';
        }
    }
    if (isset($_GET['itemCode']) AND !empty($_GET['itemCode'])) {
        $item_code = $dbs->escape_string(trim($_GET['itemCode']));
        $criteria .= ' AND i.item_code LIKE \'%'.$item_code.'%\'';
    }
    if (isset($_GET['member']) AND !empty($_GET['member'])) {
        $member = $dbs->escape_string($_GET['member']);
        $criteria .= ' AND (m.member_name LIKE \'%'.$member.'%\' OR m.member_id LIKE \'%'.$member.'%\')';
    }
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(r.reserve_date) BETWEEN TO_DAYS(\''.$_GET['startDate'].'\') AND
            TO_DAYS(\''.$_GET['untilDate'].'\'))';
    }

    $reportgrid->setSQLCriteria($criteria);

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, 20);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
