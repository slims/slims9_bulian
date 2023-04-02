<?php
/**
 *
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

/* Backup Management section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require_once LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// create token in session
$_SESSION['token'] = utility::createRandomString(32);

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if($_SESSION['uid'] != 1){
  $can_write = false;
}

/* DOWNLOAD OPERATION */
if(isset($_GET['action']) && isset($_GET['id']) && $_GET['action'] == 'download'){
  $id = utility::filterData('id', 'get', true, true, true);
  $_q = $dbs->query("SELECT backup_file FROM backup_log WHERE backup_log_id=".$id);
  $path = $_q->fetch_row()[0];
  if(file_exists($path)){
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: " . mime_content_type($path));
    header("Content-Length: " .(string)(filesize($path)) );
    header('Content-Disposition: attachment; filename="'.basename($path).'"');
    header("Content-Transfer-Encoding: binary\n");
    echo file_get_contents($path);
    exit();
  }
}

/* RECORD OPERATION */
if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!$can_read) { 
      die();  
    }

    if (!is_array($_POST['itemID'])) {
      // make an array
      $_POST['itemID'] = array($_POST['itemID']);
    }

    $error_num == 0;
    foreach ($_POST['itemID'] as $itemID) {
      //delete file
      $_q = $dbs->query("SELECT backup_file FROM backup_log WHERE backup_log_id=".$itemID);
      $file = $_q->fetch_row()[0];
      if(file_exists($file)){
         @unlink($file);
      } 
      //delete record
      $sql_op = new simbio_dbop($dbs);
      if (!$sql_op->delete('backup_log', "backup_log_id=$itemID")) {
        $error_num++;
      }
    }
  // error alerting
  if ($error_num == 0) {
    utility::jsToastr(__('Database Backup'), __('All Data Successfully Deleted'), 'success');
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
  } else {
    utility::jsToastr(__('Database Backup'), __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'), 'warning');
    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
  }
  exit();
}

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner backupIcon">
	<div class="per_title">
	    <h2><?php echo __('Database Backup'); ?></h2>
  </div>
  <?php
 // if (!file_exists($sysconf['mysqldump'])) {
 //     echo '<div class="alert alert-danger rounded-none">'.__('The PATH for <strong>mysqldump</strong> program is not right! Please check configuration file or you won\'t be able to do any database backups.').'</div>';
 // }
  ?>
	<div class="sub_section">
	  <div class="btn-group d-flex flex-column">
      <div class="d-flex">
        <button id="startBackup" class="notAJAX btn btn-success d-block mb-1"><?php echo __('Start New Backup'); ?></button>
        <?php if ($_SESSION['uid'] == 1): ?>
        <a href="<?= MWB ?>system/backup_config.php" title="<?= __('Database Backup Configuration') ?>" class="notAJAX openPopUp btn btn-secondary d-block mb-1"><?php echo __('Backup Configuration'); ?></a>
        <?php endif; ?>
      </div>
      <div>
        <input type="checkbox" value="yes" id="activateVerbose"/> <label><?= __('Verbose process')?></label>
      </div>
	  </div>
    <form name="search" action="<?php echo MWB; ?>system/backup.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
      <input type="text" name="keywords" class="form-control col-md-3" />
      <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
    <form name="createBackup" id="createBackup" target="backupVerbose" action="<?php echo MWB; ?>system/backup_proc.php" method="post" style="display: inline; visibility: hidden;">
      <input type="hidden" name="verbose" value="no"/>  
      <input type="hidden" name="start" value="true"/>
      <input type="hidden" name="tkn" value="<?php echo $_SESSION['token']; ?>" />
    </form>
    <iframe name="backupVerbose" class="d-none w-100 my-2 rounded-lg" style="height: 150px; background: black;color: white"></iframe>
  </div>
</div>
</div>
<script>
  $('#startBackup').click(function(){
    let input = $('#activateVerbose');

    // Change ui
    input.attr('disabled', '');
    $(this).removeClass('btn-success').addClass('btn-secondary');
    $(this).text('<?= __('Processing') ?>');
    $('#createBackup').submit()
  });

  $('#activateVerbose').click(function(){
    let input = $('input[name="verbose"]');
    let iframe = $('iframe[name="backupVerbose"]');
    let button = $('#startBackup');

    if ($(this).is(':checked'))
    {
      input.val('yes');
      button.trigger('click');
      iframe.removeClass('d-none');
    }
    else
    {
      iframe.addClass('d-none');
      input.val('false');
    }
  });
</script>
<?php

/* BACKUP LOG LIST */
// table spec
$table_spec = 'backup_log AS bl LEFT JOIN user AS u ON bl.user_id=u.user_id';
// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('bl.backup_log_id',
    'u.realname AS  \''.__('Backup Executor').'\'',
    'bl.backup_time AS \''.__('Backup Time').'\'',
    'bl.backup_file AS \''.__('Backup File Location').'\'',
    'bl.backup_file AS \''.__('File Size').'\'');
$datagrid->setSQLorder('backup_time DESC');
$datagrid->modifyColumnContent(4, 'callback{showFileSize}');
if (!$can_write) $datagrid->invisible_fields = [0];

// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
   $keywords = $dbs->escape_string($_GET['keywords']);
   $datagrid->setSQLCriteria("bl.backup_time LIKE '%$keywords%' OR bl.backup_file LIKE '%$keywords%'");
}
// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
$datagrid->edit_property = false;
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
$datagrid->modifyColumnContent(4, 'callback{showFileSize}'); 

function showFilesize($obj_db,$array_data) {
    $str = __('File not found');
    $decimal  = 2;
    if(file_exists($array_data[3])){
      $file = filesize($array_data[3]);
      $factor = floor((strlen($file) - 1) / 3);
      if ($factor > 0) 
        $sz = 'KMGT';
        $str  = sprintf("%.{$decimal}f ", $file / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
        $str .= '&nbsp;<a class="btn btn-sm btn-info pull-right" href="'.MWB.'system/backup.php?action=download&id='.$array_data[0].'" target="_SELF">'.__('Download').'</a>';
    }
  return $str;
}

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20,($can_read AND $can_write));

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
    echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
}

echo $datagrid_result;
