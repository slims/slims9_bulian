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

/* Global application configuration */

// key to authenticate
use SLiMS\SearchEngine\DefaultEngine;
use SLiMS\SearchEngine\Engine;

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
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

if (!function_exists('addOrUpdateSetting')) {
    function addOrUpdateSetting($name, $value) {
        global $dbs;
        $sql_op = new simbio_dbop($dbs);
        $name = $dbs->escape_string($name);
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

?>
<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('System Configuration'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Modify global application preferences'); ?>
    </div>
  </div>
</div>
<?php
/* main content */
/* Config Vars EDIT FORM */
/* Config Vars update process */

/* remove logo */
if (isset($_POST['removeImage'])) {
      foreach (['limg' => 'logo_image','wimg' => 'webicon'] as $key => $data) {
        if (isset($_POST[$key]))
        {
          @unlink(IMGBS.'default/'.$sysconf[$data]);
          addOrUpdateSetting($data, NULL); // set null
        }
      }

      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' remove logo', 'Logo', 'Delete');
      utility::jsToastr(__('System Configuration'), __('Logo Image removed. Refreshing page'), 'success'); 
      echo '<script type="text/javascript">setTimeout(() => {top.location.href = \''.AWB.'index.php?mod=system\'}, 2500)</script>';
      exit();
}

if (isset($_POST['updateData'])) {

    if (!empty($_FILES['image']) AND $_FILES['image']['size']) {
      // remove previous image
      @unlink(IMGBS.'default/'.$sysconf['logo_image']);
      // create upload object
      $image_upload = new simbio_file_upload();
      $image_upload->setAllowableFormat($sysconf['allowed_images']);
      $image_upload->setMaxSize($sysconf['max_image_upload']*1024);
      $image_upload->setUploadDir(IMGBS.'default');
      $img_upload_status = $image_upload->doUpload('image','logo');
      if ($img_upload_status == UPLOAD_SUCCESS) {
        addOrUpdateSetting('logo_image', $dbs->escape_string($image_upload->new_filename));
      }else{
        utility::jsToastr(__('System Configuration'), $image_upload->error, 'error'); 
      }
      addOrUpdateSetting('static_file_version', rand());
    }

    if (!empty($_FILES['icon']) AND $_FILES['icon']['size']) {
      // remove previous image
      @unlink(IMGBS.'default/'.$sysconf['webicon']);
      // create upload object
      $image_upload = new simbio_file_upload();
      $image_upload->setAllowableFormat(['.ico','.png']);
      $image_upload->setMaxSize(100*1024);
      $image_upload->setUploadDir(IMGBS.'default');
      $img_upload_status = $image_upload->doUpload('icon','webicon');
      if ($img_upload_status == UPLOAD_SUCCESS) {
        addOrUpdateSetting('webicon', $dbs->escape_string($image_upload->new_filename));
      }else{
        utility::jsToastr(__('System Configuration'), $image_upload->error, 'error'); 
      }
      addOrUpdateSetting('static_file_version', rand());
    }

    // reset/truncate setting table content
    // library name
    $library_name = $dbs->escape_string(strip_tags(trim($_POST['library_name'])));
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($library_name)).'\' WHERE setting_name=\'library_name\'');

    // library subname
    $library_subname = $dbs->escape_string(strip_tags(trim($_POST['library_subname'])));
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($library_subname)).'\' WHERE setting_name=\'library_subname\'');

    // // initialize template arrays
    // $template = array('theme' => $sysconf['template']['theme'], 'css' => $sysconf['template']['css']);
    // $admin_template = array('theme' => $sysconf['admin_template']['theme'], 'css' => $sysconf['admin_template']['css']);
    //
    // // template
    // $template['theme'] = $_POST['template'];
    // $template['css'] = str_replace($sysconf['template']['theme'], $template['theme'], $sysconf['template']['css']);
    // $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($template)).'\' WHERE setting_name=\'template\'');
    //
    // // admin template
    // $admin_template['theme'] = $_POST['admin_template'];
    // $admin_template['css'] = str_replace($sysconf['admin_template']['theme'], $admin_template['theme'], $sysconf['admin_template']['css']);
    // $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($admin_template)).'\' WHERE setting_name=\'admin_template\'');

    // language
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($_POST['default_lang'])).'\' WHERE setting_name=\'default_lang\'');

    // timezone
    addOrUpdateSetting('timezone', utility::filterData('timezone', 'post', true, true, true));

    // search engine
    addOrUpdateSetting('search_engine', utility::filterData('search_engine', 'post', true, true, true));

    // opac num result
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($_POST['opac_result_num'])).'\' WHERE setting_name=\'opac_result_num\'');

    // promoted titles in homepage
    if (isset($_POST['enable_promote_titles'])) {
        $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($_POST['enable_promote_titles'])).'\' WHERE setting_name=\'enable_promote_titles\'');
    } else {
        $dbs->query('UPDATE setting SET setting_value=\'N;\' WHERE setting_name=\'enable_promote_titles\'');
    }

    // quick return
    $quick_return = $_POST['quick_return'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($quick_return)).'\' WHERE setting_name=\'quick_return\'');

    // loan and due date manual change
    $circulation_receipt = $_POST['circulation_receipt'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($circulation_receipt)).'\' WHERE setting_name=\'circulation_receipt\'');

    // loan and due date manual change
    $allow_loan_date_change = $_POST['allow_loan_date_change'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($allow_loan_date_change)).'\' WHERE setting_name=\'allow_loan_date_change\'');

    // loan limit override
    $loan_limit_override = $_POST['loan_limit_override'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($loan_limit_override)).'\' WHERE setting_name=\'loan_limit_override\'');

    // ignore holidays fine calculation
    // added by Indra Sutriadi
    $ignore_holidays_fine_calc = $_POST['ignore_holidays_fine_calc'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($ignore_holidays_fine_calc)).'\' WHERE setting_name=\'ignore_holidays_fine_calc\'');

    // xml detail
    $xml_detail = $_POST['enable_xml_detail'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($xml_detail)).'\' WHERE setting_name=\'enable_xml_detail\'');

    // xml result
    $xml_result = $_POST['enable_xml_result'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($xml_result)).'\' WHERE setting_name=\'enable_xml_result\'');

    // file download
    $file_download = $_POST['allow_file_download'] == '1'?true:false;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($file_download)).'\' WHERE setting_name=\'allow_file_download\'');

    // session timeout
    $session_timeout = intval($_POST['session_timeout']) >= 1800?$_POST['session_timeout']:1800;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($session_timeout)).'\' WHERE setting_name=\'session_timeout\'');

    // barcode encoding
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($_POST['barcode_encoding'])).'\' WHERE setting_name=\'barcode_encoding\'');

    // counter by ip
    $enable_counter_by_ip = utility::filterData('enable_counter_by_ip', 'post', true, true, true);
    addOrUpdateSetting('enable_counter_by_ip', $enable_counter_by_ip);

    $allowed_counter_ip = utility::filterData('allowed_counter_ip', 'post', true, true, true);
    $allowed_counter_ip = explode(';', $allowed_counter_ip);
    $allowed_counter_ip = array_map(function ($ip) {return trim($ip);}, $allowed_counter_ip);
    addOrUpdateSetting('allowed_counter_ip', $allowed_counter_ip);

    // reserve
    $reserve_direct_database = utility::filterData('reserve_direct_database', 'post', true, true, true);
    addOrUpdateSetting('reserve_direct_database', $reserve_direct_database);
    $reserve_on_loan_only = utility::filterData('reserve_on_loan_only', 'post', true, true, true);
    addOrUpdateSetting('reserve_on_loan_only', $reserve_on_loan_only);

    // visitor limitation
    $visitor_limitation = $_POST['enable_visitor_limitation'];
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($visitor_limitation)).'\' WHERE setting_name=\'enable_visitor_limitation\'');

    // time limitation
    $time_limit = intval($_POST['time_visitor_limitation']) >= 0?$_POST['time_visitor_limitation']:60;
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($time_limit)).'\' WHERE setting_name=\'time_visitor_limitation\'');

    // spellchecker
    $spellchecker_enabled = $_POST['spellchecker_enabled'] == '1'?true:false;
    $dbs->query('REPLACE INTO setting (setting_value, setting_name) VALUES (\''.serialize($spellchecker_enabled).'\',  \'spellchecker_enabled\')');

    // enable chbox confirm
    addOrUpdateSetting('enable_chbox_confirm', (int)utility::filterData('enable_chbox_confirm', 'post', true, true, true));

    // SSL verification
    $http = config('http');
    $http['client']['verify'] = (bool)$_POST['ignore_ssl_verification'];
    addOrUpdateSetting('http', $http);

    // write log
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' change application global configuration', 'Global Config', 'Update');
    utility::jsToastr(__('System Configuration'), __('Settings saved. Refreshing page'), 'success'); 
    echo '<script type="text/javascript">setTimeout(() => { top.location.href = \''.AWB.'index.php?mod=system\' }, 2000);</script>';
    exit();
}

/* Config Vars update process end */

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="updateData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// load settings from database
utility::loadSettings($dbs);

// version status
$form->addAnything(__('SLiMS Version'), '<strong>'.SENAYAN_VERSION.'</strong>');

// library name
$form->addTextField('text', 'library_name', __('Library Name'), $sysconf['library_name'], 'class="form-control"');

// library subname
$form->addTextField('text', 'library_subname', __('Library Subname'), $sysconf['library_subname'], 'class="form-control"');

//logo
$str_input = '';
$str_input .= '<strong class="d-block">'.__('Main Logo').'</strong>';
if(isset($sysconf['logo_image']) && file_exists(IMGBS.'default/'.$sysconf['logo_image']) && $sysconf['logo_image']!=''){
    $str_input .= '<div style="padding:10px;">';
    $str_input .= '<img src="../lib/minigalnano/createthumb.php?filename=images/default/'.$sysconf['logo_image'].'&width=130" class="img-fluid rounded" alt="Image cover">';
    $str_input .= '<a href="'.MWB.'system/index.php" postdata="removeImage=true&limg='.$sysconf['logo_image'].'" class="btn btn-sm btn-danger">'.__('Remove Image').'</a></div>';
}
$str_input .= '<div class="custom-file col-3 d-block">';
$str_input .= simbio_form_element::textField('file', 'image', '', 'class="custom-file-input" id="customFile"');
$str_input .= '<label class="custom-file-label" for="customFile">'.__('Choose file').'</label>';
$str_input .= '</div>';
$str_input .= '<div class="mt-2 ml-2">Maximum '.$sysconf['max_image_upload'].' KB</div>';

// Web icon
$str_input .= '<strong class="d-block mt-2">'.__('Favicon').'</strong>';
if(isset($sysconf['webicon']) && file_exists(IMGBS.'default/'.$sysconf['webicon']) && $sysconf['webicon']!=''){
    $str_input .= '<div style="padding:10px;">';
    $str_input .= '<img src="../lib/minigalnano/createthumb.php?filename=images/default/'.$sysconf['webicon'].'&width=130" class="img-fluid rounded" alt="Image cover">';
    $str_input .= '<a href="'.MWB.'system/index.php" postdata="removeImage=true&wimg='.$sysconf['webicon'].'" class="btn btn-sm btn-danger">'.__('Remove Image').'</a></div>';
}
$str_input .= '<div class="custom-file col-3">';
$str_input .= simbio_form_element::textField('file', 'icon', '', 'class="custom-file-input" id="customFile"');
$str_input .= '<label class="custom-file-label" for="customFile">'.__('Choose file').'</label>';
$str_input .= '</div>';
$str_input .= '<div class="mt-2 ml-2">Maximum 100 KB</div>';
$str_input .= <<<HTML
<script>
$('.custom-file input').on('change',function(){
    //get the file name
    const fileName = $(this).val();
    //replace the "Choose a file" label
    $(this).next('.custom-file-label').html(fileName);
});
</script>
HTML;
$form->addAnything(__('Logo Image'), $str_input);

/* Form Element(s) */
// public template
// scan template directory
// $template_dir = SB.$sysconf['template']['dir'];
// $dir = new simbio_directory($template_dir);
// $dir_tree = $dir->getDirectoryTree(1);
// // sort array by index
// ksort($dir_tree);
// // loop array
// foreach ($dir_tree as $dir) {
//     $tpl_options[] = array($dir, $dir);
// }
// $form->addSelectList('template', __('Public Template'), $tpl_options, $sysconf['template']['theme']);
//
// // admin template
// // scan admin template directory
// $admin_template_dir = SB.'admin'.DS.$sysconf['admin_template']['dir'];
// $dir = new simbio_directory($admin_template_dir);
// $dir_tree = $dir->getDirectoryTree(1);
// // sort array by index
// ksort($dir_tree);
// // loop array
// foreach ($dir_tree as $dir) {
//     $admin_tpl_options[] = array($dir, $dir);
// }
// $form->addSelectList('admin_template', __('Admin Template'), $admin_tpl_options, $sysconf['admin_template']['theme']);

// application language
require_once(LANG.'localisation.php');
$form->addSelectList('default_lang', __('Default App. Language'), $available_languages, $sysconf['default_lang'], 'class="form-control col-3"');

// timezone
$html  = '<input type="text" class="form-control col-2" name="timezone" value="' . ($sysconf['timezone'] ?? 'Asia/Jakarta') . '"/>';
$html .= '<a target="_blank" href="https://www.php.net/manual/en/timezones.php">' . __('List of timezones supported by PHP') . '</a>';
$form->addAnything(__('Default App. Timezone'), $html);

// search engine
$engine = array_map(fn($e) => [$e, $e], Engine::init()->get());
$form->addSelectList('search_engine', __('Search Engine'), $engine, $sysconf['search_engine'] ?? DefaultEngine::class, 'class="select2 col-md-6"');

// opac result list number
$result_num_options[] = array('10', '10');
$result_num_options[] = array('20', '20');
$result_num_options[] = array('30', '30');
$result_num_options[] = array('40', '40');
$result_num_options[] = array('50', '50');
$form->addSelectList('opac_result_num', __('Number Of Collections To Show In OPAC Result List'), $result_num_options, $sysconf['opac_result_num'],'class="form-control col-1"');

// homepage setting
$promote_options[] = array('1', __('Yes'));
$form->addCheckBox('enable_promote_titles', __('Show Promoted Titles at Homepage'), $promote_options, $sysconf['enable_promote_titles']?'1':'0','class="form-control col-3"');

// enable quick return
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('quick_return', __('Quick Return'), $options, $sysconf['quick_return']?'1':'0','class="form-control col-3"');

// circulation receipt
$options = null;
$options[] = array('0', __('Don\'t Print'));
$options[] = array('1', __('Print'));
$form->addSelectList('circulation_receipt', __('Print Circulation Receipt'), $options, $sysconf['circulation_receipt']?'1':'0','class="form-control col-3"');

// enable manual changes of loan and due date in circulation transaction
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('allow_loan_date_change', __('Loan and Due Date Manual Change'), $options, $sysconf['allow_loan_date_change']?'1':'0','class="form-control col-3"');

// enable loan limit overriden
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('loan_limit_override', __('Loan Limit Override'), $options, $sysconf['loan_limit_override']?'1':'0','class="form-control col-3"');

// enable ignore holidays fine calc
// added by Indra Sutriadi
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('ignore_holidays_fine_calc', __('Ignore Holidays Fine Calculation'), $options, $sysconf['ignore_holidays_fine_calc']?'1':'0','class="form-control col-3"');

// enable bibliography xml detail
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('enable_xml_detail', __('OPAC XML Detail'), $options, $sysconf['enable_xml_detail']?'1':'0','class="form-control col-3"');

// enable bibliography xml result set
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('enable_xml_result', __('OPAC XML Result'), $options, $sysconf['enable_xml_result']?'1':'0','class="form-control col-3"');

// enable spell checker on search
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('spellchecker_enabled', __('Enable Search Spellchecker'), $options, $sysconf['spellchecker_enabled']?'1':'0','class="form-control col-3"');

// allow file attachment download
$options = null;
$options[] = array('0', __('Forbid'));
$options[] = array('1', __('Allow'));
$form->addSelectList('allow_file_download', __('Allow OPAC File Download'), $options, $sysconf['allow_file_download']?'1':'0','class="form-control col-3"');

// session timeout
$form->addTextField('text', 'session_timeout', __('Session Login Timeout'), $sysconf['session_timeout'], 'style="width: 10%;" class="form-control"');

// barcode encoding
$form->addSelectList('barcode_encoding', __('Barcode Encoding'), $barcodes_encoding, $sysconf['barcode_encoding'],'class="form-control col-3"');

// enable visitor limitation
$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('enable_counter_by_ip', __('Visitor Counter by IP'), $options, $sysconf['enable_counter_by_ip']?'1':'0','class="form-control col-3"');
$form->addTextField('textarea', 'allowed_counter_ip', __('Allowed Counter IP'), implode('; ', $sysconf['allowed_counter_ip']), 'style="width: 100%;" class="form-control"', __('Separate ip with ;'));

$form->addSelectList('enable_visitor_limitation', __('Visitor Limitation by Time'), $options, $sysconf['enable_visitor_limitation']?'1':'0','class="form-control col-3"');

// time visitor limitation
$form->addTextField('text', 'time_visitor_limitation', __('Time visitor limitation (in minute)'), $sysconf['time_visitor_limitation'], 'style="width: 10%;" class="form-control"');

$options = null;
$options[] = array('0', __('Email'));
$options[] = array('1', __('Database'));
$form->addSelectList('reserve_direct_database', __('Reserve methode'), $options, $sysconf['reserve_direct_database']?'1':'0','class="form-control col-3"');

$options = null;
$options[] = array('0', __('Disable'));
$options[] = array('1', __('Enable'));
$form->addSelectList('reserve_on_loan_only', __('Reserve for item on loan only'), $options, $sysconf['reserve_on_loan_only']?'1':'0','class="form-control col-3"');

$options = null;
$options[] = array('1', __('Enable'));
$options[] = array('0', __('Disable'));
$form->addSelectList('enable_chbox_confirm', __('Activate Confirm Alert?'), $options, $sysconf['enable_chbox_confirm']??'1','class="form-control col-3"');

$options = null;
$options[] = array('1', __('Enable'));
$options[] = array('0', __('Disable'));
$form->addSelectList('ignore_ssl_verification', __('Ignore SSL verification'), $options, ((int)config('http.client.verify')),'class="form-control col-3"', __('SLiMS will ignore all error about SSL validation while download contents from other resource'));

// print out the object
echo $form->printOut();
/* main content end */
