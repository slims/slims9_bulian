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

/* Authority List */

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
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

// page title
$page_title = 'Biblio Relation';
// get id from url
$biblioID = 0;
if (isset($_GET['biblioID']) AND !empty($_GET['biblioID'])) {
  $biblioID = (integer)$_GET['biblioID'];
}

// start the output buffer
ob_start();
?>
<script type="text/javascript">
function confirmProcess(int_biblio_id, int_item_id)
{
  var confirmBox = confirm('<?php echo addslashes(__('Are you sure to remove selected title?'));?>' + "\n" + '<?php echo addslashes(__('Once deleted, it can\'t be restored!'));?>');
  if (confirmBox) {
    // set hidden element value
    document.hiddenActionForm.bid.value = int_biblio_id;
    document.hiddenActionForm.remove.value = int_item_id;
    // submit form
    document.hiddenActionForm.submit();
  }
}
</script>
<?php
/* main content */
// author of removal
if (isset($_GET['removesess'])) {
  $idx = $_GET['removesess'];
  unset($_SESSION['biblioToBiblio'][$idx]);
  echo '<script type="text/javascript">';
  echo 'alert(\''.__('Biblio relation succesfully removed!').'\');';
  echo 'location.href = \'iframe_biblio_rel.php\';';
  echo '</script>';
}

if (isset($_POST['remove'])) {
  $id = (integer)$_POST['remove'];
  $bid = (integer)$_POST['bid'];
  $sql_op = new simbio_dbop($dbs);
  $sql_op->delete('biblio_relation', 'biblio_id='.$bid.' AND rel_biblio_id='.$id);
  echo '<script type="text/javascript">';
  echo 'alert(\''.__('Biblio relation removed!').'\');';
  echo 'location.href = \'iframe_biblio_rel.php?biblioID='.$bid.'\';';
  echo '</script>';
}

// if biblio ID is set
if ($biblioID) {
  $table = new simbio_table();
  $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

  // database list
  $biblio_relation_q = $dbs->query("SELECT b2.title, b2.edition, b2.publish_year, ba.rel_biblio_id FROM biblio_relation AS ba
      LEFT JOIN biblio AS b2 ON ba.rel_biblio_id=b2.biblio_id
      WHERE ba.biblio_id=$biblioID ORDER BY rel_type ASC");
  $row = 1;
  while (isset($biblio_relation_q->num_rows) && $biblio_relation_d = $biblio_relation_q->fetch_assoc()) {
    // alternate the row color
    $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

    // remove link
    $remove_link = '<a href="#" class="notAJAX btn button btn-danger btn-delete" onclick="confirmProcess('.$biblioID.', '.$biblio_relation_d['rel_biblio_id'].')">' . __('Delete') . '</a>';
    $title = $biblio_relation_d['title'];
    $publish_year = $biblio_relation_d['publish_year'];
    $edition = $biblio_relation_d['edition'];

    $table->appendTableRow(array($remove_link, $title, $publish_year, $edition));
    $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 10%;"');
    $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="width: 50%;"');
    $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="width: 20%;"');
    $table->setCellAttr($row, 3, 'valign="top" class="'.$row_class.'" style="width: 20%;"');
    $row++;
  }

  echo $table->printTable();
  // hidden form
  echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="bid" value="0" /><input type="hidden" name="remove" value="0" /></form>';
} else {
  if ($_SESSION['biblioToBiblio']) {
    $table = new simbio_table();
    $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

    $row = 1;
    $row_class = 'alterCell2';
    foreach ($_SESSION['biblioToBiblio'] as $biblio_session) {
      // remove link
      $remove_link = '<a class="notAJAX btn button btn-danger btn-delete" href="iframe_biblio_rel.php?removesess='.$biblio_session[0].'">' . __('Remove') . '</a>';

      if ($biblio_session) {
          $title_q = $dbs->query("SELECT title, publish_year, edition FROM biblio
              WHERE biblio_id=".$biblio_session[0]);
          $title_d = $title_q->fetch_row();
          $title = $title_d[0];
          $publish_year = $title_d[1];
          $edition = $title_d[2];
      }

      $table->appendTableRow(array($remove_link, $title, $publish_year, $edition));
      $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold; background-color: #ffc466; width: 10%;"');
      $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="background-color: #ffc466; width: 50%;"');
      $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="background-color: #ffc466; width: 20%;"');
      $table->setCellAttr($row, 3, 'valign="top" class="'.$row_class.'" style="background-color: #ffc466; width: 20%;"');
      $row++;
    }

    echo $table->printTable();
  }
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
