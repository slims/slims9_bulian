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

use SLiMS\Filesystems\Storage;

/* Biblio file Adding Pop Windows */

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
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// page title
$page_title = 'File Attachment Upload';

// check for biblio ID in url
$biblioID = 0;
if (isset($_GET['biblioID']) AND $_GET['biblioID']) {
  $biblioID = (integer)$_GET['biblioID'];
}
// check for file ID in url
$fileID = 0;
if (isset($_GET['fileID']) AND $_GET['fileID']) {
  $fileID = (integer)$_GET['fileID'];
}

// start the output buffer
ob_start();
/* main content */
// biblio topic save proccess
if (isset($_POST['upload']) AND trim(strip_tags($_POST['fileTitle'])) != '') {
  $uploaded_file_id = 0;
  $title = trim(strip_tags($_POST['fileTitle']));
  $url = trim(strip_tags($_POST['fileURL']));
  // create new sql op object
  $sql_op = new simbio_dbop($dbs);
  // FILE UPLOADING
  if ($_FILES['file2attach']['error'] == 1)
  {
    toastr(__('Invalid attacment, make sure your file is not exceeded system max upload'))->error();
  }

  if (isset($_FILES['file2attach']) AND $_FILES['file2attach']['size']) {
    $file_dir = trim($_POST['fileDir']);
    // create upload object
    $file_upload = Storage::repository()->upload('file2attach', function($repository) use($sysconf) {

      // Extension check
      $repository->isExtensionAllowed();

      // File size check
      $repository->isLimitExceeded($sysconf['max_upload']*1024);

      // destroy it if failed
      if (!empty($repository->getError())) $repository->destroyIfFailed();

    })->as(md5(date('Y-m-d H:i:s'))); // set new name

    if ($file_upload->getUploadStatus()) {
        $file_ext = $file_upload->getExt($file_upload->getUploadedFileName());
        $fdata['uploader_id'] = $_SESSION['uid'];
        $fdata['file_title'] = $dbs->escape_string($title);
        $fdata['file_name'] = $dbs->escape_string($file_upload->getUploadedFileName());
        $fdata['file_url'] = $dbs->escape_string($url);
        $fdata['file_dir'] = $dbs->escape_string($file_dir);
        $fdata['file_desc'] = $dbs->escape_string(trim(strip_tags($_POST['fileDesc'])));
        if(isset($_POST['fileKey']) && trim($_POST['fileKey']) !== '')
          $fdata['file_key'] = $dbs->escape_string(trim(strip_tags($_POST['fileKey'])));
        $fdata['mime_type'] = $sysconf['mimetype'][trim($file_ext, '.')]??'';
        $fdata['input_date'] = date('Y-m-d H:i:s');
        $fdata['last_update'] = $fdata['input_date'];
        // insert file data to database
        @$sql_op->insert('files', $fdata);
        $uploaded_file_id = $sql_op->insert_id;
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' upload file ('.$file_upload->getUploadedFileName().')', 'Attachment', 'Add');
    } else {
      utility::jsToastr('File Attachment', __('Upload FAILED! Forbidden file type or file size too big!'), 'error');
      echo '<script type="text/javascript">';
      echo 'self.location.href = "'.$_SERVER['PHP_SELF'].'";';
      echo '</script>';
      die();
    }
  } else {
    if ($url && preg_match('@^(http|https|ftp|gopher):\/\/@i', $url)) {
      $fdata['uploader_id'] = $_SESSION['uid'];
      $fdata['file_title'] = $dbs->escape_string($title);
      $fdata['file_name'] = $dbs->escape_string($url);
      $fdata['file_url'] = $dbs->escape_string($fdata['file_name']);
      $fdata['file_dir'] = 'literal{NULL}';
      $fdata['file_desc'] = $dbs->escape_string(trim(strip_tags($_POST['fileDesc'])));
      if(isset($_POST['fileKey']) && trim($_POST['fileKey']) !== '')
          $fdata['file_key'] = $dbs->escape_string(trim(strip_tags($_POST['fileKey'])));
      $fdata['mime_type'] = 'text/uri-list';
      $fdata['input_date'] = date('Y-m-d H:i:s');
      $fdata['last_update'] = $fdata['input_date'];
      // insert file data to database
      @$sql_op->insert('files', $fdata);
      $uploaded_file_id = $sql_op->insert_id;
    }
  }

  // BIBLIO FILE RELATION DATA UPDATE
  // check if biblio_id POST var exists
  if (isset($_POST['updateBiblioID']) AND !empty($_POST['updateBiblioID'])) {
    $updateBiblioID = (integer)$_POST['updateBiblioID'];
    $data['biblio_id'] = $updateBiblioID;
    $data['file_id'] = $uploaded_file_id;
    $data['placement'] = utility::filterData('placement', 'post', true, true, true);
    $data['access_type'] = trim($_POST['accessType']);
    $data['access_limit'] = 'literal{NULL}';
    // parsing member type data
    if ($data['access_type'] == 'public') {
      $groups = '';
      if (isset($_POST['accLimit']) AND count($_POST['accLimit']) > 0) {
        $groups = serialize($_POST['accLimit']);
      } else {
        $groups = 'literal{NULL}';
      }
      $data['access_limit'] = trim($groups);
    }

    if (isset($_POST['updateFileID'])) {
      $fileID = (integer)$_POST['updateFileID'];
      // file biblio access update
      $update1 = $sql_op->update('biblio_attachment', array('access_type' => $data['access_type'], 'access_limit' => $data['access_limit'], 'placement' => $data['placement']), 'biblio_id='.$updateBiblioID.' AND file_id='.$fileID);
      // file description update
      $file_desc_update = array('file_title' => $title, 'file_url' => $url, 'file_desc' => $dbs->escape_string(trim($_POST['fileDesc'])));
      if(isset($_POST['fileKey']))
          $file_desc_update['file_key'] = $dbs->escape_string(trim(strip_tags($_POST['fileKey'])));
      $update2 = $sql_op->update('files', $file_desc_update, 'file_id='.$fileID);
      if ($update1) {
        utility::jsToastr('File Attachment', __('File Attachment data updated!'), 'success');
        echo '<script type="text/javascript">';
        echo 'parent.setIframeContent(\'attachIframe\', \''.MWB.'bibliography/iframe_attach.php?biblioID='.$updateBiblioID.'\');';
        echo '</script>';
      } else {
          utility::jsToastr('File Attachment', ''.__('File Attachment data FAILED to update!').''."\n".$sql_op->error, 'error');
      }
    } else {
      if ($sql_op->insert('biblio_attachment', $data)) {
        utility::jsToastr('File Attachment', __('File Attachment uploaded succesfully!'), 'success');
        echo '<script type="text/javascript">';
        echo 'parent.setIframeContent(\'attachIframe\', \''.MWB.'bibliography/iframe_attach.php?biblioID='.$data['biblio_id'].'\');';
        echo '</script>';
      } else {
        utility::jsToastr('File Attachment',''.__('File Attachment data FAILED to save!').''."\n".$sql_op->error, 'error');
      }
    }
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' updating file attachment data', 'Attachment', 'Update');
  } else {
    if ($uploaded_file_id) {
      // add to session array
      $fdata['file_id'] = $uploaded_file_id;
      $fdata['access_type'] = trim($_POST['accessType']);
      $_SESSION['biblioAttach'][$uploaded_file_id] = $fdata;
      utility::jsToastr('File Attachment', __('File Attachment uploaded succesfully!'), 'success');
      echo '<script type="text/javascript">';
      echo 'parent.setIframeContent(\'attachIframe\', \''.MWB.'bibliography/iframe_attach.php\');';
      echo '</script>';
    }
  }
}

// create new instance
$form = new simbio_form_table('attachUploadForm', $_SERVER['PHP_SELF'].'?biblioID='.$biblioID, 'post');
$form->submit_button_attr = 'name="upload" value="'.__('Upload Now').'" class="btn btn-primary"';
// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// query
$file_attach_q = $dbs->query("SELECT fl.*, batt.* FROM files AS fl
  LEFT JOIN biblio_attachment AS batt ON fl.file_id=batt.file_id
  WHERE batt.biblio_id=$biblioID AND batt.file_id=$fileID");
$file_attach_d = $file_attach_q->fetch_assoc();

// edit mode
if (isset($file_attach_d['biblio_id']) AND isset($file_attach_d['file_id'])) {
  $form->addHidden('updateBiblioID', $file_attach_d['biblio_id']);
  $form->addHidden('updateFileID', $file_attach_d['file_id']);
} else if ($biblioID) {
  $form->addHidden('updateBiblioID', $biblioID);
}

// file title
$form->addTextField('text', 'fileTitle', __('Title').'*', $file_attach_d['file_title']??'', 'class="form-control"');
// file attachment
if (isset($file_attach_d['file_name'])) {
  $form->addAnything('Attachment', $file_attach_d['file_dir'].'/'.$file_attach_d['file_name']);
} else {
  // file upload dir
  // create simbio directory object
  $repo = new simbio_directory(REPOBS);
  $repo_dir_tree = $repo->getDirectoryTree(5);
  $repodir_options[] = array('', __('Repository ROOT'));
  if (is_array($repo_dir_tree)) {
    // sort array by index
    ksort($repo_dir_tree);
    // loop array
    foreach ($repo_dir_tree as $dir) {
      $repodir_options[] = array($dir, $dir);
    }
  }
  // add repo directory options to select list
  $form->addSelectList('fileDir', __('Repo. Directory'), $repodir_options,'','class="form-control"');
  // file upload
  $str_input  = '<div class="container-fluid">';
  $str_input .= '<div class="row">';
  $str_input .= '<div class="custom-file col">';
  $str_input .= simbio_form_element::textField('file', 'file2attach','','class="custom-file-input"');
  $str_input .= '<label class="custom-file-label" for="customFile">Choose file</label>';
  $str_input .= '</div>';
  $str_input .= ' <div class="col-4 mt-2">Maximum '.$sysconf['max_upload'].' KB</div>';
  $str_input .= '</div>';
  $str_input .= '</div>';
  $form->addAnything(__('File To Attach'), $str_input);
}
// file url
$form->addTextField('textarea', 'fileURL', __('URL'), $file_attach_d['file_url']??'', 'rows="1" class="form-control"');

// placement
$str_input = '<div class="font-italic">*) '.__('Work for embedded link or video attachment').'</div>';
$str_input .= simbio_form_element::radioButton('placement', [['link', __('Link')], ['popup', __('Popup')], ['embed', __('Embed')]], $file_attach_d['placement']??'link');
$form->addAnything(__('Placement'), $str_input);

// file description
$form->addTextField('textarea', 'fileDesc', __('Description'), $file_attach_d['file_desc']??'', 'rows="2" class="form-control"');
// file key
$form->addTextField('textarea', 'fileKey', __('File Password'), $file_attach_d['file_key']??'', 'rows="2" class="form-control"');
// file access
$acctype_options[] = array('public', __('Public'));
$acctype_options[] = array('private', __('Private'));
$form->addSelectList('accessType', __('Access'), $acctype_options, $file_attach_d['access_type']??'Public','class="form-control col-4"');
// file access limit if set to public
$group_query = $dbs->query('SELECT member_type_id, member_type_name FROM mst_member_type');
$group_options = array();
while ($group_data = $group_query->fetch_row()) {
  $group_options[] = array($group_data[0], $group_data[1]);
}
$form->addCheckBox('accLimit', __('Access Limit by Member Type'), $group_options, !empty($file_attach_d['access_limit'])?unserialize($file_attach_d['access_limit']):null );
?>
<strong><?php echo __('File Attachment'); ?></strong>
<hr />
<?php
// print out the object
echo $form->printOut();
?>
<br>
<script type="text/javascript">
  $(document).on('change', '.custom-file-input', function () {
      let fileName = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
      $(this).parent('.custom-file').find('.custom-file-label').text(fileName);
  });
</script>
<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
