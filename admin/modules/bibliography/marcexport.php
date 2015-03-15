<?php

/**
 *
 * Modified 2010  by Wardiyono (wynerst@gmail.com)
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

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

function marc_array($target_file, $input_id) {
	require 'File/MARC.php';

	$marc = new File_MARC_Record();

	$marc->appendField(new File_MARC_Data_Field('245', array(
			new File_MARC_Subfield('a', 'Main title: '),
			new File_MARC_Subfield('b', 'subtitle'),
			new File_MARC_Subfield('c', 'author')
		), null, null
	));

	$rec_bib_q = $dbs->query('SELECT s.*, b.collation FROM search_biblio AS s LEFT JOIN biblio AS b ON b.biblio_id = s.biblio_id WHERE s.biblio_id ='. $input_id);
	while ($_recs = $rec_bib_q->fetch_assoc()) {
		if (isset($_recs['title']) && $_recs['title'] <> "") {
			$tag['245'][] = File_MARC_Subfield('a', trim(strstr($_recs['title'], ':', true)));
			$tag['245'][] = File_MARC_Subfield('b', ltrim(strstr($_recs['title'], ':'),':'));
			if (isset($_recs['sor']) && $_recs['sor'] <> "") {
				$tag['245'][] = File_MARC_Subfield('c', $_recs['sor']);
			}
			if (isset($_recs['gmd']) && $_recs['gmd'] <> "") {
				$tag['245'][] = File_MARC_Subfield('h', $_recs['gmd']);
			}
			$marc->appendField(new File_MARC_Data_Field('245', $tag['245'],0), null, null);
			// $tag['245'] = $sd.'a'.$_recs['title'].$sd.'h'.$_recs['gmd'];
		}
		if (isset($_recs['isbn_issn']) && $_recs['isbn_issn'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('020', array(
					new File_MARC_Subfield('a', $_recs['isbn_issn']),
				), null, null
			));
			// $tag['020'] = $sd.'a'.$_recs['isbn_issn'];
		}
		if (isset($_recs['edition']) && $_recs['edition'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('250', array(
					new File_MARC_Subfield('a', $_recs['edition']),
				), null, null
			));
			//$tag['250'] = $sd.'a'.$_recs['edition'];
		}
		// $tag[] = $_recs['author'];
		// get author name and roles first
		$_aut_q = $dbs->query('SELECT a.author_name,a.author_year,a.authority_type, i.level FROM biblio_author as i LEFT JOIN `mst_author` as a on a.author_id=i.author_id WHERE i.biblio_id='.$_recs['biblio_id']);
		while ($_rs_aut = $_aut_q->fetch_assoc()) {
			if ($_rs_aut['level'] = 1) {
				if ($_rs_aut['authority_type'] = 'p') {
					$marc->appendField(new File_MARC_Data_Field('100', array(
							new File_MARC_Subfield('a', $_rs_aut['author_name']),
						), null, null
					));
					//$tag['100'] = $sd.'a'.$_rs_aut['author_name'];
				} elseif ($_rs_aut['authority_type'] = 'o') {
					$marc->appendField(new File_MARC_Data_Field('110', array(
							new File_MARC_Subfield('a', $_rs_aut['author_name']),
						), null, null
					));
					//$tag['110'] = $sd.'a'.$_rs_aut['author_name'];
				} elseif ($_rs_aut['authority_type'] = 'c') {
					$marc->appendField(new File_MARC_Data_Field('111', array(
							new File_MARC_Subfield('a', $_rs_aut['author_name']),
						), null, null
					));
					//$tag['111'] = $sd.'a'.$_rs_aut['author_name'];
				}
			} else {
				if ($_rs_aut['authority_type'] = 'p') {
					if (!isset($tag['700'])) {
						$marc->appendField(new File_MARC_Data_Field('700', array(
								new File_MARC_Subfield('a', $_rs_aut['author_name']),
							), null, null
						));
					} elseif ($_rs_aut['authority_type'] = 'o') {
						$marc->appendField(new File_MARC_Data_Field('710', array(
								new File_MARC_Subfield('a', $_rs_aut['author_name']),
							), null, null
						));
					} elseif ($_rs_aut['authority_type'] = 'c') {
						$marc->appendField(new File_MARC_Data_Field('711', array(
								new File_MARC_Subfield('a', $_rs_aut['author_name']),
							), null, null
						));
					}
				}
			}
		}
		// $tag[] = $_recs['topic'];
		// get topic and its type first
		$_aut_q = $dbs->query('SELECT t.topic,t.topic_type,i.level FROM biblio_topic as i LEFT JOIN `mst_topic` as t on t.topic_id=i.topic_id WHERE i.biblio_id='.$_recs['biblio_id']);
		while ($_rs_aut = $_aut_q->fetch_assoc()) {
			if ($_rs_aut['authority_type'] = 't') {
				if (!isset($tag['650'])) {
					$marc->appendField(new File_MARC_Data_Field('650', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
				//$tag['650'] = $sd.'a'.$_rs_aut['topic'];
			} elseif ($_rs_aut['authority_type'] = 'n') {
				if (!isset($tag['600'])) {
					$marc->appendField(new File_MARC_Data_Field('600', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
				//$tag['600'] = $sd.'a'.$_rs_aut['topic'];
			} elseif ($_rs_aut['authority_type'] = 'c') {
				if (!isset($tag['610'])) {
					$marc->appendField(new File_MARC_Data_Field('610', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
				//$tag['610'] = $sd.'a'.$_rs_aut['topic'];
			} elseif ($_rs_aut['authority_type'] = 'g') {
				if (!isset($tag['651'])) {
					$marc->appendField(new File_MARC_Data_Field('651', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
				//$tag['651'] = $sd.'a'.$_rs_aut['topic'];
			} elseif ($_rs_aut['authority_type'] = 'tm' OR $_rs_aut['authority_type'] = 'oc') {
				if (!isset($tag['653'])) {
					$marc->appendField(new File_MARC_Data_Field('653', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
			} elseif ($_rs_aut['authority_type'] = 'gr') {
				if (!isset($tag['655'])) {
					$marc->appendField(new File_MARC_Data_Field('655', array(
							new File_MARC_Subfield('a', $_rs_aut['topic']),
						), null, null
					));
				}
			}
		}
		$marc->appendField(new File_MARC_Data_Field('005', array(
				new File_MARC_Subfield('a', preg_replace("(-|:| )", "", $_recs['last_update'])),
			), null, null
		));
		//$tag['005'] = $sd.'a'.preg_replace("(-|:| )", "", $_recs['last_update']);
		$marc->appendField(new File_MARC_Data_Field('260', array(
				new File_MARC_Subfield('a', $_recs['publish_place']),
				new File_MARC_Subfield('b', $_recs['publisher']),
				new File_MARC_Subfield('c', $_recs['publish_year']),
			), null, null
		));
		//$tag['260'] = $sd.'a'.$_recs['publish_place'].$sd.'b'.$_recs['publisher'].$sd.'c'.$_recs['publish_year'];
		$marc->appendField(new File_MARC_Data_Field('041', array(
				new File_MARC_Subfield('a', $_recs['language']),
			), null, null
		));
		//$tag['041'] = $sd.'a'.$_recs['language'];
		$marc->appendField(new File_MARC_Data_Field('084', array(
				new File_MARC_Subfield('a', $_recs['classification']),
			), null, null
		));
		//$tag['084'] = $sd.'a'.$_recs['classification'];
		//$tag['245'] = $_recs['spec_detail_info'];
		if (isset($_recs['collation']) && $_recs['collation'] <> "") {
			$tag['300'] = $sd.'a'.preg_replace("/;/", ";".$sd."c", preg_replace("/:/", ":".$sd."b", $_recs['collation']));
		}
		if (isset($_recs['notes']) && $_recs['notes'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('500', array(
					new File_MARC_Subfield('a', $_recs['notes']),
				), null, null
			));
			//$tag['500'] = $sd.'a'.$_recs['notes'];
		}
		if (isset($_recs['series_title']) && $_recs['series_title'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('490', array(
					new File_MARC_Subfield('a', $_recs['series_title']),
				), null, null
			));
			//$tag['490'] = $sd.'a'.$_recs['series_title'];
		}
		if (isset($_recs['content_type']) && $_recs['content_type'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('336', array(
					new File_MARC_Subfield('a', $_recs['content_type']),
				), null, null
			));
			//$tag['336'] = $sd.'a'.$_recs['content_type'];
		} 
		if (isset($_recs['media_type']) && $_recs['media_type'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('337', array(
					new File_MARC_Subfield('a', $_recs['media_type']),
				), null, null
			));
			//$tag['337'] = $sd.'a'.$_recs['media_type'];
		}
		if (isset($_recs['carrier_type']) && $_recs['carrier_type'] <> "") {
			$marc->appendField(new File_MARC_Data_Field('338', array(
					new File_MARC_Subfield('a', $_recs['carrier_type']),
				), null, null
			));
			//$tag['338'] = $sd.'a'.$_recs['carrier_type'];
		}

	//print_r($tag);
	$fh = fopen($target_file, 'w');
	fwrite($fh, $marc->toRaw());
	fclose($fh);

	unset($tag);
	}
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
      // send ajax request
      //echo 'jQuery.ajax({ url: \''.SWB.'lib/phpbarcode/barcode.php?code='.$itemID.'&encoding='.$sysconf['barcode_encoding'].'&scale='.$size.'&mode=png&act=save\', type: \'GET\', error: function() { alert(\'Error creating barcode!\'); } });'."\n";
      // add to sessions
      $_SESSION['marcexport'][$itemID] = $itemID;
      $print_count++;
    }
  }
  echo 'top.$(\'#queueCount\').html(\''.$print_count.'\')';
  echo '</script>';
  utility::jsAlert(__('Selected items added to print queue'));
  exit();
}
  
// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
  // update print queue count object
  echo '<script type="text/javascript">top.$(\'#queueCount\').html(\'0\');</script>';
  utility::jsAlert(__('Export queue cleared!'));
  unset($_SESSION['marcexport']);
  exit();
}

// Starting Export to MARC
if (isset($_GET['action']) AND $_GET['action'] == 'export') {
  // check if label session array is available
  if (!isset($_SESSION['marcexport'])) {
    utility::jsAlert(__('There is no data to print!*'));
    die();
  }
  if (count($_SESSION['marcexport']) < 1) {
    utility::jsAlert(__('There is no data to print!'));
    die();
  }

  // concat all ID together
  $item_ids = '';
  foreach ($_SESSION['marcexport'] as $id) {
    $item_ids .= '\''.$id.'\',';
  }
  // strip the last comma
  $item_ids = substr_replace($item_ids, '', -1);
  marc_array("slims8-akasia.mrc", 1);
  utility::jsAlert('Done..');

  /** send query to database
  $item_q = $dbs->query('SELECT b.title, i.item_code FROM item AS i
    LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
    WHERE i.item_code IN('.$item_ids.')');
  $item_data_array = array();
  while ($item_d = $item_q->fetch_row()) {
    if ($item_d[0]) {
      $item_data_array[] = $item_d;
    }
  }
  */
  
  // unset the session
  unset($_SESSION['marcexport']);

}
?>

<fieldset class="menuBox">
<div class="menuBoxInner printIcon">
  <div class="per_title">
	  <h2><?php echo __('Export Catalog to Marc Format '); ?></h2>
  </div>
  <div class="sub_section">
	  <div class="btn-group">
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/marcoutput.php?action=clear" class="notAJAX btn btn-default"><i class="glyphicon glyphicon-trash"></i>&nbsp;<?php echo __('Clear selected catalog'); ?></a>
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/marcoutput.php?action=export" class="notAJAX btn btn-default"><i class="glyphicon glyphicon-print"></i>&nbsp;<?php echo __('Export now');?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>bibliography/marcoutput.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
  </div>
  <div class="infoBox">
  <?php
  echo __('Currently there is').' ';
  if (isset($_SESSION['marcexport'])) {
    echo '<font id="queueCount" style="color: #f00">'.count($_SESSION['marcexport']).'</font>';
  } else { echo '<font id="queueCount" style="color: #f00">0</font>'; }
  echo ' '.__('in queue waiting to be processed.');
  ?>
  </div>
</div>
</fieldset>
<?php
/* search form end */

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
  $table_spec = 'search_biblio';
  $datagrid->setSQLColumn('biblio_id',
    'title AS \''.__('Title').'\'',
    'author AS \''.__('Author').'\'');
} else {
  require LIB.'biblio_list.inc.php';
  // table spec
  $table_spec = 'search_biblio';
  $datagrid->setSQLColumn('biblio_id',
    'title AS \''.__('Title').'\'',
    'author AS \''.__('Author').'\'');
}
$datagrid->setSQLorder('last_update DESC');
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
  $keywords = $dbs->escape_string(trim($_GET['keywords']));
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
$datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
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
  echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"<div>'.__('Query took').' <b>'.$datagrid->query_time.'</b> '.__('second(s) to complete').'</div></div>';
}
echo $datagrid_result;
/* main content end */
