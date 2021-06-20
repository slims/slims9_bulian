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

/* Topic Management section */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
if (!defined('SB')) {
  require '../../../sysconfig.inc.php';
}

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

$in_pop_up = false;
// check if we are inside pop-up window
if (isset($_GET['inPopUp'])) {
  $in_pop_up = true;
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    $topic = trim(strip_tags($_POST['topic']));
    // check form validity
    if (empty($topic)) {
        utility::jsToastr(__('Subject'), __('Subject can\'t be empty'),'error');
        exit();
    } else {
        $data['topic'] = $dbs->escape_string($topic);
        $data['topic_type'] = trim($dbs->escape_string($_POST['subjectType']));
        $data['auth_list'] = trim($dbs->escape_string(strip_tags($_POST['authList'])));
        $data['classification'] = trim($dbs->escape_string($_POST['class']));
        $data['input_date'] = date('Y-m-d');
        $data['last_update'] = date('Y-m-d');

        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            // update the data
            $update = $sql_op->update('mst_topic', $data, 'topic_id='.$updateRecordID);
            if ($update) {
                utility::jsToastr(__('Subject'), __('Subject Data Successfully Updated'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'topicIframe\', \''.MWB.'bibliography/iframe_topic.php?biblioID='.$_GET['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                }
            } else { utility::jsToastr(__('Subject'), __('Subject Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('mst_topic', $data);
            if ($insert) {
                $last_biblio_id = $sql_op->insert_id;
                utility::jsToastr(__('Subject'), __('New Subject Data Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.MWB.'master_file/topic.php\', {method: \'post\', addData: \'itemID='.$last_biblio_id.'&detail=true\'});</script>';
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'topicIframe\', \''.MWB.'bibliography/iframe_topic.php?biblioID='.$_GET['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                }
            } else { utility::jsToastr(__('Subject'), __('Subject Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        }
    }
    exit();
} else if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!($can_read AND $can_write)) {
        die();
    }
    /* DATA DELETION PROCESS */
    // create sql op object
    $sql_op = new simbio_dbop($dbs);
    $failed_array = array();
    $error_num = 0;
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        // check if this author data still used biblio
        $_sql_biblio_topic_q = sprintf('SELECT  mt.topic, COUNT(mt.topic_id),b.title FROM biblio AS b
        LEFT JOIN biblio_topic bt ON bt.biblio_id=b.biblio_id
        LEFT JOIN mst_topic mt ON mt.topic_id=bt.topic_id
        WHERE mt.topic_id=%d GROUP BY b.title', $itemID);
        $biblio_topic_q = $dbs->query($_sql_biblio_topic_q);
        $biblio_topic_d = $biblio_topic_q->fetch_row();

        if ($biblio_topic_d[1] < 1) {
            if (!$sql_op->delete('mst_topic', 'topic_id='.$itemID)) {
                $error_num++;
            } else {
                // delete related topic
                $sql_op->delete('mst_voc_ctrl', 'topic_id='.$itemID.' OR related_topic_id='.$itemID);
            }
        }else{
            $still_used_biblio[] = sprintf(__('%s ... still used biblio %s').'<br/>',substr($biblio_topic_d[0], 0, 6),substr($biblio_topic_d[2], 0, 6));
            $error_num++;            
        }
    }

    if ($still_used_biblio) {
        $titles = '';
        foreach ($still_used_biblio as $title) {
          $titles .= $title."\n";
        }
        utility::jsToastr(__('Subject'),__('Below data can not be deleted:')."\n".$titles, 'warning');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
        exit();
    }

    // error alerting
    if ($error_num == 0) {
        utility::jsToastr(__('Subject'), __('All Data Successfully Deleted'),'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::jsToastr(__('Subject'), __('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'warning');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */
if (!$in_pop_up) {
/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Subject'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
		  <a href="<?php echo MWB; ?>master_file/topic.php" class="btn btn-default"><?php echo __('Subject List'); ?></a>
		  <a href="<?php echo MWB; ?>master_file/topic.php?action=detail" class="btn btn-default"><?php echo __('Add New Subject'); ?></a>
          <a href="<?php echo MWB; ?>master_file/cross_reference.php" class="btn btn-success"><?php echo __('Cross Reference'); ?></a>
	  </div>
	  <form name="search" action="<?php echo MWB; ?>master_file/topic.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
		  <input type="text" name="keywords" class="form-control col-md-3" />
		  <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
	  </form>
  </div>
</div>
</div>
<?php
}
/* search form end */
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
        die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
    }
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_topic WHERE topic_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('topicForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
       // record ID for delete process
        if (!$in_pop_up) {
            $form->record_id = $itemID;
        } else {
            $form->addHidden('updateRecordID', $itemID);
            $form->back_button = false;
            $form->delete_button = false;
        }

        // form record title
        $form->record_title = $rec_d['topic'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // subject
    $form->addTextField('text', 'topic', __('Subject').'*', $rec_d['topic']??'', 'class="form-control" style="width: 60%;"');
	// classification
    $form->addTextField('text', 'class', __('Classification Code'), $rec_d['classification']??'', 'class="form-control" style="width: 30%;"');
    // subject type
    foreach ($sysconf['subject_type'] as $subj_type_id => $subj_type) {
        $subj_type_options[] = array($subj_type_id, $subj_type);
    }
    $form->addSelectList('subjectType', __('Subject Type'), $subj_type_options, $rec_d['topic_type']??'','class="form-control col-3"');
    // authority list
    $form->addTextField('text', 'authList', __('Authority Files'), $rec_d['auth_list']??'', 'class="form-control" style="width: 30%;"');
    //  vocabolary control
    if (!$in_pop_up AND $form->edit_mode) {
    $str_input  = '<div class="makeHidden">';
    $str_input .= '<a class="s-margin__bottom-1 s-btn btn btn-default notAJAX openPopUp" href="'.MWB.'master_file/pop_vocabolary_control.php?itemID='.$itemID.'" height="550px" title="'.__('Vocabulary Control').'">'.__('Add New Vocabulary').'</a>';
    $str_input .= '<a class="s-margin__bottom-1 s-btn btn btn-default notAJAX openPopUp" href="'.MWB.'master_file/pop_scope_vocabolary.php?itemID='.$itemID.'" title="'.__('Scope Note Vocabulary').'" height="400">'.__('Scope Note').'</a>';
    $str_input .= '</div>';
    $str_input .= '<iframe name="itemIframe" id="itemIframe" class="form-control" style="width: 100%; height: 200px;" src="'.MWB.'master_file/iframe_vocabolary_control.php?itemID='.$itemID.'"></iframe>'."\n";
    $form->addAnything(__('Vocabulary Control'), $str_input);
    }

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit Subject data').' : <b>'.$rec_d['topic'].'</b>  <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();

} else {
    /* TOPIC LIST */
    // table spec
    $sql_criteria = 't.topic_id >= 1';
    if (isset($_GET['type']) && $_GET['type'] == 'orphaned') {
        $table_spec = 'mst_topic AS t LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id';
        $sql_criteria = 'bt.biblio_id IS NULL OR bt.topic_id IS NULL';
    } else {
        $table_spec = 'mst_topic AS t';
    }

    $subj_type_fld = 1;
    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $subj_type_fld = 3;
        $datagrid->setSQLColumn('t.topic_id',
            't.topic AS \''.__('Subject').'\'',
			't.classification AS \''.__('Class. Code').'\'',
            't.topic_type AS \''.__('Subject Type').'\'',
            't.auth_list AS \''.__('Authority Files').'\'',
            't.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('t.topic AS \''.__('Subject').'\'',
			't.classification AS \''.__('Class. Code').'\'',
            't.topic_type AS \''.__('Subject Type').'\'',
            't.auth_list AS \''.__('Authority Files').'\'',
            't.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('topic ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $keyword = utility::filterData('keywords', 'get', true, true, true);
        $words = explode(' ', $keyword);
        if (count($words) > 1) {
            $concat_sql = ' AND (';
            foreach ($words as $word) {
                $concat_sql .= " (t.topic LIKE '%$word%' OR t.classification LIKE '%$word%') AND";
            }
            // remove the last AND
            $concat_sql = substr_replace($concat_sql, '', -3);
            $concat_sql .= ') ';
            $sql_criteria .= $concat_sql;
        } else {
            $sql_criteria .= " AND t.topic LIKE '%$keyword%' OR t.classification LIKE '%$keyword%' ";
        }
    }
    $datagrid->setSQLCriteria($sql_criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // callback function to change value of subject type
    function callbackSubjectType($obj_db, $rec_d)
    {
        global $sysconf, $subj_type_fld;
        return $sysconf['subject_type'][$rec_d[$subj_type_fld]];
    }
    // modify column content
    $datagrid->modifyColumnContent($subj_type_fld, 'callback{callbackSubjectType}');
    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
