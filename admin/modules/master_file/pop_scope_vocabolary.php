<?php

/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Create by Waris Agung Widodo (ido.alit@gmail.com)
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
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

// GET ID FROM URL
$itemID = (integer)isset($_GET['itemID'])?$_GET['itemID']:0;
if (isset($_POST['save'])) {
  $data['topic_id'] = (integer)$_POST['topic_id'];
  $data['scope'] = trim($dbs->escape_string(strip_tags($_POST['scope'])));

  # create new sql op object
  $sql_op = new simbio_dbop($dbs);

  if (!empty($_POST['vocabolary_id'])) {
    // do update
    $save = $sql_op->update('mst_voc_ctrl', $data, 'vocabolary_id='.$_POST['vocabolary_id']);
  } else {
    // insert
    $save = $sql_op->insert('mst_voc_ctrl', $data);
  }

  if (isset($_POST['delete'])) {
    # create new sql op object
    $save = $sql_op->delete('mst_voc_ctrl', 'vocabolary_id='.$_POST['vocabolary_id']);
  }

  if ($save) {
    $alert_save  = '<script type="text/javascript">';
    $alert_save .= 'alert(\''.__('Data saved!').'\');';
    $alert_save .= 'parent.setIframeContent(\'itemIframe\', \''.MWB.'master_file/iframe_vocabolary_control.php?itemID='.$data['topic_id'].'\');';
    $alert_save .= 'top.jQuery.colorbox.close();';
    $alert_save .= '</script>';
    echo $alert_save;
  } else {
    utility::jsAlert(__('Failed to save data!'));
  }
}

// start buffer
ob_start();

// query scope
$scope_q = $dbs->query('SELECT scope, vocabolary_id FROM mst_voc_ctrl WHERE topic_id='.$itemID.' AND scope IS NOT NULL');
$scope_d = $scope_q->fetch_row();

$page_title = __('Scope Note Vocabulary');
?>
<h1><?php echo $page_title; ?></h1>
<form name="scopeForm" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
  <div class="form-group">
      <label for="exampleInputEmail1"><?php echo __('Scope'); ?></label>
    <textarea style="height:auto;" class="form-control" name="scope" rows="5"><?php echo $scope_d[0]; ?></textarea>
  </div>
  <input type="hidden" name="topic_id" value="<?php echo $itemID; ?>">
  <input type="hidden" name="vocabolary_id" value="<?php echo $scope_d[1]; ?>">
  <div class="checkbox">
    <label>
        <input type="checkbox" name="delete"> <?php echo __('Delete this scope'); ?>
    </label>
  </div>
  <input type="submit" name="save" class="btn btn-primary" value="<?php echo __('Save');?>">
</form>


<?php
/* main content end */
$content = ob_get_clean();
// include the page template
require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';