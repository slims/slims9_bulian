<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2021-10-27 17:12:17
 * @modify date 2023-04-11 14:02:21
 * @desc [description]
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

$envSample = SB . 'config' . DS . 'env.sample.php';

function setValue($key)
{
    return '';
}

function getCurrentIp()
{
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    return $_SERVER['REMOTE_ADDR'];
}

if (isset($_POST['saveData']))
{
    if (!file_exists($envSample)) toastr(__('env.sample.php file is not exists!'))->error();

    // get env file
    $EnvFile = file_get_contents($envSample);
    
    // get environment user input
    $InputEnv = (utility::filterData('env', 'post') == 0 ? 'development' : 'production');

    /* replacing data */

    // change environment
    $EnvFile = str_replace('<environment>', ($_POST['basedIp'] == 0 ? $InputEnv : $env), $EnvFile);

    // based ip ?
    $InputRangeIp = !empty(trim($_POST['rangeIp'])) ? implode("','", explode(';', trim($_POST['rangeIp']))) : "";
    $EnvFile = str_replace('\'<based_on_ip>\'', (isset($_POST['basedIp']) && $_POST['basedIp'] == 0? 'false' : 'true'), $EnvFile);
    $EnvFile = str_replace('<conditional_environment>', $InputEnv, $EnvFile);
    $EnvFile = str_replace('<ip_range>', $InputRangeIp, $EnvFile);

    // write env file
    // file_put_contents(str_replace('.inc.php', '.inc-debug.php', ENV_FILE), $EnvFile); // debug
    $write = file_put_contents(SB . 'config' . DS . 'env.php', $EnvFile);

    utility::jsToastr(__('Success'), __('Configuration has been saved!'), 'success');

    // Redirect
    redirect()->simbioAJAX(timeout: 0, url: $_SERVER['PHP_SELF']);
    exit;
}

?>

<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('System Environment Settings'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('SLiMS Environment preferences.'); ?>
    </div>
  </div>
</div>

<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');

// Save setting button
$form->submit_button_attr = 'name="saveData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// Your Environment
if ($based_on_ip)
{
    $thisEnv = ucfirst((!in_array(getCurrentIp(), $range_ip) ? $env : $conditional_environment));
    $HTML = '<b>' . __($thisEnv) . '</b>';
    $form->addAnything('Your Environment Mode', $HTML);

    $env = $conditional_environment;
}

// Environment List
$EnvOptions = [
    [1, __('Production')],
    [0, __('Development')]
  ];
$label = __('System Environment Mode');
$form->addSelectList('env', $label, $EnvOptions, ( $env == 'production' ? 1 : 0 ) ,'class="form-control col-3"');
$BasedIpOptions = [
    [0, __('Disable')],
    [1, __('Enable')]
];
$form->addSelectList('basedIp', __('Environment for some IP?'), $BasedIpOptions, ( $based_on_ip ? 1 : 0 ) ,'class="form-control col-3"');
$form->addTextField('textarea', 'rangeIp', __('Range Ip wil be impacted with Environment. Example : 10.120.33.40;20.100.34.10. '), implode(';', $range_ip), 'style="margin-top: 0px; margin-bottom: 0px; height: 149px;" class="form-control" placeholder="'.__('Leave it empty, if you want to set environment to impact for all IP').'"');
// print out the object
echo $form->printOut();