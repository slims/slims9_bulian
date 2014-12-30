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

/* Topic List */

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

$sysconf['subject_level'][1] = 'Primary';
$sysconf['subject_level'][2] = 'Additional';

// page title
$page_title = 'Topic List';
// get id from url
$biblioID = 0;
if (isset($_GET['biblioID']) AND !empty($_GET['biblioID'])) {
  $biblioID = intval($_GET['biblioID']);
}

// start the output buffer
ob_start();
?>
<script type="text/javascript">
function confirmProcess(int_biblio_id, int_item_id)
{
  var confirmBox = confirm('Are you sure to remove selected topic?' + "\n" + 'Once deleted, it can\'t be restored!');
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
// topic of removal
if (isset($_GET['removesess'])) {
  $idx = $_GET['removesess'];
  unset($_SESSION['biblioTopic'][$idx]);
  echo '<script type="text/javascript">';
  echo 'alert(\'Topic removed!\');';
  echo 'location.href = \'iframe_topic.php\';';
  echo '</script>';
}

if (isset($_POST['remove'])) {
  $id = (integer)$_POST['remove'];
  $bid = (integer)$_POST['bid'];
  $sql_op = new simbio_dbop($dbs);
  $sql_op->delete('biblio_topic', 'topic_id='.$id.' AND biblio_id='.$bid);
  echo '<script type="text/javascript">';
  echo 'alert(\'Topic succesfully removed!\');';
  echo 'location.href = \'iframe_topic.php?biblioID='.$bid.'\';';
  echo '</script>';
}

// if biblio ID is set
if ($biblioID) {
  $table = new simbio_table();
  $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

  // database list
  $biblio_topic_q = $dbs->query("SELECT bt.*, t.topic, t.topic_type FROM biblio_topic AS bt
    LEFT JOIN mst_topic AS t ON bt.topic_id=t.topic_id
    WHERE bt.biblio_id=$biblioID ORDER BY level ASC");

  $row = 1;
  while ($biblio_topic_d = $biblio_topic_q->fetch_assoc()) {
    // alternate the row color
    $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

    // remove link
    $remove_link = '<a href="#" class="notAJAX btn button btn-danger btn-delete" onclick="confirmProcess('.$biblioID.', '.$biblio_topic_d['topic_id'].')">Delete</a>';
    $topic = $biblio_topic_d['topic'];
    $topic_type = $sysconf['subject_type'][$biblio_topic_d['topic_type']];

    $table->appendTableRow(array($remove_link, $topic, $topic_type, $sysconf['subject_level'][$biblio_topic_d['level']]));
    $table->setCellAttr($row, 0, 'class="'.$row_class.'" style="font-weight: bold; width: 10%;"');
    $table->setCellAttr($row, 1, 'class="'.$row_class.'" style="font-weight: bold; width: 50%;"');
    $table->setCellAttr($row, 2, 'class="'.$row_class.'" style="font-weight: bold; width: 20%;"');
    $table->setCellAttr($row, 3, 'class="'.$row_class.'" style="width: 20%;"');

    $row++;
  }

  echo $table->printTable();
  // hidden form
  echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="bid" value="0" /><input type="hidden" name="remove" value="0" /></form>';
} else {
  if ($_SESSION['biblioTopic']) {
    $table = new simbio_table();
    $table->table_attr = 'align="center" style="width: 100%;" cellpadding="2" cellspacing="0"';

    $row = 1;
    $row_class = 'alterCell2';
    foreach ($_SESSION['biblioTopic'] as $biblio_session) {
      // remove link
      $remove_link = '<a class="notAJAX btn button btn-danger btn-delete" href="iframe_topic.php?removesess='.$biblio_session[0].'">Remove</a>';

      if ($biblio_session) {
          $topic_q = $dbs->query("SELECT topic, topic_type FROM mst_topic WHERE topic_id=".$biblio_session[0]);
          $topic_d = $topic_q->fetch_row();
          $topic = $topic_d[0];
          $topic_type = $sysconf['subject_type'][$topic_d[1]];
      }

      $table->appendTableRow(array($remove_link, $topic, $topic_type, $sysconf['subject_level'][$biblio_session[1]]));
      $table->setCellAttr($row, 0, 'class="'.$row_class.'" style="font-weight: bold; background-color: #ffc466; width: 10%;"');
      $table->setCellAttr($row, 1, 'class="'.$row_class.'" style="background-color: #ffc466; width: 50%;"');
      $table->setCellAttr($row, 2, 'class="'.$row_class.'" style="background-color: #ffc466; width: 20%;"');
      $table->setCellAttr($row, 3, 'class="'.$row_class.'" style="background-color: #ffc466; width: 20%;"');

      $row++;
    }

    echo $table->printTable();
  }
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
