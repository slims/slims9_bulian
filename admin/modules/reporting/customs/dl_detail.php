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
$_IDFile = "";

if (isset($_GET['reportView'])) {
    $reportView = true;
}

if (isset($_GET['fileID'])) {
    $_IDFile = $dbs->escape_string(strip_tags($_GET['fileID']));
}

if (!$reportView) {
?>
<!-- filter -->
<div class="per_title">
    <h2><?php echo __('Attachment Report (Detail)'); ?></h2>
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
                <label><?php echo __('Author(s)'); ?></label>
                <?php echo simbio_form_element::textField('text', 'author', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Publish year'); ?></label>
                <?php echo simbio_form_element::textField('text', 'publishYear', '', 'class="form-control col-4"'); ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Access Date after'); ?></label>
                <?php
                echo simbio_form_element::dateField('startDate', '2000-01-01','class="form-control"');
                ?>
            </div>
            <div class="form-group divRow">
                <label><?php echo __('Access Date before'); ?></label>
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
        <input type="hidden" name="fileID" value="<?= $_IDFile;?>" />
    </form>
</div>
<!-- filter end -->
<div class="paging-area"><div class="pt-3 pr-3" id="pagingBox"></div></div>
<iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true&fileID='.$_IDFile; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->table_attr = 'class="s-table table table-sm table-bordered"';
    $reportgrid->setSQLColumn('b.title `'.__('Title').'`', 'f.file_title `'.__('File title').'`', 'f.mime_type `'.__('Type').'`', 'COALESCE(m.member_name, u.realname, \''.__('UnKnown').'\') `Contact`', 'fr.client_ip `'.__('Address').'`', 'fr.date_read `'.__('Date').'`');
    $reportgrid->setSQLorder('fr.date_read DESC');
//    $reportgrid->invisible_fields = array(0);

    // is there any search
    $criteria = 'fr.file_id ='. $_IDFile. ' ';
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
    left join biblio_attachment ba on f.file_id = ba.file_id
    left join search_biblio b  on b.biblio_id = ba.biblio_id
    left join member m on m.member_id = fr.member_id
    left join user u on u.user_id = fr.user_id';

    $reportgrid->setSQLCriteria($criteria);

    $reportgrid->debug = true;

    // show spreadsheet export button
    $reportgrid->show_spreadsheet_export = true;

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, $num_recs_show);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $xlsquery = 'select b.title \''. __('Title').'\', f.file_title AS \''. __('File title').'\', f.mime_type AS \''. __('Type').'\', fr.date_read AS \''. __('Date').'\', COALESCE(m.member_name, u.realname, \'UnKnown\') \''. __('Contact').'\', fr.client_ip \''. __('Address') .'\'
        from files_read fr
        left join files f on f.file_id = fr.file_id 
        left join biblio_attachment ba on f.file_id = ba.file_id 
        left join search_biblio b  on b.biblio_id = ba.biblio_id
        left join member m on m.member_id = fr.member_id
        left join user u on u.user_id = fr.user_id
        WHERE '. $criteria ;
        // echo $xlsquery;
        unset($_SESSION['xlsdata']);
        $_SESSION['xlsquery'] = $xlsquery;
        $_SESSION['tblout'] = "Download_Counter";
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
