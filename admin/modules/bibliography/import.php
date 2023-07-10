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
use SLiMS\Filesystems\Storage;
use SLiMS\DB;
use SLiMS\Csv\Writer;
use SLiMS\Csv\Reader;
use SLiMS\Csv\Row;

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
require __DIR__ . '/biblio_utils.inc.php';

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

// Redirect content
if (isset($_SESSION['csv']['name']) && !isset($_POST['process'])) redirect()->simbioAJAX(MWB . 'bibliography/import_preview.php');

if (isset($_GET['action']) && $_GET['action'] === 'download_sample')
{
  // Create Csv instance
  $csv = new Writer;
  $csv->add(new Row([
    'title','gmd_name','edition',
    'isbn_issn','publisher_name',
    'publish_year','collation',
    'series_title','call_number',
    'language_name','place_name',
    'classification','notes','image',
    'sor','authors','topics','item_code'
  ]));

  // Download CSV
  $csv->download('biblio_sample_import');
}

// max chars in line for file operations
$max_chars = 1024*100;

if (isset($_POST['doImport'])) {
  // create upload object
  $files_disk = Storage::files();
  
  // check for form validity
  if (!isset($_POST['process'])) {

    if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
      utility::jsToastr(__('Import Tool'), __('Required fields (*)  must be filled correctly!'), 'error');
      exit();
    }

    // get system temporary directory location
    $_SESSION['csv'] = [];
    $_SESSION['csv']['name'] = md5($_FILES['importFile']['name'] . date('this'));
    
    if (!$files_disk->isExists('temp')) $files_disk->makeDirectory('temp');

    if ($files_disk->isExists('temp' . DS . $_SESSION['csv']['name'])) {
      $files_disk->delete('temp' . DS . $_SESSION['csv']['name']);
    }
    
    // set csv format
    $_SESSION['csv']['format'] = [
      'recordNum' => intval($_POST['recordNum']),
      'fieldEnc' => trim($_POST['fieldEnc']),
      'fieldSep' => trim($_POST['fieldSep']),
      'recordOffset' => intval($_POST['recordOffset'])
    ];

    $_SESSION['csv']['section'] = 'biblio';
    $_SESSION['csv']['action'] = $_SERVER['PHP_SELF'];
    if (isset($_POST['header'])) $_SESSION['csv']['header'] = 1;

    // create upload object
    $csv_upload = $files_disk->upload('importFile', function($files) use($sysconf) {
      // Extension check
      $files->isExtensionAllowed(['.csv']);

      // File size check
      $files->isLimitExceeded($sysconf['max_upload']*1024);

      // destroy it if failed
      if (!empty($files->getError())) $files->destroyIfFailed();

    })->as('temp' . DS . $_SESSION['csv']['name']);
    
    if (!$csv_upload->getUploadStatus())
    {
      toastr(__('Upload failed! File type not allowed or the size is more than').($sysconf['max_upload']/1024).' MB')->error(__('Import Tool'));
      exit;
    }

    // Redirect content
    redirect()->simbioAJAX(MWB . 'bibliography/import_preview.php');
  } else {
    $start_time = time();
    // set PHP time limit
    set_time_limit(0);
    // set ob implicit flush
    ob_implicit_flush();
    $record_offset = intval($_SESSION['csv']['format']['recordOffset']);
    $record_offset = $record_offset - 1;

    // read file line by line
    $inserted_row = 0;
    $file = $files_disk->readStream('temp' . DS . $_SESSION['csv']['name'] . '.csv');
    $fileNumber = $files_disk->readStream('temp' . DS . $_SESSION['csv']['name'] . '.csv');
    $n = 0;

    $reader = new Reader([
      'separator' => trim($_SESSION['csv']['format']['fieldSep']),
      'enclosed_with' => trim($_SESSION['csv']['format']['fieldEnc']),
      'record_separator' => [
          'newline' => "\n",
          'return' => "\t"
      ]
    ]);

    // get total line
    $lineNumber = $reader->readFromStream($fileNumber)->getTotalLine();
    

    try {
      $reader->readFromStream($file);

      $reader->each(formatter: function(&$field, $row, $index) use($dbs) {
        $currentValue = $field[$index];

        switch ($index) {
          // title formatter
          case 0:
            $currentValue = empty($currentValue) ? NULL : $currentValue;
            break;

          case 1:
            $currentValue = empty($currentValue) ? NULL : utility::getID($dbs, 'mst_gmd', 'gmd_id', 'gmd_name', $currentValue);
            break;

          case 4:
            $currentValue = empty($currentValue) ? NULL : utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $currentValue);
            break;

          case 9:
            $currentValue = empty($currentValue) ? NULL : utility::getID($dbs, 'mst_language', 'language_id', 'language_name', $currentValue);
            break;

          case 10:
            $currentValue = empty($currentValue) ? NULL : utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $currentValue);
            break;
          
          default:
            $currentValue = empty($currentValue) ? NULL : $currentValue;
            break;
        }
        // end formatter

        // strip escape chars from all fields
        $field[$index] = str_replace('\\', '', trim($currentValue??''));

      }, processor: function($reader, $row) use($dbs, $sysconf, $indexer, $lineNumber) {

        $fields = $reader->getFields();
        $fields = array_pop($fields);

        $authors = $fields[15];
        $subjects = $fields[16];
        $items = $fields[17];

        // remove data with tag format
        unset($fields[15]);
        unset($fields[16]);
        unset($fields[17]);
        
        $state = DB::getInstance()->prepare(<<<SQL
        INSERT IGNORE INTO biblio (title, gmd_id, edition,
                    isbn_issn, publisher_id, publish_year,
                    collation, series_title, call_number,
                    language_id, publish_place_id, classification,
                    notes, image, sor, input_date, last_update)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,now(),now());
        SQL);

        $state->execute($fields);

        if ($state) {
          $biblio_id = DB::getInstance()->lastInsertId();

          if (!empty($authors)) {
            $biblio_author_sql = DB::getInstance()->prepare('INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES (?,?,?)');
            $authors = explode('><', $authors);
            foreach ($authors as $author) {
              $author = trim(str_replace(array('>', '<'), '', $author));
              $author_id = utility::getID($dbs, 'mst_author', 'author_id', 'author_name', $author);
              $biblio_author_sql->execute([$biblio_id, $author_id, 2]);
            }
          }

          if (!empty($subjects)) {
            $biblio_subject_sql = DB::getInstance()->prepare('INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES (?,?,?)');
            $subjects = explode('><', $subjects);
            foreach ($subjects as $subject) {
              $subject = trim(str_replace(array('>', '<'), '', $subject));
              $subject_id = utility::getID($dbs, 'mst_topic', 'topic_id', 'topic', $subject);
              $biblio_subject_sql->execute([$biblio_id, $subject_id , 2]);
            }
          }

          if (!empty($items)) {
            $biblio_subject_sql = DB::getInstance()->prepare('INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES (?,?,?)');
            $subjects = explode('><', $subjects);
            foreach ($subjects as $subject) {
              $subject = trim(str_replace(array('>', '<'), '', $subject));
              $subject_id = utility::getID($dbs, 'mst_topic', 'topic_id', 'topic', $subject);
              $biblio_subject_sql->execute([$biblio_id, $subject_id , 2]);
            }
          }

          // items
          if (!empty($items)) {
            $item_sql = DB::getInstance()->prepare('INSERT IGNORE INTO item (biblio_id, item_code) VALUES (?,?)');
            $item_array = explode('><', $items);
            foreach ($item_array as $item) {
              $item = trim(str_replace(array('>', '<'), '', $item));
              $item_sql->execute([$biblio_id, $item]);
            }
          }

          // create biblio index
          if ($sysconf['index']['type'] == 'index') {
            $indexer->makeIndex($biblio_id ?? 0);
          }

          $row++;
          importProgress(round($row/$lineNumber * 100));
          sleep(1);
        }
      });
    } catch (Exception $e) {
      dd($e);
      $errorMessage = $e->getMessage();
      exit(<<<HTML
      <script>
      parent.\$('.infoBox').html('{$errorMessage}')
      parent.\$('.infoBox').addClass('errorBox');
      parent.\$('.infoBox').removeClass('infoBox');
      </script>
      HTML);
    }

    // close file handle
    fclose($file);

    // set information variable before reset csv session
    $redirectTo = $_SESSION['csv']['action'];
    $fileName = $_SESSION['csv']['name'];

    // delete temp file
    $files_disk->delete('temp' . DS . $fileName . '.csv');

    // Reset session
    unset($_SESSION['csv']);

    $end_time = time();
    $import_time_sec = $end_time-$start_time;
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'Importing '.$inserted_row.' bibliographic records from file : '.$fileName, 'Import' );
    $label = str_replace(['{row_count}','{time_to_finish}'], [$row, $import_time_sec], __('Success imported <strong>{row_count}</strong> title in <strong>{time_to_finish}</strong> second'));
    exit(<<<HTML
    <script>
    parent.\$('.infoBox').html('{$label}')
    setTimeout(() => parent.\$('#mainContent').simbioAJAX('{$redirectTo}'), 2500)
    </script>
    HTML);
  }
}
?>
<div class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    <h2><?php echo __('Import Tool'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Import for bibliographics data from CSV file. For guide on CSV fields order and format please refer to documentation or visit <a href="http://slims.web.id" target="_blank">Official Website</a>'); ?>
      &nbsp;<a href="<?= $_SERVER['PHP_SELF'] ?>?action=download_sample" class="s-btn btn btn-secondary notAJAX"><?= __('Download Sample') ?></a>
	  </div>
</div>
</div>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Process').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input  = '<div class="container-fluid">';
$str_input .= '<div class="row">';
$str_input .= '<div class="custom-file col-6">';
$str_input .= simbio_form_element::textField('file', 'importFile','','class="custom-file-input"');
$str_input .= '<label class="custom-file-label" for="customFile">Choose file</label>';
$str_input .= '</div>';
$str_input .= '<div class="col">';
$str_input .= '<div class="mt-2">Maximum '.$sysconf['max_upload'].' KB</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$form->addAnything(__('File To Import'), $str_input);
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(config('csv.separator')).'', 'style="width: 10%;" maxlength="3" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities(config('csv.enclosed_with')).'', 'style="width: 10%;" class="form-control"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of Records To Import (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;" class="form-control"');
// header (column name)
$form->addCheckBox('header', __('The first row is the columns names'), array( array('1', __('Yes')) ), '');
// output the form
echo $form->printOut();
?>
<script>
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});
</script>
