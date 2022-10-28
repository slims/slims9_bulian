<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
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

/* Item Import section */

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
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// check if PEAR is installed
ob_start();
include 'System.php';
include 'File/MARC.php';
ob_end_clean();
if (!(class_exists('System') && class_exists('File_MARC'))) {
  die('<div class="errorBox">'.__('<a href="http://pear.php.net/index.php">PEAR</a>, <a href="http://pear.php.net/package/File_MARC">File_MARC</a>
    and <a href="http://pear.php.net/package/Structures_LinkedList/">Structures_LinkedList</a>
    packages need to be installed in order
    to import MARC record').'</div>');
}

// max chars in line for file operations
$max_chars = 1024*100;

if ($sysconf['index']['type'] == 'index') {
  require MDLBS.'system/biblio_indexer.inc.php';
  // create biblio_indexer class instance
  $indexer = new biblio_indexer($dbs);
}

if (isset($_POST['doImport'])) {
    // check for form validity
    if (!$_FILES['importFile']['name']) {
        utility::jsToastr('MARC Import', __('Please select the file to import!'), 'error');
        exit();
    } else {
      require MDLBS.'bibliography/biblio_utils.inc.php';
      require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

      $start_time = time();
      // set PHP time limit
      set_time_limit(0);
      // set ob implicit flush
      ob_implicit_flush();
      // create upload object
      $upload = new simbio_file_upload();
      // get system temporary directory location
      $temp_dir = UPLOAD;
      if (!is_writable($temp_dir)) {
        $temp_dir = sys_get_temp_dir();
      }
      $uploaded_file = $temp_dir.DS.$_FILES['importFile']['name'];
      // set max size
      $max_size = $sysconf['max_upload']*1024;
      $upload->setAllowableFormat(array('.mrc', '.xml', '.txt'));
      $upload->setMaxSize($max_size);
      $upload->setUploadDir($temp_dir);
      $upload_status = $upload->doUpload('importFile');
      if ($upload_status !== UPLOAD_SUCCESS) {
          utility::jsToastr('MARC Import', __('Upload failed! File type not allowed or the size is more than').($sysconf['max_upload']/1024).' MB', 'error');
          exit();
      }
      $updated_row = 0;
      $marc_string = file_get_contents($uploaded_file);
      $marc_string = mb_convert_encoding($marc_string, "UTF-8", "auto");
      // var_dump($marc_string); die();

      $marc_data = new File_MARC($marc_string, File_MARC::SOURCE_STRING);
      // create dbop object
      $sql_op = new simbio_dbop($dbs);

      $gmd_cache = array();
      $publ_cache = array();
      $place_cache = array();
      $lang_cache = array();
      $sor_cache = array();
      $author_cache = array();
      $subject_cache = array();

      while ($record = $marc_data->next()) {
        $data = array();
        $input_date = date('Y-m-d H:i:s');
        $data['input_date'] = $input_date;
        $data['last_update'] = $input_date;

        // Identifier - ISBN
        $id_fld = $record->getField('020');
        if ($id_fld) {
          $isbn_issn = $id_fld->getSubfields('a');
          if (isset($isbn_issn[0])) {
            // echo "\n"; echo 'ISBN/ISSN: '.$isbn_issn[0]->getData();
            $data['isbn_issn'] = $isbn_issn[0]->getData();
          }
        }

        // Identifier - ISSN
        $id_fld = $record->getField('022');
        if ($id_fld) {
          echo "\n";
          $isbn_issn = $id_fld->getSubfields('a');
          if (isset($isbn_issn[0])) {
            // echo 'ISBN/ISSN: '.$isbn_issn[0]->getData();
            $data['isbn_issn'] = $isbn_issn[0]->getData();
          }
        }

        // Classification DDC
        $cls_fld = $record->getField('082');
        if ($cls_fld) {
          $classification = $cls_fld->getSubfields('a');
          if (isset($classification[0])) {
            // echo 'Classification: '.$classification[0]->getData();
            $data['classification'] = $classification[0]->getData();
          }
        }

        $title_fld = $record->getField('245');
        // Main title
        $title_main = $title_fld->getSubfields('a');
        // echo $title_main[0]->getData();
        $data['title'] = $dbs->escape_string(trim($title_main[0]->getData()));
        // Sub title
        $subtitle = $title_fld->getSubfields('b');
        if (isset($subtitle[0])) {
          // echo 'Subtitle: '.$subtitle[0]->getData();
          $data['title'] .= $dbs->escape_string(trim($subtitle[0]->getData()));
        }

        // GMD
        $gmd = $title_fld->getSubFields('h');
        if (isset($gmd[0])) {
          // echo "\n"; echo 'GMD: '.$gmd[0]->getData();
          $data['gmd_id'] = utility::getID($dbs, 'mst_gmd', 'gmd_id', 'gmd_name', $gmd[0]->getData(), $gmd_cache);
        }

        // Statement of Responsibility
        $sor = $title_fld->getSubfields('c');
        if (isset($sor[0])) {
          // $data['title'] .= $sor[0]->getData();
          $data['sor'] = $dbs->escape_string(trim($sor[0]->getData()));
          // echo "\n"; echo 'Statement of responsibility: '.$sor[0]->getData();
          // $data['sor_id'] = utility::getID($dbs, 'mst_sor', 'sor_id', 'sor', $sor[0]->getData(), $sor_cache);
        }

        // Edition
        $ed_fld = $record->getField('250');
        if ($ed_fld) {
          $ed = $ed_fld->getSubfields('a');
          $ed2 = $ed_fld->getSubfields('b');
          if (isset($ed[0])) {
            // echo "\n"; echo 'Edition: '.$ed[0]->getData();
            $data['edition'] = $dbs->escape_string(trim($ed[0]->getData()));
          }
          if (isset($ed2[0])) {
            // echo "\n"; echo 'Edition: '.$ed[0]->getData();
            $data['edition'] .= $dbs->escape_string(trim($ed2[0]->getData()));
          }
        }

        // Publication
        $pbl_fld = $record->getField('260');
        if ($pbl_fld) {
          $place = $pbl_fld->getSubfields('a');
          $publisher = $pbl_fld->getSubfields('b');
          $publish_year = $pbl_fld->getSubfields('c');
          if (isset($place[0])) {
            // echo "\n"; echo 'Publish place: '.$place[0]->getData();
            $data['publish_place_id'] = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $place[0]->getData(), $place_cache);
          }
          if (isset($publisher[0])) {
            // echo 'Publisher: '.$publisher[0]->getData();
            $data['publisher_id'] = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $publisher[0]->getData(), $publ_cache);
          }
          if (isset($publish_year[0])) {
            // echo 'Publish year: '.$publish_year[0]->getData();
            $data['publish_year'] = $publish_year[0]->getData();
          }
        }

        // Collation
        $clt_fld = $record->getField('300');
        if ($clt_fld) {
          $data['collation'] = '';
          $pages = $clt_fld->getSubfields('a');
          $ilus = $clt_fld->getSubfields('b');
          $dimension = $clt_fld->getSubfields('c');
          if (isset($pages[0])) {
            // echo 'Pages: '.$pages[0]->getData();
            $data['collation'] .= $pages[0]->getData();
          }
          if (isset($ilus[0])) {
            // echo 'Ilus.: '.$ilus[0]->getData();
            $data['collation'] .= $ilus[0]->getData();
          }
          if (isset($dimension[0])) {
            // echo 'Dimension: '.$dimension[0]->getData();
            $data['collation'] .= $dimension[0]->getData();
          }
        }

        // RDA Content type
        $rct_fld = $record->getField('336');
        if ($rct_fld) {
          $content_type = $rct_fld->getSubfields('a');
          // get ID
          $q = $dbs->query(sprintf('SELECT id FROM mst_content_type WHERE content_type=\'%s\'', $content_type));
          $d = $q->fetch_row();
          $data['content_type_id'] = $d[0];
        }

        // RDA Media type
        $rmt_fld = $record->getField('337');
        if ($rmt_fld) {
          $media_type = $rmt_fld->getSubfields('a');
          // get ID
          $q = $dbs->query(sprintf('SELECT id FROM mst_media_type WHERE media_type=\'%s\'', $media_type));
          $d = $q->fetch_row();
          $data['media_type_id'] = $d[0];
        }

        // RDA Carrier type
        $rcrt_fld = $record->getField('338');
        if ($rcrt_fld) {
          $carrier_type = $rcrt_fld->getSubfields('a');
          // get ID
          $q = $dbs->query(sprintf('SELECT id FROM mst_carrier_type WHERE carrier_type=\'%s\'', $carrier_type));
          $d = $q->fetch_row();
          $data['carrier_type_id'] = $d[0];
        }

        // Series title
        $series_fld = $record->getField('440');
        if ($series_fld) {
          $series = $series_fld->getSubfields('a');
          if (isset($series[0])) {
            // echo "\n"; echo 'Series: '.$series[0]->getData();
            $data['series_title'] = $dbs->escape_string(trim($series[0]->getData()));
          }
        }

        // Notes
        $notes_flds = $record->getFields('^5', true);
        if ($notes_flds) {
            $data['notes'] = '';
            // echo "\n"; echo 'Notes: ';
            foreach ($notes_flds as $note_fld) {
                if ($note_fld) {
                  $notes = $note_fld->getSubfields('a');
                  if (isset($notes[0])) {
                    $data['notes'] .= $dbs->escape_string(trim($notes[0]->getData()));
                  }
                }
            }
        }

        // insert biblio data
        $sql_op->insert('biblio', $data);
        // echo '<p>'.$sql_op->error.'</p><p>&nbsp;</p>';
        $biblio_id = $sql_op->insert_id;
        if ($biblio_id < 1) {
          continue;
        }
        $updated_row++;

        // Main entry
        $author_flds = $record->getFields('100|110|111', true);
        if ($author_flds) {
            foreach ($author_flds as $tag => $auth_fld) {
                if ($tag == '110') {
                  $author_type = 'o';
                } else if ($tag == '111') {
                  $author_type = 'c';
                } else {
                  $author_type = 'p';
                }

                if ($auth_fld) {
                  $author = $auth_fld->getSubfields('a');
                  if (isset($author[0])) {
                    $author_id = getAuthorID($dbs->escape_string(trim($author[0]->getData())), $author_type, $author_cache);
                    @$dbs->query("INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ($biblio_id, $author_id, 1)");
                  }
                }
            }
        }

        // Author additional
        $author_flds = null;
        $author_flds = $record->getFields('700|710|711', true);
        if ($author_flds) {
            // echo 'Author: ';
            foreach ($author_flds as $tag => $auth_fld) {
                // if ($tag == '710') {
                if (stripos($tag, '10') === true) {
                  $author_type = 'o';
                } else if (stripos($tag, '11') === true) {
                  $author_type = 'c';
                } else {
                  $author_type = 'p';
                }

                if ($auth_fld) {
                  $author = $auth_fld->getSubfields('a');
                  if (isset($author[0])) {
                    $author_id = getAuthorID($dbs->escape_string(trim($author[0]->getData())), $author_type, $author_cache);
                    @$dbs->query("INSERT IGNORE INTO biblio_author (biblio_id, author_id, level) VALUES ($biblio_id, $author_id, 2)");
                  }
                }
            }
        }

        // Subject
        $subject_flds = $record->getFields('650|651|648|655|656|657', true);
        if ($subject_flds) {
            // echo 'Subject: ';
            foreach ($subject_flds as $subj_fld) {
                if ($subj_fld) {
                  $subject = $subj_fld->getSubfields('a');
                  if (isset($subject[0])) {
                    // echo $subject[0]->getData();
                    $subject_type = 't';
                    $subject_id = getSubjectID($dbs->escape_string(trim($subject[0]->getData())), $subject_type, $subject_cache);
                    @$dbs->query("INSERT IGNORE INTO biblio_topic (biblio_id, topic_id, level) VALUES ($biblio_id, $subject_id, 1)");
                  }
                }
            }
        }

        // create biblio index
        if ($sysconf['index']['type'] == 'index') {
          $indexer->makeIndex($biblio_id);
        }

      }

      $end_time = time();
      $import_time_sec = $end_time-$start_time;
      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'Importing '.$updated_row.' MARC records from file : '.$_FILES['importFile']['name'], 'MARC Import');
      echo '<script type="text/javascript">'."\n";
      echo 'top.jQuery(\'#importInfo\').html(\'<strong>'.$updated_row.'</strong> records imported successfully in '.$import_time_sec.' second(s)</strong>\');'."\n";
      echo 'top.jQuery(\'#importInfo\').css( {display: \'block\'} );'."\n";
      echo '</script>';
      exit();
    }
}
?>
<div class="menuBox">
<div class="menuBoxInner importIcon">
	<div class="per_title">
    	<h2><?php echo __('MARC Import tool'); ?></h2>
	</div>
	<div class="infoBox">
    <?php echo __('Import bibliographic records from MARC file. The file can be native MARC record format file (.mrc) or MARCXML XML file (.xml). You need to have PHP PEAR and PEAR\'s File_MARC package installed in your system. To convert native/legacy MARC file to MARCXML you can use <a class="notAJAX" href="http://www.loc.gov/standards/marcxml/marcxml.zip">MARCXML Toolkit</a>'); ?>
	</div>
</div>
</div>
<div id="importInfo" class="infoBox" style="display: none;">&nbsp;</div><div id="importError" class="errorBox" style="display: none;">&nbsp;</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doImport" value="'.__('Import Now').'" class="s-btn btn btn-default"';
// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// csv files
$str_input  = '<div class="container-fluid">';
$str_input .= '<div class="row">';
$str_input .= '<div class="custom-file col-6">';
$str_input .= simbio_form_element::textField('file', 'importFile','', 'class="custom-file-input"');
$str_input .= '<label class="custom-file-label" for="importFile">Choose file</label>';
$str_input .= '</div>';
$str_input .= '<div class="col">';
$str_input .= '<div class="mt-2">Maximum '.$sysconf['max_upload'].' KB</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$str_input .= '</div>';
$form->addAnything(__('File To Import'), $str_input);
// text import
// $form->addTextField('textarea', 'MARCtext', __('MARC record text'), '', 'style="width: 100%; height: 500px;"');
// number of records to import
$form->addTextField('text', 'recordNum', __('Number of records to import (0 for all records)'), '0', 'class="form-control" style="width: 10%;"');
// output the form

echo $form->printOut();
?>
<script>
$(document).on('change', '.custom-file-input', function () {
    let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
    $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
});
</script>