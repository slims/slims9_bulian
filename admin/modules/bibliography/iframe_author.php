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
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// page title
$page_title = 'Authority List';
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
  var confirmBox = confirm('Are you sure to remove selected author?' + "\n" + 'Once deleted, it can\'t be restored!');
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
  unset($_SESSION['biblioAuthor'][$idx]);
  echo '<script type="text/javascript">';
  echo 'alert(\''.__('Author succesfully removed!').'\');';
  echo 'location.href = \'iframe_author.php\';';
  echo '</script>';
}

if (isset($_POST['remove'])) {
  $id = (integer)$_POST['remove'];
  $bid = (integer)$_POST['bid'];
  $sql_op = new simbio_dbop($dbs);
  $sql_op->delete('biblio_author', 'author_id='.$id.' AND biblio_id='.$bid);
  echo '<script type="text/javascript">';
  echo 'alert(\''.__('Author removed!').'\');';
  echo 'location.href = \'iframe_author.php?biblioID='.$bid.'\';';
  echo '</script>';
}

// if biblio ID is set
if ($biblioID) {
  $table = new simbio_table();
  $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

  // database list
  $biblio_author_q = $dbs->query("SELECT ba.*, a.author_name, a.author_year, a.authority_type FROM biblio_author AS ba
      LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
      WHERE ba.biblio_id=$biblioID ORDER BY level ASC");
  $row = 1;
  while ($biblio_author_d = $biblio_author_q->fetch_assoc()) {
    // alternate the row color
    $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

    // remove link
    $remove_link = '<a href="#" class="notAJAX btn button btn-danger btn-delete" onclick="confirmProcess('.$biblioID.', '.$biblio_author_d['author_id'].')">Delete</a>';
    $author = $biblio_author_d['author_name'];
    $author_year = $biblio_author_d['author_year'];
    $authority_type = $sysconf['authority_type'][$biblio_author_d['authority_type']];

    $table->appendTableRow(array($remove_link, $author, $author_year, $authority_type, $sysconf['authority_level'][$biblio_author_d['level']]));
    $table->setCellAttr($row, 0, 'class="'.$row_class.'" style="font-weight: bold; width: 10%;"');
    $table->setCellAttr($row, 1, 'class="'.$row_class.'" style="width: 30%;"');
    $table->setCellAttr($row, 2, 'class="'.$row_class.'" style="width: 20%;"');
    $table->setCellAttr($row, 3, 'class="'.$row_class.'" style="width: 20%;"');
    $table->setCellAttr($row, 4, 'class="'.$row_class.'" style="width: 20%;"');
    $row++;
  }

  echo $table->printTable();
  // hidden form
  echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="bid" value="0" /><input type="hidden" name="remove" value="0" /></form>';
} else {
  if ($_SESSION['biblioAuthor']) {
    $table = new simbio_table();
    $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

    $row = 1;
    $row_class = 'alterCell2';
    foreach ($_SESSION['biblioAuthor'] as $biblio_session) {
      // remove link
      $remove_link = '<a class="notAJAX btn button btn-danger btn-delete" href="iframe_author.php?removesess='.$biblio_session[0].'">Remove</a>';

      if ($biblio_session) {
          $author_q = $dbs->query("SELECT author_name, author_year, authority_type FROM mst_author
              WHERE author_id=".$biblio_session[0]);
          $author_d = $author_q->fetch_row();
          $author = $author_d[0];
          $author_year = $author_d[1];
          $authority_type = $author_d[2];
      }

      $table->appendTableRow(array($remove_link, $author, $author_year, $authority_type, $sysconf['authority_level'][$biblio_session[1]]));
      $table->setCellAttr($row, 0, 'class="'.$row_class.'" style="font-weight: bold; background-color: #fff; width: 10%;"');
      $table->setCellAttr($row, 1, 'class="'.$row_class.'" style="background-color: #fff; width: 30%;"');
      $table->setCellAttr($row, 2, 'class="'.$row_class.'" style="background-color: #fff; width: 20%;"');
      $table->setCellAttr($row, 3, 'class="'.$row_class.'" style="background-color: #fff; width: 20%;"');
      $table->setCellAttr($row, 4, 'class="'.$row_class.'" style="background-color: #fff; width: 20%;"');
      $row++;
    }

    echo $table->printTable();
  }
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
