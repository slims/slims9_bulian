<?php
/**
 * @author Heru Subekti
 * @email heroe.soebekti@gmail.com
 * @create date 2026-09-24 10:01:45
 * @modify date 2026-09-24 10:39:35
 * @license GPLv3
 */

defined('INDEX_AUTH') or die('Direct access is not allowed!');

require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';

$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

$mod = isset($_GET['mod']) ? $_GET['mod'] : 'master_file';
$plugin_id = isset($_GET['id']) ? $_GET['id'] : '';

// Base URL khusus plugin container SLiMS
$base_uri = 'plugin_container.php?mod=' . $mod . ($plugin_id ? '&id=' . $plugin_id : '');



/* RECORD OPERATION: SAVE & UPDATE */
if (isset($_POST['saveData']) && $can_write) {
    $updateRecordID = isset($_POST['updateRecordID']) ? (int)$_POST['updateRecordID'] : 0;
    $custom_role_name = isset($_POST['custom_role_name']) ? trim(strip_tags($_POST['custom_role_name'])) : '';

    if (empty($custom_role_name)) {
        utility::jsToastr(__('Custom Authority'), __('Authority Name can\'t be empty'), 'error');
        exit();
    }

    $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = 'authority_level'");
    $saved_roles = [];
    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $unserialized = @unserialize($row['setting_value']);
        if (is_array($unserialized)) {
            $saved_roles = $unserialized;
        }
    }

    if ($updateRecordID > 0) {
        $saved_roles[$updateRecordID] = $custom_role_name;
        $msg = __('Authority Data Successfully Updated');
    } else {
        $next_id = 11;
        if (!empty($saved_roles)) {
            $max_key = max(array_keys($saved_roles));
            if ($max_key >= 11) {
                $next_id = $max_key + 1;
            }
        }
        $saved_roles[$next_id] = $custom_role_name;
        $msg = __('New Authority Data Successfully Saved');
    }

    try {
        $sql_op = new simbio_dbop($dbs);
        $serialized = serialize($saved_roles);
        
        if ($query && $query->num_rows > 0) {
            $sql_op->update('setting', ['setting_value' => $serialized], "setting_name='authority_level'");
        } else {
            $sql_op->insert('setting', ['setting_name' => 'authority_level', 'setting_value' => $serialized]);
        }

        utility::jsToastr(__('Custom Authority'), $msg, 'success');
        echo '<script type="text/javascript">parent.jQuery("#mainContent").simbioAJAX("' . $base_uri . '");</script>';
    } catch (\Exception $e) {
        utility::jsToastr(__('Custom Authority'), __('Authority Data FAILED to Save. Please Contact System Administrator') . "\nDEBUG : " . $e->getMessage(), 'error');
    }
    exit();
}

/* RECORD OPERATION: DELETE */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['itemID']) && !empty($_POST['itemID']) && isset($_POST['itemAction'])) {
    if (!($can_read && $can_write)) {
        die();
    }
    
    $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = 'authority_level'");
    $saved_roles = [];
    if ($query && $query->num_rows > 0) {
        $row = $query->fetch_assoc();
        $unserialized = @unserialize($row['setting_value']);
        if (is_array($unserialized)) {
            $saved_roles = $unserialized;
        }
    }

    foreach ($_POST['itemID'] as $item_id) {
        if (isset($saved_roles[$item_id])) {
            unset($saved_roles[$item_id]);
        }
    }

    try {
        $sql_op = new simbio_dbop($dbs);
        $serialized = serialize($saved_roles);
        $sql_op->update('setting', ['setting_value' => $serialized], "setting_name='authority_level'");
        echo __('All Data Successfully Deleted');
    } catch (\Exception $e) {
        echo __('Some or All Data NOT deleted successfully!\nPlease contact system administrator');
    }
    exit();
}

$add_url = $base_uri . '&action=detail';
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Custom Authority Level'); ?></h2>
    </div>
    <div class="sub_section">    
      <div class="btn-group">
        <a href="<?php echo $add_url; ?>" class="btn btn-primary ajax-load"><?php echo __('Add New Authority'); ?></a>
        <a href="<?php echo $base_uri; ?>" class="btn btn-default ajax-load"><?php echo __('Authority List'); ?></a>
    </div>

    </div>
</div>
</div>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'detail') {
    if (!$can_write) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }

    $edit_id = isset($_GET['id_item']) ? (int)$_GET['id_item'] : (isset($_GET['id']) && $_GET['id'] !== $plugin_id ? (int)$_GET['id'] : 0);
    $role_name = '';

    if ($edit_id > 0) {
        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = 'authority_level'");
        if ($query && $query->num_rows > 0) {
            $row = $query->fetch_assoc();
            $unserialized = @unserialize($row['setting_value']);
            if (is_array($unserialized) && isset($unserialized[$edit_id])) {
                $role_name = $unserialized[$edit_id];
            }
        }
    }

    $form_action_url = $base_uri . '&action=detail' . ($edit_id > 0 ? '&id_item='.$edit_id : '');
    $form = new simbio_form_table_AJAX('mainForm', $form_action_url, 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    if ($edit_id > 0) {
        $form->edit_mode = true;
        $form->record_id = $edit_id;
        $form->record_title = $role_name;
        echo '<div class="infoBox">'.__('You are going to edit authority data').' : <b>'.$role_name.'</b> (ID: '.$edit_id.')</div>';
        $form->addHidden('updateRecordID', $edit_id);
    } else {
        echo '<div class="infoBox">'.__('Adding new authority data to the system.').'</div>';
        $form->addHidden('updateRecordID', 0);
    }
    
    $form->addTextField('text', 'custom_role_name', __('Authority Level').'*', $role_name, ' class="form-control col-12" required', __('Example: Supervisor, Editor, etc.'));
    
    echo $form->printOut();

} else {
    /* MAIN CONTENT TABLE */
    $table = new simbio_table();
    $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';

    echo '<div class="p-3">
    <input value="'.__('Delete Selected Data').'" class="button btn btn-danger btn-delete" type="button"> 
    <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
    <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button"></div>';

    $table->setHeader(array(__('DELETE'), __('EDIT'), __('ID'), __('Authority Level'), __('Source')));
    $table->table_header_attr = 'class="alterCell font-weight-bold"';

    $row = 1;

    $saved_roles = [];
    if ($dbs) {
        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = 'authority_level'");
        if ($query && $query->num_rows > 0) {
            $q_row = $query->fetch_assoc();
            $unserialized = @unserialize($q_row['setting_value']);
            if (is_array($unserialized)) {
                $saved_roles = $unserialized;
            }
        }
    }

    $all_authorities = [];
    if (isset($sysconf['authority_level']) && is_array($sysconf['authority_level'])) {
        $all_authorities = $sysconf['authority_level'];
    }
    foreach ($saved_roles as $s_id => $s_label) {
        if (!isset($all_authorities[$s_id])) {
            $all_authorities[$s_id] = $s_label;
        }
    }

    foreach ($all_authorities as $auth_id => $auth_label) {
        $is_custom = ($auth_id >= 11 || array_key_exists($auth_id, $saved_roles));

        if ($is_custom) {
            $cb = '<input type="checkbox" name="itemID[]" value="'.$auth_id.'">';
            $edit_url = $base_uri . '&action=detail&id_item='.$auth_id;
            $link = '<a href="'.$edit_url.'" class="editLink ajax-load" title="'.__('Edit Authority').'"></a>';
            $source = '<span class="badge badge-success">'.__('Custom / Additional').'</span>';
        } else {
            $cb = '<input type="checkbox" disabled title="'.__('System default cannot be deleted').'">';
            $link = '-';
            $source = '<span class="badge badge-info">'.__('Default').'</span>';
        }

        $table->appendTableRow(array($cb, $link, $auth_id, $auth_label, $source));
        $table->setCellAttr($row, 0, 'class="alterCell text-center" valign="top" style="width: 5px;"');
        $table->setCellAttr($row, 1, 'class="alterCell2 text-center" valign="top" style="width: 20px;"');
        $table->setCellAttr($row, 2, 'class="alterCell" valign="top" style="width: 10%;"');
        $table->setCellAttr($row, 3, 'class="alterCell" valign="top" style="width: auto; text-align: left;"');
        $table->setCellAttr($row, 4, 'class="alterCell" valign="top" style="width: 20%;"');
        $row++;
    }

    echo $table->printTable();
}
?>

<script type="text/javascript">
(function() {
    var $ = parent.jQuery || jQuery;
    var main_uri = '<?php echo $base_uri; ?>';

    $('#mainContent').off('click', 'a.ajax-load').on('click', 'a.ajax-load', function(e) {
        e.preventDefault();
        var targetUrl = $(this).attr('href');
        $('#mainContent').simbioAJAX(targetUrl);
    });

    $('.btn-delete').off('click').on('click', function (e) {
        var data = [];
        $("input[name='itemID[]']:checked").each(function() {
           data.push($(this).val());
        });
        if (data.length === 0) {
            alert("<?php echo __('Please select data to delete!'); ?>");
            return;
        }
        if (!confirm("<?php echo __('Are you sure you want to delete the selected authority data?'); ?>")) {
            return;
        }

        $.ajax({
            url: main_uri,
            type: 'post',
            data: { itemID: data, itemAction: true }
        })
        .done(function (msg) {
             alert(msg);
             $('#mainContent').simbioAJAX(main_uri);
        })
        .fail(function() {
            alert("<?php echo __('An error occurred while deleting data.'); ?>");
        });
    });

    $(".uncheck-all").off('click').on('click', function(e) {
        e.preventDefault();
        $('input[name="itemID[]"]').prop('checked', false);
    });

    $(".check-all").off('click').on('click', function(e) {
        e.preventDefault();
        $('input[name="itemID[]"]').prop('checked', true);
    });
})();
</script>