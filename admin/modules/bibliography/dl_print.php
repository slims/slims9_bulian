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

/* Bibliography label printing */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

$max_print = 50;

/* RECORD OPERATION */
if (isset($_POST['itemID']) AND !empty($_POST['itemID']) AND isset($_POST['itemAction'])) {
    if (!$can_read) {
        die();
    }
    if (!is_array($_POST['itemID'])) {
        // make an array
        $_POST['itemID'] = array($_POST['itemID']);
    }
    /* LABEL SESSION ADDING PROCESS */
    $print_count = 0;
    if (isset($_SESSION['labels']['biblio'])) {
        $print_count_biblio = count($_SESSION['labels']['biblio']);
    }
    if (isset($_SESSION['labels']['item'])) {
        $print_count_item = count($_SESSION['labels']['item']);
    }
    // loop array
    foreach ($_POST['itemID'] as $itemID) {
        if ($print_count == $max_print) {
            $limit_reach = true;
            break;
        }
        if (stripos($itemID, 'b', 0) !== false) {
            // Biblio ID
            $biblioID = str_ireplace('b', '', $itemID);
            if (isset($_SESSION['labels']['biblio'][$biblioID])) {
                continue;
            }
            $_SESSION['labels']['biblio'][$biblioID] = $biblioID;
            $print_count_biblio++;
        } else {
            // Item ID
            $itemID = (integer)$itemID;
            if (isset($_SESSION['labels']['item'][$itemID])) {
                continue;
            }
            $_SESSION['labels']['item'][$itemID] = $itemID;
            $print_count_item++;
        }
    }
    $print_count = $print_count_item + $print_count_biblio;
    echo '<script type="text/javascript">top.$(\'#queueCount\').html(\''.$print_count.'\');</script>';
    if (isset($limit_reach)) {
        $msg = str_replace('{max_print}', $max_print, __('Selected items NOT ADDED to print queue. Only {max_print} can be printed at once'));
        utility::jsAlert($msg);
    } else {
        // update print queue count object
        utility::jsAlert(__('Selected items added to print queue'));
    }
    exit();
}

// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
    utility::jsAlert(__('Print queue cleared!'));
    echo '<script type="text/javascript">top.$(\'#queueCount\').html(\'0\');</script>';
    unset($_SESSION['labels']);
    exit();
}

// on print action
if (isset($_GET['action']) AND $_GET['action'] == 'print') {
    // check if label session array is available
    if (!isset($_SESSION['labels']['item']) && !isset($_SESSION['labels']['biblio'])) {
        utility::jsAlert(__('There is no data to print!'));
        die();
    }

    // concat item ID
    $item_ids = '';
    if (isset($_SESSION['labels']['item'])) {
        foreach ($_SESSION['labels']['item'] as $id) {
            $item_ids .= $id.',';
        }
    }
    // concat biblio ID
    $biblio_ids = '';
    if (isset($_SESSION['labels']['biblio'])) {
        foreach ($_SESSION['labels']['biblio'] as $id) {
            $biblio_ids .= $id.',';
        }
    }
    // strip the last comma
    $item_ids = substr_replace($item_ids, '', -1);
    $biblio_ids = substr_replace($biblio_ids, '', -1);

    // SQL criteria
    if ($item_ids) {
        $criteria = "i.item_id IN($item_ids)";
    }
    if ($biblio_ids) {
        $criteria = "b.biblio_id IN($biblio_ids)";
    }
    if ($item_ids && $biblio_ids) {
        $criteria = "i.item_id IN($item_ids) OR b.biblio_id IN($biblio_ids)";
    }

    // send query to database
    $biblio_q = $dbs->query('SELECT IF(i.call_number<>\'\', i.call_number, b.call_number) FROM biblio AS b LEFT JOIN item AS i ON b.biblio_id=i.biblio_id WHERE '.$criteria);
    // echo 'SELECT IF(i.call_number!=\'\', i.call_number, b.call_number) FROM biblio AS b LEFT JOIN item AS i ON b.biblio_id=i.biblio_id WHERE '.$criteria;
    $label_data_array = array();
    while ($biblio_d = $biblio_q->fetch_row()) {
      if ($biblio_d[0]) { $label_data_array[] = $biblio_d[0]; }
    }

    // include printed settings configuration file
    include SB.'admin'.DS.'admin_template'.DS.'printed_settings.inc.php';
    // check for custom template settings
    $custom_settings = SB.'admin'.DS.$sysconf['admin_template']['dir'].DS.$sysconf['template']['theme'].DS.'printed_settings.inc.php';
    if (file_exists($custom_settings)) {
      include $custom_settings;
    }

	  // load print settings from database to override value from printed_settings file
    loadPrintSettings($dbs, 'label');

    // chunk label array
    $chunked_label_arrays = array_chunk($label_data_array, $sysconf['print']['label']['items_per_row']);
    // create html ouput of images
    $html_str = '<!DOCTYPE html>'."\n";
    $html_str .= '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>Document Label Print Result</title>'."\n";
    $html_str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
    $html_str .= '<meta http-equiv="Pragma" content="no-cache" /><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" /><meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />';
    $html_str .= '<style type="text/css">'."\n";
    $html_str .= 'body { padding: 0; margin: 1cm; font-family: '.$sysconf['print']['label']['fonts'].'; font-size: '.$sysconf['print']['label']['font_size'].'pt; background: #fff; }'."\n";
    $html_str .= '.labelStyle { width: '.$sysconf['print']['label']['box_width'].'cm; height: '.$sysconf['print']['label']['box_height'].'cm; text-align: center; margin: '.$sysconf['print']['label']['items_margin'].'cm; padding: 0; border: '.$sysconf['print']['label']['border_size'].'px solid #000000; }'."\n";
    $html_str .= '.labelHeaderStyle { background-color: #CCCCCC; font-weight: bold; padding: 5px; margin-bottom: 5px; }'."\n";
    $html_str .= '</style>'."\n";
    $html_str .= '</head>'."\n";
    $html_str .= '<body>'."\n";
    $html_str .= '<a href="#" onclick="window.print()">Print Again</a>'."\n";
    $html_str .= '<table style="margin: 0; padding: 0;" cellspacing="0" cellpadding="0">'."\n";
    // loop the chunked arrays to row
    foreach ($chunked_label_arrays as $label_data) {
        $html_str .= '<tr>'."\n";
        foreach ($label_data as $label) {
            $html_str .= '<td valign="top">';
            $html_str .= '<div class="labelStyle" valign="top">';
            if ($sysconf['print']['label']['include_header_text']) { $html_str .= '<div class="labelHeaderStyle">'.($sysconf['print']['label']['header_text']?$sysconf['print']['label']['header_text']:$sysconf['library_name']).'</div>'; }
            // explode label data by space
            $sliced_label = explode(' ', $label, 5);
            foreach ($sliced_label as $slice_label_item) {
                $html_str .= $slice_label_item.'<br />';
            }
            $html_str .= '</div>';
            $html_str .= '</td>';
        }
        $html_str .= '</tr>'."\n";
    }
    $html_str .= '</table>'."\n";
    $html_str .= '<script type="text/javascript">self.print();</script>'."\n";
    $html_str .= '</body></html>'."\n";
    // unset the session
    unset($_SESSION['labels']);
    // write to file
    $print_file_name = 'label_print_result_'.strtolower(str_replace(' ', '_', $_SESSION['uname'])).'.html';
    $file_write = @file_put_contents(UPLOAD.$print_file_name, $html_str);
    if ($file_write) {
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
        // open result in new window
        echo '<script type="text/javascript">top.$.colorbox({href: "'.SWB.FLS.'/'.$print_file_name.'", iframe: true, width: 800, height: 500, title: "Label Print Result"})</script>';
    } else { utility::jsAlert('ERROR! Label failed to generate, possibly because '.SB.FLS.' directory is not writable'); }
    exit();
}

/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner printIcon">
	<div class="per_title">
    <h2><?php echo __('Labels Printing'); ?></h2>
  </div>
	<div class="sub_section">
    <div class="btn-group">
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/dl_print.php?action=clear" class="notAJAX btn btn-default"><i class="glyphicon glyphicon-trash"></i>&nbsp;<?php echo __('Clear Print Queue'); ?></a>
      <a target="blindSubmit" href="<?php echo MWB; ?>bibliography/dl_print.php?action=print" class="notAJAX btn btn-default"><i class="glyphicon glyphicon-print"></i>&nbsp;<?php echo __('Print Labels for Selected Data'); ?></a>
	    <a href="<?php echo MWB; ?>bibliography/pop_print_settings.php?type=label" class="notAJAX btn btn-default openPopUp" title="<?php echo __('Change print barcode settings'); ?>"><i class="glyphicon glyphicon-wrench"></i></a>
	</div>
    <form name="search" action="<?php echo MWB; ?>bibliography/dl_print.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="btn btn-default" />
    </form>
    </div>
    <div class="infoBox">
        <?php
        echo __('Maximum').' <font style="color: #FF0000">'.$max_print.'</font> '.__('records can be printed at once. Currently there is').' ';
        if (isset($_SESSION['labels'])) {
            echo '<font id="queueCount" style="color: #FF0000">'.@( count($_SESSION['labels']['item'])+count($_SESSION['labels']['biblio']) ).'</font>';
        } else { echo '<font id="queueCount" style="color: #FF0000">0</font>'; }
        echo ' '.__('in queue waiting to be printed.');
        ?>
    </div>
</div>
</fieldset>
<?php
/* search form end */

// create datagrid
$datagrid = new simbio_datagrid();
/* BIBLIOGRAPHY LIST */
require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
require LIB.'biblio_list_model.inc.php';
// index choice
if ($sysconf['index']['type'] == 'index' || ($sysconf['index']['type'] == 'sphinx' && file_exists(LIB.'sphinx/sphinxapi.php'))) {
    if ($sysconf['index']['type'] == 'sphinx') {
        require LIB.'sphinx/sphinxapi.php';
        require LIB.'biblio_list_sphinx.inc.php';
    } else {
        require LIB.'biblio_list_index.inc.php';
    }
    // table spec
    $table_spec = 'search_biblio AS `index` LEFT JOIN `item` ON `index`.biblio_id=`item`.biblio_id';
    if ($can_read) {
        $datagrid->setSQLColumn('IF(item.item_id IS NOT NULL, item.item_id, CONCAT(\'b\', index.biblio_id))', 'index.title AS `'.__('Title').'`',
            'IF(item.call_number<>\'\', item.call_number, index.call_number) AS `'.__('Call Number').'`');
    }
} else {
    require LIB.'biblio_list.inc.php';
    // table spec
    $table_spec = 'biblio LEFT JOIN item ON biblio.biblio_id=item.biblio_id';
    if ($can_read) {
        $datagrid->setSQLColumn('IF(item.item_id IS NOT NULL, item.item_id, CONCAT(\'b\', biblio.biblio_id))', 'biblio.title AS `'.__('Title').'`',
            'IF(item.call_number<>\'\', item.call_number, biblio.call_number) AS `'.__('Call Number').'`');
    }
}
$datagrid->setSQLorder('item.last_update DESC');
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = $dbs->escape_string(trim($_GET['keywords']));
    $searchable_fields = array('title', 'author', 'class', 'callnumber', 'itemcode');
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
$datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Add'));
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg = __('Add to print queue?');
// set delete proccess URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'];
$datagrid->column_width = array(0 => '75%', 1 => '20%');
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
    echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"<div>'.__('Query took').' <b>'.$datagrid->query_time.'</b> '.__('second(s) to complete').'</div></div>';
}
echo $datagrid_result;
/* main content end */
