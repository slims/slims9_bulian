<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified for attachement download counter (C) 2020 by Wardiyono (wynerst@gmail.com)
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

$page_title = 'Download Counter Report';
$reportView = false;
$num_recs_show = 20;
if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Attachment Report'); ?></h2>
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
                <label><?php echo __('Publish year'); ?></label>
                <?php echo simbio_form_element::textField('text', 'publishYear', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <div class="divRowContent">
                    <div>
                        <label style="width: 195px;"><?php echo __('Access Date after'); ?></label>
                        <label><?php echo __('Access Date before'); ?></label>
                    </div>
                    <div id="range">
                        <input type="text" name="startDate" value="2000-01-01">
                        <span><?= __('to') ?></span>
                        <input type="text" name="untilDate" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
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
    $reportgrid->setSQLColumn('min(b.title) `'.__('Title').'`', 'f.file_title `'.__('File title').'`', 'f.mime_type `'.__('Type').'`', 'count(fr.filelog_id) `'.__('Access').'`', 'f.file_id `&nbsp`');
    $reportgrid->setSQLorder('min(b.title) ASC');
//    $reportgrid->invisible_fields = array(0);

    // is there any search
    $criteria = 'fr.filelog_id is not null ';
//    $outer_criteria = 'b.biblio_id > 0 ';
    if (isset($_GET['title']) AND !empty($_GET['title'])) {
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
            $criteria .= ' AND (b.title LIKE \'%'.$keyword.'%\' OR b.isbn_issn LIKE \'%'.$keyword.'%\')';
        }
    }
    if (isset($_GET['author']) AND !empty($_GET['author'])) {
        $author = $dbs->escape_string($_GET['author']);
        $criteria .= ' AND b.author LIKE \'%'.$author.'%\'';
    }
    if (isset($_GET['publishYear']) AND !empty($_GET['publishYear'])) {
        $publish_year = $dbs->escape_string(trim($_GET['publishYear']));
        $criteria .= ' AND b.publish_year LIKE \'%'.$publish_year.'%\'';
    }
    if (isset($_GET['startDate']) AND isset($_GET['untilDate'])) {
        $criteria .= ' AND (TO_DAYS(fr.date_read) BETWEEN TO_DAYS(\''.utility::filterData('startDate', 'get', true, true, true).'\') AND
            TO_DAYS(\''.utility::filterData('untilDate', 'get', true, true, true).'\'))';
    }
    if (isset($_GET['recsEachPage'])) {
        $recsEachPage = (integer)$_GET['recsEachPage'];
        $num_recs_show = ($recsEachPage >= 20 && $recsEachPage <= 200)?$recsEachPage:$num_recs_show;
    }

    // table spec
    $table_spec = 'files_read fr 
        left join files f on f.file_id = fr.file_id 
        left join biblio_attachment ba on fr.file_id = ba.file_id 
        left join search_biblio b on ba.biblio_id = b.biblio_id';

    // set group by
    $reportgrid->sql_group_by = 'fr.file_id';
    $reportgrid->setSQLCriteria($criteria);

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

    // callback function to change value of authority type
    function callbackLinkDetail($obj_db, $rec_d)
    {
        //global $sysconf, $auth_type_fld;
        return '<a onclick="top.$(\'#mainContent\').simbioAJAX(\''.MWB.'reporting/customs/dl_detail.php?fileID='.$rec_d[4].'\')" title="'.__('Detail').'"><i class="fa fa-search" aria-hidden="true"></i>';
    }
    // modify column content
    $reportgrid->modifyColumnContent(4, 'callback{callbackLinkDetail}');

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $xlsquery = 'select min(b.title) AS \''. __('Title').'\', f.file_title AS \''. __('File title').'\', f.mime_type AS \''. __('Type').'\', count(fr.filelog_id) AS \''. __('Access').'\' from files_read fr left join files f on f.file_id = fr.file_id left join biblio_attachment ba on fr.file_id = ba.file_id left join search_biblio b on ba.biblio_id = b.biblio_id WHERE '. $criteria . ' group by fr.file_id';
        // echo $xlsquery;
        unset($_SESSION['xlsdata']);
        $_SESSION['xlsquery'] = $xlsquery;
        $_SESSION['tblout'] = "Download_Counter";
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}

