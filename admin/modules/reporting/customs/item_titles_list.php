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

/* Item List */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-reporting');
// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to access this area!') . '</div>');
}

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS . 'reporting/report_dbgrid.inc.php';

$page_title = 'Items/Copies Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
    <div class="per_title">
        <h2><?php echo __('Items Title List'); ?></h2>
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
                    <label><?php echo __('Classification'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'class', '', 'class="form-control col-4"'); ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('GMD'); ?></label>
                    <?php
                    $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
                    $gmd_options[] = array('0', __('ALL'));
                    while ($gmd_d = $gmd_q->fetch_row()) {
                        $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
                    }
                    echo simbio_form_element::selectList('gmd[]', $gmd_options, '', 'multiple="multiple" size="5" class="form-control col-3"');
                    ?><small class="text-muted"><?php echo __('Press Ctrl and click to select multiple entries'); ?></small>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Collection Type'); ?></label>
                    <?php
                    $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
                    $coll_type_options = array();
                    $coll_type_options[] = array('0', __('ALL'));
                    while ($coll_type_d = $coll_type_q->fetch_row()) {
                        $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
                    }
                    echo simbio_form_element::selectList('collType[]', $coll_type_options, '', 'multiple="multiple" size="5" class="form-control col-3"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Item Status'); ?></label>
                    <?php
                    $status_q = $dbs->query('SELECT item_status_id, item_status_name FROM mst_item_status');
                    $status_options = array();
                    $status_options[] = array('0', __('ALL'));
                    while ($status_d = $status_q->fetch_row()) {
                        $status_options[] = array($status_d[0], $status_d[1]);
                    }
                    echo simbio_form_element::selectList('status', $status_options, '', 'class="form-control col-2"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Location'); ?></label>
                    <?php
                    $loc_q = $dbs->query('SELECT location_id, location_name FROM mst_location');
                    $loc_options = array();
                    $loc_options[] = array('0', __('ALL'));
                    while ($loc_d = $loc_q->fetch_row()) {
                        $loc_options[] = array($loc_d[0], $loc_d[1]);
                    }
                    echo simbio_form_element::selectList('location', $loc_options, '', 'class="form-control col-2"');
                    ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Input Date'); ?></label>
                    <div class="divRowContent">
                        <div id="range">
                            <input type="text" name="inputDateStart">
                            <span><?= __('to') ?></span>
                            <input type="text" name="inputDateEnd">
                        </div>
                    </div>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Publish year'); ?></label>
                    <?php echo simbio_form_element::textField('text', 'publishYear', '', 'class="form-control col-1"'); ?>
                </div>
                <div class="form-group divRow">
                    <label><?php echo __('Record each page'); ?></label>
                    <input type="text" name="recsEachPage" size="3" class="form-control col-1" maxlength="3" value="<?php echo $num_recs_show; ?>" />
                    <small class="text-muted"><?php echo __('Set between 20 and 200'); ?></small>
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
            });
        })
    </script>
    <!-- filter end -->
    <div class="paging-area">
        <div class="pt-3 pr-3" id="pagingBox"></div>
    </div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'] . '?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // table spec
    $table_spec = 'item AS i
        LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
        LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn(
        'i.item_code AS \'' . __('Item Code') . '\'',
        'b.title AS \'' . __('Title') . '\'',
        'ct.coll_type_name AS \'' . __('Collection Type') . '\'',
        'i.item_status_id AS \'' . __('Item Status') . '\'',
        'b.call_number AS \'' . __('Call Number') . '\'',
        'i.biblio_id'
    );
    $reportgrid->setSQLorder('b.title ASC');

    // is there any search
    $criteria = 'b.biblio_id IS NOT NULL ';
    if (isset($_GET['title']) and !empty($_GET['title'])) {
        $keyword = $dbs->escape_string(trim($_GET['title']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' AND (';
            foreach ($words as $word) {
                $concat_sql .= " (b.title LIKE '%$word%' OR b.isbn_issn LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $criteria .= $concat_sql;
        } else {
            $criteria .= ' AND (b.title LIKE \'%' . $keyword . '%\' OR b.isbn_issn LIKE \'%' . $keyword . '%\')';
        }
    }
    if (isset($_GET['itemCode']) and !empty($_GET['itemCode'])) {
        $item_code = $dbs->escape_string(trim($_GET['itemCode']));
        $criteria .= ' AND i.item_code LIKE \'%' . $item_code . '%\'';
    }
    if (isset($_GET['collType'])) {
        $coll_type_IDs = '';
        foreach ($_GET['collType'] as $id) {
            $id = (int)$id;
            if ($id) {
                $coll_type_IDs .= "$id,";
            }
        }
        $coll_type_IDs = substr_replace($coll_type_IDs, '', -1);
        if ($coll_type_IDs) {
            $criteria .= " AND i.coll_type_id IN($coll_type_IDs)";
        }
    }
    if (isset($_GET['gmd']) and !empty($_GET['gmd'])) {
        $gmd_IDs = '';
        foreach ($_GET['gmd'] as $id) {
            $id = (int)$id;
            if ($id) {
                $gmd_IDs .= "$id,";
            }
        }
        $gmd_IDs = substr_replace($gmd_IDs, '', -1);
        if ($gmd_IDs) {
            $criteria .= " AND b.gmd_id IN($gmd_IDs)";
        }
    }
    if (isset($_GET['status']) and $_GET['status'] != '0') {
        $status = $dbs->escape_string(trim($_GET['status']));
        $criteria .= ' AND i.item_status_id=\'' . $status . '\'';
    }
    if (isset($_GET['class']) and ($_GET['class'] != '')) {
        $class = $dbs->escape_string($_GET['class']);
        $criteria .= ' AND b.classification LIKE \'' . $class . '%\'';
    }
    if (isset($_GET['location']) and !empty($_GET['location'])) {
        $location = $dbs->escape_string(trim($_GET['location']));
        $criteria .= ' AND i.location_id=\'' . $location . '\'';
    }
    if (isset($_GET['publishYear']) and !empty($_GET['publishYear'])) {
        $publish_year = $dbs->escape_string(trim($_GET['publishYear']));
        $criteria .= ' AND b.publish_year LIKE \'%' . $publish_year . '%\'';
    }
    if (isset($_GET['inputDateStart']) AND !empty($_GET['inputDateStart']) && isset($_GET['inputDateEnd']) AND !empty($_GET['inputDateEnd'])) {
        $inputDateStart = $dbs->escape_string(trim($_GET['inputDateStart']));
        $inputDateEnd = $dbs->escape_string(trim($_GET['inputDateEnd']));
        $criteria .= ' AND (i.input_date >= \'' . $inputDateStart . '\' AND i.input_date <= \'' . $inputDateEnd . '\')';
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (int)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 200) ? $recsEachPage : $num_recs_show;
    }

    $reportgrid->setSQLCriteria($criteria);

    // callback function to show title and authors
    function showTitleAuthors($obj_db, $array_data)
    {
        if (!$array_data[5]) {
            return;
        }
        // author name query
        $_biblio_q = $obj_db->query('SELECT b.title, a.author_name FROM biblio AS b
            LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
            LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
            WHERE b.biblio_id=' . $array_data[5]);
        $_authors = '';
        while ($_biblio_d = $_biblio_q->fetch_row()) {
            $_title = $_biblio_d[0];
            $_authors .= $_biblio_d[1] . ' - ';
        }
        $_authors = substr_replace($_authors, '', -3);
        $_output = $_title . '<br /><i>' . $_authors . '</i>' . "\n";
        return $_output;
    }
    function showStatus($obj_db, $array_data)
    {
        $output = __('Available');
        $q = $obj_db->query('SELECT item_status_name FROM mst_item_status WHERE item_status_id=\'' . $array_data[3] . '\'');
        if (!empty($q->num_rows)) {
            $d = $q->fetch_row();
            $s = $d[0];
            $output = $s;
        }

        return $output;
    }
    // modify column value
    $reportgrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
    $reportgrid->modifyColumnContent(3, 'callback{showStatus}');
    $reportgrid->invisible_fields = array(5);

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">' . "\n";
    echo 'parent.$(\'#pagingBox\').html(\'' . str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set) . '\');' . "\n";
    echo '</script>';

    $xlsquery = "SELECT i.item_code AS '" . __('Item Code') . "',
            b.title AS '" . __('Title') . "',
            ct.coll_type_name AS '" . __('Collection Type') . "',
            i.item_status_id AS '" . __('Item Status') . "',
            b.call_number AS '" . __('Call Number') . "' FROM " .
        $table_spec . " WHERE " . $criteria;
    // echo $xlsquery;
    unset($_SESSION['xlsdata']);
    $_SESSION['xlsquery'] = $xlsquery;
    $_SESSION['tblout'] = "title_list_item";

    $content = ob_get_clean();
    // include the page template
    require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/printed_page_tpl.php';
}
