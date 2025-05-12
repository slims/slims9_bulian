<?php
/**
 * Bibliography item submodule.
 * 
 * This file contains bibliographic item  management
 * such as showing adding, updating, removing, and listing of item(s) of bibliographic.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com). Modification by Hendro Wicaksono, Eddy Subratha, Waris Agung Widodo, Drajat Hasan
 * @package SLiMS
 * @subpackage Bibliography/item
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

use SLiMS\Plugins;

// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
if (!defined('SB')) {
  require '../../../sysconfig.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

$in_pop_up = false;
// check if we are inside pop-up window
if (isset($_GET['inPopUp'])) {
  $in_pop_up = true;
}

// bibliography item module initialization hook
Plugins::getInstance()->execute('bibliography_item_init');

/* RECORD OPERATION */
if (isset($_POST['saveData']) AND $can_read AND $can_write) {
    // biblio title
    $title = $dbs->escape_string(trim($_POST['biblioTitle']));
    $data['biblio_id'] = intval($_POST['biblioID']);
    if ($data['biblio_id'] < 1) { die("Invalid biblioID"); }
    $data['item_code'] = $dbs->escape_string(trim(strip_tags($_POST['itemCode'])));
    $data['call_number'] = trim($dbs->escape_string($_POST['callNumber']));
    // check inventory code
    $inventoryCode = $dbs->escape_string(trim($_POST['inventoryCode']));
    if ($inventoryCode) {
        $data['inventory_code'] = $inventoryCode;
    } else {
        $data['inventory_code'] = 'literal{NULL}';
    }

    $data['location_id'] = trim($dbs->escape_string($_POST['locationID']));
    $data['site'] = trim($dbs->escape_string(strip_tags($_POST['itemSite'])));
    $data['coll_type_id'] = intval($_POST['collTypeID']);
    if ($data['coll_type_id'] < 1) { die("Invalid coll_type_id"); }
    $data['item_status_id'] = trim($dbs->escape_string($_POST['itemStatusID']));
    $data['source'] = intval($_POST['source']);
    if ($data['source'] < 0) { die("Invalid source"); }
    $data['order_no'] = trim($dbs->escape_string(strip_tags($_POST['orderNo'])));
    $data['order_date'] = $_POST['ordDate'];
    $data['received_date'] = $_POST['recvDate'];
    $data['supplier_id'] = intval($_POST['supplierID']);
    if ($data['supplier_id'] < 0) { die("Invalid supplier_id"); }
    $data['invoice'] = trim($dbs->escape_string($_POST['invoice']));
    $data['invoice_date'] = $_POST['invcDate'];
    $data['price_currency'] = trim($dbs->escape_string(strip_tags($_POST['priceCurrency'])));
    if (!$data['price_currency']) { $data['price_currency'] = 'literal{NULL}'; }
    $data['price'] = preg_replace('@[.,\-a-z ]@i', '', strip_tags($_POST['price']));
    $data['input_date'] = date('Y-m-d H:i:s');
    $data['last_update'] = date('Y-m-d H:i:s');
    $data['uid'] = $_SESSION['uid'];

    $form_invalid = empty($data['item_code']);

    // bibliography item module initialization hook
    Plugins::getInstance()->execute('bibliography_item_form_data_validation', ['data' => $data]);

    if ($form_invalid) {
        utility::jsToastr('Item', __('Item Code can\'t be empty!'), 'error');
        exit();
    } else {
        // create sql op object
        $sql_op = new simbio_dbop($dbs);
        if (isset($_POST['updateRecordID'])) {
            /* UPDATE RECORD MODE */
            // remove input date
            unset($data['input_date']);
            unset($data['uid']);
            // filter update record ID
            $updateRecordID = (integer)$_POST['updateRecordID'];
            if ($updateRecordID < 1) { die("Invalid updateRecordID"); }
            // update the data
            Plugins::getInstance()->execute('bibliography_item_before_update', ['data' => $data]);
            $update = $sql_op->update('item', $data, "item_id=".$updateRecordID);
            if ($update) {
                // execute registered hook
                Plugins::getInstance()->execute('bibliography_item_after_update', ['data' => $data]);

                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' update item data ('.$data['item_code'].') with title ('.$title.')', 'Item', 'Update');
                if ($sysconf['bibliography_item_update_notification']) {
                    utility::jsToastr('Item', __('Item Data Successfully Updated'), 'success');
			    }
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'itemIframe\', \''.MWB.'bibliography/iframe_item_list.php?biblioID='.$data['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(parent.jQuery.ajaxHistory[0].url);</script>';
                }
            } else { utility::jsToastr('Item', __('Item Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error'); }
            exit();
        } else {

            // execute registered hook
            Plugins::getInstance()->execute('bibliography_item_before_save', ['data' => $data]);

            /* INSERT RECORD MODE */
            // insert the data
            $insert = $sql_op->insert('item', $data);
            if ($insert) {

                // execute registered hook
                Plugins::getInstance()->execute('bibliography_item_after_save', ['data' => $data]);

                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' insert item data ('.$data['item_code'].') with title ('.$title.')', 'Item', 'Add');
                utility::jsToastr('Item', __('New Item Data Successfully Saved'), 'success');
                if ($in_pop_up) {
                    echo '<script type="text/javascript">top.setIframeContent(\'itemIframe\', \''.MWB.'bibliography/iframe_item_list.php?biblioID='.$data['biblio_id'].'\');</script>';
                    echo '<script type="text/javascript">top.jQuery.colorbox.close();</script>';
                } else {
                    echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
                }
            } else { utility::jsToastr('Item', __('Item Data FAILED to Save. Please Contact System Administrator')."\nDEBUG : ".$sql_op->error, 'error'); }
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
    $still_on_loan = array();
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array((integer)$_POST['itemID']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        $itemID = (integer)$itemID;
        // check if the item still on loan
        $loan_q = $dbs->query('SELECT i.item_code, b.title, COUNT(l.loan_id) FROM item AS i
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
            LEFT JOIN loan AS l ON (i.item_code=l.item_code AND l.is_lent=1 AND l.is_return=0)
            WHERE i.item_id='.$itemID.' GROUP BY i.item_code');
        $loan_d = $loan_q->fetch_row();
        // if there is no loan
        if ($loan_d[2] < 1) {
            if (!$sql_op->delete('item', 'item_id='.$itemID)) {
                $error_num++;
            } else {
                // write log
                writeLog('staff', $_SESSION['uid'], 'bibliography', $_SESSION['realname'].' DELETE item data ('.$loan_d[0].') with title ('.$loan_d[1].')', 'Item', 'Delete');
            }
        } else {
            $still_on_loan[] = $loan_d[0].' - '.$loan_d[1];
            $error_num++;
        }
    }

    if ($still_on_loan) {
        $items = '';
        foreach ($still_on_loan as $item) {
            $items .= $item."\n";
        }
        utility::jsToastr('Item on Hold', __('Item data can not be deleted because still on hold by members')." : \n".$items, 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$dbs->escape_string($_POST['lastQueryStr']).'\');</script>';
        exit();
    }
    // error alerting
    if ($error_num == 0) {
        utility::jsToastr('Item', __('Item succesfully removed!'), 'success');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$dbs->escape_string($_POST['lastQueryStr']).'\');</script>';
    } else {
        utility::jsToastr('Item', __('Item FAILED to removed!'), 'error');
        echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'?'.$dbs->escape_string($_POST['lastQueryStr']).'\');</script>';
    }
    exit();
}
/* RECORD OPERATION END */

if (!$in_pop_up) {
/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner itemIcon">
	<div class="per_title">
    	<h2><?php echo __('Items'); ?></h2>
	</div>
	<div class="sub_section">
	    <form name="search" action="<?php echo MWB; ?>bibliography/item.php" id="search" method="get"  class="form-inline"><?php echo __('Search'); ?>
		    <input type="text" name="keywords" id="keywords" size="30" class="form-control col-md-3" />
		    <select name="searchby" class="form-control col-md-2"><option value="item">Item</option><option value="others"><?php echo __('Others'); ?> </option></select>
		    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
	    </form>
    </div>
</div>
</div>
<?php
/* search form end */
}
/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
    if (!($can_read AND $can_write)) {
      die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
    }
    /* RECORD FORM */
    // try query
    $itemID = (integer)isset($_POST['itemID'])?$_POST['itemID']:0;
    // if ($itemID < 1) { die("Invalid itemID"); }
    $rec_q = $dbs->query('SELECT item.*, b.biblio_id, b.title, s.supplier_name
        FROM item
        LEFT JOIN biblio AS b ON item.biblio_id=b.biblio_id
        LEFT JOIN mst_supplier AS s ON item.supplier_id=s.supplier_id
        WHERE item_id='.$itemID);
    $rec_d = $rec_q->fetch_assoc();

    // create new instance
    $form = new simbio_form_table_AJAX('itemForm', $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'], 'post');
    $form->submit_button_attr = 'name="saveData" value="'.__('Save').'" class="s-btn btn btn-default"';
    // form table attributes
    $form->table_attr = 'id="dataList" class="s-table table"';
    $form->table_header_attr = 'class="alterCell font-weight-bold"';
    $form->table_content_attr = 'class="alterCell2"';

    if ($in_pop_up) {
        $form->back_button = false;
    }

    // edit mode flag set
    if ($rec_q->num_rows > 0) {
        $form->edit_mode = true;
        // record ID for delete process
        if (!$in_pop_up) {
            $form->record_id = $itemID;
        } else {
            $form->addHidden('updateRecordID', $itemID);
        }
        // form record title
        $form->record_title = $rec_d['title'];
        // submit button attribute
        $form->submit_button_attr = 'name="saveData" value="'.__('Update').'" class="btn btn-default"';
        // default biblio title and biblio ID
        $b_title = $rec_d['title'];
        $b_id = $rec_d['biblio_id'];
        if (trim($rec_d['call_number']??'') == '') {
            $biblio_q = $dbs->query('SELECT call_number FROM biblio WHERE biblio_id='.$rec_d['biblio_id']);
            $biblio_d = $biblio_q->fetch_assoc();
            $rec_d['call_number'] = $biblio_d['call_number'];
        }
    } else {
        // get biblio title and biblio ID from database if we are not on edit mode
        $biblioID = 0;
        if (isset($_GET['biblioID'])) {
            $biblioID = (integer)$_GET['biblioID'];
        }
        $biblio_q = $dbs->query('SELECT biblio_id, title, call_number FROM biblio WHERE biblio_id='.$biblioID);
        $biblio_d = $biblio_q->fetch_assoc();
        $b_title = $biblio_d['title'];
        $b_id = $biblio_d['biblio_id'];
        $def_call_number = $biblio_d['call_number'];
    }

    /* Form Element(s) */
    // title
    if (!$in_pop_up) {
      $str_input = $b_title;
      $str_input .= '<div class="makeHidden"><a class="s-btn btn btn-default notAJAX openPopUp" href="'.MWB.'bibliography/pop_biblio.php?inPopUp=true&action=detail&itemID='.($rec_d['biblio_id']??'').'&itemCollID='.($rec_d['item_id']??'').'" width="750" height="500" title="'.__('Edit Biblographic data').'">'.__('Edit Biblographic data').'</a></div>';
    } else { $str_input = $b_title; }
    $form->addAnything(__('Title'), $str_input);
    $form->addHidden('biblioTitle', $b_title);
    $form->addHidden('biblioID', $b_id);
    // item code
    $str_input  = '<div class="container-fluid">';
    $str_input .= '<div class="row">';
    $str_input .= simbio_form_element::textField('text', 'itemCode', $rec_d['item_code']??'', 'onblur="ajaxCheckID(\''.SWB.'admin/AJAX_check_id.php\', \'item\', \'item_code\', \'msgBox\', \'itemCode\')" style="width: 50%;" class="form-control col-5"');
    $str_input .= '<span id="msgBox" class="col p-2"></span>';
    $str_input .= '</div>';
    $str_input .= '</div>';
    $form->addAnything(__('Item Code'), $str_input);
    // call number
    $form->addTextField('text', 'callNumber', __('Call Number'), $rec_d['call_number']??$def_call_number, 'style="width: 50%;" class="form-control"');
    // inventory code
    $form->addTextField('text', 'inventoryCode', __('Inventory Code'), $rec_d['inventory_code']??'', 'style="width: 50%;" class="form-control"');
    // item location
        // get location data related to this record from database
        $location_q = $dbs->query("SELECT location_id, location_name FROM mst_location");
        $location_options = array();
        while ($location_d = $location_q->fetch_row()) {
            $location_options[] = array($location_d[0], $location_d[1]);
        }
    $form->addSelectList('locationID', __('Location'), $location_options, $rec_d['location_id']??'','style="width: 50%" class="form-control"');
    // item site
    $form->addTextField('text', 'itemSite', __('Shelf Location'), $rec_d['site']??'', 'style="width: 50%;" class="form-control"');
    // collection type
        // get collection type data related to this record from database
        $coll_type_q = $dbs->query("SELECT coll_type_id, coll_type_name FROM mst_coll_type");
        $coll_type_options = array();
        while ($coll_type_d = $coll_type_q->fetch_row()) {
            $coll_type_options[] = array($coll_type_d[0], $coll_type_d[1]);
        }
    $form->addSelectList('collTypeID', __('Collection Type'), $coll_type_options, $rec_d['coll_type_id']??'','style="width: 40%" class="form-control"');
    // item status
        // get item status data from database
        $item_status_q = $dbs->query("SELECT item_status_id, item_status_name FROM mst_item_status");
        $item_status_options[] = array('0', __('Available'));
        while ($item_status_d = $item_status_q->fetch_row()) {
            $item_status_options[] = array($item_status_d[0], $item_status_d[1]);
        }
    $form->addSelectList('itemStatusID', __('Item Status'), $item_status_options, $rec_d['item_status_id']??'','style="width:40%" class="form-control"');
    // order number
    $form->addTextField('text', 'orderNo', __('Order Number'), $rec_d['order_no']??'', 'style="width: 40%;" class="form-control"');
    // order date
    $form->addDateField('ordDate', __('Order Date'), $rec_d['order_date']??date('Y-m-d'), 'class="form-control"');
    // received date
    $form->addDateField('recvDate', __('Receiving Date'), $rec_d['received_date']??date('Y-m-d'),'class="form-control"');
    // item supplier
        // get item status data from database
        $supplier_q = $dbs->query("SELECT supplier_id, supplier_name FROM mst_supplier");
        $supplier_options[] = array('0', __('Not Applicable'));
        while ($supplier_d = $supplier_q->fetch_row()) {
            $supplier_options[] = array($supplier_d[0], $supplier_d[1]);
        }
    $form->addSelectList('supplierID', __('Supplier'), $supplier_options, $rec_d['supplier_id']??'','class="form-control"');
    // item source
        $source_options[] = array('1', __('Buy'));
        $source_options[] = array('2', __('Prize/Grant'));
    $form->addRadio('source', __('Source'), $source_options, !empty($rec_d['source'])?$rec_d['source']:'1');
    // item invoice
    $form->addTextField('text', 'invoice', __('Invoice'), $rec_d['invoice']??'', 'style="width: 100%;" class="form-control"');
    // invoice date
    $form->addDateField('invcDate', __('Invoice Date'), $rec_d['invoice_date']??date('Y-m-d'),'class="form-control"');
    // price
    $str_input  = '<div class="container-fluid">';
    $str_input .= '<div class="row">';
    $str_input .= simbio_form_element::textField('text', 'price', !empty($rec_d['price'])?$rec_d['price']:'0', 'style="width: 40%;" class="form-control col-4"');
    $str_input .= simbio_form_element::selectList('priceCurrency', $sysconf['currencies'], $rec_d['price_currency']??'','style="width: 10%;" class="form-control col-2"');
    $str_input .= '</div>';
    $str_input .= '</div>';
    $form->addAnything(__('Price'), $str_input);

    // edit mode messagge
    if ($form->edit_mode) {
        echo '<div class="s-alert infoBox">'.__('You are going to edit Item data').': <b>'.$rec_d['title'].'</b> ' //mfc
            .'<br />'.__('Last Updated').'&nbsp;'.date('d F Y h:i:s',strtotime($rec_d['last_update']));
        echo '</div>'."\n";
    }
    // print out the form object
    echo $form->printOut();
} else {
    require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
    require LIB.'biblio_list_model.inc.php';

    if ($sysconf['index']['type'] == 'default' || (isset($_GET['searchby']) && $_GET['searchby'] == 'item')) {
        require LIB.'biblio_list.inc.php';
        $title_field_idx = 1;
        // callback function to show title and authors in datagrid
        function showTitleAuthors($obj_db, $array_data)
        {
            global $title_field_idx;
            // biblio author detail
            $_biblio_q = $obj_db->query('SELECT b.title, a.author_name FROM biblio AS b
                LEFT JOIN biblio_author AS ba ON b.biblio_id=ba.biblio_id
                LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
                WHERE b.biblio_id='.$array_data[$title_field_idx]);
            echo $obj_db->error;
            $_authors = '';
            while ($_biblio_d = $_biblio_q->fetch_row()) {
                $_title = $_biblio_d[0];
                $_authors .= $_biblio_d[1].' - ';
            }
            $_authors = substr_replace($_authors, '', -3);
            $_output = '<div style="float: left;"><span class="title">'.$_title.'</span><div class="authors">'.$_authors.'</div></div>';
            return $_output;
        }

        /* ITEM LIST */
        // table spec
        $table_spec = 'item
            LEFT JOIN biblio ON item.biblio_id=biblio.biblio_id
            LEFT JOIN mst_location AS loc ON item.location_id=loc.location_id
            LEFT JOIN mst_coll_type AS ct ON item.coll_type_id=ct.coll_type_id';

        // create datagrid
        $datagrid = new simbio_datagrid();
        if ($can_write) {
            $datagrid->setSQLColumn('item.item_id',
                'item.item_code AS \''.__('Item Code').'\'',
                'item.biblio_id AS \''.__('Title').'\'',
                'ct.coll_type_name AS \''.__('Collection Type').'\'',
                'loc.location_name AS \''.__('Location').'\'',
                'biblio.classification AS \''.__('Classification').'\'',
                'item.last_update AS \''.__('Last Updated').'\'');
            $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
            $title_field_idx = 2;
        } else {
            $datagrid->setSQLColumn('item.item_code AS \''.__('Item Code').'\'',
                'item.biblio_id AS \''.__('Title').'\'',
                'ct.coll_type_name AS \''.__('Collection Type').'\'',
                'loc.location_name AS \''.__('Location').'\'',
                'biblio.classification AS \''.__('Classification').'\'',
                'item.last_update AS \''.__('Last Updated').'\'');
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->setSQLorder('item.last_update DESC');
    } else {
        require LIB.'biblio_list_index.inc.php';

        // callback function to show title and authors in datagrid
        function showTitleAuthors($obj_db, $array_data)
        {
            global $title_field_idx;
            $_output = '<div style="float: left;"><span class="title">'.$array_data[$title_field_idx].'</span><div class="authors">'.$array_data[$title_field_idx+1].'</div></div>';
            return $_output;
        }

        /* ITEM LIST */
        // table spec
        $table_spec = '(item
            LEFT JOIN mst_location AS loc ON item.location_id=loc.location_id
            LEFT JOIN mst_coll_type AS ct ON item.coll_type_id=ct.coll_type_id)
            LEFT JOIN search_biblio AS `index` ON item.biblio_id=index.biblio_id';

        // create datagrid
        $datagrid = new simbio_datagrid();
        if ($can_write) {
            $datagrid->setSQLColumn('item.item_id',
                'item.item_code AS \''.__('Item Code').'\'',
                'index.title AS \''.__('Title').'\'',
                'index.author AS \''.__('Author').'\'',
                'ct.coll_type_name AS \''.__('Collection Type').'\'',
                'loc.location_name AS \''.__('Location').'\'',
                #'index.classification AS \''.__('Classification').'\'',
                'item.call_number AS \''.__('Call Number').'\'',
                'item.last_update AS \''.__('Last Updated').'\'');
            $datagrid->invisible_fields = array(2);
            $title_field_idx = 2;
            $datagrid->modifyColumnContent(2, 'callback{showTitleAuthors}');
        } else {
            $datagrid->setSQLColumn('item.item_code AS \''.__('Item Code').'\'',
                'index.title AS \''.__('Title').'\'',
                'index.author AS \''.__('Author').'\'',
                'ct.coll_type_name AS \''.__('Collection Type').'\'',
                'loc.location_name AS \''.__('Location').'\'',
                #'index.classification AS \''.__('Classification').'\'',
                'item.call_number AS \''.__('Call Number').'\'',
                'item.last_update AS \''.__('Last Updated').'\'');
            $datagrid->invisible_fields = array(2);
            $title_field_idx = 1;
            $datagrid->modifyColumnContent(1, 'callback{showTitleAuthors}');
        }
        $datagrid->setSQLorder('item.last_update DESC');
    }


    // is there any search
    if (isset($_GET['keywords']) && $_GET['keywords']) {
        $keywords = utility::filterData('keywords', 'get', true, true, true);
        $searchable_fields = array('title', 'author', 'subject', 'itemcode');
        $search_str = '';
        // if no qualifier in fields
        if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
            foreach ($searchable_fields as $search_field) {
                $search_str .= $search_field.'='.$keywords.' OR ';
            }
        } else {
            $search_str = $keywords;
        }
        $biblio_list = new biblio_list($dbs, 20);
        $criteria = $biblio_list->setSQLcriteria($search_str);
    }
    if (isset($criteria)) {
        $datagrid->setSQLcriteria('('.$criteria['sql_criteria'].')');
    }

    // set table and table header attributes
    $datagrid->table_attr = 'id="dataList" class="s-table table"';
    $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    // set delete proccess URL
    $datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];

    // execute registered hook
    Plugins::run('bibliography_item_before_datagrid_output');

    // put the result into variables
    $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, ($can_read AND $can_write));
    if (isset($_GET['keywords']) AND $_GET['keywords']) {
        $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
        echo '<div class="infoBox">'.$msg.' : '.htmlspecialchars($_GET['keywords']).'<div>'.__('Query took').' <b>'.$datagrid->query_time.'</b> '.__('second(s) to complete').'</div></div>'; //mfc
    }

    echo $datagrid_result;
}
/* main content end */
