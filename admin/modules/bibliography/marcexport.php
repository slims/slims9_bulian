<?php

/**
 *
 * Modified 2015  by Wardiyono (wynerst@gmail.com)
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

// key to authenticate
define('INDEX_AUTH', '1');

// load SLiMS main system configuration
require '../../../sysconfig.inc.php';

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require MDLBS.'bibliography/biblio_utils.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// check if PEAR is installed
ob_start();
include MDLBS . 'bibliography/File/MARC.php';
ob_end_clean();
if (!class_exists('File_MARC')) {
  die('<div class="errorBox">'.__('<a href="http://pear.php.net/index.php">PEAR</a>, <a href="http://pear.php.net/package/File_MARC">File_MARC</a>
    packages need to be installed in order to export MARC record').'</div>');
  }

// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
  // update print queue count object
  echo '<script type="text/javascript">top.$(\'#queueCount\').html(\'0\');</script>';
  utility::jsToastr('MARC Export', __('Export queue cleared!'), 'success');
  unset($_SESSION['marcexport']);
  exit();
}
  
/* RECORD OPERATION */
if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
  if (!$can_read) {
    die();
  }
  if (!is_array($_POST['itemID'])) {
    // make an array
    $_POST['itemID'] = array((integer)$_POST['itemID']);
  }
  // loop array
  if (isset($_SESSION['marcexport'])) {
    $print_count = count($_SESSION['marcexport']);
  } else {
    $print_count = 0;
  }
  // create AJAX request
  echo '<script type="text/javascript" src="'.JWB.'jquery.js"></script>';
  echo '<script type="text/javascript">';
  // loop array
  foreach ($_POST['itemID'] as $itemID) {
    if (isset($_SESSION['marcexport'][$itemID])) {
      continue;
    }
    if (!empty($itemID)) {
      $barcode_text = trim($itemID);
      /* replace space */
      $barcode_text = str_replace(array(' ', '/', '\/'), '_', $barcode_text);
      /* replace invalid characters */
      $barcode_text = str_replace(array(':', ',', '*', '@'), '', $barcode_text);
      // add to sessions
      $_SESSION['marcexport'][$itemID] = $itemID;
      $print_count++;
    }
  }
  echo 'top.$(\'#queueCount\').html(\''.$print_count.'\')';
  echo '</script>';
  utility::jsToastr('MARC Export', __('Selected items added to print queue'), 'success');
  exit();
}

// batch export
if (isset($_POST['doExport'])) {
  if (!$can_read) {
    die();
  }
  $format = $_POST['exportType'];
  $total = (integer)$_POST['recordNum'];
  $start = (integer)$_POST['recordOffset'];
  $start = $start-1;
  if ($format == 'XML') {
    header('Content-type: application/marcxml+xml');
    header('Content-disposition: attachment; filename=slims-marc-export.xml');
  } else if ($format == 'JSON') {
    header('Content-type: application/json');
    header('Content-disposition: attachment; filename=slims-marc-export.json');
  } else {
    header('Content-type: application/marc');
    header('Content-disposition: attachment; filename=slims-marc-export.mrc');
  }

  $biblio = new Biblio($dbs, null);
  echo $biblio->marc_export('BATCH', $start, $total, $format);
  exit();  
}

// Starting Export to MARC
if (isset($_GET['action']) AND $_GET['action'] == 'export') {
  // check if label session array is available
  if (!isset($_SESSION['marcexport'])) {
    utility::jsToastr('MARC Export', __('There is no data to export!*'), 'error');
    exit();
  }
  if (count($_SESSION['marcexport']) < 1) {
    utility::jsToastr('MARC Export', __('There is no data to export!'), 'error');
    exit();
  }
  
  // concat all ID together
  $item_ids = implode(',', $_SESSION['marcexport']);
  // unset the session
  unset($_SESSION['marcexport']);
  $biblio = new Biblio($dbs, null);
  header('Content-type: application/marc');
  header('Content-disposition: attachment; filename=slims-marc-export.mrc');
  echo $biblio->marc_export($item_ids);
  // utility::jsAlert('Done..');  
  exit();
}
?>

<div class="menuBox">
<div class="menuBoxInner printIcon">
  <div class="per_title">
	  <h2><?php echo __('Export Catalog to Marc Format '); ?></h2>
  </div>
  <div class="sub_section">
	  <div class="btn-group">
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/marcexport.php?action=clear" class="btn btn-default notAJAX"><?php echo __('Clear selected catalog'); ?></a>
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/marcexport.php?action=export" class="btn btn-default notAJAX"><?php echo __('Export now');?></a>
      <a href="<?php echo MWB; ?>bibliography/marcexport.php?action=batch" class="  btn btn-default"><?php echo __('Batch Export');?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>bibliography/marcexport.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
      <input type="text" name="keywords" class="form-control col-md-3" />
      <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
  </div>
  <div class="infoBox">
  <?php
  echo __('Currently there is').' ';
  if (isset($_SESSION['marcexport'])) {
    echo '<strong id="queueCount" class="text-danger">'.count($_SESSION['marcexport']).'</strong>';
  } else { echo '<strong id="queueCount" class="text-danger">0</strong>'; }
  echo ' '.__('in queue waiting to be processed.');
  ?>
  </div>
</div>
</div>
<?php
/* search form end */
if (isset($_GET['action']) && $_GET['action'] == 'batch') {
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
    $form->submit_button_attr = 'name="doExport" value="'.__('Export Now').'" class="sbtn btn btn-default"';
    
    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';
    
    /* Form Element(s) */
    // record separator
    $rec_sep_options[] = array('RAW', 'MARC RAW');
    $rec_sep_options[] = array('XML', 'MARCXML');
    $rec_sep_options[] = array('JSON', 'MARCJSON');
    $form->addSelectList('exportType', __('Export Type'), $rec_sep_options,'','class="form-control col-3"');
    // number of records to export
    $form->addTextField('text', 'recordNum', __('Number of Records To Export (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
    // records offset
    $form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;" class="form-control"');
    // output the form
    echo $form->printOut();    
} else {
    // List of books
    $datagrid = new simbio_datagrid();
    /* ITEM LIST */
    require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
    require LIB.'biblio_list_model.inc.php';
    // index choice
    if ($sysconf['index']['type'] == 'index' || ($sysconf['index']['type'] == 'sphinx' && file_exists(LIB.'sphinx/sphinxapi.php'))) {
      if ($sysconf['index']['type'] == 'sphinx') {
	require LIB.'sphinx/sphinxapi.php';
	require LIB.'biblio_list_sphinx.inc.php';
      } else {
	require LIB.'biblio_list_index.inc.php';
      }
      // table spec
      $table_spec = 'search_biblio AS `index`';
      $datagrid->setSQLColumn('biblio_id',
	'title AS \''.__('Title').'\'',
	'author AS \''.__('Author').'\'');
    } else {
      require LIB.'biblio_list.inc.php';
      // table spec
      $table_spec = 'search_biblio AS `index`';
      $datagrid->setSQLColumn('biblio_id',
	'title AS \''.__('Title').'\'',
	'author AS \''.__('Author').'\'');
    }
    $datagrid->setSQLorder('last_update DESC');
    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
      $keywords = utility::filterData('keywords', 'get', true, true, true);
      $searchable_fields = array('title', 'author', 'subject');
      $search_str = '';
      // if no qualifier in fields
      if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
	foreach ($searchable_fields as $search_field) {
	  $search_str .= $search_field.'='.$keywords.' OR ';
	}
      } else {
	$search_str = $keywords;
      }
      $biblio_list = new biblio_list($dbs, 20);
      $criteria = $biblio_list->setSQLcriteria($search_str);
    }
    if (isset($criteria)) {
      $datagrid->setSQLcriteria('('.$criteria['sql_criteria'].')');
    }
    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // edit and checkbox property
    $datagrid->edit_property = false;
    $datagrid->chbox_property = array('itemID', __('Add'));
    $datagrid->chbox_action_button = __('Add To Export Queue');
    $datagrid->chbox_confirm_msg = __('Add to export queue?');
    $datagrid->column_width = array('50%', '45%');
    // set checkbox action URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
      $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
      echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"<div>'.__('Query took').' <b>'.$datagrid->query_time.'</b> '.__('second(s) to complete').'</div></div>';
    }
    echo $datagrid_result;
}

/* main content end */
