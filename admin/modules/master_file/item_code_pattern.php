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
    $key = array_search(trim($_POST['itemID']), $patterns);
    unset($patterns[$key]);
    $data_serialize = serialize($patterns);
    // update
    $update = $dbs->query('UPDATE setting SET setting_value=\''.$data_serialize.'\' WHERE setting_name=\'batch_item_code_pattern\'');
    if ($update) {
      echo $succces_msg;
    } else {
      echo $failed_msg;
    }
    exit();
}
/* item status update process end */

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner masterFileIcon">
    <div class="per_title">
        <h2><?php echo __('Item Code Pattern'); ?></h2>
  </div>
    <div class="sub_section">
      <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/item_code_pattern.php" class="btn btn-default"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;<?php echo __('Pattern List'); ?></a>
          <a href="<?php echo MWB; ?>bibliography/pop_pattern.php?in=master" class="notAJAX btn btn-default openPopUp notIframe"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Add New Pattern'); ?></a>
      </div>
    </div>
</div>
</fieldset>
<div class="fluid-container">
<?php
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    // form add / edit
    echo 'tambah edit';
} else {
    // pattern list
    // load setting
    echo '<table class="table table-striped">';
    $pattern_q = $dbs->query('SELECT setting_value FROM setting WHERE setting_name = \'batch_item_code_pattern\'');
    if ($pattern_q->num_rows > 0) {
        $pattern_d = $pattern_q->fetch_row();
        $patterns = @unserialize($pattern_d[0]);
        $n = 1;
        echo '<tr>';
        echo '<th>#</th>';
        echo '<th>Pattern</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        foreach ($patterns as $pattern) {
            echo '<tr>';
            echo '<td width="40px">'.$n.'</td>';
            echo '<td>'.$pattern.'</td>';
            echo '<td><a class="btn notAJAX btn-danger delete-pattern" s-pattern="'.$pattern.'">Delete</a></td>';
            echo '</tr>';
            $n++;
        }
    } else {
        // no data
        echo 'No Patternt available. <a class="notAJAX btn btn-primary openPopUp notIframe" href="'.MWB.'bibliography/pop_pattern.php?in=master" height="420px" title="'.__('Add new pattern').'">
            <i class="glyphicon glyphicon-plus"></i> Add New Pattern</a>';
    }
    echo '</table>';
}
/* main content end */
?>
</div>

<script>
    $(document).on('click', '.delete-pattern', function (e) {
        e.preventDefault()
        var pattern = $(this).attr('s-pattern')
        var uri = '<?php echo $_SERVER['PHP_SELF']; ?>';
        $.ajax({
            url: uri,
            type: 'post',
            data: { itemID: pattern, itemAction: true }
        })
          .done(function (msg) {
            parent.jQuery('#mainContent').simbioAJAX(uri)
        })
    })
</script>