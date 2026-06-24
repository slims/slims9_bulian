<?php
/**
 * @Author: ido
 * @Date: 2016-06-17 14:18:06
 * @Last Modified by: ido
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

$is_edit_mode = false;
$current_pattern = '';
$current_prefix = 'P'; 
$current_suffix = 'S'; 
$current_length_serial = '5'; 

if (isset($_GET['pattern']) && !empty($_GET['pattern'])) {
    $current_pattern = trim($_GET['pattern']);
    $is_edit_mode = true;
    $first_zero_pos = strpos($current_pattern, '0');
    $last_zero_pos = strrpos($current_pattern, '0');

    if ($first_zero_pos !== false && $last_zero_pos !== false && $first_zero_pos <= $last_zero_pos) {
        $current_prefix = substr($current_pattern, 0, $first_zero_pos);
        $current_suffix = substr($current_pattern, $last_zero_pos + 1);
        $length_of_zeros = $last_zero_pos - $first_zero_pos + 1;
        $current_length_serial = (string)$length_of_zeros;
    } else {
         $current_prefix = $current_pattern;
         $current_suffix = '';
         $current_length_serial = '0';
    }
}

$succces_msg = 'Pattern saved';
$failed_msg = 'Failed to save Pattern';
if (isset($_POST['saveData'])) {
  $prefix = trim($dbs->escape_string(strip_tags($_POST['prefix'])));
  $suffix = trim($dbs->escape_string(strip_tags($_POST['suffix'])));
  $length_serial = trim($dbs->escape_string(strip_tags($_POST['length_serial'])));
  $old_pattern = isset($_POST['old_pattern']) ? trim($dbs->escape_string(strip_tags($_POST['old_pattern']))) : ''; 

  if ($length_serial < 2) {
    echo __('Please, fill length serial number more than 2');
  } else {
    $patterns_list = array();
    $zeros = '';
    for ($i=0; $i < $length_serial; $i++) { 
      $zeros .= '0';
    }
    $new_pattern = $prefix.$zeros.$suffix; 

    $pattern_q = $dbs->query('SELECT setting_value FROM setting WHERE setting_name = \'batch_item_code_pattern\'');
    
    $update = false;
    $insert = false;
    
    if ($pattern_q->num_rows > 0) {
      $pattern_d = $pattern_q->fetch_row();
      $val = @unserialize($pattern_d[0]);
      
      if (is_array($val) && count($val) > 0) {
        if (!empty($old_pattern)) {
            $key = array_search($old_pattern, $val);
            if ($key !== false) {
                $val[$key] = $new_pattern;
            } else {
                $val[] = $new_pattern;
            }
        } else {
            $val[] = $new_pattern;
        }

        $patterns_list = array_unique($val);
        $data_serialize = serialize(array_values($patterns_list)); 

        $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'batch_item_code_pattern\'');

      } else {
        $patterns_list[] = $new_pattern;
        $data_serialize = serialize($patterns_list);
        $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'batch_item_code_pattern\'');
      }

    } else {
      $patterns_list[] = $new_pattern;
      $data_serialize = serialize($patterns_list);
      // insert
      $insert = $dbs->query("INSERT INTO setting(setting_name, setting_value) VALUES ('batch_item_code_pattern','$data_serialize')");
    }

    if ($update || $insert) {
        echo $succces_msg;
    } else {
        echo $failed_msg;
    }
  }
  exit();
}

// page title
$page_title = $is_edit_mode ? __('Edit Pattern') : __('Add New Pattern'); 

ob_start();
// create form instance
$form = new simbio_form_table_AJAX('mainFormPattern', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-primary"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell" style="font-weight: bold; white-space: nowrap"';
$form->table_content_attr = 'class="alterCell2"';

// Prefix code pattern
$form->addTextField('text', 'prefix', __('Prefix'), $current_prefix, 'class="form-control"');

// Suffix code pattern
$form->addTextField('text', 'suffix', __('Suffix'), $current_suffix, 'class="form-control"');

// length serial number
$form->addTextField('text', 'length_serial', __('Length serial number'), $current_length_serial, 'class="form-control"');

$form->addHidden('saveData', 'save');

if (isset($_GET['in'])) {
  $form->addHidden('in', trim($_GET['in']));
}

if ($is_edit_mode) {
    $form->addHidden('old_pattern', $current_pattern);
}

// print out the object
echo '<strong>'.($is_edit_mode ? __('Edit Pattern') : __('Add New Pattern')).'</strong>'; // Ubah judul
echo '<hr>';

$initial_zeros = str_repeat('0', (int)$current_length_serial);
$initial_preview_value = $current_prefix . $initial_zeros . $current_suffix;

echo '<strong>'.__('Preview').'</strong>';
echo '<div class="alert alert-primary text-center"><div class="h4 m-0" id="preview">'.$initial_preview_value.'</div></div>';
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

    if (lengthSerial > 0) {
        for (var i = 0; i < lengthSerial; i++) {
            zeros += '0';
        }
    }
    
    $('#preview').text(prefix + zeros + suffix);
});

$('#mainFormPattern').submit(function (e) {
    var uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
    
    if ($('#length_serial').val() < 2) {
        alert("<?php echo __('Please, fill length serial number more than 2'); ?>");
        e.preventDefault();
        return;
    }

    $.ajax({
        url: uri,
        type: 'post',
        data: $( this ).serialize()
    }).done(function (msg) {
        alert(msg);
        jQuery.colorbox.close();
        <?php
        if (isset($_GET['in']) && $_GET['in'] == 'master') {
            echo 'parent.jQuery(\'#mainContent\').simbioAJAX(\''.MWB.'master_file/item_code_pattern.php\');';
        }
        ?>
    });
    e.preventDefault();
});
</script>

<?php
$content = ob_get_clean();
echo $content;
