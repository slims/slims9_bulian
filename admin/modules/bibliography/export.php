<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

use SLiMS\Csv\Writer;
use SLiMS\Csv\Row;
/* Biblio data export section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if (isset($_POST['doExport'])) {
  // check for form validity
  if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
      utility::jsToastr('Data Export', __('Required fields (*)  must be filled correctly!'), 'error');
      exit();
  } else {
    // set PHP time limit
    set_time_limit(0);

    // create local function to fetch values
    function getValues($obj_db, $str_query)
    {
      // make query from database
      $_value_q = $obj_db->query($str_query);
      if ($_value_q->num_rows > 0) {
          $_value_buffer = '';
          while ($_value_d = $_value_q->fetch_row()) {
              if ($_value_d[0]) {
                  $_value_buffer .= '<'.$_value_d[0].'>';
              }
          }
          return $_value_buffer;
      }
      return null;
    }

    // limit
    $limit = intval($_POST['recordNum']);
    $offset = intval($_POST['recordOffset']);
    // fetch all data from biblio table
    $sql = "SELECT
        b.biblio_id, b.title, gmd.gmd_name, b.edition,
        b.isbn_issn, publ.publisher_name, b.publish_year,
        b.collation, b.series_title, b.call_number,
        lang.language_name, pl.place_name, b.classification,
        b.notes, b.image, b.sor
        FROM biblio AS b
        LEFT JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
        LEFT JOIN mst_publisher AS publ ON b.publisher_id=publ.publisher_id
        LEFT JOIN mst_language AS lang ON b.language_id=lang.language_id
        LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id ORDER BY b.last_update DESC";
    if ($limit > 0) { $sql .= ' LIMIT '.$limit; }
    if ($offset > 1) {
      if ($limit > 0) {
          $sql .= ' OFFSET '.($offset-1);
      } else {
          $sql .= ' LIMIT '.($offset-1).',99999999999';
      }
    }
    // for debugging purpose only
    // die($sql);
    $all_data_q = $dbs->query($sql);
    if ($dbs->error) {
      utility::jsToastr('Data Export', __('Error on query to database, Export FAILED!'), 'error');
    } else {
        if ($all_data_q->num_rows > 0) {
          // Define CSV standart based on user input
          $standart = [
            'separator' => trim($_POST['fieldSep']),
            'enclosed_with' =>  trim($_POST['fieldEnc']),
            'record_separator' => [
                'newline' => ($_POST['recordSep'] === 'NEWLINE' ? "\n" : "\r"),
                'return' => "\r"
            ]
          ];

          $formatter = function($content) use($dbs) {
            return $dbs->escape_string($content) . ' - ' . rand(1,$limit);
          };

          $csv = new Writer;

          // Define header row instance
          $header = new Row([], array_merge($standart, ['key_based' => true]));

          while ($biblio_d = $all_data_q->fetch_assoc()) {
              $itemData = '';
              $id = $biblio_d['biblio_id'];

              // skip biblio_id
              unset($biblio_d['biblio_id']);

              // Header process
              if (count($header) === 0 && isset($_POST['header'])) {
                // main header data
                foreach ($biblio_d as $columnName => $value) $header->add($columnName, $value??'');
                // additional header data
                $header->add('authors', '');
                $header->add('topics', '');
                $header->add('item_code', '');

                // add into csv collection
                $csv->add($header);
              }
              
              // initialization csv row data with custom formatter
              $itemData = new Row($biblio_d, $standart, $formatter);

              /**
               * Author,Topic & Item Code seperated from
               * main query. After main data add into row instance
               * we need to add another with "add" method.
               */
              // authors column
              $itemData->add('authors', getValues($dbs, 'SELECT a.author_name FROM biblio_author AS ba
              LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
              WHERE ba.biblio_id='.$id)??'');

              // topics column
              $itemData->add('topics', getValues($dbs, 'SELECT t.topic FROM biblio_topic AS bt
              LEFT JOIN mst_topic AS t ON bt.topic_id=t.topic_id
              WHERE bt.biblio_id='.$id)??'');

              // item code column
              $itemData->add('item_code', getValues($dbs, 'SELECT item_code FROM item AS i
              WHERE i.biblio_id='.$id)??'');

              // add into csv collection
              $csv->add($itemData);
          }

          // After all we stream it into a file
          $csv->download('senayan_biblio_export');
        } else {
          utility::jsToastr('Data Export', __('There is no record in bibliographic database yet, Export FAILED!'), 'error');
        }
    }
  }
  exit();
}
?>
<div class="menuBox">
<div class="menuBoxInner exportIcon">
	<div class="per_title">
    	<h2><?php echo __('Export Tool'); ?></h2>
	</div>
	<div class="infoBox">
    	<?php echo __('Export bibliographics data to CSV file'); ?>
	</div>
</div>
</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doExport" value="'.__('Export Now').'" class="s-btn btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" maxlength="3" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;" class="form-control"');
// record separator
$rec_sep_options[] = array('NEWLINE', 'NEWLINE');
$rec_sep_options[] = array('RETURN', 'CARRIAGE RETURN');
$form->addSelectList('recordSep', __('Record Separator'), $rec_sep_options,'','class="form-control col-4"');
// number of records to export
$form->addTextField('text', 'recordNum', __('Number of Records To Export (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;"  class="form-control"');
// header (column name)
$form->addCheckBox('header', __('Put columns names in the first row'), array( array('1', __('Yes')) ), '');
// output the form
echo $form->printOut();
