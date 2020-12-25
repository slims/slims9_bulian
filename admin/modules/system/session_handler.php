<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2020-12-25 23:44:16
 * @modify date 2020-12-25 23:44:16
 * @desc [description]
 */

/* Global application configuration */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

// only administrator have privileges to change global settings
if ($_SESSION['uid'] != 1) {
    header('Location: '.MWB.'system/content.php');
    die();
}

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

if (!function_exists('addOrUpdateSetting')) {
    // took from index.php in system module
    function addOrUpdateSetting($name, $value) {
        global $dbs;
        $sql_op = new simbio_dbop($dbs);
        $data['setting_value'] = $dbs->escape_string(serialize($value));

        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
        if ($query->num_rows > 0) {
            // update
            $sql_op->update('setting', $data, "setting_name='{$name}'");
        } else {
            // insert
            $data['setting_name'] = $name;
            $sql_op->insert('setting', $data);
        }
    }
}

if (!function_exists('arrayEscape')) {
    function arrayEscape($data)
    {
        global $dbs;
        if (is_array($data))
        {
            foreach ($data as $key => $value) {
                if (is_array($value))
                {
                    foreach ($value as $k => $val) {
                        $value[$k] = $dbs->escape_string($val);
                    }
                    $data[$key] = $value;
                }
                else
                {
                    $data[$key] = $dbs->escape_string($value);
                }
            }

            return $data;
        }
        return $dbs->escape_string($data);
    }
}

if (isset($_POST['saveData']))
{
    $data = [];
    $data['handler'] = $dbs->escape_string($_POST['handler']);
    $data['files'] = arrayEscape($_POST['files']);
    $data['redis'] = arrayEscape($_POST['redis']);

    // save data
    addOrUpdateSetting('session', $data);
    addOrUpdateSetting('session_timeout', (int)$_POST['session_timeout']);
    // set alert
    utility::jsToastr(__('System Configuration'), __('Settings saved. Refreshing page'), 'success'); 
    echo '<script type="text/javascript">setTimeout(() => { top.location.href = \''.AWB.'\' }, 2000);</script>';
    exit;
}

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Session Handler'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Modify session storage configuration'); ?>
    </div>
  </div>
</div>
<?php
// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// load settings from database
utility::loadSettings($dbs);

// session timeout
$form->addTextField('text', 'session_timeout', __('Session Login Timeout'), $sysconf['session_timeout'], 'style="width: 10%;" class="form-control"');

// set handler
$options = [];
$options[] = ['files', 'Files'];
$options[] = ['redis', 'Redis'];
$form->addSelectList('handler', 'Handler', $options, $sysconf['session']['handler'], 'onChange="openConfig(this)" class="form-control col-3"');
// set property
foreach ($sysconf['session'] as $handler => $prop) {
    if (is_array($sysconf['session'][$handler]))
    {
        foreach ($prop as $data => $value) {
            if (!is_array($value))
            {
                $form->addTextField('text', $handler.'['.$data.']', ucfirst($handler).' '.ucfirst($data), $value, 'style="width: 25%;" class="form-control"');
            }
            else
            {
                foreach ($value as $opt => $val) {
                    $form->addTextField('text', $handler.'['.$data.']['.$opt.']', ucfirst($handler).' '.ucfirst($data).' '.ucfirst($opt), $val, 'style="width: 45%;" class="form-control"');    
                }
            }
        }
    }
}
// print out the object
echo $form->printOut();
/* main content end */
?>
<script>
    // get datalist
    let dataList = document.querySelector('#dataList tbody');

    // set opentag
    function openConfig(obj)
    {
        hiddenTag(obj.value);
    }

    // set hidden tag
    function hiddenTag(notToHidden)
    {
        dataList.childNodes.forEach(node => {
            if (node.id)
            {
                if (node.id !== 'simbioFormRowsession_timeout' && node.id !== 'simbioFormRowhandler')
                {
                    var regex = new RegExp(notToHidden, 'g');
                    if (!Array.isArray(node.id.match(regex)))
                    {
                        node.style = 'display: none;';
                    }
                    else
                    {
                        node.style = '';
                    }
                }
            }
        });
    }

    hiddenTag("<?=$sysconf['session']['handler']?>");
</script>