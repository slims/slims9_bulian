<?php
/**
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
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

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-masterfile');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('master_file', 'r');
$can_write = utility::havePrivilege('master_file', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* RECORD OPERATION */
$succces_msg = 'Pattern Deleted!';
$failed_msg = 'Pattern Delete Failed!';
if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $pattern_q = $dbs->query('SELECT setting_value FROM setting WHERE setting_name = \'batch_item_code_pattern\'');
    $pattern_d = $pattern_q->fetch_row();
    $patterns = @unserialize($pattern_d[0]);
    foreach ($_POST['itemID'] as $pattern_id) {
        $key = array_search(trim($pattern_id), $patterns);
        if ($key !== false) {
             unset($patterns[$key]);
        }
    }
    // Re-index dan serialize array
    $data_serialize = serialize(array_values($patterns));

    // update
    $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'batch_item_code_pattern\'');
    if ($update) {
      echo $succces_msg;
    } else {
      echo $failed_msg;
    }
    exit();
}


/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Item Code Pattern'); ?></h2>
  </div>
    <div class="sub_section">
      <div class="btn-group">
        <a href="<?php echo MWB; ?>master_file/item_code_pattern.php" class="btn btn-default"><?php echo __('Pattern List'); ?></a>
        <a href="<?php echo MWB; ?>bibliography/pop_pattern.php?in=master" height="500px" class="s-btn btn btn-default notAJAX openPopUp notIframe" title="<?php echo __('Add new pattern') ?>"><?php echo __('Add new pattern'); ?></a>
      </div>
    </div>
</div>
</div>
<?php 
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    
} else {

    $pattern_q = $dbs->query('SELECT setting_value FROM setting WHERE setting_name = \'batch_item_code_pattern\'');
    if ($pattern_q->num_rows > 0) {
        $pattern_d = $pattern_q->fetch_row();
        $patterns = @unserialize($pattern_d[0]);

        $table = new simbio_table();

        $table->table_attr = 'align="center" class="s-table table" cellpadding="5" cellspacing="0"';

        if(is_array($patterns) && count($patterns) > 0){

            echo  '<div class="p-3">
            <input value="'.__('Delete Selected Data').'" class="button btn btn-danger btn-delete" type="button"> 
            <input value="'.__('Check All').'" class="check-all button btn btn-default" type="button"> 
            <input value="'.__('Uncheck All').'" class="uncheck-all button btn btn-default" type="button"></div>';

            $table->setHeader(array(__('DELETE'), __('EDIT'),__('Item Code Pattern')));
            $table->table_header_attr = 'class="alterCell font-weight-bold"';
            $table->setCellAttr(0, 0, '');
            // initial row count
            $row = 1;
            foreach ($patterns  as $pattern) {
                $cb = '<input type="checkbox" name="pattern" value="'.$pattern.'">';
                
                $edit_url = MWB.'bibliography/pop_pattern.php?in=master&pattern='.urlencode($pattern);
                $link = '<a href="'.$edit_url.'" height="500px" class="editLink notAJAX openPopUp notIframe" title="'.__('Edit pattern').'"></a>';

                $table->appendTableRow(array($cb, $link, $pattern));
                // set cell attribute
                $table->setCellAttr($row, 0, 'class="alterCell" valign="top" style="width: 5px;"');
                $table->setCellAttr($row, 1, 'class="alterCell2" valign="top" style="width: 20px;"');
                $table->setCellAttr($row, 2, 'class="alterCell" valign="top" style="width: auto; text-align: left;"'); 
                // add row count
                $row++;
            }
        }
        echo $table->printTable();
    } 
}
/* main content end */
?>
<script>
    $('.btn-delete').on('click', function (e) {
    var data = [];
    var uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
    $("input[type=checkbox]:checked").each(function() {
       data.push($(this).val());
    });
    if (data.length === 0) {
        alert("<?php echo __('Please select data to delete!'); ?>");
        return;
    }
    if (!confirm("<?php echo __('Are you sure you want to delete selected pattern(s)?'); ?>")) {
        return;
    }

    $.ajax({
            url: uri,
            type: 'post',
            data: { itemID: data, itemAction: true }
        })
          .done(function (msg) {
             alert(msg);
             parent.jQuery('#mainContent').simbioAJAX(uri)
          })
          .fail(function() {
            alert("<?php echo __('An error occurred during deletion.'); ?>");
        });
    })
    $(".uncheck-all").on('click',function (e){
        e.preventDefault()
        $('[type=checkbox]').prop('checked', false);
    });
    $(".check-all").on('click',function (e){
        e.preventDefault()
        $('[type=checkbox]').prop('checked', true);
    });
</script>