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

/* Item Usage Statistic */

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

$page_title = 'Item Usage Report';
$reportView = false;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Items Usage Statistics'); ?></h2>
</div>
<div class="infoBox">
    <?php echo __('Report Filter'); ?>
</div>
<div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
        <div id="filterForm">
            <div class="form-group divRow">
                <label><?php echo __('Title/ISBN'); ?></label>
                <?php echo simbio_form_element::textField('text', 'title', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Item Code'); ?></label>
                <?php echo simbio_form_element::textField('text', 'itemCode', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Year'); ?></label>
                <?php
                $current_year = date('Y');
                $year_options = array();
                for ($y = $current_year; $y > 1999; $y--) {
                    $year_options[] = array($y, $y);
                }
                echo simbio_form_element::selectList('year', $year_options, $current_year-1,'class="form-control col-1"');
                ?>
            </div>
        </div>
        <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
        <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
        <input type="hidden" name="reportView" value="true" />
    </form>
</div>
<!-- filter end -->
<div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
<iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'item AS i
        LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-bordered"';

    $reportgrid->setSQLColumn('i.item_code AS \''.__('Item Code').'\'',
        'b.title AS \''.__('Title').'\'',
        '\'01\' AS \''.__('Jan').'\'',
        '\'02\' AS \''.__('Feb').'\'',
        '\'03\' AS \''.__('Mar').'\'',
        '\'04\' AS \''.__('Apr').'\'',
        '\'05\' AS \''.__('May').'\'',
        '\'06\' AS \''.__('Jun').'\'',
        '\'07\' AS \''.__('Jul').'\'',
        '\'08\' AS \''.__('Aug').'\'',
        '\'09\' AS \''.__('Sep').'\'',
        '\'10\' AS \''.__('Oct').'\'',
        '\'11\' AS \''.__('Nov').'\'',
        '\'12\' AS \''.__('Dec').'\''
        );
    $reportgrid->setSQLorder('b.title ASC');

    // is there any search
    $criteria = 'b.biblio_id IS NOT NULL ';
    if (isset($_GET['title']) AND !empty($_GET['title'])) {
        $keyword = $dbs->escape_string(trim($_GET['title']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' AND (';
            foreach ($words as $word) {
                $concat_sql .= " (b.title LIKE '%$word%' OR b.isbn_issn LIKE '$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $criteria .= $concat_sql;
        } else {
            $criteria .= ' AND (b.title LIKE \'%'.$keyword.'%\' OR b.isbn_issn LIKE \''.$keyword.'%\')';
        }
    }
    if (isset($_GET['itemCode']) AND !empty($_GET['itemCode'])) {
        $item_code = $dbs->escape_string(trim($_GET['itemCode']));
        $criteria .= ' AND (i.item_code LIKE \'%'.$item_code.'%\')';
    }
    if (isset($_GET['year']) AND !empty($_GET['year'])) {
        $selected_year = (integer)$_GET['year'];
    } else {
        $selected_year = date('Y')-1;
    }
    $reportgrid->setSQLCriteria($criteria);

    // callback function to show overdued list
    function showUsage($obj_db, $array_data, $int_current_field_num)
    {
        global $selected_year;
        $_usage_q = $obj_db->query('SELECT COUNT(*) FROM loan AS l
            WHERE l.item_code=\''.$array_data[0].'\' AND l.loan_date LIKE \''.$selected_year.'-'.$array_data[$int_current_field_num].'%\'');
        $_usage_d = $_usage_q->fetch_row();
        return ($_usage_d[0]=='0')?'<span>0</span>':'<strong>'.$_usage_d[0].'</strong>';
    }
    // modify column value
    $reportgrid->modifyColumnContent(2, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(3, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(4, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(5, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(6, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(7, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(8, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(9, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(10, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(11, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(12, 'callback{showUsage}');
    $reportgrid->modifyColumnContent(13, 'callback{showUsage}');

    // no sort column
    $reportgrid->disableSort(__('Jan'), __('Feb'), __('Mar'), __('Apr'), __('May'), __('Jun'), __('Jul'), __('Aug'), __('Sep'), __('Oct'), __('Nov'), __('Dec'));

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, 20);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
