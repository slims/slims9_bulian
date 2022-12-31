<?php

/**
 * @Created by          : Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 2020-03-14
 * @File name           : procurement_list.php
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../../sysconfig.inc.php';
// IP based access limitation
require_once LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_element.inc.php';

// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

ob_start();

$page_title = 'Procurement List';

$filter = isset($_GET['filter'])?$dbs->escape_string(simbio_security::xssFree($_GET['filter'])):'%%';
?>
<div class="menuBox">
<div class="menuBoxInner backupIcon">
    <div class="per_title">
        <h2><?= __('Procurement List in Year : ').$filter ?></h2>
   </div>
    <div class="sub_section">
      <form name="search" action="<?= MWB; ?>reporting/customs/pop_procurement_list.php" id="search" method="get">
        <input type="hidden" name="filter" value="<?= $filter??date('Y'); ?>" />
        <input type="hidden" name="search" value="true" />
        <div class="form-group row">
          <label for="inputKeywords" class="col-sm-2 col-form-label"><?= __('Keyword')?></label>
          <div class="col-sm-10">
            <input type="text"  class="form-control col-8" id="keywords" name="keywords" value="<?= $_GET['keywords']??'';?>">
          </div>
        </div>
        <div class="form-group row">
          <label for="inputClassification" class="col-sm-2 col-form-label"><?= __('Classification')?></label>
          <div class="col-sm-10">
          <?php
          $class_options[] = array('',__('ALL'));
          for ($c = 0; $c < 10; $c++) {
              $class_options[] = array($c, $c.'00');
          } 
          $class_options[] = array('other', __('Others'));   
          echo simbio_form_element::selectList('classification', $class_options,$_GET['classification']??'','class="form-control col-4"');
          ?>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputCollectionType" class="col-sm-2 col-form-label"><?= __('Collection Type')?></label>
          <div class="col-sm-10">
          <?php
          $coll_type_q = $dbs->query('SELECT coll_type_id, coll_type_name FROM mst_coll_type WHERE coll_type_name!=\'\'');
          $coll_type_options[] = array('', __('ALL'));
          while ($coll_type_d = $coll_type_q->fetch_row()) {
              $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
          }
          $coll_type_options[] = array('other', __('Others'));             
          echo simbio_form_element::selectList('coll_type', $coll_type_options,$_GET['coll_type']??'','class="form-control col-4"');
          ?>
          </div>
        </div>
        <div class="form-group row">
          <label for="inputLocation" class="col-sm-2 col-form-label"><?= __('Location')?></label>
          <div class="col-sm-10">
          <?php
          $loc_type_q = $dbs->query('SELECT location_id, location_name FROM mst_location WHERE location_name!=\'\'');
          $loc_type_options[] = array('', __('ALL'));
          while ($loc_type_d = $loc_type_q->fetch_row()) {
              $loc_type_options[] = array($loc_type_d[0], $loc_type_d[1]);
          }
          $loc_type_options[] = array('other', __('Others'));   
          echo simbio_form_element::selectList('location', $loc_type_options,$_GET['location']??'','class="form-control col-4"');   
          ?>
          </div>
        </div>
            <div class="btn-group">
              <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
              <a href="../xlsoutput.php" class="btn btn-info" target="_BLANK"><?= __('Export to spreadsheet format');?></a>
          </div>
      </form>
  </div>
</div>
</div>

<?php
// table spec
$table_spec = 'item i left join biblio b on b.biblio_id=i.biblio_id 
LEFT JOIN mst_coll_type mct ON mct.coll_type_id=i.coll_type_id 
LEFT JOIN mst_location ml ON i.location_id=ml.location_id
LEFT JOIN mst_publisher mp ON mp.publisher_id=b.publisher_id
LEFT JOIN mst_gmd mg ON mg.gmd_id=b.gmd_id
LEFT JOIN mst_language mlang ON  mlang.language_id=b.language_id
LEFT JOIN mst_place mpl ON mpl.place_id=b.publish_place_id';
$criteria = " YEAR(i.input_date) LIKE '".($filter==__('ALL')?'%%':$filter)."'";
// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn(
    'i.item_code AS \''.__('Item Code').'\'', 
    'b.title AS  \''.__('Title').'\'',     
    'b.classification AS \''.__('Classification').'\'',    
    'mct.coll_type_name AS \''.__('Collection Type').'\'', 
    'i.input_date AS \''.__('Input Date').'\'');
$datagrid->setSQLorder('i.input_date DESC');

// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
   $keywords = $dbs->escape_string($_GET['keywords']);
   $criteria .= " AND (b.title LIKE '%$keywords%' OR i.item_code LIKE '%$keywords%') ";
}
if (isset($_GET['classification']) AND $_GET['classification'] !== '') {
   $classification = $dbs->escape_string($_GET['classification']);
   $criteria .= ($classification!='other')?" AND b.classification LIKE '$classification%'":" AND (trim(b.classification) REGEXP '^[^0-9]' OR trim(b.classification)='' OR trim(b.classification) IS NULL)";
}

if (isset($_GET['coll_type']) AND $_GET['coll_type']) {
   $coll_type = $dbs->escape_string($_GET['coll_type']);
   $criteria .= ($coll_type!='other')?" AND i.coll_type_id='$coll_type'":" AND i.coll_type_id=''";
}

if (isset($_GET['location']) AND $_GET['location']) {
   $location = $dbs->escape_string($_GET['location']);
   $criteria .=($location!='other')?" AND i.location_id LIKE '$location'":" AND i.location_id = ''";
}

$datagrid->setSQLCriteria($criteria);
// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 10, false);

$msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
echo '<div class="infoBox">'.$msg.'</div>';
echo $datagrid_result;
$content = ob_get_clean();

$xlsquery = 'SELECT i.item_code AS \''.__('Item Code').'\''. 
    ',i.inventory_code AS \''.__('Inventory Code').'\''. 
    ',b.title AS  \''.__('Title').'\''.     
    ',mp.publisher_name AS \''.__('Publisher').'\''.
    ',b.publish_year AS \''.__('Publishing Year').'\''.
    ',mpl.place_name AS \''.__('Publishing Place').'\''.
    ',mlang.language_name AS \''.__('Language').'\''.
    ',b.classification AS \''.__('Classification').'\''.    
    ',mct.coll_type_name AS \''.__('Collection Type').'\''.
    ',i.site AS \''.__('Self Location').'\''.
    ',ml.location_name AS \''.__('Location').'\''. 
    ',i.price AS \''.__('Price').'\''.
    ',i.input_date AS \''.__('Input Date').'\''.
    ' FROM '.$table_spec.' WHERE '.$criteria;

unset($_SESSION['xlsdata']);
$_SESSION['xlsquery'] = $xlsquery;
$_SESSION['tblout'] = "procurement_list:$filter";
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/printed_page_tpl.php';