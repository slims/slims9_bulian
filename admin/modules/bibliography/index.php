<?php
/**
 * Copyright (C) 2007,2008,2009,2010  Arie Nugraha (dicarve@yahoo.com)
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

/* Bibliography Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
  // main system configuration
  require '../../../sysconfig.inc.php';
  // start the session
  require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';
require MDLBS.'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

$in_pop_up = false;
// check if we are inside pop-up window
if (isset($_GET['inPopUp'])) {
  $in_pop_up = true;
}

if (!function_exists('getimagesizefromstring')) {
  function getimagesizefromstring($string_data)
  {
     $uri = 'data://application/octet-stream;base64,'  . base64_encode($string_data);
     return getimagesize($uri);
  }
}

// RDA Content, Media and Carrier
$rda_cmc = array('content' => 'Content Type', 'media' => 'Media Type', 'carrier' => 'Carrier Type');

/* REMOVE IMAGE */
if (isset($_POST['removeImage']) && isset($_POST['bimg']) && isset($_POST['img'])) {
  $_delete = $dbs->query(sprintf('UPDATE biblio SET image=NULL WHERE biblio_id=%d', $_POST['bimg']));
  $_delete2 = $dbs->query(sprintf('UPDATE search_biblio SET image=NULL WHERE biblio_id=%d', $_POST['bimg']));
  if ($_delete) {
    @unlink(sprintf(IMGBS.'docs/%s',$_POST['img']));
    exit('<script type="text/javascript">alert(\''.$_POST['img'].' successfully removed!\'); $(\'#biblioImage, #imageFilename\').remove();</script>');
  }
  exit();
}
/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
  $title = trim(strip_tags($_POST['title']));
  // check form validity
  if (empty($title)) {
    utility::jsAlert(__('Title can not be empty'));
    exit();
  } else {
    // include custom fields file
    if (file_exists(MDLBS.'bibliography/custom_fields.inc.php')) {
      include MDLBS.'bibliography/custom_fields.inc.php';
    }

    // create biblio_indexer class instance
    $indexer = new biblio_indexer($dbs);

    /**
     * Custom fields
     */
    if (isset($biblio_custom_fields)) {
      if (is_array($biblio_custom_fields) && $biblio_custom_fields) {
        foreach ($biblio_custom_fields as $fid => $cfield) {
          // custom field data
          $cf_dbfield = $cfield['dbfield'];
          if (isset($_POST[$cf_dbfield])) {
            $cf_val = $dbs->escape_string(strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']));
            if ($cf_val) {
              $custom_data[$cf_dbfield] = $cf_val;
            } else {
              $custom_data[$cf_dbfield] = 'literal{\'\'}';
            }
          }
        }
      }
    }

    $data['title'] = $dbs->escape_string($title);
    /* modified by hendro */
    $data['sor'] = trim($dbs->escape_string(strip_tags($_POST['sor'])));
    /* end of modification */
    $data['edition'] = trim($dbs->escape_string(strip_tags($_POST['edition'])));
    $data['gmd_id'] = $_POST['gmdID'];
    $data['isbn_issn'] = trim($dbs->escape_string(strip_tags($_POST['isbn_issn'])));

    $class = str_ireplace('NEW:', '', trim(strip_tags($_POST['class'])));
    $data['classification'] = trim($dbs->escape_string(strip_tags($class)));

    // check publisher
    // echo stripos($_POST['publisherID'], 'NEW:');
    if (stripos($_POST['publisherID'], 'NEW:') === 0) {
      $new_publisher = str_ireplace('NEW:', '', trim(strip_tags($_POST['publisherID'])));
      $new_id = utility::getID($dbs, 'mst_publisher', 'publisher_id', 'publisher_name', $new_publisher);
      $data['publisher_id'] = $new_id;
    } else {
      $data['publisher_id'] = intval($_POST['publisherID']);
    }

    $data['publish_year'] = trim($dbs->escape_string(strip_tags($_POST['year'])));
    $data['collation'] = trim($dbs->escape_string(strip_tags($_POST['collation'])));
    $data['series_title'] = trim($dbs->escape_string(strip_tags($_POST['seriesTitle'])));
    $data['call_number'] = trim($dbs->escape_string(strip_tags($_POST['callNumber'])));
    $data['language_id'] = trim($dbs->escape_string(strip_tags($_POST['languageID'])));
    // check place
    if (stripos($_POST['placeID'], 'NEW:') === 0) {
      $new_place = str_ireplace('NEW:', '', trim(strip_tags($_POST['placeID'])));
      $new_id = utility::getID($dbs, 'mst_place', 'place_id', 'place_name', $new_place);
      $data['publish_place_id'] = $new_id;
    } else {
      $data['publish_place_id'] = intval($_POST['placeID']);
    }

    $data['notes'] = trim($dbs->escape_string(strip_tags($_POST['notes'], '<br><p><div><span><i><em><strong><b><code>s')));
    $data['opac_hide'] = ($_POST['opacHide'] == '0')?'literal{0}':'1';
    $data['promoted'] = ($_POST['promote'] == '0')?'literal{0}':'1';
    // labels
    $arr_label = array();
    if ($_POST['labels']) {
      foreach ($_POST['labels'] as $label) {
      if (trim($label) != '') {
        $arr_label[] = array($label, isset($_POST['label_urls'][$label])?$_POST['label_urls'][$label]:null );
      }
      }
    }

    $data['labels'] = $arr_label?serialize($arr_label):'literal{NULL}';
    $data['frequency_id'] = ($_POST['frequencyID'] == '0')?'literal{0}':(integer)$_POST['frequencyID'];
    $data['spec_detail_info'] = trim($dbs->escape_string(strip_tags($_POST['specDetailInfo'])));
    
    // RDA Content, Media anda Carrier Type
    foreach ($rda_cmc as $cmc => $cmc_name) {
      if (isset($_POST[$cmc.'TypeID']) && $_POST[$cmc.'TypeID'] <> 0) {
        $data[$cmc.'_type_id'] = filter_input(INPUT_POST, $cmc.'TypeID', FILTER_SANITIZE_NUMBER_INT); 
      }
    }
    
    $data['input_date'] = date('Y-m-d H:i:s');
    $data['last_update'] = date('Y-m-d H:i:s');

    // image uploading
    if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
      // create upload object
      $image_upload = new simbio_file_upload();
      $image_upload->setAllowableFormat($sysconf['allowed_images']);
      $image_upload->setMaxSize($sysconf['max_image_upload']*1024);
      $image_upload->setUploadDir(IMGBS.'docs');
      // upload the file and change all space characters to underscore
      $img_upload_status = $image_upload->doUpload('image', preg_replace('@\s+@i', '_', $_FILES['image']['name']));
      if ($img_upload_status == UPLOAD_SUCCESS) {
        $data['image'] = $dbs->escape_string($image_upload->new_filename);
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' upload image file '.$image_upload->new_filename);
        utility::jsAlert(__('Image Uploaded Successfully'));
      } else {
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', 'ERROR : '.$_SESSION['realname'].' FAILED TO upload image file '.$image_upload->new_filename.', with error ('.$image_upload->error.')');
        utility::jsAlert(__('Image Uploaded Failed'));
      }
    } else if (!empty($_POST['base64picstring'])) {
      list($filedata, $filedom) = explode('#image/type#', $_POST['base64picstring']);
      $filedata = base64_decode($filedata);
      $fileinfo = getimagesizefromstring($filedata);
      $valid = strlen($filedata)/1024 < $sysconf['max_image_upload'];
      $valid = (!$fileinfo || $valid === false) ? false : in_array($fileinfo['mime'], $sysconf['allowed_images_mimetype']);
      $new_filename = strtolower('cover_'
        .preg_replace("/[^a-zA-Z0-9]+/", "_", $data['title'])
        .'.'.$filedom);

      if ($valid AND file_put_contents(IMGBS.'docs/'.$new_filename, $filedata)) {
        $data['image'] = $dbs->escape_string($new_filename);
        if (!defined('UPLOAD_SUCCESS')) define('UPLOAD_SUCCESS', 1);
        $upload_status = UPLOAD_SUCCESS;
      }
    }

    // create sql op object
    $sql_op = new simbio_dbop($dbs);
    if (isset($_POST['updateRecordID'])) {
      /* UPDATE RECORD MODE */
      // remove input date
      unset($data['input_date']);
      // filter update record ID
      $updateRecordID = (integer)$_POST['updateRecordID'];
      // update data
      $update = $sql_op->update('biblio', $data, 'biblio_id='.$updateRecordID);
      // send an alert
      if ($update) {
        // update custom data
        if (isset($custom_data)) {
          // check if custom data for this record exists
          $_sql_check_custom_q = sprintf('SELECT biblio_id FROM biblio_custom WHERE biblio_id=%d', $updateRecordID);
          $check_custom_q = $dbs->query($_sql_check_custom_q);
          if ($check_custom_q->num_rows) {
            $update2 = @$sql_op->update('biblio_custom', $custom_data, 'biblio_id='.$updateRecordID);
          } else {
            $custom_data['biblio_id'] = $updateRecordID;
            @$sql_op->insert('biblio_custom', $custom_data);
          }
        }
      	if ($sysconf['bibliography_update_notification']) {
          utility::jsAlert(__('Bibliography Data Successfully Updated'));
			  }
        // auto insert catalog to UCS if enabled
        if ($sysconf['ucs']['enable']) {
          echo '<script type="text/javascript">parent.ucsUpload(\''.MWB.'bibliography/ucs_upload.php\', \'itemID[]='.$updateRecordID.'\', false);</script>';
        }
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' update bibliographic data ('.$data['title'].') with biblio_id ('.$_POST['itemID'].')');
        // close window OR redirect main page
        if ($in_pop_up) {
          $itemCollID = (integer)$_POST['itemCollID'];
          echo '<script type="text/javascript">top.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url, {method: \'post\', addData: \''.( $itemCollID?'itemID='.$itemCollID.'&detail=true':'' ).'\'});</script>';
          echo '<script type="text/javascript">top.closeHTMLpop();</script>';
        } else {
          echo '<script type="text/javascript">top.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
        }
        // update index
        // delete from index first
        $sql_op->delete('search_biblio', "biblio_id=$updateRecordID");
        $indexer->makeIndex($updateRecordID);
      } else { utility::jsAlert(__('Bibliography Data FAILED to Updated. Please Contact System Administrator')."\n".$sql_op->error); }
    } else {
      /* INSERT RECORD MODE */
      // insert the data
      $insert = $sql_op->insert('biblio', $data);
      if ($insert) {
        // get auto id of this record
        $last_biblio_id = $sql_op->insert_id;
        // add authors
        if ($_SESSION['biblioAuthor']) {
          foreach ($_SESSION['biblioAuthor'] as $author) {
            $sql_op->insert('biblio_author', array('biblio_id' => $last_biblio_id, 'author_id' => $author[0], 'level' => $author[1]));
          }
        }
        // add topics
        if ($_SESSION['biblioTopic']) {
          foreach ($_SESSION['biblioTopic'] as $topic) {
            $sql_op->insert('biblio_topic', array('biblio_id' => $last_biblio_id, 'topic_id' => $topic[0], 'level' => $topic[1]));
          }
        }
        // add attachment
        if ($_SESSION['biblioAttach']) {
          foreach ($_SESSION['biblioAttach'] as $attachment) {
            $sql_op->insert('biblio_attachment', array('biblio_id' => $last_biblio_id, 'file_id' => $attachment['file_id'], 'access_type' => $attachment['access_type']));
          }
        }
        // biblio to biblio
        if ($_SESSION['biblioToBiblio']) {
          foreach ($_SESSION['biblioToBiblio'] as $rel_biblio_id) {
            $sql_op->insert('biblio_relation', array('biblio_id' => $last_biblio_id, 'rel_biblio_id' => $rel_biblio_id[0]));
          }
        }
        // insert custom data
        if ($custom_data) {
          $custom_data['biblio_id'] = $last_biblio_id;
          @$sql_op->insert('biblio_custom', $custom_data);
        }


        utility::jsAlert(__('New Bibliography Data Successfully Saved'));
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' insert bibliographic data ('.$data['title'].') with biblio_id ('.$last_biblio_id.')');
        // clear related sessions
        $_SESSION['biblioAuthor'] = array();
        $_SESSION['biblioTopic'] = array();
        $_SESSION['biblioAttach'] = array();
        $_SESSION['biblioToBiblio'] = array();
        // update index
        $indexer->makeIndex($last_biblio_id);
        // auto insert catalog to UCS if enabled
        if ($sysconf['ucs']['enable'] && $sysconf['ucs']['auto_insert']) {
          echo '<script type="text/javascript">parent.ucsUpload(\''.MWB.'bibliography/ucs_upload.php\', \'itemID[]='.$last_biblio_id.'\');</script>';
        }
      } else { utility::jsAlert(__('Bibliography Data FAILED to Save. Please Contact System Administrator')."\n".$sql_op->error); }
    }

    // item batch insert
    if (trim($_POST['itemCodePattern']) != '' && $_POST['itemCodeStart'] > 0 && $_POST['itemCodeEnd'] > 0) {
      $hasil = array();
      $pattern = trim($_POST['itemCodePattern']);
      // get last zero chars
      preg_match('@0+$@i', $pattern, $hasil);
      $zeros = strlen($hasil[0]);
      $start = (integer)$_POST['itemCodeStart'];
      $end = (integer)$_POST['itemCodeEnd'];
      for ($b = $start; $b <= $end; $b++) {
      $len = strlen($b);
      if ($zeros > 0) {
        $itemcode = preg_replace('@0{'.$len.'}$@i', $b, $pattern);
      } else { $itemcode = $pattern.$b; }

      $item_insert_sql = sprintf("INSERT IGNORE INTO item (biblio_id, item_code, call_number, coll_type_id)
        VALUES (%d, '%s', '%s', %d)", $updateRecordID?$updateRecordID:$last_biblio_id, $itemcode, $data['call_number'], $_POST['collTypeID']);
      @$dbs->query($item_insert_sql);
      }
    }

    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'bibliography/index.php\', {method: \'post\', addData: \'itemID='.$last_biblio_id.'&detail=true\'});</script>';
    exit();
  }
  exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
  if (!($can_read AND $can_write)) {
    die();
  }
  /* DATA DELETION PROCESS */
  // create sql op object
  $sql_op = new simbio_dbop($dbs);
  $failed_array = array();
  $error_num = 0;
  $still_have_item = array();
  if (!is_array($_POST['itemID'])) {
    // make an array
    $_POST['itemID'] = array((integer)$_POST['itemID']);
  }
  // loop array
  $http_query = '';
  foreach ($_POST['itemID'] as $itemID) {
    $itemID = (integer)$itemID;
    // check if this biblio data still have an item
    $_sql_biblio_item_q = sprintf('SELECT b.title, COUNT(item_id) FROM biblio AS b
      LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
      WHERE b.biblio_id=%d GROUP BY title', $itemID);
    $biblio_item_q = $dbs->query($_sql_biblio_item_q);
    $biblio_item_d = $biblio_item_q->fetch_row();
    if ($biblio_item_d[1] < 1) {
      if (!$sql_op->delete('biblio', "biblio_id=$itemID")) {
        $error_num++;
      } else {
        // write log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' DELETE bibliographic data ('.$biblio_item_d[0].') with biblio_id ('.$itemID.')');
        // delete related data
        $sql_op->delete('biblio_topic', "biblio_id=$itemID");
        $sql_op->delete('biblio_author', "biblio_id=$itemID");
        $sql_op->delete('biblio_attachment', "biblio_id=$itemID");
        $sql_op->delete('biblio_relation', "biblio_id=$itemID");
        $sql_op->delete('search_biblio', "biblio_id=$itemID");
        // add to http query for UCS delete
        $http_query .= "itemID[]=$itemID&";
      }
    } else {
      $still_have_item[] = substr($biblio_item_d[0], 0, 45).'... still have '.$biblio_item_d[1].' copies';
      $error_num++;
    }
  }

  if ($still_have_item) {
    $titles = '';
    foreach ($still_have_item as $title) {
      $titles .= $title."\n";
    }
    utility::jsAlert(__('Below data can not be deleted:')."\n".$titles);
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
    exit();
  }
  // auto delete data on UCS if enabled
  if ($http_query && $sysconf['ucs']['enable'] && $sysconf['ucs']['auto_delete']) {
    echo '<script type="text/javascript">parent.ucsUpdate(\''.MWB.'bibliography/ucs_update.php\', \'nodeOperation=delete&'.$http_query.'\');</script>';
  }
  // error alerting
  if ($error_num == 0) {
    utility::jsAlert(__('All Data Successfully Deleted'));
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
  } else {
    utility::jsAlert(__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'));
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
  }
  exit();
}
/* RECORD OPERATION END */

if (!$in_pop_up) {
/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner biblioIcon">
  <div class="per_title">
	  <h2><?php echo __('Bibliographic'); ?></h2>
  </div>
  <div class="sub_section">
	  <div class="btn-group">
		  <a href="<?php echo MWB; ?>bibliography/index.php" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('Bibliographic List'); ?></a>
		  <a href="<?php echo MWB; ?>bibliography/index.php?action=detail" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New Bibliography'); ?></a>
	  </div>
	  <form name="search" action="<?php echo MWB; ?>bibliography/index.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
		  <input type="text" name="keywords" id="keywords" size="30" />
		  <select name="field"><option value="0"><?php echo __('All Fields'); ?></option><option value="title"><?php echo __('Title/Series Title'); ?> </option><option value="subject"><?php echo __('Topics'); ?></option><option value="author"><?php echo __('Authors'); ?></option><option value="isbn"><?php echo __('ISBN/ISSN'); ?></option><option value="publisher"><?php echo __('Publisher'); ?></option></select>
		  <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
	  </form>
		  <?php
		  // enable UCS?
			if ($sysconf['ucs']['enable']) {
		  ?>
		  <a href="#" onclick="ucsUpload('<?php echo MWB; ?>bibliography/ucs_upload.php', serializeChbox('dataList'))" class="notAJAX"><div class="btn btn-default"><?php echo __('Upload Selected Bibliographic data to Union Catalog Server*'); ?></div></a>
		  <?php
		  }
		  ?>
  </div>
</div>
</fieldset>
<?php
/* search form end */
}
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
  if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
  }
  /* RECORD FORM */
  // try query
  $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
  $_sql_rec_q = sprintf('SELECT b.*, p.publisher_name, pl.place_name FROM biblio AS b
    LEFT JOIN mst_publisher AS p ON b.publisher_id=p.publisher_id
    LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
    WHERE biblio_id=%d', $itemID);
  $rec_q = $dbs->query($_sql_rec_q);
  $rec_d = $rec_q->fetch_assoc();

  // create new instance
  $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
  $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="btn btn-default"';
  // form table attributes
  $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
  $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
  $form->table_content_attr = 'class="alterCell2"';

  $visibility = 'makeVisible';
  // edit mode flag set
  if ($rec_q->num_rows > 0) {
    $form->edit_mode = true;
    // record ID for delete process
    if (!$in_pop_up) {
      // form record id
      $form->record_id = $itemID;
    } else {
      $form->addHidden('updateRecordID', $itemID);
      $form->addHidden('itemCollID', $_POST['itemCollID']);
      $form->back_button = false;
    }
    // form record title
    $form->record_title = $rec_d['title'];
    // submit button attribute
    $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="btn btn-default"';
    // element visibility class toogle
    $visibility = 'makeHidden';

    // custom field data query
    $_sql_rec_cust_q = sprintf('SELECT * FROM biblio_custom WHERE biblio_id=%d', $itemID);
    $rec_cust_q = $dbs->query($_sql_rec_cust_q);
    $rec_cust_d = $rec_cust_q->fetch_assoc();
  } else {
    $_SESSION['biblioToBiblio'] = array();
  }

  // include custom fields file
  if (file_exists(MDLBS.'bibliography/custom_fields.inc.php')) {
    include MDLBS.'bibliography/custom_fields.inc.php';
  }

  /* Form Element(s) */
  // biblio title
  $form->addTextField('textarea', 'title', __('Title').'*', $rec_d['title'], 'rows="1" style="width: 100%; overflow: auto;"',
    __('Main title of collection. Separate child title with colon and pararel title with equal (=) sign.'));

  // biblio authors
  // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_author.php?biblioID='.$rec_d['biblio_id'].'\', 500, 200, \''.__('Authors/Roles').'\')">'.__('Add Author(s)').'</a></div>';
  $str_input = '<div class="'.$visibility.'"><a class="notAJAX button btn btn-info openPopUp" href="'.MWB.'bibliography/pop_author.php?biblioID='.$rec_d['biblio_id'].'" title="'.__('Authors/Roles').'">'.__('Add Author(s)').'</a></div>';
  $str_input .= '<iframe name="authorIframe" id="authorIframe" class="borderAll" style="width: 100%; height: 70px;" src="'.MWB.'bibliography/iframe_author.php?biblioID='.$rec_d['biblio_id'].'&block=1"></iframe>';
  $form->addAnything(__('Author(s)'), $str_input);

  // modified by hendro wicaksono
  // biblio sor statement of responsibility
  $form->addTextField('text', 'sor', __('Statement of Responsibility'), $rec_d['sor'], 'style="width: 40%;"', __('Main source of information to show who has written, composed, illustrated, or in other ways contributed to the existence of the item.'));
  // end of modification

  // biblio edition
  $form->addTextField('text', 'edition', __('Edition'), $rec_d['edition'], 'style="width: 40%;"', __('A version of publication having substantial changes or additions.'));
  // biblio specific detail info/area
  $form->addTextField('textarea', 'specDetailInfo', __('Specific Detail Info'), $rec_d['spec_detail_info'], 'rows="2" style="width: 100%"', __('explain more details about an item e.g. scale within a map, running time in a movie dvd.'));
  // biblio item batch add
  $str_input = __('Pattern').': <input type="text" class="small_input" name="itemCodePattern" value="'.$sysconf['batch_item_code_pattern'].'" /> &nbsp;';
  $str_input .= __('From').': <input type="text" class="small_input" name="itemCodeStart" value="0" /> '.__('To').' <input type="text" class="small_input" name="itemCodeEnd" value="0" /> &nbsp;';
    // get collection type data related to this record from database
    $coll_type_q = $dbs->query("SELECT coll_type_id, coll_type_name FROM mst_coll_type");
    $coll_type_options = array();
    while ($coll_type_d = $coll_type_q->fetch_row()) {
        $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
    }
  $str_input .= __('Collection Type').': '.simbio_form_element::selectList('collTypeID', $coll_type_options, '', 'style="width: 100px;"');;
  $form->addAnything(__('Item(s) code batch generator'), $str_input);
  // biblio item add
  if (!$in_pop_up AND $form->edit_mode) {
    // $str_input = '<div class="makeHidden"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_item.php?inPopUp=true&action=detail&biblioID='.$rec_d['biblio_id'].'\', 650, 400, \''.__('Items/Copies').'\')">'.__('Add New Items').'</a></div>';
    $str_input = '<div class="makeHidden"><a class="notAJAX button btn btn-info openPopUp" href="'.MWB.'bibliography/pop_item.php?inPopUp=true&action=detail&biblioID='.$rec_d['biblio_id'].'" title="'.__('Items/Copies').'" height="500">'.__('Add New Items').'</a></div>';
    $str_input .= '<iframe name="itemIframe" id="itemIframe" class="borderAll" style="width: 100%; height: 70px;" src="'.MWB.'bibliography/iframe_item_list.php?biblioID='.$rec_d['biblio_id'].'&block=1"></iframe>'."\n";
    $form->addAnything(__('Item(s) Data'), $str_input);
  }
  // biblio gmd
  // get gmd data related to this record from database
  $gmd_q = $dbs->query('SELECT gmd_id, gmd_name FROM mst_gmd');
  $gmd_options = array();
  while ($gmd_d = $gmd_q->fetch_row()) {
    $gmd_options[] = array($gmd_d[0], $gmd_d[1]);
  }
  $form->addSelectList('gmdID', __('GMD'), $gmd_options, $rec_d['gmd_id'], 'class="select2"', __('General material designation. The physical form of publication.'));
  
  // biblio RDA content, media, carrier type
  foreach ($rda_cmc as $cmc => $cmc_name) {
    $cmc_options = array();
    $cmc_q = $dbs->query('SELECT id, '.$cmc.'_type FROM mst_'.$cmc.'_type');
    $cmc_options = array();
    $cmc_options[] = array(0, __('Not set'));
    while (isset($cmc_q->num_rows) && $cmc_q->num_rows > 0 && $cmc_d = $cmc_q->fetch_row()) {
      $cmc_options[] = array($cmc_d[0], $cmc_d[1]);
    }
    if (isset($rec_d[$cmc.'_type_id'])) {
      $form->addSelectList($cmc.'TypeID', __($cmc_name), $cmc_options, $rec_d[$cmc.'_type_id'], 'class="select2"', __('RDA '.$cmc_name.' designation.'));
    } else {
      $form->addSelectList($cmc.'TypeID', __($cmc_name), $cmc_options, '', 'class="select2"', __('RDA '.$cmc_name.' designation.'));
    }
  }

  
  // biblio publish frequencies
  // get frequency data related to this record from database
  $freq_q = $dbs->query('SELECT frequency_id, frequency FROM mst_frequency');
  $freq_options[] = array('0', __('Not Applicable'));
  while ($freq_d = $freq_q->fetch_row()) {
    $freq_options[] = array($freq_d[0], $freq_d[1]);
  }
  $str_input = simbio_form_element::selectList('frequencyID', $freq_options, $rec_d['frequency_id'], 'class="select2"');
  $str_input .= '&nbsp;';
  $str_input .= ' '.__('Use this for Serial publication');
  $form->addAnything(__('Frequency'), $str_input);
  // biblio ISBN/ISSN
  $form->addTextField('text', 'isbn_issn', __('ISBN/ISSN'), $rec_d['isbn_issn'], 'style="width: 40%;"', __('Unique publishing number for each title of publication.'));
  // biblio publisher
  $publ_options[] = array('NONE', '');
  if ($rec_d['publisher_id']) {
    $publ_q = $dbs->query(sprintf('SELECT publisher_id, publisher_name FROM mst_publisher WHERE publisher_id=%d', $rec_d['publisher_id']));
    while ($publ_d = $publ_q->fetch_row()) {
      $publ_options[] = array($publ_d[0], $publ_d[1]);
    }
  }
  $form->addSelectList('publisherID', __('Publisher'), $publ_options, $rec_d['publisher_id'], 'class="select2" data-src="'.SWB.'admin/AJAX_lookup_handler.php?format=json&allowNew=true" data-src-table="mst_publisher" data-src-cols="publisher_id:publisher_name"');
  // biblio publish year
  $form->addTextField('text', 'year', __('Publishing Year'), $rec_d['publish_year'], 'style="width: 40%;"', __('Year of publication'));
  // biblio publish place
  $plc_options[] = array('NONE', '');
  if ($rec_d['publish_place_id']) {
    $plc_q = $dbs->query(sprintf('SELECT place_id, place_name FROM mst_place WHERE place_id=%d', $rec_d['publish_place_id']));
    while ($plc_d = $plc_q->fetch_row()) {
      $plc_options[] = array($plc_d[0], $plc_d[1]);
    }
  }
  $form->addSelectList('placeID', __('Publishing Place'), $plc_options, $rec_d['publish_place_id'], 'class="select2" data-src="'.SWB.'admin/AJAX_lookup_handler.php?format=json&allowNew=true" data-src-table="mst_place" data-src-cols="place_id:place_name"');
  // biblio collation
  $form->addTextField('text', 'collation', __('Collation'), $rec_d['collation'], 'style="width: 40%;"', __('Physical description of a publication e.g. publication length, width, page numbers, etc.'));
  // biblio series title
  $form->addTextField('textarea', 'seriesTitle', __('Series Title'), $rec_d['series_title'], 'rows="1" style="width: 100%;"');
  // biblio classification
  $cls_options[] = array('NONE', '');
  if ($rec_d['classification']) {
    $cls_options[] = array($rec_d['classification'], $rec_d['classification']);
  }
  $form->addSelectList('class', __('Classification'), $cls_options, $rec_d['classification'], 'class="select2" data-src="'.SWB.'admin/AJAX_lookup_handler.php?format=json&allowNew=true" data-src-table="mst_topic" data-src-cols="classification:classification:topic"');
  // biblio call_number
  $form->addTextField('text', 'callNumber', __('Call Number'), $rec_d['call_number'], 'style="width: 40%;"', __('Sets of ID that put in the book spine.'));
  // biblio topics
  // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_topic.php?biblioID='.$rec_d['biblio_id'].'\', 500, 200, \''.__('Subjects/Topics').'\')">'.__('Add Subject(s)').'</a></div>';
  $str_input = '<div class="'.$visibility.'"><a class="notAJAX button btn btn-info openPopUp" href="'.MWB.'bibliography/pop_topic.php?biblioID='.$rec_d['biblio_id'].'" title="'.__('Subjects/Topics').'">'.__('Add Subject(s)').'</a></div>';
  $str_input .= '<iframe name="topicIframe" id="topicIframe" class="borderAll" style="width: 100%; height: 70px;" src="'.MWB.'bibliography/iframe_topic.php?biblioID='.$rec_d['biblio_id'].'&block=1"></iframe>';
  $form->addAnything(__('Subject(s)'), $str_input);
  // biblio language
  // get language data related to this record from database
  $lang_q = $dbs->query("SELECT language_id, language_name FROM mst_language");
  $lang_options = array();
  while ($lang_d = $lang_q->fetch_row()) {
    $lang_options[] = array($lang_d[0], $lang_d[1]);
  }
  $form->addSelectList('languageID', __('Language'), $lang_options, $rec_d['language_id'], 'class="select2"', __('Language use by publication.'));
  // biblio note
  $form->addTextField('textarea', 'notes', __('Abstract/Notes'), $rec_d['notes'], 'style="width: 100%;" rows="2"', __('Insert here any abstract or notes from the publication.'));
  // biblio cover image
  $str_input = '';
  if ($rec_d['image']) {
    $str_input = '<div id="imageFilename"><a href="'.SWB.'images/docs/'.$rec_d['image'].'" class="openPopUp notAJAX"><strong>'.$rec_d['image'].'</strong></a> <a href="'.MWB.'bibliography/index.php" postdata="removeImage=true&bimg='.$itemID.'&img='.$rec_d['image'].'" loadcontainer="imageFilename" class="makeHidden removeImage">'.__('REMOVE IMAGE').'</a></div>';
  }
  $str_input .= simbio_form_element::textField('file', 'image');
  $str_input .= ' Maximum '.$sysconf['max_image_upload'].' KB';
  if ($sysconf['scanner'] !== false) {
    $str_input .= '<p>'.__('or scan a cover').'</p>';
    $str_input .= '<textarea id="base64picstring" name="base64picstring" style="display: none;"></textarea>';

    if ($sysconf['scanner'] == 'html5') {
        $str_input .= '<input type="button" value="'.__('Show scan dialog').'" class="button btn openPopUp" onclick="toggle_dialog();" />';
        $str_input .= '<input type="button" value="'.__('Reset').'" class="button btn openPopUp" onclick="scan_reset();" />';
        $str_input .= '<div id="scan_overlay" style="display: none; position: absolute; left: 0; top: 0; width: 100%; height: 100%; z-index: 1000; background: rgba(192, 194, 201, 0.5);">';
        $str_input .= '<div id="scan_dialog" title="'.__('Scan a cover').'">';
        $str_input .= '<div id="scan_options_std" style="margin: 5px;"><label>'.__('Format:').' <select id="scan_type" onchange="scan_type();">';
        $str_input .= '<option value="png">PNG</option><option value="jpg">JPEG</option></select></label> ';
        $str_input .= '<input type="button" id="btn_getscan" class="button btn" onclick="scan()" value="'.__('Scan').'" />';
        $str_input .= '<i style="margin-left: 10px; cursor: pointer; cursor: hand;" title="'.__('Click to show or hide options').'" onclick="toggle_options()" class="fa fa-gear fa-2x"></i></div>';
        $str_input .= '<div id="scan_options" class="makeHidden" style="margin: 5px;">';
        $str_input .= '<p style="padding: 3px 0;"><label>'.__('History index:').' <input type="text" id="scan_history" value="1" style="width: 60px;" /></label> <input type="button" id="btn_getrecall" class="button btn" onclick="scan_recall" value="'.__('Recall').'" /></p>';
        $str_input .= '<p style="padding: 3px 0;"><label>'.__('Host:').' <input type="text" id="scan_host" value="localhost" /></label> | <label>Port: <input type="text" id="scan_port" patter="\d*" maxlength="6" size="6" style="width: 60px;" value="8811" /></label> <input type="button" id="btn_getmachine" class="button btn" onclick="scan_init()" value="'.__('Get machine').'" /></p>';
        $str_input .= '<p style="padding: 3px 0;"><label>'.__('Scanner:').' <select id="scan_machine" readonly><option>'.__('Default').'</option></select></label></p>';
        $str_input .= '<p style="padding: 3px 0;">'.__('Resolution').', <label>'.__('Horizontal:').' <input type="text" id="scan_res_x" value="300" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />dpi</label> - <label>'.__('Vertical:').' <input type="text" id="scan_res_y" value="300" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />dpi</label></p>';
        $str_input .= '<p style="padding: 3px 0;">'.__('Capture').', <label>'.__('Width:').' <input type="text" id="scan_capture_w" value="2550" pattern="\d*" maxlength="4" size="4" style="width: 60px;" />px</label> - <label>'.__('Height:').' <input type="text" id="scan_capture_h" value="3507" pattern="\d*" maxlength="4" size="4" style="width: 60px;" />px</label></p>';
        $str_input .= '<p>'.__('Result').', <label>'.__('Max Width:').' <input type="text" id="scan_max_w" value="360" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />px</label> - <label>'.__('Max Height:').' <input type="text" id="scan_max_h" value="480" pattern="\d*" maxlength="3" size="3" style="width: 60px;" />px</label></p></div>';
        $str_input .= '<div id="scan_container" style="margin: 5px;"><div style="height: 550px; width: 390px; overflow: auto; float: left;"><p>'.__('Scan result').'</p><img id="my_imgdata" style="margin: auto;" /></div>';
        $str_input .= '<div style="padding-left: 10px; height: 550; width: 400px; overflow: auto; float: left;"><p>'.__('Preview').' <input type="button" class="button btn" value="'.__('Rotate Left').'" onclick="scan_rotate(\'left\')" /> <input type="button" class="button btn" value="'.__('Rotate Right').'" onclick="scan_rotate(\'right\')" /></p><canvas id="my_selected" style="border: 1px solid #CCC; margin: auto;"></canvas></div></div></div></div>';
    }
  }

  $form->addAnything(__('Image'), $str_input);

  // biblio file attachment
  // $str_input = '<div class="'.$visibility.'"><a class="notAJAX button" href="javascript: openHTMLpop(\''.MWB.'bibliography/pop_attach.php?biblioID='.$rec_d['biblio_id'].'\', 600, 300, \''.__('File Attachments').'\')">'.__('Add Attachment').'</a></div>';
  $str_input = '<div class="'.$visibility.'"><a class="notAJAX button btn btn-info openPopUp" href="'.MWB.'bibliography/pop_attach.php?biblioID='.$rec_d['biblio_id'].'" title="'.__('File Attachments').'">'.__('Add Attachment').'</a></div>';
  $str_input .= '<iframe name="attachIframe" id="attachIframe" class="borderAll" style="width: 100%; height: 70px;" src="'.MWB.'bibliography/iframe_attach.php?biblioID='.$rec_d['biblio_id'].'&block=1"></iframe>';
  $form->addAnything(__('File Attachment'), $str_input);

  // biblio relation
  $str_input = '<div class="'.$visibility.'"><a class="notAJAX button btn btn-info openPopUp" href="'.MWB.'bibliography/pop_biblio_rel.php?biblioID='.$rec_d['biblio_id'].'" title="'.__('Biblio Relation').'">'.__('Add Relation').'</a></div>';
  $str_input .= '<iframe name="biblioIframe" id="biblioIframe" class="borderAll" style="width: 100%; height: 100px;" src="'.MWB.'bibliography/iframe_biblio_rel.php?biblioID='.$rec_d['biblio_id'].'&block=1"></iframe>';
  $form->addAnything(__('Related Biblio Data'), $str_input);
  
  /**
   * Custom fields
   */
  if (isset($biblio_custom_fields)) {
  if (is_array($biblio_custom_fields) && $biblio_custom_fields) {
    foreach ($biblio_custom_fields as $fid => $cfield) {

    // custom field properties
    $cf_dbfield = $cfield['dbfield'];
    $cf_label = $cfield['label'];
    $cf_default = $cfield['default'];
    $cf_data = (isset($cfield['data']) && $cfield['data'])?$cfield['data']:array();

    // custom field processing
    if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
      $cf_max = isset($cfield['max'])?$cfield['max']:'200';
      $cf_width = isset($cfield['width'])?$cfield['width']:'50';
      $form->addTextField( ($cfield['type'] == 'longtext')?'textarea':'text', $cf_dbfield, $cf_label, isset($rec_cust_d[$cf_dbfield])?$rec_cust_d[$cf_dbfield]:$cf_default, 'style="width: '.$cf_width.'%;" maxlength="'.$cf_max.'"');
    } else if ($cfield['type'] == 'dropdown') {
      $form->addSelectList($cf_dbfield, $cf_label, $cf_data, isset($rec_cust_d[$cf_dbfield])?$rec_cust_d[$cf_dbfield]:$cf_default);
    } else if ($cfield['type'] == 'checklist') {
      $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, isset($rec_cust_d[$cf_dbfield])?$rec_cust_d[$cf_dbfield]:$cf_default);
    } else if ($cfield['type'] == 'choice') {
      $form->addRadio($cf_dbfield, $cf_label, $cf_data, isset($rec_cust_d[$cf_dbfield])?$rec_cust_d[$cf_dbfield]:$cf_default);
    } else if ($cfield['type'] == 'date') {
      $form->addDateField($cf_dbfield, $cf_label, isset($rec_cust_d[$cf_dbfield])?$rec_cust_d[$cf_dbfield]:$cf_default);
    }
    }
  }
  }

  // biblio hide from opac
  $hide_options[] = array('0', __('Show'));
  $hide_options[] = array('1', __('Hide'));
  $form->addRadio('opacHide', __('Hide in OPAC'), $hide_options, $rec_d['opac_hide']?'1':'0');
  // biblio promote to front page
  $promote_options[] = array('0', __('Don\'t Promote'));
  $promote_options[] = array('1', __('Promote'));
  $form->addRadio('promote', __('Promote To Homepage'), $promote_options, $rec_d['promoted']?'1':'0');
  // biblio labels
  $arr_labels = !empty($rec_d['labels'])?unserialize($rec_d['labels']):array();
  if ($arr_labels) {
    foreach ($arr_labels as $label) { $arr_labels[$label[0]] = $label[1]; }
  }
  $str_input = '';
  // get label data from database
  $label_q = $dbs->query("SELECT * FROM mst_label LIMIT 20");
  while ($label_d = $label_q->fetch_assoc()) {
    $checked = isset($arr_labels[$label_d['label_name']])?' checked':'';
    $url = isset($arr_labels[$label_d['label_name']])?$arr_labels[$label_d['label_name']]:'';
    $str_input .= '<div '
    .'style="background: url('.SWB.IMG.'/labels/'.$label_d['label_image'].') left center no-repeat; padding-left: 30px; height: 45px;" class="'.$label_d['label_name'].'"> '
    .'<input type="checkbox" name="labels[]" value="'.$label_d['label_name'].'"'.$checked.' /> '.$label_d['label_desc']
    .'<div>URL : <input type="text" title="Enter a website link/URL to make this label clickable" '
    .'name="label_urls['.$label_d['label_name'].']" size="50" maxlength="300" value="'.$url.'" /></div></div>';
  }
  $form->addAnything('Label', $str_input);
  // $form->addCheckBox('labels', 'Label', $label_options, explode(' ', $rec_d['labels']));

  // edit mode messagge
  if ($form->edit_mode) {
  echo '<div class="infoBox">'
    .'<div style="float: left; width: 80%;">'.__('You are going to edit biblio data').' : <b>'.$rec_d['title'].'</b>  <br />'.__('Last Updated').'&nbsp;'. $rec_d['last_update'].'</div>'; //mfc
    if ($rec_d['image']) {
    if (file_exists(IMGBS.'docs/'.$rec_d['image'])) {
      $upper_dir = '';
      if ($in_pop_up) {
      $upper_dir = '../../';
      }
      echo '<div id="biblioImage" style="float: right;"><img src="'.$upper_dir.'../lib/minigalnano/createthumb.php?filename=../../images/docs/'.urlencode($rec_d['image']).'&width=53" style="border: 1px solid #999999" /></div>';
    }
    }
  echo '</div>'."\n";
  }
  // print out the form object
  echo $form->printOut();
  // javascript
  ?>
  <script type="text/javascript">
  $(document).ready(function() {
    $('#class').change(function() {
      $('#callNumber').val($(this).val().replace('NEW:',''));
    });
  });
  </script>
  <?php
} else {
  require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
  require MDLBS.'bibliography/biblio_utils.inc.php';
  require LIB.'biblio_list_model.inc.php';

  // number of records to show in list
  $biblio_result_num = ($sysconf['biblio_result_num']>100)?100:$sysconf['biblio_result_num'];

  // create datagrid
  $datagrid = new simbio_datagrid();

  // index choice
  if ($sysconf['index']['type'] == 'index' ||  $sysconf['index']['type'] == 'sphinx' ) {
  if ($sysconf['index']['type'] == 'sphinx') {
    require LIB.'sphinx/sphinxapi.php';
    require LIB.'biblio_list_sphinx.inc.php';
  } else {
    require LIB.'biblio_list_index.inc.php';
  }

  // table spec
  $table_spec = 'search_biblio AS `index` LEFT JOIN item ON `index`.biblio_id=item.biblio_id';

  if ($can_read AND $can_write) {
    $datagrid->setSQLColumn('index.biblio_id', 'index.title AS \''.__('Title').'\'', 'index.labels',
    'index.author',
    'index.isbn_issn AS \''.__('ISBN/ISSN').'\'',
    'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">'.__('None').'</strong>\') AS \''.__('Copies').'\'',
    'index.last_update AS \''.__('Last Update').'\'');
    $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
  } else {
    $datagrid->setSQLColumn('index.title AS \''.__('Title').'\'', 'index.author', 'index.labels',
    'index.isbn_issn AS \''.__('ISBN/ISSN').'\'',
    'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">'.__('None').'</strong>\') AS \''.__('Copies').'\'',
    'index.last_update AS \''.__('Last Update').'\'');
    $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
  }
  $datagrid->invisible_fields = array(1,2);
  $datagrid->setSQLorder('index.last_update DESC');

  // set group by
  $datagrid->sql_group_by = 'index.biblio_id';

  } else {
  require LIB.'biblio_list.inc.php';

  // table spec
  $table_spec = 'biblio LEFT JOIN item ON biblio.biblio_id=item.biblio_id';

  if ($can_read AND $can_write) {
    $datagrid->setSQLColumn('biblio.biblio_id', 'biblio.biblio_id AS bid',
    'biblio.title AS \''.__('Title').'\'',
    'biblio.isbn_issn AS \''.__('ISBN/ISSN').'\'',
    'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">'.__('None').'</strong>\') AS \''.__('Copies').'\'',
    'biblio.last_update AS \''.__('Last Update').'\'');
    $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
  } else {
    $datagrid->setSQLColumn('biblio.biblio_id AS bid', 'biblio.title AS \''.__('Title').'\'',
    'biblio.isbn_issn AS \''.__('ISBN/ISSN').'\'',
    'IF(COUNT(item.item_id)>0, COUNT(item.item_id), \'<strong style="color: #f00;">'.__('None').'</strong>\') AS \''.__('Copies').'\'',
    'biblio.last_update AS \''.__('Last Update').'\'');
    // modify column value
    $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
  }
  $datagrid->invisible_fields = array(0);
  $datagrid->setSQLorder('biblio.last_update DESC');

  // set group by
  $datagrid->sql_group_by = 'biblio.biblio_id';
  }

	$stopwords= "@\sAnd\s|\sOr\s|\sNot\s|\sThe\s|\sDan\s|\sAtau\s|\sAn\s|\sA\s@i";

  // is there any search
  if (isset($_GET['keywords']) AND $_GET['keywords']) {
  $keywords = $dbs->escape_string(trim($_GET['keywords']));
		$keywords = preg_replace($stopwords,' ',$keywords);
  $searchable_fields = array('title', 'author', 'subject', 'isbn', 'publisher');
  if ($_GET['field'] != '0' AND in_array($_GET['field'], $searchable_fields)) {
    $field = $_GET['field'];
    $search_str = $field.'='.$keywords;
  } else {
    $search_str = '';
    foreach ($searchable_fields as $search_field) {
    $search_str .= $search_field.'='.$keywords.' OR ';
    }
    $search_str = substr_replace($search_str, '', -4);
  }

  $biblio_list = new biblio_list($dbs, $biblio_result_num);
  $criteria = $biblio_list->setSQLcriteria($search_str);
  }

  if (isset($criteria)) {
  $datagrid->setSQLcriteria('('.$criteria['sql_criteria'].')');
  }

  // set table and table header attributes
  $datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
  $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
  // set delete proccess URL
  $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
  $datagrid->debug = true;

  // put the result into variables
  $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, $biblio_result_num, ($can_read AND $can_write));
  if (isset($_GET['keywords']) AND $_GET['keywords']) {
  $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
  echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"<div>'.__('Query took').' <b>'.$datagrid->query_time.'</b> '.__('second(s) to complete').'</div></div>'; //mfc
  }

  echo $datagrid_result;
}
/* main content end */
