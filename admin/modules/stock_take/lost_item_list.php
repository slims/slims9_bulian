<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 * Some patches: Hendro Wicaksono (hendrowicaksono@yahoo.com)
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
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'reporting/report_dbgrid.inc.php';

$page_title = 'Stock Take Lost Items';

$reportView = false;
if (isset($_GET['reportView'])) {
  $reportView = true;
}

if (!$reportView) {
?>
    <!-- filter -->
	  <div class="per_title">
	    <h2><?php echo __('Current Lost Item'); ?></h2>
    </div>
	  <div class="infoBox">
      <?php echo __('Report Filter'); ?>
    </div>
    <div class="sub_section">
    <form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="reportView">
    <div id="filterForm">
    <div class="form-group divRow">
        <label><?php echo __('Title/ISBN'); ?></label>
        <div class="divRowContent">
        <?php
        echo simbio_form_element::textField('text', 'title', '', ' class="form-control col-5"');
        ?>
        </div>
    </div>
    <div class="form-group divRow">
        <label><?php echo __('Item Code'); ?></label>
        <div class="divRowContent">
        <?php
        echo simbio_form_element::textField('text', 'itemCode', '', ' class="form-control col-5"');
        ?>
        </div>
    </div>
    <div class="form-group divRow">
        <label><?php echo __('Classification'); ?></label>
        <div class="divRowContent">
        <?php
        echo simbio_form_element::textField('text', 'class', '', ' class="form-control col-5"');
        ?>
        </div>
    </div>
    <div class="form-group divRow">
        <label><?php echo __('GMD'); ?></label>
        <?php
        $ct_q = $dbs->query('SELECT gmd_name FROM mst_gmd');
        $ct_options = array();
        $ct_options[] = array('0', __('ALL'));
        while ($ct_d = $ct_q->fetch_row()) {
            $ct_options[] = array($ct_d[0], $ct_d[0]);
        }
        echo simbio_form_element::selectList('gmd', $ct_options,'','class="form-control col-3"');
        ?>
    </div>
    <div class="form-group divRow">
        <label><?php echo __('Collection Type'); ?></label>
        <?php
        $ct_q = $dbs->query('SELECT coll_type_name FROM mst_coll_type');
        $ct_options = array();
        $ct_options[] = array('0', __('ALL'));
        while ($ct_d = $ct_q->fetch_row()) {
            $ct_options[] = array($ct_d[0], $ct_d[0]);
        }
        echo simbio_form_element::selectList('collType', $ct_options,'','class="form-control col-3"');
        ?>
    </div>
    <div class="form-group divRow">
        <label><?php echo __('Location'); ?></label>
        <?php
        $loc_q = $dbs->query('SELECT location_name FROM mst_location');
        $loc_options = array();
        $loc_options[] = array('0', __('ALL'));
        while ($loc_d = $loc_q->fetch_row()) {
            $loc_options[] = array($loc_d[0], $loc_d[0]);
        }
        echo simbio_form_element::selectList('location', $loc_options,'','class="form-control col-3"');
        ?>
    </div>
</div>
      <input type="submit" name="applyFilter" class="btn btn-primary" value="<?php echo __('Apply Filter'); ?>" />
      <input type="button" name="moreFilter" class="btn btn-default" value="<?php echo __('Show More Filter Options'); ?>" />
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
    $table_spec = 'stock_take_item AS i';

    // create datagrid
    $reportgrid = new report_datagrid();
    $reportgrid->setSQLColumn('item_code AS \''.__('Item Code').'\'',
        'title AS \''.__('Title').'\'',
        'gmd_name AS \''.__('GMD').'\'',
        'classification AS \''.__('Classification').'\'',
        'coll_type_name AS \''.__('Collection Type').'\'',
        'call_number AS \''.__('Call Number').'\'');
    $reportgrid->setSQLorder('title ASC');

    // is there any search
    $criteria = 'status=\'m\' ';
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
            $criteria .= ' AND (title LIKE \'%'.$keyword.'%\')';
        }
    }
    if (isset($_GET['itemCode']) AND !empty($_GET['itemCode'])) {
        $item_code = $dbs->escape_string(trim($_GET['itemCode']));
        $criteria .= ' AND item_code LIKE \'%'.$item_code.'%\'';
    }
    if (isset($_GET['class']) AND ($_GET['class'] != '')) {
        $class = $dbs->escape_string($_GET['class']);
        $criteria .= ' AND classification LIKE \''.$class.'%\'';
    }
    if (isset($_GET['gmd']) AND !empty($_GET['gmd'])) {
        $gmd = $dbs->escape_string(trim($_GET['gmd']));
        $criteria .= ' AND gmd_name=\''.$gmd.'\'';
    }
    if (isset($_GET['collType']) AND !empty($_GET['collType'])) {
        $collType = $dbs->escape_string(trim($_GET['collType']));
        $criteria .= ' AND coll_type_name=\''.$collType.'\'';
    }
    if (isset($_GET['location']) AND !empty($_GET['location'])) {
        $location = $dbs->escape_string(trim($_GET['location']));
        $criteria .= ' AND location_name=\''.$location.'\'';
    }
    $reportgrid->setSQLCriteria($criteria);

    // put the result into variables
    echo $reportgrid->createDataGrid($dbs, $table_spec, 50);

    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#pagingBox\').html(\''.str_replace(array("\n", "\r", "\t"), '', $reportgrid->paging_set).'\');'."\n";
    echo '</script>';

    $content = ob_get_clean();
    // include the page template
    require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';
}
