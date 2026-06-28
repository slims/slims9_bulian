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


$backup_base_dir = realpath($sysconf['backup_dir']);

if (!$backup_base_dir) {
    die('<div class="errorBox">'.__('Backup directory is not accessible or not configured correctly.').'</div>');
}

function validate_path($requested_path, $base_dir) {
    if (empty($requested_path)) {
        return false;
    }

    $full_path = realpath($requested_path);
    if (!$full_path) {
        $filename = basename($requested_path);
        $full_path = realpath($base_dir . '/' . $filename);
    }

    if (!$full_path) {
        return false;
    }

    if (strpos($full_path, $base_dir) === 0) {
        return $full_path;
    }
    return false;
}

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
  if (!is_numeric($id)) {
      exit();
  }
  $_q = $dbs->query("SELECT backup_file FROM backup_log WHERE backup_log_id=".$id);
  if ($_q->num_rows > 0) {
      $requested_path = $_q->fetch_row()[0];
      $path = validate_path($requested_path, $backup_base_dir);
      if($path !== false){
        $mime_type = function_exists('mime_content_type') ? mime_content_type($path) : 'application/octet-stream';
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: " . $mime_type);
        header("Content-Length: " .(string)(filesize($path)) );
        header('Content-Disposition: attachment; filename="'.basename($path).'"');
        header("Content-Transfer-Encoding: binary\n");
        $fo = fopen($path, 'rb');

        while (!feof($fo)) {
          echo fread($fo, 8192);
          ob_flush();
          flush();
        }
        fclose($fo);
        exit();
      }
      header("HTTP/1.0 404 Not Found");
      exit();
  }
}

/* RECORD OPERATION */
if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!$can_write) {
      die();  
    }

    if (!is_array($_POST['itemID'])) {
      // make an array
      $_POST['itemID'] = array($_POST['itemID']);
    }

    $error_num = 0;
    foreach ($_POST['itemID'] as $itemID) {
      $itemID = (int)$itemID;

      //delete file
      $_q = $dbs->query("SELECT backup_file FROM backup_log WHERE backup_log_id=".$itemID);
      $requested_file = $_q->fetch_row()[0];
      $file_to_delete = validate_path($requested_file, $backup_base_dir);
      if($file_to_delete !== false && file_exists($file_to_delete)){
          @unlink($file_to_delete);
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
 //    echo '<div class="alert alert-danger rounded-none">'.__('The PATH for <strong>mysqldump</strong> program is not right! Please check configuration file or you won\'t be able to do any database backups.').'</div>';
 // }
  ?>
  <div class="sub_section">
    <div class="btn-group d-flex flex-column">
      <div class="d-flex">
        <button id="startBackup" class="notAJAX btn btn-success d-block mb-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16">
            <path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l-2-2a.5.5 0 0 0-.707.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 1 .707-.708l-2 2V2a2 2 0 0 1 2-2H2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h7.5V9.5z"/>
          </svg>
          <?php echo __('Start New Backup'); ?>
        </button>
        <?php if ($_SESSION['uid'] == 1): ?>
        <a href="<?= MWB ?>system/backup_config.php" title="<?= __('Database Backup Configuration') ?>" class="notAJAX openPopUp btn btn-secondary d-block mb-1">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
            <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
            <path d="M9.796 1.343c-.527-1.79-3.04-1.79-3.565 0l-.622 1.942-1.8.63-.965-.967-1.13.16.5.866-1.92.51-.54 1.943c-.544 1.83-3.1 1.83-3.645 0l-.54-1.943-1.92-.51.5-.866-1.13-.16-.965.967-1.8-.63-.622-1.942c-.527-1.79-3.04-1.79-3.565 0l-.622 1.942-1.8.63-.965-.967-1.13.16.5.866-1.92.51-.54 1.943c-.544 1.83-3.1 1.83-3.645 0l-.54-1.943-1.92-.51.5-.866-1.13-.16-.965.967-1.8-.63-.622-1.942z"/>
          </svg>
          <?php echo __('Backup Configuration'); ?>
        </a>
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

    input.attr('disabled', 'disabled');
    $(this).removeClass('btn-success').addClass('btn-secondary');
    $(this).contents().filter(function(){ return this.nodeType === 3; }).each(function(){
        this.nodeValue = '<?= __('Processing') ?>';
    });
    if ($(this).text().trim() !== '<?= __('Processing') ?>') {
        $(this).html('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-save" viewBox="0 0 16 16"><path d="M2 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H9.5a1 1 0 0 0-1 1v7.293l-2-2a.5.5 0 0 0-.707.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 1 .707-.708l-2 2V2a2 2 0 0 1 2-2H2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h7.5V9.5z"/></svg>' + ' <?= __('Processing') ?>');
    }

    $('#createBackup').submit()
  });

  $('#activateVerbose').click(function(){
    let input = $('input[name="verbose"]');
    let iframe = $('iframe[name="backupVerbose"]');

    if ($(this).is(':checked'))
    {
      input.val('yes');
      iframe.removeClass('d-none');
    }
    else
    {
      iframe.addClass('d-none');
      input.val('no');
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
    'bl.backup_file AS \''.__('Backup File').'\'', 
    'bl.backup_file AS \''.__('OPTION').'\'');
$datagrid->setSQLorder('backup_time DESC');
$datagrid->modifyColumnContent(4, 'callback{showFilesize}');
$datagrid->modifyColumnContent(3, 'callback{getFilenameFromPath}');

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

function showFilesize($obj_db,$array_data) {
    global $backup_base_dir;
    $path_index = 3;
    $str = __('File not found');
    $safe_path = validate_path($array_data[$path_index], $backup_base_dir);
    if($safe_path !== false){
        $str = '&nbsp;<a class="btn btn btn-info" href="'.MWB.'system/backup.php?action=download&id='.$array_data[0].'" target="_SELF">'.
        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16" style="margin-right: 5px;">'.
        '<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>'.
        '<path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>'.
        '</svg>'.
        __('Download').'</a>';
    }
  return $str;
}

function getFilenameFromPath($obj_db,$array_data) {
    global $backup_base_dir;
    $str = __('File not found');
    $decimal  = 2;
    $safe_path = validate_path($array_data[3], $backup_base_dir);
    if($safe_path !== false){
      $file = filesize($safe_path);
      $factor = floor((strlen($file) - 1) / 3);
      if ($factor > 0) 
        $sz = 'KMGT';
      else
        $sz = '';
        $factor = max(0, $factor);
        $str  = sprintf("%.{$decimal}f ", $file / pow(1024, $factor)) . ($factor > 0 ? $sz[((int)$factor - 1)] : '') . 'B';
        $str =  basename($array_data[3]).'<br/><small><i>( '.$str.' )</i></small>';
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
// END OF FILE