<?php

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

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

if (isset($_POST['theme'])) {
  if ($_POST['theme'] == 'public') {
    // template
    $template['theme'] = $_POST['name'];
    $template['css'] = str_replace($sysconf['template']['theme'], $template['theme'], $sysconf['template']['css']);
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($template)).'\' WHERE setting_name=\'template\'');
  } else {
    // admin template
    $admin_template['theme'] = $_POST['name'];
    $admin_template['css'] = str_replace($sysconf['admin_template']['theme'], $admin_template['theme'], $sysconf['admin_template']['css']);
    $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($admin_template)).'\' WHERE setting_name=\'admin_template\'');
  }
  exit();
}

if (isset($_POST['updateData'])) {

  include_once SB.$sysconf['template']['dir'].DS.$_POST['themeDir'].DS.'tinfo.inc.php';

  if (isset($sysconf['template']['option'][$_POST['themeDir']])) {
    if (is_array($sysconf['template']['option'][$_POST['themeDir']]) && $sysconf['template']['option'][$_POST['themeDir']]) {
      // initialize template arrays
      $template = array('theme' => $sysconf['template']['theme'], 'css' => $sysconf['template']['css']);
      $admin_template = array('theme' => $sysconf['admin_template']['theme'], 'css' => $sysconf['admin_template']['css']);

      foreach ($sysconf['template']['option'][$_POST['themeDir']] as $fid => $cfield) {
        // custom field data
        $cf_dbfield = $cfield['dbfield'];
        if (isset($_POST[$cf_dbfield])) {
          $template[$cf_dbfield] = $dbs->escape_string(strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']));
        }
      }
      if ($_POST['themeType'] == 'public') {
        $_update = $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($template)).'\' WHERE setting_name=\'template\'');
      } else {
        $_update = $dbs->query('UPDATE setting SET setting_value=\''.$dbs->escape_string(serialize($admin_template)).'\' WHERE setting_name=\'admin_template\'');
      }
      if ($_update) {
        utility::jsAlert(__('Custom data saved!'));
      } else {
        utility::jsAlert(__('Error saving custom data!'));
      }
    }
  }
  exit();
}

if (isset($_GET['customize'])) {

  if($_GET['customize'] == 'admin') {
    include_once SB.$_GET['customize'].DS.'admin_template'.DS.$_GET['theme'].DS.'tinfo.inc.php';
  } else {
    include_once SB.$sysconf['template']['dir'].DS.$_GET['theme'].DS.'tinfo.inc.php';
  }

  if (isset($sysconf['template']['option'][$_GET['theme']]) && $_GET['customize'] == 'public') {

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
    $form->submit_button_attr = 'name="updateData" value="'.__('Save Settings').'" class="btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    foreach ($sysconf['template']['option'][$_GET['theme']] as $fid => $cfield) {
      // custom field properties
      $cf_dbfield = $cfield['dbfield'];
      $cf_label = $cfield['label'];
      $cf_default = $cfield['default'];
      $cf_data = (isset($cfield['data']) && $cfield['data'])?$cfield['data']:array();
      $form->addHidden('themeDir', $_GET['theme']);
      $form->addHidden('themeType', $_GET['customize']);
      // custom field processing
      if (in_array($cfield['type'], array('text', 'longtext', 'numeric'))) {
        $cf_max = isset($cfield['max'])?$cfield['max']:'200';
        $cf_width = isset($cfield['width'])?$cfield['width']:'50';
        $form->addTextField( ($cfield['type'] == 'longtext')?'textarea':'text', $cf_dbfield, $cf_label, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default, 'style="width: '.$cf_width.'%;" maxlength="'.$cf_max.'"');
      } else if ($cfield['type'] == 'dropdown') {
        $form->addSelectList($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default,'class="form-control"');
      } else if ($cfield['type'] == 'checklist') {
        $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default,'class="form-control"');
      } else if ($cfield['type'] == 'choice') {
        $form->addRadio($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default,'class="form-control"');
      } else if ($cfield['type'] == 'date') {
        $form->addDateField($cf_dbfield, $cf_label, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default,'class="form-control"');
      }
    }

    // print out the form object
    $content = $form->printOut();

  } else {
    $content = __('This theme not customizable');
  }
  require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';
  exit();
}

?>
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
// public template
// scan template directory
$template_dir = SB.$sysconf['template']['dir'];
$dir = new simbio_directory($template_dir);
$dir_tree = $dir->getDirectoryTree(1);
// sort array by index
ksort($dir_tree);
echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="col-12 my-3">
        <h5 class="font-weight-bold">'.__('Public Template').'</h5>
      </div>';

foreach ($dir_tree as $dir) {
  $_btn = '<a href="'.MWB.'system/theme.php?customize=public&theme='.$dir.'" data-value="'.$dir.'" class="btn btn-default notAJAX setPublicTheme">'.__('Activate').'</a>';
  if ($dir == $sysconf['template']['theme']) {
    $_btn = '<a href="'.MWB.'system/theme.php?customize=public&theme='.$dir.'" data-value="'.$dir.'" title="'.__('Theme Configuration') .'" class="btn btn-success customePublicTheme notAJAX openPopUp">'.__('Customize').'</a>';
  }

  $output  = '<div class="col-3">';
  $output .= '<div class="card border-0 mb-4">';
  $output .= '<div class="card-body">';
  $output .= '<div class="mb-2 font-weight-bold">'.ucwords($dir).'</div>';
  $output .= '<img class="card-img-top rounded" src="../template/'.$dir.'/preview.png" height="150" />';
  $output .= '</div>';
  $output .= '<div class="card-footer border-0">'.$_btn.'</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div>';

// admin template
// scan admin template directory
$admin_template_dir = SB.'admin'.DS.$sysconf['admin_template']['dir'];
$dir = new simbio_directory($admin_template_dir);
$dir_tree = $dir->getDirectoryTree(1);
// sort array by index
ksort($dir_tree);
echo '<div class="row">';
echo '<div class="col-12 my-3">
        <h5 class="font-weight-bold">'.__('Admin Template').'</h5>
      </div>';
foreach ($dir_tree as $dir) {
  $_btn = '<a href="'.MWB.'system/theme.php?customize=admin&theme='.$dir.'" data-value="'.$dir.'" class="btn btn-default notAJAX setAdminTheme">'.__('Activate').'</a>';
  if ($dir == $sysconf['admin_template']['theme']) {
    $_btn = '<a href="'.MWB.'system/theme.php?customize=admin&theme='.$dir.'" data-value="'.$dir.'" title="'.__('Admin Theme Configuration').'" class="btn btn-success notAJAX customeAdminTheme openPopUp">'.__('Customize').'</a>';
  }
  $output  = '<div class="col-3">';
  $output .= '<div class="card border-0 mb-4">';
  $output .= '<div class="card-body">';
  $output .= '<div class="mb-2 font-weight-bold">'.ucwords($dir).'</div>';
  $output .= '<img class="card-img-top rounded" src="admin_template/'.$dir.'/preview.png" height="150" />';
  $output .= '</div>';
  $output .= '<div class="card-footer border-0">'.$_btn.'</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div>';
echo '</div>';
?>

<script type="text/javascript">
  (function() {
    $(document).ready(function() {
      $(document).on('click', '.setPublicTheme', function(e) {
        e.preventDefault();
        var current = $(this);
        var theme = current.attr('data-value');
        $.ajax({
          url: '<?php echo $_SERVER['PHP_SELF']; ?>',
          method: 'POST',
          data: {theme: 'public', name: theme}
        }).done(function(msg) {
          $('a.btn-success.customePublicTheme').removeClass('btn-success customePublicTheme openPopUp').addClass('btn-default setPublicTheme').text('<?php echo __('Activate') ?>');
          current.removeClass('btn-default setPublicTheme').addClass('btn-success customePublicTheme openPopUp').text('<?php echo __('Customize') ?>');
        });
      });

      $(document).on('click', '.setAdminTheme', function(e) {
        e.preventDefault();
        var current = $(this);
        var theme = current.attr('data-value');
        $.ajax({
          url: '<?php echo $_SERVER['PHP_SELF']; ?>',
          method: 'POST',
          data: {theme: 'admin', name: theme}
        }).done(function(msg) {
          $('a.btn-success.customeAdminTheme').removeClass('btn-success customeAdminTheme openPopUp').addClass('btn-default setAdminTheme').text('<?php echo __('Activate') ?>');
          current.removeClass('btn-default setAdminTheme').addClass('btn-success customeAdminTheme openPopUp').text('<?php echo __('Customize') ?>');
          window.location.href = 'index.php';
        });
      });

      $('.card').hover(
      function() {
        $(this).addClass('shadow').css('cursor', 'pointer'); 
      }, function() {
        $(this).removeClass('shadow');
      });
    });
  })();
</script>
