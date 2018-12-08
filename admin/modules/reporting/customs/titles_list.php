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
    <fieldset>
    <div class="per_title">
    	<h2><?php echo __('Title List'); ?></h2>
	  </div>
    <div class="infoBox">
    <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Title/ISBN'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'title', '', 'style="width: 50%"'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Author'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'author', '', 'style="width: 50%"'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Classification'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'class', '', 'style="width: 50%"'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('GMD'); ?></div>
            <div class="divRowContent">
            <?php
            $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
            $gmd_options[] = array('0', __('ALL'));
            while ($gmd_d = $gmd_q->fetch_row()) {
                $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
            }
            echo simbio_form_element::selectList('gmd[]', $gmd_options, '','multiple="multiple" size="5"');
            ?> <?php echo __('Press Ctrl and click to select multiple entries'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Collection Type'); ?></div>
            <div class="divRowContent">
            <?php
            $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type');
            $coll_type_options = array();
            $coll_type_options[] = array('0', __('ALL'));
            while ($coll_type_d = $coll_type_q->fetch_row()) {
                $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
            }
            echo simbio_form_element::selectList('collType[]', $coll_type_options, '', 'multiple="multiple" size="5"');
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Language'); ?></div>
            <div class="divRowContent">
            <?php
            $lang_q = $dbs->query('SELECT language_id, language_name FROM mst_language');
            $lang_options = array();
            $lang_options[] = array('0', __('ALL'));
            while ($lang_d = $lang_q->fetch_row()) {
                $lang_options[] = array($lang_d[0], $lang_d[1]);
            }
            echo simbio_form_element::selectList('language', $lang_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Location'); ?></div>
            <div class="divRowContent">
            <?php
            $loc_q = $dbs->query('SELECT location_id, location_name FROM mst_location');
            $loc_options = array();
            $loc_options[] = array('0', __('ALL'));
            while ($loc_d = $loc_q->fetch_row()) {
                $loc_options[] = array($loc_d[0], $loc_d[1]);
            }
            echo simbio_form_element::selectList('location', $loc_options);
            ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Publish year'); ?></div>
            <div class="divRowContent">
            <?php echo simbio_form_element::textField('text', 'publishYear', '', 'style="width: 50%"'); ?>
            </div>
        </div>
        <div class="divRow">
            <div class="divRowLabel"><?php echo __('Record each page'); ?></div>
            <div class="divRowContent"><input type="text" name="recsEachPage" size="3" maxlength="3" value="<?php echo $num_recs_show; ?>" /> <?php echo __('Set between 20 and 200'); ?></div>
        </div>
    </div>
    <div style="padding-top: 10px; clear: both;">
    <input type="button" name="moreFilter" value="<?php echo __('Show More Filter Options'); ?>" />
    <input type="submit" name="applyFilter" value="<?php echo __('Apply Filter'); ?>" />
    <input type="hidden" name="reportView" value="true" />
    </div>
    </form>
	</div>
    </fieldset>
    <!-- filter end -->
    <div class="dataListHeader" style="padding: 3px;"><span id="pagingBox"></span></div>
    <iframe name="reportView" id="reportView" src="<?php echo $_SERVER['PHP_SELF'].'?reportView=true'; ?>" frameborder="0" style="width: 100%; height: 500px;"></iframe>
<?php
} else {
    ob_start();
    // create datagrid
    $reportgrid = new report_datagrid();
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
	echo '<a href="../xlsoutput.php" class="button">'.__('Export to spreadsheet format').'</a>';
    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
