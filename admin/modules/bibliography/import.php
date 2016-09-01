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

/* Biblio Import section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if ($sysconf['index']['type'] == 'index') {
  require MDLBS.'system/biblio_indexer.inc.php';
  // create biblio_indexer class instance
  $indexer = new biblio_indexer($dbs);
}

// max chars in line for file operations
$max_chars = 1024*100;

if (isset($_POST['doImport'])) {
  // check for form validity
  if (!$_FILES['importFile']['name']) {
    utility::jsAlert(__('Please select the file to import!'));
    exit();
  } else if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
    utility::jsAlert(__('Required fields (*)  must be filled correctly!'));
    exit();
  } else {
    $start_time = time();
    // set PHP time limit
    set_time_limit(0);
    // set ob implicit flush
    ob_implicit_flush();
    // create upload object
    $upload = new simbio_file_upload();
    // get system temporary directory location
    $temp_dir = sys_get_temp_dir();
    $uploaded_file = $temp_dir.DIRECTORY_SEPARATOR.$_FILES['importFile']['name'];
    unlink($uploaded_file);
    // set max size
    $max_size = $sysconf['max_upload']*1024;
    $upload->setAllowableFormat(array('.csv'));
    $upload->setMaxSize($max_size);
    $upload->setUploadDir($temp_dir);
    $upload_status = $upload->doUpload('importFile');
    if ($upload_status != UPLOAD_SUCCESS) {
        utility::jsAlert(__('Upload failed! File type not allowed or the size is more than').($sysconf['max_upload']/1024).' MB'); //mfc
        exit();
    }
    // uploaded file path
    $uploaded_file = $temp_dir.DIRECTORY_SEPARATOR.$_FILES['importFile']['name'];
    $row_count = 0;
    // check for import setting
    $record_num = intval($_POST['recordNum']);
    $field_enc = trim($_POST['fieldEnc']);
    $field_sep = trim($_POST['fieldSep']);
    $record_offset = intval($_POST['recordOffset']);
    $record_offset = $record_offset-1;
    // get current datetime
    $curr_datetime = date('Y-m-d H:i:s');
    $curr_datetime = '\''.$curr_datetime.'\'';
    // foreign key id cache
    $gmd_id_cache = array();
    $publ_id_cache = array();
    $lang_id_cache = array();
    $place_id_cache = array();
    $author_id_cache = array();
    $subject_id_cache = array();
    // read file line by line
    $inserted_row = 0;
    $file = fopen($uploaded_file, 'r');
    while (!feof($file)) {
      // record count
      if ($record_num > 0 AND $row_count == $record_num) {
        break;
      }
      // go to offset
      if ($row_count < $record_offset) {
        // pass and continue to next loop
        $field = fgetcsv($file, 1024, $field_sep, $field_enc);
        $row_count++;
        continue;
      } else {
          // get an array of field
          $field = fgetcsv($file, $max_chars, $field_sep, $field_enc);
          if ($field) {
              // strip escape chars from all fields
              foreach ($field as $idx => $value) {
                  $field[$idx] = str_replace('\\', '', trim($value));
                  $field[$idx] = $dbs->escape_string($field[$idx]);
              }
              // strip leading field encloser if any
              $title = preg_replace('@^\\\s*'.$field_enc.'@i', '', $field[0]);
              $title = '\''.$title.'\'';
              $gmd_id = utility::getID($dbs, 'mst_gmd', 'gmd_id', 'gmd_name', $field[1], $gmd_id_cache);
              $edition = $field[2]?'\''.$field[2].'\'':'NULL';
              $isbn_issn = $field[3]?'\''.$field[3].'\'':'NULL';
              $publisher_id = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $field[4], $publ_id_cache);
              $publish_year = $field[5]?'\''.$field[5].'\'':'NULL';
              $collation = $field[6]?'\''.$field[6].'\'':'NULL';
              $series_title = $field[7]?'\''.$field[7].'\'':'NULL';
              $call_number = $field[8]?'\''.$field[8].'\'':'NULL';
              $language_id = utility::getID($dbs, 'mst_language', 'language_id', 'language_name', $field[9], $lang_id_cache);
              $language_id = '\''.$language_id.'\'';
              $publish_place_id = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $field[10], $place_id_cache);
              $classification = $field[11]?'\''.$field[11].'\'':'NULL';;
              $notes = $field[12]?'\''.$field[12].'\'':'NULL';;
              $image = $field[13]?'\''.$field[13].'\'':'NULL';
              $sor = $field[14]?'\''.$field[14].'\'':'NULL';
              // $authors = preg_replace('@\\\s*'.$field_enc.'$@i', '', $field[15]);
              $authors = trim($field[15]);
              $subjects = trim($field[16]);
              $items = trim($field[17]);
              // sql insert string
              $sql_str = "INSERT IGNORE INTO biblio (title, gmd_id, edition,
                  isbn_issn, publisher_id, publish_year,
                  collation, series_title, call_number,
                  language_id, publish_place_id, classification,
                  notes, image, sor, input_date, last_update)
                      VALUES ($title, $gmd_id, $edition,
                      $isbn_issn, $publisher_id, $publish_year,
                      $collation, $series_title, $call_number,
                      $language_id, $publish_place_id, $classification,
                      $notes, $image, $sor, $curr_datetime, $curr_datetime)";
              // send query
              $dbs->query($sql_str);
              $biblio_id = $dbs->insert_id;
              if (!$dbs->error) {
                  $inserted_row++;
                  // set authors
                  if (!empty($authors)) {
                      $biblio_author_sql = 'INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ';
                      $authors = explode('><', $authors);
                      foreach ($authors as $author) {
                          $author = trim(str_replace(array('>', '<'), '', $author));
                          $author_id = utility::getID($dbs, 'mst_author', 'author_id', 'author_name', $author, $author_id_cache);
                          $biblio_author_sql .= " ($biblio_id, $author_id, 2),";
                      }
                      // remove last comma
                      $biblio_author_sql = substr_replace($biblio_author_sql, '', -1);
                      // execute query
                      $dbs->query($biblio_author_sql);
                      // echo $dbs->error;
                  }
                  // set topic
                  if (!empty($subjects)) {
                      $biblio_subject_sql = 'INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES ';
                      $subjects = explode('><', $subjects);
                      foreach ($subjects as $subject) {
                          $subject = trim(str_replace(array('>', '<'), '', $subject));
                          $subject_id = utility::getID($dbs, 'mst_topic', 'topic_id', 'topic', $subject, $subject_id_cache);
                          $biblio_subject_sql .= " ($biblio_id, $subject_id, 2),";
                      }
                      // remove last comma
                      $biblio_subject_sql = substr_replace($biblio_subject_sql, '', -1);
                      // execute query
                      $dbs->query($biblio_subject_sql);
                      // echo $dbs->error;
                  }
                  // items
                  if (!empty($items)) {
                      $item_sql = 'INSERT IGNORE INTO item (biblio_id, item_code) VALUES ';
                      $item_array = explode('><', $items);
                      foreach ($item_array as $item) {
                          $item = trim(str_replace(array('>', '<'), '', $item));
                          $item_sql .= " ($biblio_id, '$item'),";
                      }
                      // remove last comma
                      $item_sql = substr_replace($item_sql, '', -1);
                      // execute query
                      $dbs->query($item_sql);
                  }
              }

              // create biblio index
              if ($sysconf['index']['type'] == 'index') {
                $indexer->makeIndex($biblio_id);
              }
          }
          $row_count++;
      }
    }
    // close file handle
    fclose($file);
    $end_time = time();
    $import_time_sec = $end_time-$start_time;
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'Importing '.$inserted_row.' bibliographic records from file : '.$_FILES['importFile']['name']);
    echo '<script type="text/javascript">'."\n";
    echo 'parent.$(\'#importInfo\').html(\''
    . str_replace(array('{numberOfInsertedRecords}', '{recordOffset}', '{timeInSeconds}'),array($inserted_row,intval($_POST['recordOffset']), $import_time_sec), __( '<strong>{numberOfInsertedRecords}</strong> records inserted successfully to bibliographic database, from record <strong>{recordOffset} in {timeInSeconds} second(s)</strong>'))
            . '\');'."\n";
    echo 'parent.$(\'#importInfo\').css( {\'display\': \'block\'} );'."\n";
    echo '</script>';
    exit();
  }
}
?>
<fieldset class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    <h2><?php echo __('Import Tool'); ?></h2>
    </div>
    <div class="infoBox">
    <?php echo __('Import for bibliographics data from CSV file. For guide on CVS fields order and format please refer to documentation or visit <a href="http://slims.web.id" target="_blank">Official Website</a>'); ?>
	</div>
</div>
</fieldset>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Import Now').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input = simbio_form_element::textField('file', 'importFile');
$str_input .= ' Maximum '.$sysconf['max_upload'].' KB';
$form->addAnything(__('File To Import'), $str_input);
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" maxlength="3"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of Records To Import (0 for all records)'), '0', 'style="width: 10%;"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;"');
// output the form
echo $form->printOut();
