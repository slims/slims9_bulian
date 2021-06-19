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

/* Author Management section */

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
    $authorName = trim(strip_tags($_POST['authorName']));
    // check form validity
    if (empty($authorName)) {
        utility::jsToastr(__('Author'),__('Author name can\'t be empty'),'warning');
        exit();
    } else {
        $data['author_name'] = $dbs->escape_string($authorName);
        $author_year = $dbs->escape_string(trim($_POST['authorYear']));
        if ($author_year) { $data['author_year'] = $author_year; }
        $data['authority_type'] = trim($dbs->escape_string(strip_tags($_POST['authorityType'])));
        $data['auth_list'] = trim($dbs->escape_string(strip_tags($_POST['authList'])));
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
            $update = $sql_op->update('mst_author', $data, 'author_id='.$updateRecordID);
            if ($update) {
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'Master file', $_SESSION['realname'].' update author ('.$data['author_name'].').', 'Author', 'update');
                utility::jsToastr(__('Author'),__('Author Data Successfully Updated'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php?biblioID='.$_GET['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                }
            } else { utility::jsToastr(__('Author'),__('Author Data FAILED to Updated. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('mst_author', $data);
            if ($insert) {
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'Master file', $_SESSION['realname'].' add new author ('.$data['author_name'].').', 'Author', 'Add');
                utility::jsToastr(__('Author'),__('New Author Data Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'authorIframe\', \''.MWB.'bibliography/iframe_author.php?biblioID='.$_GET['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                }

            } else { 
                utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'Master file', $_SESSION['realname'].' can not add new author ('.$data['author_name'].').', 'Author', 'Fail');
                utility::jsToastr(__('Author'),__('Author Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error');
            }
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
    $still_used_biblio = array();
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        
        // check if this author data still used biblio
        $_sql_biblio_author_q = sprintf('SELECT  ma.author_name, COUNT(ma.author_id),b.title FROM biblio AS b
        LEFT JOIN biblio_author ba ON ba.biblio_id=b.biblio_id
        LEFT JOIN mst_author ma ON ma.author_id=ba.author_id
        WHERE ma.author_id=%d GROUP BY b.title', $itemID);
        $biblio_author_q = $dbs->query($_sql_biblio_author_q);
        $biblio_author_d = $biblio_author_q->fetch_row();
        $_log_authors .= $biblio_author_d['author_name'].', ';

        if ($biblio_author_d[1] < 1) {
            if (!$sql_op->delete('mst_author', 'author_id='.$itemID)) {
                $error_num++;
            }
        }
         else {
            $still_used_biblio[] = substr($biblio_author_d[0], 0, 6).'... still used biblio '.substr($biblio_author_d[2], 0, 6).' ..'."\n";
            $error_num++;
        }
    }

    if ($still_used_biblio) {
        $titles = '';
        foreach ($still_used_biblio as $title) {
          $titles .= $title."\n";
        }
        utility::jsToastr(__('Author'),__('Below data can not be deleted:')."\n".$titles, 'warning');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\', {addData: \''.$_POST['lastQueryStr'].'\'});</script>';
        exit();
    }

    // error alerting
    if ($error_num == 0) {
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'Master file', $_SESSION['realname'].' delete  author ('.implode(', ', $_log_authors).').', 'Author', 'delete');
        utility::jsToastr(__('Author'),__('All Data Successfully Deleted'),'success');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    } else {
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'Master file', $_SESSION['realname'].' delete  author(s), BUT not all ('.implode(', ', $_log_authors).').', 'Author', 'delete');
        utility::jsToastr(__('Author'),__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'error');
        echo '<script type="text/javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
if (!$in_pop_up) {
?>
<div class="menuBox">
<div class="menuBoxInner masterFileIcon">
	<div class="per_title">
	    <h2><?php echo __('Author'); ?></h2>
  </div>
	<div class="sub_section">
	  <div class="btn-group">
      <a href="<?php echo MWB; ?>master_file/author.php" class="btn btn-default"><?php echo __('Author List'); ?></a>
      <a href="<?php echo MWB; ?>master_file/author.php?action=detail" class="btn btn-default"><?php echo __('Add New Author'); ?></a>
    </div>
    <form name="search" action="<?php echo MWB; ?>master_file/author.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
    $rec_q = $dbs->query('SELECT * FROM mst_author WHERE author_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('authorForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
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
        $form->record_title = $rec_d['author_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // author name
    $form->addTextField('text', 'authorName', __('Author Name').'*', $rec_d['author_name']??'', ' class="form-control col-6"');
    // author year
    $form->addTextField('text', 'authorYear', __('Author Birth Year'), $rec_d['author_year']??'', ' class="form-control col-6"');
    // authority type
    foreach ($sysconf['authority_type'] as $auth_type_id => $auth_type) {
        $auth_type_options[] = array($auth_type_id, $auth_type);
    }
    $form->addSelectList('authorityType', __('Authority Type'), $auth_type_options, $rec_d['authority_type']??'',' class="form-control col-6"');
    // authority list
    $form->addTextField('text', 'authList', __('Authority Files'), $rec_d['auth_list']??'', ' class="form-control col-6"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit author data').' : <b>'.$rec_d['author_name'].'</b> <br />'.__('Last Update').' '.$rec_d['last_update'].'</div>'; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* AUTHOR LIST */
    // table spec
    $sql_criteria = 'a.author_id > 0';
    if (isset($_GET['type']) && $_GET['type'] == 'orphaned') {
        $table_spec = 'mst_author AS a LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id';
        $sql_criteria = 'ba.biblio_id IS NULL OR ba.author_id IS NULL';
    } else {
        $table_spec = 'mst_author AS a';
    }


    // authority field num
    $auth_type_fld = 2;
    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $auth_type_fld = 3;
        $datagrid->setSQLColumn('a.author_id', 'a.author_name AS \''.__('Author Name').'\'',
            'a.author_year AS \''.__('Author Year').'\'',
            'a.authority_type AS \''.__('Authority Type').'\'',
            'a.auth_list AS \''.__('Authority Files').'\'',
            'a.last_update AS \''.__('Last Update').'\'');
    } else {
        $datagrid->setSQLColumn('a.author_name AS \''.__('Author Name').'\'',
            'a.author_year AS \''.__('Author Year').'\'',
            'a.authority_type AS \''.__('Authority Type').'\'',
            'a.auth_list AS \''.__('Authority Files').'\'',
            'a.last_update AS \''.__('Last Update').'\'');
    }
    $datagrid->setSQLorder('author_name ASC');

    // change the record order
    if (isset($_GET['fld']) AND isset($_GET['dir'])) {
        $datagrid->setSQLorder("'".urldecode($_GET['fld'])."' ".$dbs->escape_string($_GET['dir']));
    }

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $sql_criteria .= " AND a.author_name LIKE '%$keywords%'";
    }
    $datagrid->setSQLCriteria($sql_criteria);

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // callback function to change value of authority type
    function callbackAuthorType($obj_db, $rec_d)
    {
        global $sysconf, $auth_type_fld;
        return $sysconf['authority_type'][$rec_d[$auth_type_fld]];
    }
    // modify column content
    $datagrid->modifyColumnContent($auth_type_fld, 'callback{callbackAuthorType}');
    // put the result into variable
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
    }

    echo $datagrid_result;
}
/* main content end */
