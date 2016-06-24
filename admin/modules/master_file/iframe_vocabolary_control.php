<?php
// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// GET ID FROM URL
$itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;

// start buffer
ob_start();
?>
<script type="text/javascript">
function confirmProcess(topic_id, vocabolary_id)
{
  var confirmBox = confirm('<?php echo addslashes(__('Are you sure to remove selected topic?'));?>' + "\n" + '<?php echo addslashes(__('Once deleted, it can\'t be restored!'));?>');
  if (confirmBox) {
    // set hidden element value
    document.hiddenActionForm.tid.value = topic_id;
    document.hiddenActionForm.remove.value = vocabolary_id;
    // submit form
    document.hiddenActionForm.submit();
  }
}
</script>
<?php
if (isset($_POST['remove'])) {
  $tid = (integer)$_POST['tid'];
  $vid = (integer)$_POST['remove'];
  $sql_op = new simbio_dbop($dbs);
  $sql_op->delete('mst_voc_ctrl', 'topic_id='.$tid.' AND vocabolary_id='.$vid);
  echo '<script type="text/javascript">';
  echo 'alert(\''. addslashes(__('Topic succesfully removed!')) . '\');';
  echo 'location.href = \'iframe_vocabolary_control.php?itemID='.$tid.'\';';
  echo '</script>';
}

if($itemID){
$table = new simbio_table();
$table->table_attr = 'align="center" class="detailTable" style="width: 98%;" cellpadding="2" cellspacing="0"';

// query database
$voc_q = $dbs->query('SELECT * FROM mst_voc_ctrl WHERE topic_id='.$itemID);

$row = 1;
while ($voc_d = $voc_q->fetch_assoc()) {

  if (!is_null($voc_d['scope'])) {
    echo '<b>Scope note: </b>'.$voc_d['scope'].'<hr>';
  }

  if (is_null($voc_d['scope'])) {
    // fallback related topic id
    $topic_q = $dbs->query('SELECT topic FROM mst_topic WHERE topic_id='.$voc_d['related_topic_id']);
    $topic_d = $topic_q->fetch_row();

    // alternate the row color
      $row_class = ($row%2 == 0)?'alterCell':'alterCell2';

      // links
      $edit_link = '<a class="notAJAX btn btn-primary button openPopUp" href="'.MWB.'master_file/pop_vocabolary_control.php?editTopic=true&itemID='.$itemID.'&vocID='.$voc_d['vocabolary_id'].'" height="450" title="'.__('Vocabolary Control').'" style="text-decoration: underline;"><i class="glyphicon glyphicon-pencil"></i></a>';
      $remove_link = '<a href="#" class="notAJAX btn button btn-danger btn-delete" onclick="javascript: confirmProcess('.$itemID.', '.$voc_d['vocabolary_id'].')"><i class="glyphicon glyphicon-trash"></i></a>';
      $related_term = $voc_d['rt_id'];

      $table->appendTableRow(array($remove_link, $edit_link, $related_term, $topic_d[0]));
      $table->setCellAttr($row, null, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: auto;"');
      $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 5%;"');
      $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 5%;"');
      $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="font-weight: bold; width: 8%;"');

      $row++;
  }
}
echo $table->printTable();
// hidden form
echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="tid" value="0" /><input type="hidden" name="remove" value="0" /></form>';
}
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';