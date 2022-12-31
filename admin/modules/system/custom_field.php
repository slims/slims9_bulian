<?php
/**
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

/* Custom Field Management section */
/* Modified Heru Subekti (heroe.soebekti@gmail.com) */

use SLiMS\Table\Schema;

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

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
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!$can_read && $_SESSION['uid'] != 1) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* custom field update process */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $label = trim(strip_tags($_POST['label']));  
    // check form validity
    if (empty($label)) {
        utility::jsToastr(__('Custom Field'), __('Field or Label can\'t be empty'), 'error');
        exit();
    } else {
        $data['primary_table'] = $dbs->escape_string(trim($_POST['table']));        
        $data['label'] = ucwords($dbs->escape_string($label));
        $data['type'] = $dbs->escape_string(trim($_POST['type']));   
        $data['is_public'] = $dbs->escape_string(trim($_POST['is_public']));  
        $data['class'] = $dbs->escape_string(trim($_POST['class']));  
        $data['note'] = $dbs->escape_string(trim($_POST['note']));
        $data['width'] = '100';
        $data['data'] = NULL;
        if($data['type'] == 'choice' || $data['type'] == 'checklist' || $data['type'] == 'dropdown'){
            if(isset($_POST['data'])){
                foreach ($_POST['data'] as $key => $value) {
                    if($value==''){
                        utility::jsToastr(__('Custom Field'), __('Data List can\'t be empty'), 'error');
                exit();
                    }
                    $arr[$key] = array($key,$value);
                }
            $data['data'] = $dbs->escape_string(serialize($arr));
            }else{
                utility::jsToastr(__('Custom Field'), __('Data List can\'t be empty'), 'error');
                exit();
            }
        } 

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // filter update record ID
            $updateRecordID = $dbs->escape_string(trim($_POST['updateRecordID']));
            //get last field table
            $_q = $dbs->query("SELECT primary_table,dbfield,label FROM mst_custom_field WHERE field_id=".$updateRecordID);
            if($_q->num_rows){
                $_d = $_q->fetch_row();
                if($_d[0]!=$data['primary_table']){
                    // drop column
                    $schemaParams = [$_d[0].'_custom', $_d[1]];
                    if (Schema::hasColumn(...$schemaParams)) Schema::dropColumn(...$schemaParams);
                }
            }
            // update the data
            $update = $sql_op->update('mst_custom_field', $data, 'field_id=\''.$updateRecordID.'\'');
            if ($update) {
                utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' update custom field ('.$_d[2].'->'.$data['label'].') on '.$_d[0], $_d[0].' custom', 'Update');
                utility::jsToastr(__('Custom Field'), __('New Custom Field Successfully Updated'), 'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
            } else { 
                utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' can not update custom field ('.$_d[2].') '. $sql_op->error, $_d[0].' custom', 'Fail');
                utility::jsToastr(__('Custom Field'),__('Custom Field Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $data['dbfield'] = 'cf_'.substr(md5(microtime()),rand(0,26),5);
            $insert = $sql_op->insert('mst_custom_field', $data);
            if ($insert) {
                utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' create custom field ('.$data['label'].') on '. $data['primary_table'], $data['primary_table'] .' custom', 'Add');
                utility::jsToastr(__('Custom Field'), __('New Custom Field Successfully Saved'), 'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { 
                utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' can not create custom field ('.$data['label'].'): '. $sql_op->error , $data['primary_table'] .' custom', 'Fail');
                utility::jsToastr(__('Custom Field'),__('Custom Field Data Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error'); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($dbs->escape_string(trim($_POST['itemID'])));
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = $dbs->escape_string(trim($itemID));
        //get dbfield name
        $dbfield_q = $dbs->query("SELECT dbfield,primary_table,label FROM mst_custom_field WHERE field_id=".$itemID);
        $field = $dbfield_q->fetch_row();
        
        // drop column
        $schemaParams = [$field[1].'_custom', $field[0]];
        if (Schema::hasColumn(...$schemaParams)) Schema::dropColumn(...$schemaParams);
        
        // Delete from custom field
        if (!$sql_op->delete('mst_custom_field', "field_id='$itemID'")) {
            $error_num++;
        }
    }
    // error alerting
    if ($error_num == 0) {
        utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' remove custom field '.$field[2].' with id '.$itemID, $field[1]. ' custom', 'Delete');
        utility::jsToastr(__('Custom Field'), __('All Data Successfully Deleted'), 'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::writelogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' can not remove custom field '.$field[2].' with id '.$itemID, $field[1]. ' custom', 'Fail');
        utility::jsToastr(__('Custom Field'), __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'), 'warning');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* custom field update process end */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Custom Field Editor'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>system/custom_field.php" class="btn btn-default"><?php echo __('Field List'); ?></a>
      <a href="<?php echo MWB; ?>system/custom_field.php?action=detail" class="btn btn-default"><?php echo __('Add New Field'); ?></a>
	  </div>
    <form name="search" action="<?php echo MWB; ?>system/custom_field.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" class="form-control col-md-3" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
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
    /* RECORD FORM */
    $itemID = $dbs->escape_string(trim(isset($_POST['itemID'])?$_POST['itemID']:0));
    $rec_q = $dbs->query("SELECT * FROM mst_custom_field WHERE field_id='$itemID'");
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    $visibility = 'makeVisible s-margin__bottom-1';
    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['label'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';

        $visibility = 'makeHidden s-margin__bottom-1';
    }

    /* Form Element(s) */
    $table_options[] = array('biblio', __('Bibliography'));
    $table_options[] = array('member', __('Membership'));  
    $form->addSelectList('table', __('Primary Menu'), $table_options, isset($rec_d['primary_table']) && $rec_d['primary_table'] ?$rec_d['primary_table']:'biblio',' class="form-control col-3"');

    $form->addTextField('text', 'label', __('Label').'*', $rec_d['label']??'', ' required class="form-control col-6"');

    $type_options[] = array('text', __('Text'));
    $type_options[] = array('longtext', __('Text Area'));
    $type_options[] = array('numeric', __('Numeric'));  
    $type_options[] = array('dropdown', __('Drop Down'));   
    $type_options[] = array('checklist', __('Check List')); 
    $type_options[] = array('choice', __('Choice'));
    $type_options[] = array('date', __('Date'));
    $form->addSelectList('type', __('Input Type'), $type_options, isset($rec_d['type']) && $rec_d['type'] ?$rec_d['type']:'text',' class="field-type form-control col-3"');

    $options[] = array('0', __('Hide'));
    $options[] = array('1', __('Show'));
    $form->addSelectList('is_public', __('Is Public'), $options, $rec_d['is_public']??'1','class="form-control col-3"');

    $form->addTextField('text', 'class', __('Custom Style'), $rec_d['class']??'', ' class="form-control col-3"');

    $str_input = '<div class="wrp"><div id="more"><button class="add_field_button btn btn-primary '.$visibility.'" type="button" id="more">'.__('Add Item').'</button>';
    if(isset($rec_d['data'])){
        $data = unserialize($rec_d['data']);
        if(is_array($data)){
            $x = 1;
            foreach ($data as $key => $value) {
                $str_input .= '<div class="item" style="display:flex;"><input type="text" class="itemCode form-control col-6 mb-2" id="data-'.$x.'" name="data[]" value="'.$value[1].'"/><button class="remove_field btn btn-danger btn btn-sm '.$visibility.'">'.__('Remove').'</button></div>';
                $x++;
            }
        }
        $str_input .= '</div></div>';
    }
    $form->addAnything(__('Data List'), $str_input);       

    $form->addTextField('textarea', 'note', __('Note'), $rec_d['note']??'', 'rows="1" class="form-control"');

     // edit mode message
    if ($form->edit_mode) {    
    echo '<div class="infoBox">'.sprintf(__('You are going to edit %s custom field'),$rec_d['primary_table']).' : <b>'.$rec_d['label'].'</b></div>'; //mfc
    echo '<div class="alert alert-danger m-3">'.__('<strong>Warning</strong> : Update data list or primary table will be delete this table content').'</div>';
    }
    // print out the form object
    echo $form->printOut();
} else {

    /* DOCUMENT LANGUAGE LIST */
    // table spec
    $table_spec = 'mst_custom_field';

    // create datagrid
    $datagrid = new simbio_datagrid();
    $datagrid->setSQLColumn('field_id',
            'primary_table AS \''.__('Primary Menu').'\'', 
            'label AS \''.__('Label').'\'', 
            'type AS \''.__('Type').'\'',
            'note AS \''.__('Note').'\'');
    $datagrid->setSQLorder('dbfield ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $datagrid->setSQLCriteria("label LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';

    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
?>
<script type="text/javascript">
$(document).ready(function() {
    function toggleList(){
        $(".wrp").closest("tr").show();
        str_val = ( $(".field-type").find(":selected").val());
        if(str_val=='text' || str_val=='longtext' || str_val=='date' || str_val=='numeric'){
            $(".wrp").closest("tr").hide();                
        }
    }
    toggleList();
    var x = 0; 
    $(".add_field_button").click(function(e){ 
        x = x+1;
        $("#more").append('<div class="item" style="display:flex;"><input type="text" class="itemCode form-control col-6 mb-2" id="data-'+x+'" name="data[]" /><button class="remove_field btn btn-danger btn btn-sm s-margin__bottom-1"><?= __('Remove')?></button></div>');
    }); 
    $(".wrp").on("click",".remove_field", function(e){ 
        $(this).parent('div').remove(); 
        x--;
    });
    $(".field-type").change(function() {
        toggleList();
    });
});

</script>