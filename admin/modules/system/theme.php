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
        utility::jsAlert(__('custom data saved!'));
      } else {
        utility::jsAlert(__('Error saving custom data!'));
      }
    }
  }
  exit();
}

if (isset($_GET['customize'])) {

  include_once SB.$sysconf['template']['dir'].DS.$_GET['theme'].DS.'tinfo.inc.php';

  if (isset($sysconf['template']['option'][$_GET['theme']]) && $_GET['customize'] == 'public') {

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
    $form->submit_button_attr = 'name="updateData" value="'.__('Save Settings').'" class="btn btn-default"';
    // form table attributes
    $form->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
    $form->table_header_attr = 'class="alterCell" style="font-weight: bold;"';
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
        $form->addSelectList($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default);
      } else if ($cfield['type'] == 'checklist') {
        $form->addCheckBox($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default);
      } else if ($cfield['type'] == 'choice') {
        $form->addRadio($cf_dbfield, $cf_label, $cf_data, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default);
      } else if ($cfield['type'] == 'date') {
        $form->addDateField($cf_dbfield, $cf_label, isset($sysconf['template'][$cf_dbfield])?$sysconf['template'][$cf_dbfield]:$cf_default);
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
<fieldset class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Theme Configuration'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Customize theme preferences'); ?>
    </div>
  </div>
</fieldset>
<?php
// public template
// scan template directory
$template_dir = SB.$sysconf['template']['dir'];
$dir = new simbio_directory($template_dir);
$dir_tree = $dir->getDirectoryTree(1);
// sort array by index
ksort($dir_tree);
echo '<div class="row" style="width=100%"><div class="col-md-12">';
foreach ($dir_tree as $dir) {
  $_btn = '<a href="'.MWB.'system/theme.php?customize=public&theme='.$dir.'" data-value="'.$dir.'" class="btn notAJAX btn-default set-public-theme">'.__('Activate').'</a>';
  if ($dir == $sysconf['template']['theme']) {
    $_btn = '<a href="'.MWB.'system/theme.php?customize=public&theme='.$dir.'" data-value="'.$dir.'" class="btn notAJAX btn-success custome-public-theme openPopUp">'.__('Customize').'</a>';
  }
  $output  = '<div class="col-md-3">';
  $output .= '<div class="panel panel-default">';
  $output .= '<div class="panel-heading">';
  $output .= '<h3 class="panel-title">'.__('Public Template').' '.$dir.'</h3>';
  $output .= '</div>';
  $output .= '<div class="panel-body">';
  $output .= '<img style="width: 100%;" src="../template/'.$dir.'/preview.png" />';
  $output .= '</div>';
  $output .= '<div class="panel-footer">'.$_btn.'</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div></div>';

// admin template
// scan admin template directory
$admin_template_dir = SB.'admin'.DS.$sysconf['admin_template']['dir'];
$dir = new simbio_directory($admin_template_dir);
$dir_tree = $dir->getDirectoryTree(1);
// sort array by index
ksort($dir_tree);
echo '<div class="row" style="width=100%"><div class="col-md-12">';
foreach ($dir_tree as $dir) {
  $_btn = '<a href="'.MWB.'system/theme.php?customize=admin&theme='.$dir.'" data-value="'.$dir.'" class="btn notAJAX btn-default set-admin-theme">'.__('Activate').'</a>';
  if ($dir == $sysconf['admin_template']['theme']) {
    $_btn = '<a href="'.MWB.'system/theme.php?customize=admin&theme='.$dir.'" data-value="'.$dir.'" class="btn notAJAX btn-success custome-admin-theme openPopUp">'.__('Customize').'</a>';
  }
  $output  = '<div class="col-md-3">';
  $output .= '<div class="panel panel-default">';
  $output .= '<div class="panel-heading">';
  $output .= '<h3 class="panel-title">'.__('Admin Template').' '.$dir.'</h3>';
  $output .= '</div>';
  $output .= '<div class="panel-body">';
  $output .= '<img style="width: 100%;" src="admin_template/'.$dir.'/preview.png" />';
  $output .= '</div>';
  $output .= '<div class="panel-footer">'.$_btn.'</div>';
  $output .= '</div>';
  $output .= '</div>';
  echo $output;
}
echo '</div></div>';
?>

<script type="text/javascript">
  (function() {
    $(document).ready(function() {
      $(document).on('click', '.set-public-theme', function(e) {
        e.preventDefault();
        var current = $(this);
        var theme = current.attr('data-value');
        $.ajax({
          url: '<?php echo $_SERVER['PHP_SELF']; ?>',
          method: 'POST',
          data: {theme: 'public', name: theme}
        }).done(function(msg) {
          $('a.btn-success.custome-public-theme').removeClass('btn-success custome-public-theme openPopUp').addClass('btn-default set-public-theme').text('<?php echo __('Activate') ?>');
          current.removeClass('btn-default set-public-theme').addClass('btn-success custome-public-theme openPopUp').text('<?php echo __('Customize') ?>');
        });
      });

      $(document).on('click', '.set-admin-theme', function(e) {
        e.preventDefault();
        var current = $(this);
        var theme = current.attr('data-value');
        $.ajax({
          url: '<?php echo $_SERVER['PHP_SELF']; ?>',
          method: 'POST',
          data: {theme: 'admin', name: theme}
        }).done(function(msg) {
          $('a.btn-success.custome-admin-theme').removeClass('btn-success custome-admin-theme openPopUp').addClass('btn-default set-admin-theme').text('<?php echo __('Activate') ?>');
          current.removeClass('btn-default set-admin-theme').addClass('btn-success custome-admin-theme openPopUp').text('<?php echo __('Customize') ?>');
          window.location.href = 'index.php';
        });
      });
    });
  })();
</script>
