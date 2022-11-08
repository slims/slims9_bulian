<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified for Excel output (C) 2010 by Wardiyono (wynerst@gmail.com)
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

/* Report By Titles */

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

$page_title = 'Titles Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Title List'); ?></h2>
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
                <label><?php echo __('Author'); ?></label>
                <?php echo simbio_form_element::textField('text', 'author', '', 'class="form-control col-4"'); ?>
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
                echo simbio_form_element::selectList('gmd[]', $gmd_options, '','multiple="multiple" size="5" class="form-control col-3"');
                ?><small class="text-muted"><?php echo __('Press Ctrl and click to select multiple entries'); ?></small>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Language'); ?></label>
                <?php
                $lang_q = $dbs->query('SELECT language_id, language_name FROM mst_language');
                $lang_options = array();
                $lang_options[] = array('0', __('ALL'));
                while ($lang_d = $lang_q->fetch_row()) {
                    $lang_options[] = array($lang_d[0], $lang_d[1]);
                }
                echo simbio_form_element::selectList('language', $lang_options,'','class="form-control col-3"');
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
                <?php echo simbio_form_element::textField('text', 'publishYear', '', 'class="form-control col-4"'); ?>
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
<div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
<iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn('b.biblio_id', 'b.title AS \''.__('Title').'\'', 'COUNT(item_id) AS \''.__('Copies').'\'',
		'pl.place_name AS \''.__('Publishing Place').'\'',
		'pb.publisher_name AS \''.__('Publisher').'\'',
        'b.isbn_issn AS \''.__('ISBN/ISSN').'\'',
        'b.call_number AS \''.__('Call Number').'\'');
    $reportgrid->setSQLorder('b.title ASC');
    $reportgrid->invisible_fields = array(0);

    // is there any search
    $criteria = 'bsub.biblio_id IS NOT NULL ';
    $outer_criteria = 'b.biblio_id > 0 ';
    if (isset($_GET['title']) AND !empty($_GET['title'])) {
        $keyword = $dbs->escape_string(trim($_GET['title']));
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' AND (';
            foreach ($words as $word) {
                $concat_sql .= " (bsub.title LIKE '%$word%' OR bsub.isbn_issn LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $criteria .= $concat_sql;
        } else {
            $criteria .= ' AND (bsub.title LIKE \'%'.$keyword.'%\' OR bsub.isbn_issn LIKE \'%'.$keyword.'%\')';
        }
    }
    if (isset($_GET['author']) AND !empty($_GET['author'])) {
        $author = $dbs->escape_string($_GET['author']);
        $criteria .= ' AND ma.author_name LIKE \'%'.$author.'%\'';
    }
    if (isset($_GET['class']) AND !empty($_GET['class'])) {
        $class = $dbs->escape_string($_GET['class']);
        $criteria .= ' AND bsub.classification LIKE \''.$class.'%\'';
    }
    if (isset($_GET['gmd']) AND !empty($_GET['gmd'])) {
        $gmd_IDs = '';
        foreach ($_GET['gmd'] as $id) {
            $id = (integer)$id;
            if ($id) {
                $gmd_IDs .= "$id,";
            }
        }
        $gmd_IDs = substr_replace($gmd_IDs, '', -1);
        if ($gmd_IDs) {
            $outer_criteria .= " AND b.gmd_id IN($gmd_IDs)";
        }
    }
    if (isset($_GET['collType'])) {
        $coll_type_IDs = '';
        foreach ($_GET['collType'] as $id) {
            $id = (integer)$id;
            if ($id) {
                $coll_type_IDs .= "$id,";
            }
        }
        $coll_type_IDs = substr_replace($coll_type_IDs, '', -1);
        if ($coll_type_IDs) {
            $outer_criteria .= " AND i.coll_type_id IN($coll_type_IDs)";
        }
    }
    if (isset($_GET['language']) AND !empty($_GET['language'])) {
        $language = $dbs->escape_string(trim($_GET['language']));
        $criteria .= ' AND bsub.language_id=\''.$language.'\'';
    }
    if (isset($_GET['location']) AND !empty($_GET['location'])) {
        $location = $dbs->escape_string(trim($_GET['location']));
        $outer_criteria .= ' AND i.location_id=\''.$location.'\'';
    }
    if (isset($_GET['publishYear']) AND !empty($_GET['publishYear'])) {
        $publish_year = $dbs->escape_string(trim($_GET['publishYear']));
        $criteria .= ' AND bsub.publish_year LIKE \'%'.$publish_year.'%\'';
    }
    if (isset($_GET['inputDateStart']) AND !empty($_GET['inputDateStart']) && isset($_GET['inputDateEnd']) AND !empty($_GET['inputDateEnd'])) {
        $inputDateStart = $dbs->escape_string(trim($_GET['inputDateStart']));
        $inputDateEnd = $dbs->escape_string(trim($_GET['inputDateEnd']));
        $criteria .= ' AND (bsub.input_date >= \'' . $inputDateStart . '\' AND bsub.input_date <= \'' . $inputDateEnd . '\')';
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
    }

    // subquery/view string
    $subquery_str = '(SELECT DISTINCT bsub.biblio_id, bsub.gmd_id, bsub.title, bsub.isbn_issn, bsub.call_number, bsub.classification, bsub.language_id,
		bsub.publish_place_id, bsub.publisher_id
        FROM biblio AS bsub
        LEFT JOIN biblio_author AS ba ON bsub.biblio_id = ba.biblio_id
        LEFT JOIN mst_author AS ma ON ba.author_id = ma.author_id
        LEFT JOIN biblio_topic AS bt ON bsub.biblio_id = bt.biblio_id
        LEFT JOIN mst_topic AS mt ON bt.topic_id = mt.topic_id WHERE '.$criteria.')';

    // table spec
    $table_spec = $subquery_str.' AS b
        LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
		LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
		LEFT JOIN mst_publisher AS pb ON b.publisher_id=pb.publisher_id';

    // set group by
    $reportgrid->sql_group_by = 'b.biblio_id';
    $reportgrid->setSQLCriteria($outer_criteria);

    // callback function to show title and authors
    function showTitleAuthors($obj_db, $array_data)
    {
        // author name query
        $_biblio_q = $obj_db->query('SELECT b.title, a.author_name FROM biblio AS b
            LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
            LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
            WHERE b.biblio_id='.$array_data[0]);
        $_authors = '';
        while ($_biblio_d = $_biblio_q->fetch_row()) {
            $_title = $_biblio_d[0];
            $_authors .= $_biblio_d[1].' - ';
        }
        $_authors = substr_replace($_authors, '', -3);
        $_output = $_title.'<br /><i>'.$_authors.'</i>'."\n";
        return $_output;
    }
    // modify column value
    $reportgrid->modifyColumnContent(1, 'callback{showTitleAuthors}');

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $xlsquery = 'SELECT b.biblio_id, b.title AS \''.__('Title').'\''.
        ', COUNT(item_id) AS \''.__('Copies').'\''.
        ', pl.place_name AS \''.__('Publishing Place').'\''.
        ', pb.publisher_name AS \''.__('Publisher').'\''.
        ',  b.isbn_issn AS \''.__('ISBN/ISSN').'\', b.call_number AS \''.__('Call Number').'\' FROM '.
        $table_spec . ' WHERE '. $outer_criteria . ' group by b.biblio_id';
        // echo $xlsquery;
    unset($_SESSION['xlsdata']);
    $_SESSION['xlsquery'] = $xlsquery;
    $_SESSION['tblout'] = "title_list";
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
