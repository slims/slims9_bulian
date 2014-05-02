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

/* Attachment List */

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
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// page title
$page_title = 'Attachment List';
// get id from url
$biblioID = 0;
if (isset($_GET['biblioID']) AND !empty($_GET['biblioID'])) {
    $biblioID = (integer)$_GET['biblioID'];
}

// start the output buffer
ob_start();
?>
<script type="text/javascript">
function confirmProcess(int_biblio_id, int_file_id, str_file_name)
{
  // confirmation to remove file from repository
  var confirmBox = confirm('Are you sure to remove the file attachment data?');
  if (confirmBox) {
    // set hidden element value
    var confirmBox2 = confirm('Do you also want to remove ' + str_file_name.sub('\'', '\\\'') + ' file from repository?');
    if (confirmBox2) { document.hiddenActionForm.alsoDeleteFile.value = '1'; }
    document.hiddenActionForm.bid.value = int_biblio_id;
    document.hiddenActionForm.remove.value = int_file_id;
    // submit form
    document.hiddenActionForm.submit();
  }
}
</script>
<?php
/* main content */
// temporary attachment removal
if (isset($_GET['removesess'])) {
  $idx = (integer)$_GET['removesess'];
  // remove file from filesystem
  @unlink(REPOBS.str_replace('/', DS, $_SESSION['biblioAttach'][$idx]['file_dir']).DS.$_SESSION['biblioAttach'][$idx]['file_name']);
  // remove session array
  unset($_SESSION['biblioAttach'][$idx]);
  echo '<script type="text/javascript">';
  echo 'alert(\''.__('Attachment removed!').'\');';
  echo 'location.href = \'iframe_attach.php\';';
  echo '</script>';
}

if (isset($_POST['bid']) AND isset($_POST['remove'])) {
  $bid = (integer)$_POST['bid'];
  $file = (integer)$_POST['remove'];
  // query file data from database
  $file_q = $dbs->query('SELECT * FROM files WHERE file_id='.$file);
  $file_d = $file_q->fetch_assoc();
  // attachment data delete
  $sql_op = new simbio_dbop($dbs);
  $sql_op->delete('biblio_attachment', "file_id=$file AND biblio_id=$bid");

  echo '<script type="text/javascript">';
  if ($_POST['alsoDeleteFile'] == '1') {
      // remove file from repository and filesystem
      @unlink(REPOBS.str_replace('/', DS, $file_d['file_dir']).DS.$file_d['file_name']);
      echo 'alert(\'Attachment '.$file_d['file_name'].' succesfully removed!\');';
  }
  echo 'location.href = \'iframe_attach.php?biblioID='.$bid.'\';';
  echo '</script>';
}

// if biblio ID is set
if ($biblioID) {
  $table = new simbio_table();
  $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

  // database list
  $biblio_attach_q = $dbs->query('SELECT att.*,fl.* FROM biblio_attachment AS att
      LEFT JOIN files AS fl ON att.file_id=fl.file_id WHERE biblio_id='.$biblioID);

  $row = 1;
  $row_class = 'alterCell2';
  while ($biblio_attach_d = $biblio_attach_q->fetch_assoc()) {
    // alternate the row color
    $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

    // remove link
    $remove_link = '<a href="#" onclick="confirmProcess('.$biblioID.', '.$biblio_attach_d['file_id'].', \''.addslashes($biblio_attach_d['file_name']).'\')" class="notAJAX btn button btn-danger btn-delete">Delete</a>';

    // edit link
    $edit_link = '<a class="notAJAX button btn btn-default openPopUp" href="'.MWB.'bibliography/pop_attach.php?biblioID='.$biblioID.'&fileID='.$biblio_attach_d['file_id'].'" width="600" height="300" title="'.__('File Attachments').'">Edit</a>';

    // file link
    if (preg_match('@(video|audio|image)/.+@i', $biblio_attach_d['mime_type'])) {
        $file = '<a class="notAJAX openPopUp" href="'.SWB.'index.php?p=multimediastream&fid='.$biblio_attach_d['file_id'].'&bid='.$biblio_attach_d['biblio_id'].'" width="640" height="480" title="'.$biblio_attach_d['file_title'].'">'.$biblio_attach_d['file_title'].'</a>';
    } else {
        $file = '<a class="notAJAX openPopUp" href="'.SWB.'admin/view.php?fid='.urlencode($biblio_attach_d['file_id']).'" width="640" height="480" target="_blank">'.$biblio_attach_d['file_title'].'</a>';
    }

    $table->appendTableRow(array($remove_link, $edit_link, $file, $biblio_attach_d['file_desc']));
    $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 5%;"');
    $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 5%;"');
    $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="width: 40%;"');
    $table->setCellAttr($row, 3, 'valign="top" class="'.$row_class.'" style="width: 50%;"');

    $row++;
  }
  echo $table->printTable();
  // hidden form
  echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="bid" value="0" /><input type="hidden" name="remove" value="0" /><input type="hidden" name="alsoDeleteFile" value="0" /></form>';
} else {
  if ($_SESSION['biblioAttach']) {
    $table = new simbio_table();
    $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

    $row = 1;
    $row_class = 'alterCell2';
    foreach ($_SESSION['biblioAttach'] as $idx=>$biblio_session) {
      // remove link
      $remove_link = '<a href="iframe_attach.php?removesess='.$idx.'" class="notAJAX btn button btn-danger btn-delete">Remove</a>';

      $table->appendTableRow(array($remove_link, $biblio_session['file_name'], $biblio_session['last_update']));
      $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold; background-color: #ffc466; width: 10%;"');
      $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="background-color: #ffc466; width: 60%;"');
      $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="background-color: #ffc466; width: 30%;"');

      $row++;
    }
    echo $table->printTable();
  }
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
