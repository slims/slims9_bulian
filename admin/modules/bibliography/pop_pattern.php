<?php
/**
 * @Author: ido
 * @Date:   2016-06-17 14:18:06
 * @Last Modified by:   ido
 * @Last Modified time: 2016-06-17 15:20:04
 */


// key to authenticate
define('INDEX_AUTH', '1');
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
do_checkIP('smc-bibliography');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_FILE/simbio_directory.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_write = utility::havePrivilege('bibliography', 'w');
if (!$can_write) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}
$succces_msg = 'Pattern saved';
$failed_msg = 'Failed to save Pattern';
if (isset($_POST['saveData'])) {
  $prefix = trim($dbs->escape_string(strip_tags($_POST['prefix'])));
  $suffix = trim($dbs->escape_string(strip_tags($_POST['suffix'])));
  $length_serial = trim($dbs->escape_string(strip_tags($_POST['length_serial'])));

  if ($length_serial <= 2) {
    echo __('Please, fill length serial number more than 2');
  } else {
    // get database setting
    $patterns = array();
    $zeros = '';
    for ($i=0; $i < $length_serial; $i++) { 
      $zeros .= '0';
    }
    $patterns[] = $prefix.$zeros.$suffix;
    // get pattern from database
    $pattern_q = $dbs->query('SELECT setting_value FROM setting WHERE setting_name = \'batch_item_code_pattern\'');
    if ($pattern_q->num_rows > 0) {
      $pattern_d = $pattern_q->fetch_row();
      $val = @unserialize($pattern_d[0]);
      if (!empty($val) || count($val) == 0) {
        foreach ($val as $v) {
          $patterns[] = $v;
        }
        $patterns = array_unique($patterns);
        $data_serialize = serialize($patterns);
        // update
        $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'batch_item_code_pattern\'');
        if ($update) {
          echo $succces_msg;
        } else {
          echo $failed_msg;
        }
      } else {
        $data_serialize = serialize($patterns);
        // insert
        $insert = $dbs->query("INSERT INTO setting(setting_name, setting_value) VALUES ('batch_item_code_pattern','$data_serialize')");
        if ($insert) {
          echo $succces_msg;
        } else {
          echo $failed_msg;
        }
      }
    } else {
      $data_serialize = serialize($patterns);
      // insert
      $insert = $dbs->query("INSERT INTO setting(setting_name, setting_value) VALUES ('batch_item_code_pattern','$data_serialize')");
      if ($insert) {
        echo $succces_msg;
      } else {
        echo $failed_msg;
      }
    }
  }
  exit();
}

// page title
$page_title = 'Add New Pattern';

ob_start();
// create form instance
$form = new simbio_form_table_AJAX('mainFormPattern', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-primary"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold; white-space: nowrap"';
$form->table_content_attr = 'class="alterCell2"';

// Prefix code pattern
$form->addTextField('text', 'prefix', __('Prefix'), 'P', 'class="form-control"');

// Suffix code pattern
$form->addTextField('text', 'suffix', __('Suffix'), 'S', 'class="form-control"');

// length serial number
$form->addTextField('text', 'length_serial', __('Length serial number'), '5', 'class="form-control"');

$form->addHidden('saveData', 'save');

if (isset($_GET['in'])) {
  $form->addHidden('in', trim($_GET['in']));
}

// print out the object
echo '<strong>'.__('Add New Pattern').'</strong>';
echo '<hr>';
// preview patternt
echo '<strong>'.__('Preview').'</strong>';
echo '<div class="alert alert-primary text-center"><div class="h4 m-0" id="preview">P00000S</div></div>';
echo $form->printOut();

?>
<script type="text/javascript">
  $('#mainFormPattern').keyup(function (e) {
    e.preventDefault();
    var prefix, suffix, lengthSerial, zeros;
    prefix = $('#prefix').val();
    suffix = $('#suffix').val();
    lengthSerial = $('#length_serial').val();
    zeros = '';
    for (var i = lengthSerial - 1; i >= 0; i--) {
      zeros += '0';
    }
    $('#preview').text(prefix + zeros + suffix);
  });
  $('#mainFormPattern').submit(function (e) {
    var uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
    $.ajax({
      url: uri,
      type: 'post',
      data: $( this ).serialize()
    }).done(function (msg) {
      alert(msg);
      var pattern = $('#preview').text();
      $('#itemCodePattern').append('<option value="'+ pattern +'">'+ pattern +'</option>');
      jQuery.colorbox.close();
      <?php
      if (isset($_GET['in']) && $_GET['in'] == 'master') {
        echo 'parent.jQuery(\'#mainContent\').simbioAJAX(\''.MWB.'master_file/item_code_pattern.php\');';
      }
      ?>
    });
    event.preventDefault();
  });
</script>
<?php
$content = ob_get_clean();
echo $content;
// include the page template
//require SB.'/admin/'.$sysconf['admin_template']['dir'].'/notemplate_page_tpl.php';