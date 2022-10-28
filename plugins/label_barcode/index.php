<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 06/11/20 00.56
 * @File name           : index.php
 */

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

$max_print = 50;

// for generate barcode && force use zend barcode
ini_set('include_path', LIB);
require_once LIB . 'Zend/Barcode.php';

function generateBarcode($code)
{
    $file_name = __DIR__ . '/../../images/barcodes/' . $code . '.png';
    $renderer = Zend_Barcode:: factory(
        'code128', 'image', [
            'text' => urldecode($code),
            'factor' => 2,
            'font' => realpath(LIB . 'phpbarcode/DejaVuSans.ttf'),
            'fontSize' => 8,
        ]
    );
    call_user_func('imagepng', $renderer->draw(), $file_name);
}

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
            $print_count++;
        }
    }
    $print_count = $print_count_item + $print_count_biblio;
    echo '<script type="text/javascript">top.$(\'#queueCount\').html(\'' . $print_count . '\');</script>';
    if (isset($limit_reach)) {
        $msg = str_replace('{max_print}', $max_print, __('Selected items NOT ADDED to print queue. Only {max_print} can be printed at once'));
        utility::jsToastr('Labels Printing', $msg, 'warning');
    } else {
        // update print queue count object
        utility::jsToastr('Labels Printing', __('Selected items added to print queue'), 'success');
    }
    exit();
}

// clean print queue
if (isset($_GET['action']) AND $_GET['action'] == 'clear') {
    utility::jsToastr('Labels Printing', __('Print queue cleared!'), 'success');
    echo '<script type="text/javascript">top.$(\'#queueCount\').html(\'0\');</script>';
    unset($_SESSION['labels']);
    exit();
}

// on print action
if (isset($_GET['action']) AND $_GET['action'] == 'print') {
    // check if label session array is available
    if (!isset($_SESSION['labels']['item']) && !isset($_SESSION['labels']['biblio'])) {
        utility::jsToastr('Labels Printing', __('There is no data to print!'), 'error');
        die();
    }

    // concat item ID
    $item_ids = '';
    if (isset($_SESSION['labels']['item'])) {
        foreach ($_SESSION['labels']['item'] as $id) {
            $item_ids .= $id . ',';
        }
    }
    // concat biblio ID
    $biblio_ids = '';
    if (isset($_SESSION['labels']['biblio'])) {
        foreach ($_SESSION['labels']['biblio'] as $id) {
            $biblio_ids .= $id . ',';
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
    $biblio_q = $dbs->query('SELECT IF(i.call_number<>\'\', i.call_number, b.call_number), i.item_code, b.title FROM biblio AS b LEFT JOIN item AS i ON b.biblio_id=i.biblio_id WHERE ' . $criteria);

    $label_data_array = array();
    while ($biblio_d = $biblio_q->fetch_row()) {
        if ($biblio_d[0]) {
            $label_data_array[] = $biblio_d;
        }
    }

    // include printed settings configuration file
    include SB . 'admin' . DS . 'admin_template' . DS . 'printed_settings.inc.php';
    // check for custom template settings
    $custom_settings = SB . 'admin' . DS . $sysconf['admin_template']['dir'] . DS . $sysconf['template']['theme'] . DS . 'printed_settings.inc.php';
    if (file_exists($custom_settings)) {
        include $custom_settings;
    }

    // load print settings from database to override value from printed_settings file
    loadPrintSettings($dbs, 'label');

    // chunk label array
    $chunked_label_arrays = array_chunk($label_data_array, 2);
    // create html ouput of images
    $html_str = '';
    // loop the chunked arrays to row
    $html_str .= '<table class="table table-borderless">' . "\n";
    echo '<script type="text/javascript" src="' . JWB . 'jquery.js"></script>';
    foreach ($chunked_label_arrays as $label_data) {
        $html_str .= '<tr>' . "\n";
        foreach ($label_data as $labels) {
            $barcode_text = trim($labels[1]);
            /* replace space */
            $barcode_text = str_replace(array(' ', '/', '\/'), '_', $barcode_text);
            /* replace invalid characters */
            $barcode_text = str_replace(array(':', ',', '*', '@'), '', $barcode_text);
            generateBarcode($barcode_text);

            $label = $labels[0];
            $html_str .= '<td valign="top">';
            $html_str .= '<div class="card card-body"><div class="d-flex align-items-center">';
            $html_str .= '<div style="width:240px; margin-right: 40px;position:relative;">';
            $html_str .= '<div style="padding:0 1rem;font-size:10pt;text-align:center;position:absolute;top:-1px;left:0;right:0;z-index:1;background:white;text-overflow: ellipsis;white-space: nowrap;overflow: hidden;line-height:1.25">'.$labels[2].'</div>';
            $html_str .= '<img class="img-fluid" src="' . SWB . IMG . '/barcodes/' . urlencode(urlencode($barcode_text)) . '.png?' . date('YmdHis') . '" border="0" />';
            $html_str .= '</div>';
            $html_str .= '<div>';
            if ($sysconf['print']['label']['include_header_text']) {
                $html_str .= '<div class="labelHeaderStyle">' . ($sysconf['print']['label']['header_text'] ? $sysconf['print']['label']['header_text'] : $sysconf['library_name']) . '</div>';
            }
            // explode label data by space except callnumber
            $sliced_label = preg_split("/((?<=\w)\s+(?=\D))|((?<=\D)\s+(?=\d))/m", $label);
            $html_str .= '<div class="labelStyle">';
            foreach ($sliced_label as $slice_label_item) {
                $html_str .= $slice_label_item . '<br />';
            }
            $html_str .= '</div></div></div>';
            $html_str .= '</div>';
            $html_str .= '</td>';
        }
        $html_str .= '</tr>' . "\n";
    }
    $html_str .= '</table>' . "\n";

    $__ = '__';
    $SWB = SWB;
    $template = <<<HTML
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Label & Barcode Printing</title>
        <link rel="stylesheet" href="{$SWB}css/bootstrap.min.css">
        <style>
            @media print {
                .no-print {
                    display: none !important;
                }            
            }
            .rotate {
              transform: rotate(-90deg);
              -webkit-transform: rotate(-90deg);
              -moz-transform: rotate(-90deg);
              -ms-transform: rotate(-90deg);
              -o-transform: rotate(-90deg);
              filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }
            .labelHeaderStyle {
                border-bottom: 1px solid #8d8d8d;
                padding-bottom: 8px;
                margin-bottom: 8px;
            }
            .labelStyle {
                font-weight: bold;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <a href="#" class="no-print btn btn-success mb-4" onclick="window.print()">{$__('Print Again')}</a>
        {$html_str}
        <script type="text/javascript">self.print();</script>
    </body>
</html>
HTML;

    // unset the session
    unset($_SESSION['labels']);
    // write to file
    $print_file_name = 'label_print_result_' . strtolower(str_replace(' ', '_', $_SESSION['uname'])) . '.html';
    $file_write = @file_put_contents(UPLOAD . $print_file_name, $template);
    if ($file_write) {
        echo '<script type="text/javascript">parent.$(\'#queueCount\').html(\'0\');</script>';
        // open result in new window
        echo '<script type="text/javascript">top.$.colorbox({href: "' . SWB . FLS . '/' . $print_file_name . '?v='.date('YmdHis').'", iframe: true, width: (1200), height: (parent.window.innerHeight - 200), title: "' . __('Labels Printing') . '"})</script>';
    } else {
        utility::jsToastr('Labels Printing', str_replace('{directory}', SB . FLS, __('ERROR! Label failed to generate, possibly because {directory} directory is not writable')), 'error');
    }
    exit();
}

/* search form */
?>
    <div class="menuBox">
        <div class="menuBoxInner printIcon">
            <div class="per_title">
                <h2><?php echo __('Labels & Barcode Printing'); ?></h2>
            </div>
            <div class="sub_section">
                <div class="btn-group">
                    <a target="blindSubmit" href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'clear']) ?>"
                       class="btn btn-default notAJAX "><?php echo __('Clear Print Queue'); ?></a>
                    <a target="blindSubmit" href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['action' => 'print']) ?>"
                       class="btn btn-success notAJAX "><?php echo __('Print Labels for Selected Data'); ?></a>
                    <!--                    <a href="-->
                    <?php //echo MWB; ?><!--bibliography/pop_print_settings.php?type=label" width="780" height="500" class="btn btn-default notAJAX openPopUp" title="-->
                    <?php //echo __('Change print label settings'); ?><!--">-->
                    <?php //echo __('Change print label settings'); ?><!--</a>-->
                </div>
                <form name="search" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="get"
                      class="form-inline"><?php echo __('Search'); ?>
                    <input type="text" name="keywords" class="form-control col-md-3"/>
                    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>"
                           class="s-btn btn btn-default"/>
                </form>
            </div>
            <div class="infoBox">
                <?php
                echo __('Maximum') . ' <strong class="text-danger">' . $max_print . '</strong> ' . __('records can be printed at once. Currently there is') . ' ';
                if (isset($_SESSION['labels'])) {
                    echo '<strong id="queueCount" class="text-danger">' . @(count($_SESSION['labels']['item']) + count($_SESSION['labels']['biblio'])) . '</strong>';
                } else {
                    echo '<strong id="queueCount" class="text-danger">0</strong>';
                }
                echo ' ' . __('in queue waiting to be printed.');
                ?>
            </div>
        </div>
    </div>
<?php
/* search form end */

// create datagrid
$datagrid = new simbio_datagrid();
/* BIBLIOGRAPHY LIST */
require SIMBIO . 'simbio_UTILS/simbio_tokenizecql.inc.php';
require LIB . 'biblio_list_model.inc.php';
// index choice
if ($sysconf['index']['type'] == 'index' || ($sysconf['index']['type'] == 'sphinx' && file_exists(LIB . 'sphinx/sphinxapi.php'))) {
    if ($sysconf['index']['type'] == 'sphinx') {
        require LIB . 'sphinx/sphinxapi.php';
        require LIB . 'biblio_list_sphinx.inc.php';
    } else {
        require LIB . 'biblio_list_index.inc.php';
    }
    // table spec
    $table_spec = 'search_biblio AS `index` LEFT JOIN `item` ON `index`.biblio_id=`item`.biblio_id';
    if ($can_read) {
        $datagrid->setSQLColumn('IF(item.item_id IS NOT NULL, item.item_id, CONCAT(\'b\', index.biblio_id))', 'index.title AS "' . __('Title') . '"', 'IF(item.call_number<>\'\', item.call_number, index.call_number) AS `' . __('Call Number') . '`', 'item.item_code AS `' . __('Item Code') . '`');
    }
} else {
    require LIB . 'biblio_list.inc.php';
    // table spec
    $table_spec = 'biblio LEFT JOIN item ON biblio.biblio_id=item.biblio_id';
    if ($can_read) {
        $datagrid->setSQLColumn('IF(item.item_id IS NOT NULL, item.item_id, CONCAT(\'b\', biblio.biblio_id))', 'biblio.title AS `' . __('Title') . '`',
            'IF(item.call_number<>\'\', item.call_number, biblio.call_number) AS `' . __('Call Number') . '`', 'item.item_code AS `' . __('Item Code') . '`');
    }
}
$datagrid->setSQLorder('item.last_update DESC');
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    $searchable_fields = array('title', 'author', 'class', 'callnumber', 'itemcode');
    $search_str = '';
    // if no qualifier in fields
    if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
        foreach ($searchable_fields as $search_field) {
            $search_str .= $search_field . '=' . $keywords . ' OR ';
        }
    } else {
        $search_str = $keywords;
    }
    $biblio_list = new biblio_list($dbs, 20);
    $criteria = $biblio_list->setSQLcriteria($search_str);
}
$criteria_str = 'item.item_code IS NOT NULL';
if (isset($criteria)) {
    $criteria_str .= ' AND (' . $criteria['sql_criteria'] . ')';
}
$datagrid->setSQLCriteria($criteria_str);
// set table and table header attributes
$datagrid->table_attr = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// edit and checkbox property
$datagrid->edit_property = false;
$datagrid->chbox_property = array('itemID', __('Add'));
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg = __('Add to print queue?');
// set delete proccess URL
$datagrid->chbox_form_URL = $_SERVER['PHP_SELF'] . '?' . httpQuery();
$datagrid->column_width = array(0 => '75%', 1 => '20%');
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords'));
    echo '<div class="infoBox">' . $msg . ' : "' . htmlspecialchars($_GET['keywords']) . '"<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s) to complete') . '</div></div>';
}
echo $datagrid_result;
/* main content end */
