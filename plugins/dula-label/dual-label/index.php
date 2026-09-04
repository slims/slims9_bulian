<?php

/**
 * Dual Label Print - Apu
 * Version 1.0.1
 * Prints two stickers per item in a single print job:
 *   1. Spine label  : library name header + call number (38×25 mm)
 *   2. Barcode label: library name header + title + barcode image (38×25 mm)
 *
 * Search by accession number (item_code). Items are queued in the session and
 * printed all at once with one browser print dialog.
 *
 * @author    Nurul Alam Apu
 * @website   https://www.slimsbd.com
 * @whatsapp  +8801674066064
 * @email     slimsbd@gmail.com
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────
defined('INDEX_AUTH') or die('Direct access not allowed!');

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

// ── Privilege check ──────────────────────────────────────────────────────────
$can_read = utility::havePrivilege('bibliography', 'r');
if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

// ── Helper: preserve plugin container query params (especially 'id') ────────
function dlp_http_query(array $merge = []): string
{
    $base = $_GET ?? [];
    unset($base['action'], $base['keywords']);
    return http_build_query(array_merge($base, $merge));
}

// ── Constants ────────────────────────────────────────────────────────────────
$max_print   = 50;
$session_key = 'dlp_apu_dual_labels';

// ── Helper: current queue count ──────────────────────────────────────────────
function dlp_queue_count(): int
{
    global $session_key;
    return isset($_SESSION[$session_key]) ? count($_SESSION[$session_key]) : 0;
}

// ── Helper: JS queue counter update ─────────────────────────────────────────
function dlp_update_counter(): void
{
    echo '<script>top.document.getElementById("dlpQueueCount").innerHTML = "' . dlp_queue_count() . '";</script>';
}

// ═══════════════════════════════════════════════════════════════════════════
// ACTION: ADD items to queue (POST from datagrid checkbox)
// ═══════════════════════════════════════════════════════════════════════════
if (isset($_POST['itemCode'], $_POST['itemAction']) && !empty($_POST['itemCode'])) {
    global $dbs;
    if (!$can_read) {
        die();
    }

    $codes = is_array($_POST['itemCode']) ? $_POST['itemCode'] : [$_POST['itemCode']];
    $added = 0;
    $limit_reached = false;

    foreach ($codes as $raw_code) {
        if (dlp_queue_count() >= $max_print) {
            $limit_reached = true;
            break;
        }
        $code = trim($dbs->real_escape_string($raw_code));
        if (isset($_SESSION[$session_key][$code])) {
            continue;
        }
        $q = $dbs->query(
            "SELECT b.title,
                    IF(i.call_number <> '', i.call_number, b.call_number) AS call_number,
                    i.item_code
             FROM item AS i
             LEFT JOIN biblio AS b ON i.biblio_id = b.biblio_id
             WHERE i.item_code = '$code'
             LIMIT 1"
        );
        if ($row = $q->fetch_assoc()) {
            $_SESSION[$session_key][$code] = [
                'title'       => $row['title'],
                'call_number' => $row['call_number'],
                'item_code'   => $row['item_code'],
            ];
            $added++;
        }
    }

    dlp_update_counter();

    if ($limit_reached) {
        $msg = str_replace('{max}', $max_print, __('Queue full — only {max} items allowed at once. Some items were NOT added.'));
        utility::jsToastr('Dual Label Print', $msg, 'warning');
    } else {
        utility::jsToastr('Dual Label Print', __('Item(s) added to print queue'), 'success');
    }
    exit();
}

// ═══════════════════════════════════════════════════════════════════════════
// ACTION: CLEAR queue
// ═══════════════════════════════════════════════════════════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION[$session_key]);
    echo '<script>top.document.getElementById("dlpQueueCount").innerHTML = "0";</script>';
    utility::jsToastr('Dual Label Print', __('Print queue cleared'), 'success');
    exit();
}

// ═══════════════════════════════════════════════════════════════════════════
// ACTION: PRINT — generate HTML file and open in colorbox
// ═══════════════════════════════════════════════════════════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'print') {
    global $dbs;

    if (empty($_SESSION[$session_key])) {
        utility::jsToastr('Dual Label Print', __('No items in the print queue!'), 'error');
        exit();
    }

    // Load print settings
    require SB . 'admin' . DS . 'admin_template' . DS . 'printed_settings.inc.php';
    $custom = SB . 'admin' . DS . $sysconf['admin_template']['dir'] . DS . $sysconf['template']['theme'] . DS . 'printed_settings.inc.php';
    if (file_exists($custom)) {
        include $custom;
    }
    loadPrintSettings($dbs, 'label');
    loadPrintSettings($dbs, 'barcode');

    $library_name = htmlspecialchars($sysconf['library_name'] ?? 'Library');

    // ── Barcode parameters (from old, working version) ────────────────────
    $barcode_size     = 2;
    $barcode_encoding = $sysconf['barcode_encoding'] ?? 'CODE39';

    // ── Build HTML ──────────────────────────────────────────────────────────
    $spine_fonts    = $sysconf['print']['label']['fonts']           ?? 'Arial, sans-serif';
    $spine_border   = (int)($sysconf['print']['label']['border_size'] ?? 1);
    $barcode_fonts  = $sysconf['print']['barcode']['barcode_fonts']      ?? 'Arial, sans-serif';
    $barcode_border = (int)($sysconf['print']['barcode']['barcode_border_size'] ?? 1);
    $barcode_scale  = (int)($sysconf['print']['barcode']['barcode_scale'] ?? 90);
    $spine_header_text   = $sysconf['print']['label']['header_text']          ?: $library_name;
    $barcode_header_text = $sysconf['print']['barcode']['barcode_header_text'] ?: $library_name;
    $cut = (int)($sysconf['print']['barcode']['barcode_cut_title'] ?? 40);

    ob_start();
?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate" />
        <title>Dual Label Print Result</title>
        <style>
            @page {
                size: 38mm 25mm;
                margin: 0;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .sticker {
                width: 38mm;
                height: 25mm;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                box-sizing: border-box;
                overflow: hidden;
                page-break-after: always;
                break-after: page;
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .sticker-header {
                width: 100%;
                background-color: #CCCCCC;
                font-weight: bold;
                font-size: 6pt;
                line-height: 1.2;
                padding: 5px 1mm 0.4mm 1mm;
                box-sizing: border-box;
                text-align: center;
                flex-shrink: 0;
                margin-bottom: 2px;
            }

            .sticker-spine {
                font-family: <?php echo $spine_fonts; ?>;
                border: <?php echo $spine_border; ?>px solid #000;
                padding: 0;
                justify-content: flex-start;
            }

            .spine-body {
                flex: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                width: 100%;
                padding: 5px 1mm 0 1mm;
                box-sizing: border-box;
            }

            .spine-call-line {
                font-size: 12pt;
                font-weight: bold;
                line-height: 1.15;
                word-break: break-word;
                text-align: left;
                width: fit-content;
                min-width: 60%;
            }

            .sticker-barcode {
                font-family: <?php echo $barcode_fonts; ?>;
                border: <?php echo $barcode_border; ?>px solid #000;
                padding: 0;
            }

            .barcode-title {
                font-size: 6pt;
                line-height: 1.1;
                max-height: 2.4em;
                overflow: hidden;
                width: 100%;
                padding: 5px 2mm 0 2mm;
                box-sizing: border-box;
                text-align: center;
                flex-shrink: 0;
            }

            .barcode-img-wrap {
                flex-shrink: 0;
                display: flex;
                align-items: flex-start;
                justify-content: center;
                width: 100%;
                overflow: hidden;
                margin-top: 1px;
            }

            .barcode-img-wrap img {
                width: <?php echo $barcode_scale; ?>%;
                max-height: 12mm;
                display: block;
            }
        </style>
    </head>

    <body>
        <?php
        foreach ($_SESSION[$session_key] as $item) {
            $title       = htmlspecialchars($item['title']       ?? '');
            $call_number = htmlspecialchars($item['call_number'] ?? '');
            $item_code   = htmlspecialchars($item['item_code']   ?? '');

            $short_title = ($cut && strlen($title) > $cut) ? substr($title, 0, $cut) . '…' : $title;
            $call_segments = explode(' ', preg_replace('/\s+/', ' ', trim($call_number)));

            // ── Barcode generation using core barcode.php (old working method) ──
            // Trigger barcode generation via JavaScript (saves PNG to images/barcodes/)
            $bc_clean = str_replace([' ', '/', '\\', ':', ',', '*', '@'], ['_', '', '', '', '', '', ''], $item_code);
            $bc_encoded = urlencode(urlencode($item_code));
            $bc_url = SWB . IMG . '/barcodes/' . $bc_encoded . '.png?' . date('YmdHis');

            // Output the JavaScript to generate the barcode image
            echo '<script>
        (function(){
            var i = new Image();
            i.src = "' . SWB . 'lib/phpbarcode/barcode.php?code=' . rawurlencode($item_code) . '&encoding=' . $barcode_encoding . '&scale=' . $barcode_size . '&mode=png&act=save";
        })();
        </script>' . "\n";

            // ── Spine label ──────────────────────────────────────
            echo '<div class="sticker sticker-spine">';
            echo '<div class="sticker-header">' . $spine_header_text . '</div>';
            echo '<div class="spine-body">';
            foreach ($call_segments as $seg) {
                echo '<div class="spine-call-line">' . htmlspecialchars($seg) . '</div>';
            }
            echo '</div>';
            echo '</div>' . "\n";

            // ── Barcode label ────────────────────────────────────
            echo '<div class="sticker sticker-barcode">';
            echo '<div class="sticker-header">' . $barcode_header_text . '</div>';
            echo '<div class="barcode-title">' . $short_title . '</div>';
            echo '<div class="barcode-img-wrap">';
            echo '<img src="' . $bc_url . '" alt="' . $item_code . '" />';
            echo '</div>';
            echo '</div>' . "\n";
        }
        ?>
        <script>
            // Give barcode PNGs a moment to be written to disk before printing
            window.onload = function() {
                setTimeout(function() {
                    self.print();
                }, 1200);
            };
        </script>
    </body>

    </html>
<?php
    $html_str = ob_get_clean();

    // Clear queue
    unset($_SESSION[$session_key]);

    // ── Write HTML file ──────────────────────────────────────────────────────
    $upload_dir = defined('UPLOAD') ? UPLOAD : SB . 'files' . DS;
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0755, true)) {
            utility::jsToastr('Dual Label Print', __('Cannot create directory: ') . $upload_dir, 'error');
            exit();
        }
    }

    $file_name  = 'dual_label_print_' . strtolower(str_replace(' ', '_', $_SESSION['uname'] ?? 'user')) . '.html';
    $file_path  = $upload_dir . $file_name;
    if (file_put_contents($file_path, $html_str) === false) {
        utility::jsToastr('Dual Label Print', str_replace('{dir}', $upload_dir, __('Could not write file to {dir} — check permissions')), 'error');
        exit();
    }

    // ── Build URL for the file ──────────────────────────────────────────────
    $file_url = SWB . 'files/' . $file_name . '?v=' . date('YmdHis');
    if (defined('FLS')) {
        $file_url = SWB . FLS . '/' . $file_name . '?v=' . date('YmdHis');
    }

    // ── Output Colorbox script ──────────────────────────────────────────────
    echo '<script>
        top.document.getElementById("dlpQueueCount").innerHTML = "0";
        top.$.colorbox({
            href: "' . $file_url . '",
            iframe: true,
            width: 800,
            height: 500,
            title: "' . __('Dual Label Print') . '"
        });
    </script>';
    exit();
}

// ═══════════════════════════════════════════════════════════════════════════
// DEFAULT VIEW — search form + datagrid
// ═══════════════════════════════════════════════════════════════════════════
?>
<div class="menuBox">
    <div class="menuBoxInner printIcon">
        <div class="per_title">
            <h2><?php echo __('Dual Label Print'); ?></h2>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a target="blindSubmit"
                    href="<?php echo $_SERVER['PHP_SELF'] . '?' . dlp_http_query(['action' => 'clear']); ?>"
                    class="btn btn-default notAJAX">
                    <?php echo __('Clear Print Queue'); ?>
                </a>

                <!-- 🔧 FIX APPLIED: target="blindSubmit" allows the backend to return Colorbox JS correctly -->
                <a target="blindSubmit"
                    href="<?php echo $_SERVER['PHP_SELF'] . '?' . dlp_http_query(['action' => 'print']); ?>"
                    class="btn btn-primary notAJAX">
                    <?php echo __('Print Dual Labels'); ?>
                </a>
            </div>

            <form name="dlpSearch" id="dlpSearch" method="get"
                action="<?php echo $_SERVER['PHP_SELF'] . '?' . dlp_http_query(); ?>" class="form-inline" style="margin-top:8px;">
                <?php echo __('Search by Accession No. / Title / Author'); ?>&nbsp;
                <input type="text" name="keywords"
                    value="<?php echo isset($_GET['keywords']) ? htmlspecialchars($_GET['keywords']) : ''; ?>"
                    class="form-control col-md-3" />
                <input type="submit" value="<?php echo __('Search'); ?>"
                    class="btn btn-default" />
            </form>
        </div>

        <div class="infoBox">
            <?php
            echo __('Maximum') . ' <strong class="text-danger">' . $max_print . '</strong> '
                . __('items can be queued at once. Currently:') . ' ';
            echo '<strong id="dlpQueueCount" class="text-danger">' . dlp_queue_count() . '</strong>';
            echo ' ' . __('item(s) in queue.');
            ?>
        </div>
    </div>
</div>

<?php
// ── Datagrid ─────────────────────────────────────────────────────────────────
use SLiMS\SearchEngine\Engine;
use SLiMS\SearchEngine\SearchBiblioEngine;
use SLiMS\SearchEngine\SphinxSearchEngine;

require SIMBIO . 'simbio_UTILS/simbio_tokenizecql.inc.php';
require LIB    . 'biblio_list_model.inc.php';

$search_engine = Engine::active();
$is_sphinx     = ($search_engine == SphinxSearchEngine::class && file_exists(LIB . 'sphinx/sphinxapi.php'));

$datagrid = new simbio_datagrid();

if ($search_engine === SearchBiblioEngine::class || $is_sphinx) {
    if ($is_sphinx) {
        require LIB . 'sphinx/sphinxapi.php';
        require LIB . 'biblio_list_sphinx.inc.php';
    } else {
        require LIB . 'biblio_list_index.inc.php';
    }
    $table_spec = 'item LEFT JOIN search_biblio AS `index` ON item.biblio_id = `index`.biblio_id';
    $datagrid->setSQLColumn(
        'item.item_code',
        'item.item_code AS \'' . __('Accession No.') . '\'',
        'index.title AS \'' . __('Title') . '\'',
        'IF(item.call_number <> \'\', item.call_number, index.call_number) AS \'' . __('Call Number') . '\''
    );
} else {
    require LIB . 'biblio_list.inc.php';
    $table_spec = 'item LEFT JOIN biblio ON item.biblio_id = biblio.biblio_id';
    $datagrid->setSQLColumn(
        'item.item_code',
        'item.item_code AS \'' . __('Accession No.') . '\'',
        'biblio.title AS \'' . __('Title') . '\'',
        'IF(item.call_number <> \'\', item.call_number, biblio.call_number) AS \'' . __('Call Number') . '\''
    );
}

$datagrid->setSQLorder('item.last_update DESC');

// Search criteria
if (!empty($_GET['keywords'])) {
    $keywords = utility::filterData('keywords', 'get', true, true, true);
    if (!preg_match('@[a-z]+\s*=\s*@i', $keywords)) {
        $search_str = '';
        foreach (['title', 'author', 'subject', 'itemcode'] as $f) {
            $search_str .= $f . '=' . $keywords . ' OR ';
        }
    } else {
        $search_str = $keywords;
    }
    $biblio_list = new biblio_list($dbs, 20);
    $criteria    = $biblio_list->setSQLcriteria($search_str);
}
if (isset($criteria)) {
    $datagrid->setSQLcriteria('(' . $criteria['sql_criteria'] . ')');
}

// 🔧 CRITICAL: table id MUST be "dataList" for the core simbio_checkAll to work
$datagrid->table_attr        = 'id="dataList" class="s-table table"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight:bold;"';
$datagrid->edit_property     = false;
$datagrid->column_width      = ['10%', '50%', '35%'];

$datagrid->chbox_property      = ['itemCode', __('Add')];
$datagrid->chbox_action_button = __('Add To Print Queue');
$datagrid->chbox_confirm_msg   = __('Add selected items to the dual-label print queue?');
$datagrid->chbox_form_URL      = $_SERVER['PHP_SELF'] . '?' . dlp_http_query();

$result = $datagrid->createDataGrid($dbs, $table_spec, 20, $can_read);

if (!empty($_GET['keywords'])) {
    $msg = str_replace(
        '{result->num_rows}',
        $datagrid->num_rows,
        __('Found <strong>{result->num_rows}</strong> from your keywords')
    );
    echo '<div class="infoBox">' . $msg . ' : "'
        . htmlspecialchars($_GET['keywords']) . '"'
        . '<div>' . __('Query took') . ' <b>' . $datagrid->query_time . '</b> ' . __('second(s)') . '</div></div>';
}

echo $result;
