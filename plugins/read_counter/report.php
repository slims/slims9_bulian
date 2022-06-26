<?php
/**
 * @Created by          : Drajat Hasan
 * @Date                : 2022-06-26 14:11:46
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

require MDLBS . 'reporting/report_dbgrid.inc.php';

$page_title = 'Read Counter Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Read Counter Report'); ?></h2>
</div>
<div class="infoBox">
    <?php echo __('Report Filter'); ?>
</div>
<div class="sub_section">
    <form method="get" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(); ?>" target="reportView">
        <input type="hidden" name="id" value="<?= $_GET['id'] ?>"/>
        <input type="hidden" name="mod" value="<?= $_GET['mod'] ?>"/>
        <input type="hidden" name="report" value="yes"/>
        <div id="filterForm">
            <!-- Copy me if you want to make your custom field -->
            <div class="form-group divRow">
                <label><?= __('Title') ?></label>
                <?php echo simbio_form_element::textField('text', 'title', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <label><?= __('Item Code') ?></label>
                <?php echo simbio_form_element::textField('text', 'itemcode', '', 'class="form-control col-4"'); ?>
            </div>
            <!-- until here -->
            <div class="form-group divRow">
                <label><?= __('Read Start'); ?></label>
                <?php
                echo simbio_form_element::dateField('startDate', '2000-01-01','class="form-control"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?= __('Read Until'); ?></label>
                <?php
                echo simbio_form_element::dateField('untilDate', date('Y-m-d'),'class="form-control"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Record each page'); ?></label>
                <input type="text" name="recsEachPage" size="3" maxlength="3" class="form-control col-1" value="<?php echo $num_recs_show; ?>" /><small class="text-muted"><?php echo __('Set between 20 and 200'); ?></small>
            </div>
        </div>
        <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
        <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
        <input type="hidden" name="reportView" value="true" />
    </form>
</div>
<!-- filter end -->
<div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
<iframe name="reportView" id="reportView" src="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['reportView' => 'true']); ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn("item_code AS '" . __('Item Code') . "'", "title AS '" . __('Title') . "'", "created_at AS '" . __('Input Date') . "'");
    $reportgrid->setSQLorder('created_at DESC');
//    $reportgrid->invisible_fields = array(0);

    // is there any search
    $criteria = 'title IS NOT NULL ';

    if (isset($_GET['title']) && !empty($_GET['title']))
    {
        $title = utility::filterData('title', 'get', true, true, true);
        $criteria .=  ' AND title like \'%' . $title . '%\'';
    }

    if (isset($_GET['itemcode']) && !empty($_GET['itemcode']))
    {
        $title = utility::filterData('itemcode', 'get', true, true, true);
        $criteria .=  ' AND item_code = \'' . $title . '\'';
    }

        // loan date
        if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
            $criteria .= ' AND (TO_DAYS(created_at) BETWEEN TO_DAYS(\''.utility::filterData('startDate', 'get', true, true, true).'\') AND
                TO_DAYS(\''.utility::filterData('untilDate', 'get', true, true, true).'\'))';
        }

    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
    }

    // table spec
    $table_spec = 'read_counter';

    // set group by
    $reportgrid->setSQLCriteria($criteria);

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;
    $reportgrid->spreadsheet_export_btn = '<a href="'.AWB.'modules/reporting/spreadsheet.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a>';

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $xlsquery = 'select item_code AS \'' . __('Item Code') . '\', title AS \'' . __('Title') . '\', created_at AS \'' . __('Input Date') . '\' from ' . $table_spec . ' WHERE '. $criteria;
    // echo $xlsquery;
    unset($_SESSION['xlsdata']);
    $_SESSION['xlsquery'] = $xlsquery;
    $_SESSION['tblout'] = "read-counter-report";
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}