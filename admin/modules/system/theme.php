<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-23 12:35
 * @File name           : theme.php
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


if (!function_exists('addOrUpdateSetting')) {
  function addOrUpdateSetting($name, $value)
  {
    global $dbs;
    $sql_op = new simbio_dbop($dbs);
    foreach ($value as $key => $val) {
      $settings[$key] = trim(str_replace(array('\n', '\r','\t', '\\'), '', $val));
    }
    $data['setting_value'] = $dbs->escape_string(serialize($settings));

    // save personalized user template
    if($name == 'admin_template'){
      $_d['admin_template'] = serialize($value);
      $update = $sql_op->update('user', $_d, "user_id=".$_SESSION['uid']);
      if (!$update) return $dbs->error;
    }
    else{
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
    }
    return true;
  }
}

// save theme change
if (isset($_POST['theme'])) {
  $data['theme'] = utility::filterData('name', 'post', true, true, true);
  if ($_POST['theme'] == 'public') {
    $data['css'] = str_replace($sysconf['template']['theme'], $data['theme'], $sysconf['template']['css']);
    addOrUpdateSetting('template', $data);
  } else {
    $data['css'] = str_replace($sysconf['admin_template']['theme'], $data['theme'], $sysconf['admin_template']['css']);
    addOrUpdateSetting('admin_template', $data);
  }
  exit();
}

// save action
if (isset($_POST['updateData'])) {
  $theme_type = utility::filterData('themeType', 'post', true, true, true);
  $theme_dir = utility::filterData('themeDir', 'post', true, true, true);
  // include tinfo.inc.php
  if ($theme_type == 'public') {
    $path = SB . $sysconf['template']['dir'] . '/' . $theme_dir . '/tinfo.inc.php';
    $theme_key = 'template';
  } else {
    $path = SB . 'admin/' . $sysconf['admin_template']['dir'] . '/' . $theme_dir . '/tinfo.inc.php';
    $theme_key = 'admin_template';
  }

  if (file_exists($path)) {
    include_once $path;
    if (isset($sysconf[$theme_key]['option'][$theme_dir])) {
      $data = ['theme' => $sysconf[$theme_key]['theme'], 'css' => $sysconf[$theme_key]['css']];
      foreach ($sysconf[$theme_key]['option'][$theme_dir] as $fid => $cfield) {
        $cf_dbfield = $cfield['dbfield'];
        if (isset($_POST[$cf_dbfield])) {
          $data[$cf_dbfield] = $dbs->escape_string(strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']));
        }
      }
      $update = addOrUpdateSetting($theme_key, $data);
      if ($update !== true) {
        toastr(__('Error saving custom data!') . ' ' . $update)->error();
      } else {
        toastr(__('Custom data saved! Reload the page to see changes.'))->success();
      }
    }
  } else {
    toastr(__('This theme not customizable. ' . $path))->warning();
  }

  exit();
}

// show form
if (isset($_GET['customize'])) {
  ob_start();
  $theme_type = utility::filterData('customize', 'get', true, true, true);
  $theme_dir = utility::filterData('theme', 'get', true, true, true);
  // include tinfo.inc.php
  if ($theme_type == 'public') {
    $path = SB . $sysconf['template']['dir'] . '/' . $theme_dir . '/tinfo.inc.php';
    $theme_key = 'template';
  } else {
    $path = SB . 'admin/' . $sysconf['admin_template']['dir'] . '/' . $theme_dir . '/tinfo.inc.php';
    $theme_key = 'admin_template';
  }

  if (file_exists($path)) {
    include_once $path;
    if (isset($sysconf[$theme_key]['option'][$theme_dir])) {
      utility::loadSettings($dbs);
      utility::loadUserTemplate($dbs,$_SESSION['uid']);
      // create new instance
      $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
      $form->submit_button_attr = 'name="updateData" value="' . __('Save Settings') . '" class="btn btn-default"';
      // form table attributes
      $form->table_attr = 'id="dataList" class="s-table table"';
      $form->table_header_attr = 'class="alterCell font-weight-bold"';
      $form->table_content_attr = 'class="alterCell2"';

      foreach ($sysconf[$theme_key]['option'][$_GET['theme']] as $fid => $cfield) {
        // custom field properties
        $cf_dbfield = $cfield['dbfield'];
        $cf_label = $cfield['label'];
        $cf_default = $cfield['default'];
        $cf_class = $cfield['class']??'';
        $cf_data = (isset($cfield['data']) && $cfield['data']) ? $cfield['data'] : array();
        $cf_width = isset($cfield['width']) ? $cfield['width'] : '50';
        $form->addHidden('themeDir', $_GET['theme']);
        $form->addHidden('themeType', $_GET['customize']);
        // custom field processing
        if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
          $cf_max = isset($cfield['max']) ? $cfield['max'] : '200';
          $form->addTextField(($cfield['type'] == 'longtext') ? 'textarea' : 'text', $cf_dbfield, $cf_label, isset($sysconf[$theme_key][$cf_dbfield]) ? $sysconf[$theme_key][$cf_dbfield] : $cf_default, 'class="form-control '.$cf_class.'" style="width: ' . $cf_width . '%;" maxlength="' . $cf_max . '"');
        } else if ($cfield['type'] == 'dropdown') {
          $value = $cf_default;
          if (isset($sysconf[$theme_key][$cf_dbfield])) {
              $value = $sysconf[$theme_key][$cf_dbfield];
              if (gettype($cf_default) == 'integer') $value = intval($sysconf[$theme_key][$cf_dbfield]);
          }
          $form->addSelectList($cf_dbfield, $cf_label, $cf_data, $value, 'class="form-control"');
        } else if ($cfield['type'] == 'checklist') {
          $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, isset($sysconf[$theme_key][$cf_dbfield]) ? $sysconf[$theme_key][$cf_dbfield] : $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'choice') {
          $form->addRadio($cf_dbfield, $cf_label, $cf_data, isset($sysconf[$theme_key][$cf_dbfield]) ? $sysconf[$theme_key][$cf_dbfield] : $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'date') {
          $form->addDateField($cf_dbfield, $cf_label, isset($sysconf[$theme_key][$cf_dbfield]) ? $sysconf[$theme_key][$cf_dbfield] : $cf_default, 'class="form-control"');
        } else if ($cfield['type'] == 'ckeditor') {
          if (!isset($ckCount)) $ckCount = 0;
          $form->addAnything($cf_label, '<div id="container'.$ckCount.'" data-field="'.$cf_dbfield.'"><div id="ckeditor-toolbar'.$ckCount.'"></div><div id="ckeditor-content'.$ckCount.'">' . (isset($sysconf[$theme_key][$cf_dbfield]) ? $sysconf[$theme_key][$cf_dbfield] : $cf_default) . '</div></div>');
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
  $js .= "<script>createMultiEditor('{$ckCount}', '#mainForm')</script>";
  endif;
  require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
  exit();
}

?>
<style type="text/css"></style>
<div class="menuBox">
    <div class="menuBoxInner systemIcon">
        <div class="per_title">
            <h2><?php echo __('Theme Configuration'); ?></h2>
        </div>
        <div class="infoBox">
          <?php echo __('Customize theme preferences'); ?>
        </div>
    </div>
</div>
<?php

echo '<div class="container-fluid">';

// public template
// scan template directory
if($_SESSION['uid'] == '1'){
  $template_dir = SB . $sysconf['template']['dir'];
  $dir = new simbio_directory($template_dir);
  $dir_tree = $dir->getDirectoryTree(1);
  // sort array by index
  ksort($dir_tree);
  echo '<div class="row">';
  echo '<div class="col-12 my-3">
          <h5 class="font-weight-bold">' . __('Public Template') . '</h5>
        </div>';

  foreach ($dir_tree as $dir) {
    $_btn = '<a href="' . MWB . 'system/theme.php?customize=public&theme=' . $dir . '" data-value="' . $dir . '" class="btn btn-default notAJAX setPublicTheme">' . __('Activate') . '</a>';
    if ($dir == $sysconf['template']['theme']) {
      $_btn = '<a href="' . MWB . 'system/theme.php?customize=public&theme=' . $dir . '" data-value="' . $dir . '" title="' . __('Theme Configuration') . '" class="btn btn-success customePublicTheme notAJAX openPopUp" width="600" height="500">' . __('Customize') . '</a>';
    }

    $output = '<div class="col-3">';
    $output .= '<div class="card border-0 mb-4">';
    $output .= '<div class="card-body">';
    $output .= '<div class="mb-2 font-weight-bold">' . ucwords($dir) . '</div>';
    $preview = file_exists(SB.'template/'.$dir.'/preview.jpg') ? 'preview.jpg' : 'preview.png';
    $output .= '<img class="card-img-top rounded" src="'.SWB.'template/'. $dir . '/'.$preview.'" height="150" />';
    $output .= '</div>';
    $output .= '<div class="card-footer border-0">' . $_btn . '</div>';
    $output .= '</div>';
    $output .= '</div>';
    echo $output;
  }
  echo '</div>';
}

// admin template
utility::loadUserTemplate($dbs,$_SESSION['uid']);
// scan admin template directory
$admin_template_dir = SB . 'admin' . DS . $sysconf['admin_template']['dir'];
$dir = new simbio_directory($admin_template_dir);
$dir_tree = $dir->getDirectoryTree(1);
// sort array by index
ksort($dir_tree);
echo '<div class="row">';
echo '<div class="col-12 my-3">
        <h5 class="font-weight-bold">' . __('Admin Template') . '</h5>
      </div>';
foreach ($dir_tree as $dir) {
  $_btn = '<a href="' . MWB . 'system/theme.php?customize=admin&theme=' . $dir . '" data-value="' . $dir . '" class="btn btn-default notAJAX setAdminTheme">' . __('Activate') . '</a>';
  if ($dir == $sysconf['admin_template']['theme']) {
    $_btn = '<a href="' . MWB . 'system/theme.php?customize=admin&theme=' . $dir . '" data-value="' . $dir . '" title="' . __('Admin Theme Configuration') . '" class="btn btn-success notAJAX customeAdminTheme openPopUp">' . __('Customize') . '</a>';
  }
  $output = '<div class="col-3">';
  $output .= '<div class="card border-0 mb-4">';
  $output .= '<div class="card-body">';
  $output .= '<div class="mb-2 font-weight-bold">' . ucwords($dir) . '</div>';
  $preview = file_exists(SB.'admin/admin_template/'.$dir.'/preview.jpg') ? 'preview.jpg' : 'preview.png';
  $output .= '<img class="card-img-top rounded" src="'.SWB.'admin/admin_template/'. $dir . '/'.$preview.'" height="150" />';
  $output .= '</div>';
  $output .= '<div class="card-footer border-0">' . $_btn . '</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div>';
echo '</div>';
?>

<script type="text/javascript">
    (function () {
        $(document).ready(function () {
            $(document).on('click', '.setPublicTheme', function (e) {
                e.preventDefault();
                var current = $(this);
                var theme = current.attr('data-value');
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    method: 'POST',
                    data: {theme: 'public', name: theme}
                }).done(function (msg) {
                    $('a.btn-success.customePublicTheme').removeClass('btn-success customePublicTheme openPopUp').addClass('btn-default setPublicTheme').text('<?php echo __('Activate') ?>');
                    current.removeClass('btn-default setPublicTheme').addClass('btn-success customePublicTheme openPopUp').text('<?php echo __('Customize') ?>');
                });
            });

            $(document).on('click', '.setAdminTheme', function (e) {
                e.preventDefault();
                var current = $(this);
                var theme = current.attr('data-value');
                $.ajax({
                    url: '<?php echo $_SERVER['PHP_SELF']; ?>',
                    method: 'POST',
                    data: {theme: 'admin', name: theme}
                }).done(function (msg) {
                    $('a.btn-success.customeAdminTheme').removeClass('btn-success customeAdminTheme openPopUp').addClass('btn-default setAdminTheme').text('<?php echo __('Activate') ?>');
                    current.removeClass('btn-default setAdminTheme').addClass('btn-success customeAdminTheme openPopUp').text('<?php echo __('Customize') ?>');
                    window.location.href = 'index.php';
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
