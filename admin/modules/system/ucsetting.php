<?php
/**
 * @Author: ido
 * @Date:   2016-06-16 21:29:29
 * @Last Modified by:   ido
 * @Last Modified time: 2016-06-16 23:28:54
 */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

if (isset($_POST['updateData'])) {
  $ucs['enable'] = $_POST['enable'];
  $ucs['auto_delete'] = $_POST['auto_delete'];
  $ucs['auto_insert'] = $_POST['auto_insert'];
  $ucs['serveraddr'] = $dbs->escape_string(strip_tags(trim($_POST['serveraddr'])));
  $ucs['id'] = $dbs->escape_string(strip_tags(trim($_POST['id'])));
  $ucs['password'] = trim($_POST['password']);
  $ucs['name'] = $dbs->escape_string(strip_tags(trim($_POST['name'])));

  // data serialize
  $data_serialize = serialize($ucs);
    // insert if not available value ucsettings
  $insert = $dbs->query("INSERT INTO setting(setting_name, setting_value) VALUES ('ucs','$data_serialize')");
  if (!$insert) {
    // update into database
    $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'ucs\'');
    if ($update) {
      // write log
      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' change UCS Settings');
      utility::jsAlert(__('Settings updated.'));
    } else {
      // write log
      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' change UCS Settings');
      utility::jsAlert(__('Failed save settings!'));
    }
  } else {
    utility::jsAlert(__('Settings inserted.'));
  }
  echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
  exit();
}

?>
<fieldset class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('UCS Configuration'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Modify UCS preferences'); ?>
    </div>
  </div>
</fieldset>

<?php
// load settings
utility::loadSettings($dbs);

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="updateData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
$form->table_content_attr = 'class="alterCell2"';

/**
 * UCS global settings
 */
// UCS Enabled
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('enable', __('Enable UCS'), $options, $sysconf['ucs']['enable']?'1':'0');

// auto delete same record on UCS?
$form->addSelectList('auto_delete', __('Auto delete record'), $options, $sysconf['ucs']['auto_delete']?'1':'0');

// auto insert new record to UCS?
$form->addSelectList('auto_insert', __('Auto insert record'), $options, $sysconf['ucs']['auto_insert']?'1':'0');

// server uri
$form->addTextField('text', 'serveraddr', __('Server address'), $sysconf['ucs']['serveraddr'], 'style="width: 60%;"');

// server uri
$form->addTextField('text', 'id', __('Server ID'), $sysconf['ucs']['id'], 'style="width: 60%;"');

// server uri
$form->addTextField('text', 'password', __('Server Password'), $sysconf['ucs']['password'], 'style="width: 60%;"');

// server uri
$form->addTextField('text', 'name', __('Server Name'), $sysconf['ucs']['name'], 'style="width: 60%;"');

// print out the object
echo $form->printOut();