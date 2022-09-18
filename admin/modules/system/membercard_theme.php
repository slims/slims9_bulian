<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com), Heru Subekti (heroe.soebekti@gmail.com)
 * @Date                : 2020-01-23 12:35
 * @File name           : membercard_theme.php
 */

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
  require SB . 'admin/default/session.inc.php';
}
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_FILE/simbio_directory.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

// load default settings
require SB.'admin'.DS.'admin_template'.DS.'printed_settings.inc.php';
loadPrintSettings($dbs, 'membercard');

if (!function_exists('addOrUpdateSetting')) {
  function addOrUpdateSetting($name, $value)
  {
    global $dbs;
    $sql_op = new simbio_dbop($dbs);
    $data['setting_value'] = $dbs->escape_string(serialize($value));
    $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
    if ($query->num_rows > 0) {
      // update
      $update = $sql_op->update('setting', $data, "setting_name='{$name}'");
      if (!$update) return $dbs->error;
    } else {
     // insert
      $data['setting_name'] = $name;
      $insert = $sql_op->insert('setting', $data);
      if (!$insert) return $dbs->error;
    }
    return true;
  }
}

// save theme change
if (isset($_POST['template'])) {
  if(isset($_POST['data'])){
    foreach ($_POST['data'] as $key => $value) {
      $data[$key] = htmlentities($value);
    }
  }else{
    //restore default settings
    $data = $sysconf['print']['membercard'];
  }

  //load custom css
  if(file_exists(UPLOAD.'membercard/' . $sysconf['print']['membercard']['template'] . '/style.css')){
    $data['css'] = file_get_contents(UPLOAD.'membercard/' . $sysconf['print']['membercard']['template']. '/style.css');
  }

  $data['template'] = utility::filterData('template', 'post', true, true, true);
  
  // execute registered hook
  \SLiMS\Plugins::getInstance()->execute('membercard_theme_update', [$data]); 

  $update = addOrUpdateSetting('membercard_print_settings', $data);
  if ($update !== true) {
    toastr(__('Error saving custom data!') . ' ' . $update)->error();
  } else {
    toastr(__('Custom data saved!'))->success();
 }
  
  exit();
}

// show form
if (isset($_GET['customize'])) {
  ob_start();
  $theme_type = utility::filterData('customize', 'get', true, true, true);
  $theme_dir = utility::filterData('theme', 'get', true, true, true);

  // execute registered hook
  \SLiMS\Plugins::getInstance()->execute('membercard_theme_customize', [$theme_dir]);  

  // include tinfo.inc.php
  $path = UPLOAD.'membercard/' . $theme_dir . '/tinfo.inc.php';
  $theme_key = 'membercard';

  if (file_exists($path)) {
    include_once $path;
    if ($sysconf['print'][$theme_key]['template']['default'] == $theme_dir) {
      // create new instance
      $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
      $form->submit_button_attr = 'name="updateData" value="' . __('Save Settings') . '" class="btn btn-default"';
      // form table attributes
      $form->table_attr = 'id="dataList" class="s-table table"';
      $form->table_header_attr = 'class="alterCell font-weight-bold"';
      $form->table_content_attr = 'class="alterCell2"';

      foreach ($sysconf['print'][$theme_key] as $fid => $cfield) {
        // custom field properties
        $cf_dbfield = $cfield['dbfield']??null;
        $cf_label = $cfield['label']??null;
        $cf_default = $cfield['default']??null;
        $cf_class = $cfield['class']??null;
        $cf_data = (isset($cfield['data']) && $cfield['data']) ? $cfield['data'] : array();
        $cf_width = $cfield['width'] ?? '50';
         // custom field processing
        if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
          $cf_max = $cfield['max'] ?? '200';
          $form->addTextField(($cfield['type'] == 'longtext') ? 'textarea' : 'text', $cf_dbfield, $cf_label, $sysconf[$theme_key][$cf_dbfield] ?? $cf_default, 'class="form-control '.$cf_class.'" style="width: ' . $cf_width . '%;" maxlength="' . $cf_max . '"');
        } else if ($cfield['type'] == 'dropdown') {
          $form->addSelectList($cf_dbfield, $cf_label, $cf_data, $sysconf[$theme_key][$cf_dbfield] ?? $cf_default, 'class="form-control"  style="width: ' . $cf_width . '%;"');
        } else if ($cfield['type'] == 'checklist') {
          $form->addCheckBox($cf_dbfield, $cf_label, $cf_data,  $sysconf[$theme_key][$cf_dbfield] ?? $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'choice') {
          $form->addRadio($cf_dbfield, $cf_label, $cf_data, $sysconf[$theme_key][$cf_dbfield] ?? $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'date') {
          $form->addDateField($cf_dbfield, $cf_label, $sysconf[$theme_key][$cf_dbfield] ?? $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'hidden') {
          $form->addHidden($cf_dbfield, $sysconf[$theme_key][$cf_dbfield] ?? $cf_default);
        } else if ($cfield['type'] == 'anything') {
          $form->addAnything($cf_label,$cf_default);
        } else if ($cfield['type'] == 'ckeditor') {
          if (!isset($ckCount)) $ckCount = 0;
          $form->addAnything($cf_label, '<div id="container'.$ckCount.'" data-field="'.$cf_dbfield.'"><div id="ckeditor-toolbar'.$ckCount.'"></div><div id="ckeditor-content'.$ckCount.'">' . (isset($sysconf[$theme_key][$cf_dbfield]) ? html_entity_decode($sysconf[$theme_key][$cf_dbfield]) : html_entity_decode($cf_default)) . '</div></div>');
          $ckCount++;
        }
      }
      // print out the form object
      echo $form->printOut();
    } else {
      echo __('This theme not customizable');
    }
  } else {
    echo __('This theme not customizable');
  }

  $content = ob_get_clean();
  $css = '<link rel="stylesheet" href="'.SWB.'css/bootstrap-colorpicker.min.css"/>';
  $js  = '<script type="text/javascript" src="'.JWB.'bootstrap-colorpicker.min.js"></script>';
  $js .= '<script type="text/javascript" src="'.JWB.'/ckeditor5/ckeditor.js"></script>';
  $js .= '<script type="text/javascript" src="'.JWB.'/ckeditor5/ckeditor.tinfo.js"></script>';
  $js .= '<script type="text/javascript">$(function () {  $(\'.colorpicker\').colorpicker() })</script>';
  if (isset($ckCount)):
  $js .= "<script>createMultiEditor('{$ckCount}', '#mainForm', ['bold','italic','underline','strikethrough','bulletedList', 'numberedList','alignment:left', 'alignment:right', 'alignment:center', 'alignment:justify'])</script>";
  endif;
  // $js .= "<script type=\"text/javascript\">CKEDITOR.config.toolbar = [['Bold','Italic','Underline','StrikeThrough','NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']] ;</script>";
  require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
  exit();
}

?>
<style type="text/css"></style>
<div class="menuBox">
    <div class="menuBoxInner systemIcon">
        <div class="per_title">
            <h2><?php echo __('Membercard Configuration'); ?></h2>
        </div>
        <div class="infoBox">
          <?php echo __('Customize theme preferences'); ?>
        </div>
    </div>
</div>
<?php

// scan template directory
$template_dir = UPLOAD.'membercard';
$dir = new simbio_directory($template_dir);
$dir_tree = $dir->getDirectoryTree(1);

// sort array by index
ksort($dir_tree);

echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="col-12 my-3">
      </div>';

// execute registered hook
 \SLiMS\Plugins::getInstance()->execute('membercard_theme_init', []);

$default_template = $sysconf['membercard_print_settings']['template']??'classic';
foreach ($dir_tree as $dir) {
  $_btn = '<a href="' . MWB . 'system/membercard_theme.php?customize=membercard&theme=' . $dir . '" data-value="' . $dir . '" class="btn btn-default notAJAX setMembercardTheme">' . __('Activate') . '</a>';
  if ($dir == $default_template) {
  $_btn = '<a href="' . MWB . 'system/membercard_theme.php?customize=membercard&theme=' . $dir . '" data-value="' . $dir . '" title="' . __('Membercard Configuration') . '" class="btn btn-success customeMembercardTheme notAJAX openPopUp" width="800" height="500">' . __('Customize') . '</a>';
  }

  $output = '<div class="col-3">';
  $output .= '<div class="card border-0 mb-4">';
  $output .= '<div class="card-body">';
  $output .= '<div class="mb-2 font-weight-bold">' . ucwords(str_replace('_', ' ', $dir)) . '</div>';
  $preview = file_exists(UPLOAD.'membercard/'.$dir.'/preview.jpg') ? 'preview.jpg' : 'preview.png';
  $output .= '<img class="card-img-top rounded" src="../files/membercard/'. $dir . '/'.$preview.'" height="150" />';
  $output .= '</div>';
  $output .= '<div class="card-footer border-0">' . $_btn . '</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div>';
echo '</div>';
echo '</div>';
?>

<script type="text/javascript">
    (function () {
        $(document).ready(function () {
            $(document).on('click', '.setMembercardTheme', function (e) {
                e.preventDefault();
                var current = $(this);
                var theme = current.attr('data-value');
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    method: 'POST',
                    data: {theme: 'membercard', template: theme}
                }).done(function (msg) {
                    $('a.btn-success.customeMembercardTheme').removeClass('btn-success customeMembercardTheme openPopUp').addClass('btn-default setMembercardTheme').text('<?php echo __('Activate') ?>');
                    current.removeClass('btn-default setMembercardTheme').addClass('btn-success customeMembercardTheme openPopUp').attr({'title': '<?= __('Membercard Configuration') ?>', 'width': '800','height':'500'}).text('<?php echo __('Customize') ?>');
                });
            });

            $('.card').hover(
                function () {
                    $(this).addClass('shadow').css('cursor', 'pointer');
                }, function () {
                    $(this).removeClass('shadow');
                });

        });
    })();
</script>
