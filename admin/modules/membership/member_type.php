<?php
/**
 * Member type management.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Membership
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
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
do_checkIP('smc-membership');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('membership', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    // check form validity
    $memberTypeName = trim(strip_tags($_POST['memberTypeName']));
    if (empty($memberTypeName)) {
        utility::jsToastr(__('Member Type'),__('Member Type Name can\'t be empty'),'error'); //mfc
        exit();
    } else {
        $data['member_type_name'] = $dbs->escape_string($memberTypeName);
        # loan_limit
        if (isset($_POST['loanLimit'])) {
            if ( (is_numeric($_POST['loanLimit'])) AND ($_POST['loanLimit'] > 0) ) {
                $data['loan_limit'] = $_POST['loanLimit'];
            } else {
                $data['loan_limit'] = "0";
            }
        } else {
            $data['loan_limit'] = 0;
        }
        # loan_periode
        if (isset($_POST['loanPeriode'])) {
            if ( (is_numeric($_POST['loanPeriode'])) AND ($_POST['loanPeriode'] > 0) ) {
                $data['loan_periode'] = $_POST['loanPeriode'];
            } else {
                $data['loan_periode'] = "0";
            }
        } else {
            $data['loan_periode'] = 0;
        }
        # enable_reserve
        $allowed_er = array (0, 1);
        if (in_array($_POST['enableReserve'], $allowed_er)) {
            $data['enable_reserve'] = $_POST['enableReserve'];
        } else {
            $data['enable_reserve'] = 0;
        }
        # reserve_limit
        if (isset($_POST['reserveLimit'])) {
            if ( (is_numeric($_POST['reserveLimit'])) AND ($_POST['reserveLimit'] > 0) ) {
                $data['reserve_limit'] = $_POST['reserveLimit'];
            } else {
                $data['reserve_limit'] = "0";
            }
        } else {
            $data['reserve_limit'] = 0;
        }
        # member_periode
        if (isset($_POST['memberPeriode'])) {
            if ( (is_numeric($_POST['memberPeriode'])) AND ($_POST['memberPeriode'] > 0) ) {
                $data['member_periode'] = $_POST['memberPeriode'];
            } else {
                $data['member_periode'] = "0";
            }
        } else {
            $data['member_periode'] = 0;
        }
        # reborrow_limit
        if (isset($_POST['reborrowLimit'])) {
            if ( (is_numeric($_POST['reborrowLimit'])) AND ($_POST['reborrowLimit'] > 0) ) {
                $data['reborrow_limit'] = $_POST['reborrowLimit'];
            } else {
                $data['reborrow_limit'] = "0";
            }
        } else {
            $data['reborrow_limit'] = 0;
        }
        # fine_each_day
        if (isset($_POST['fineEachDay'])) {
            if ( (is_numeric($_POST['fineEachDay'])) AND ($_POST['fineEachDay'] > 0) ) {
                $data['fine_each_day'] = $_POST['fineEachDay'];
            } else {
                $data['fine_each_day'] = "0";
            }
        } else {
            $data['fine_each_day'] = 0;
        }
        # grace_periode
        if (isset($_POST['gracePeriode'])) {
            if ( (is_numeric($_POST['gracePeriode'])) AND ($_POST['gracePeriode'] > 0) ) {
                $data['grace_periode'] = $_POST['gracePeriode'];
            } else {
                $data['grace_periode'] = 0;
            }
        } else {
            $data['grace_periode'] = 0;
        }
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
            $update = $sql_op->update('mst_member_type', $data, 'member_type_id='.$updateRecordID);
            if ($update) {
                utility::jsToastr(__('Member Type'),__('Member Type Successfully Updated'),'success');
                // update all member expire date
                $dbs->query('UPDATE member AS m SET expire_date=DATE_ADD( COALESCE(register_date, now()),INTERVAL '.$data['member_periode'].'  DAY)
                    WHERE member_type_id='.$updateRecordID);
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr(__('Member Type'),__('Member Type Data FAILED to Save/Update. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error,'error'); }
            exit();
        } else {
            /* INSERT RECORD MODE */
            // insert the data
            if ($sql_op->insert('mst_member_type', $data)) {
                utility::jsToastr(__('Member Type'),__('New Member Type Successfully Saved'),'success');
                echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
            } else { utility::jsToastr(__('Member Type'),__('Member Type Data FAILED to Save/Update. Please Contact System Administrator')."\n".$sql_op->error,'error'); }
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
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        $lrStatus = circapi::is_any_active_membershipType($dbs, $itemID);

        // check if this label data still in use biblio
        $_sql_type_member_q = 'SELECT mmt.member_type_name, COUNT(mmt.member_type_id) FROM member AS m
        LEFT JOIN mst_member_type AS mmt ON mmt.member_type_id=m.member_type_id
        WHERE mmt.member_type_id='.$itemID.' GROUP BY mmt.member_type_name';
        $type_member_q = $dbs->query($_sql_type_member_q);
        $type_member_d = $type_member_q->fetch_row();
        if ($type_member_d[1] < 1) {
            if (!$lrStatus) {
                if (!$sql_op->delete('mst_member_type', 'member_type_id='.$itemID)) {
                    $error_num++;
                }
            }
        }else{
            $still_use_member[] = sprintf(__('Member Type %s still in use %d member(s)')."<br/>",substr($type_member_d[0], 0, 45),$type_member_d[1]);
            $error_num++;                       
        }
    }

    if ($still_use_member) {
        $titles = '';
        foreach ($still_use_member as $title) {
            $titles .= $title . "\n";
        }
        utility::jsToastr( __('Member Type'), __('Below data can not be deleted:') . "<br/>" . $titles, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\'' . $_SERVER['PHP_SELF'] . '\', {addData: \'' . $_POST['lastQueryStr'] . '\'});</script>';
        exit();
    }

    // error alerting
    if ($error_num == 0) {
        #utility::jsToastr(__('All Data Successfully Deleted'));
        #echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';

        if (!$lrStatus) {
            utility::jsToastr(__('Member Type'),__('All Data Successfully Deleted'),'success');
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
        } else {
            utility::jsToastr(__('Member Type'),__('Sorry. There is active loan transaction(s) using this membership type.'),'error');
            echo '<script language="Javascript">parent.jQuery(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
        }

    } else {
        utility::jsToastr(__('Member Type'),__('Some or All Data NOT deleted successfully!\nPlease contact system administrator'),'warning');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$_POST['lastQueryStr'].'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner memberTypeIcon">
	<div class="per_title">
    	<h2><?php echo __('Member Type'); ?></h2>
    </div>
    <div class="sub_section">
	    <div class="btn-group">
		    <a href="<?php echo MWB; ?>membership/member_type.php" class="btn btn-default"><?php echo __('Member Type List'); ?></a>
		    <a href="<?php echo MWB; ?>membership/member_type.php?action=detail" class="btn btn-default"><?php echo __('Add New Member Type'); ?></a>
	    </div>
	    <form name="search" action="<?php echo MWB; ?>membership/member_type.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
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
        die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
    }
    /* RECORD FORM */
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    $rec_q = $dbs->query('SELECT * FROM mst_member_type WHERE member_type_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';

    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        $form->record_id = $itemID;
        // form record title
        $form->record_title = $rec_d['member_type_name'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="s-btn btn btn-primary"';
    }

    /* Form Element(s) */
    // member type name
    $form->addTextField('text', 'memberTypeName', __('Member Type Name').'*', $rec_d['member_type_name']??'', 'class="form-control"');
    // loan limit
    $form->addTextField('text', 'loanLimit', __('Loan Limit'), $rec_d['loan_limit']??'', 'style="width:25%" class="form-control"');
    // loan periode
    $form->addTextField('text', 'loanPeriode', __('Loan Periode (In Days)'), $rec_d['loan_periode']??'', 'style="width:25%"  class="form-control"');
    // enable reserve
    $enable_resv_chbox[0] = array('1', __('Enable'));
    $enable_resv_chbox[1] = array('0', __('Disable'));
    $form->addRadio('enableReserve', __('Reserve'), $enable_resv_chbox, isset($rec_d['enable_reserve'])?$rec_d['enable_reserve']:'1');
    // reserve limit
    $form->addTextField('text', 'reserveLimit', __('Reserve Limit'), $rec_d['reserve_limit']??'0', 'style="width:25%" class="form-control"');
    // membership period
    $form->addTextField('text', 'memberPeriode', __('Membership Period (In Days)'), $rec_d['member_periode']??'365', 'style="width:25%" class="form-control"');
    // reborrow limit
    $form->addTextField('text', 'reborrowLimit', __('Reborrow Limit'), $rec_d['reborrow_limit']??'0', 'style="width:25%" class="form-control"');
    // fine each day
    $form->addTextField('text', 'fineEachDay', __('Fine Each Day'), $rec_d['fine_each_day']??'0','style="width:25%" class="form-control"');
    // overdue grace periode
    $form->addTextField('text', 'gracePeriode', __('Overdue Grace Period'), $rec_d['grace_periode']??'0','style="width:25%" class="form-control"');

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="infoBox">'.__('You are going to edit member data').' : <b>'.$rec_d['member_type_name'].'</b> <br />'.__('Last Updated').' '.$rec_d['last_update'].'</div>'."\n"; //mfc
    }
    // print out the form object
    echo $form->printOut();
} else {
    /* MEMBER TYPE NAME LIST */
    // table spec
    $table_spec = 'mst_member_type AS mt';

    // create datagrid
    $datagrid = new simbio_datagrid();
    if ($can_read AND $can_write) {
        $datagrid->setSQLColumn('mt.member_type_id',
            'mt.member_type_name AS \''.__('Membership Type').'\'',
            'mt.loan_limit AS \''.__('Loan Limit').'\'',
            'mt.member_periode AS \''.__('Membership Period (In Days)').'\'',
            'mt.reborrow_limit AS \''.__('Reborrow Limit').'\'',
            'mt.last_update AS \''.__('Last Updated').'\'');
    } else {
        $datagrid->setSQLColumn('mt.member_type_name AS \''.__('Membership Type').'\'',
            'mt.loan_limit AS \''.__('Loan Limit').'\'',
            'mt.member_periode AS \''.__('Membership Period (In Days)').'\'',
            'mt.reborrow_limit AS \''.__('Reborrow Limit').'\'',
            'mt.last_update AS \''.__('Last Updated').'\'');
    }
    $datagrid->setSQLorder('member_type_name ASC');

    // is there any search
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
       $keywords = utility::filterData('keywords', 'get', true, true, true);
       $datagrid->setSQLCriteria("mt.member_type_name LIKE '%$keywords%'");
    }

    // set table and table header attributes
    $datagrid->icon_edit = SWB.'admin/'.$sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme'].'/edit.gif';
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
