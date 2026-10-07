<?php
/**
 * Loan History Maintenance – regenerate or fix loan_history data              *
 *                                                                             *   
 * File: loan_history_maintenance.php                                          *
 * Project: circulation                                                        *
 * Created Date: Saturday, September 13th 2025, 1:25:57 pm                     *
 * Author: Waris Agung Widodo <ido.alit@gmail.com>                             *
 * -----                                                                       *
 * Last Modified: Sat Sep 13 2025                                              *
 * Modified By: Waris Agung Widodo                                             *
 * -----                                                                       *
 * Copyright (c) 2025 Waris Agung Widodo                                       *
 * -----                                                                       *
 * HISTORY:                                                                    *
 * Date         By  Comments                                                   *
 * ----------   --- ---------------------------------------------------------  *
 */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB . 'admin/default/session.inc.php';
}
// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');

require SB . 'admin/default/session_check.inc.php';

// privileges checking
$can_read = utility::havePrivilege('circulation', 'r');
$can_write = utility::havePrivilege('circulation', 'w');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

if ($_SESSION['uid'] != 1 && !utility::haveAccess('circulation.loan-history-maintenance')) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

if (empty($_SESSION['csrf_token']) || is_array($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$session_csrf = is_array($_SESSION['csrf_token']) ? (($_SESSION['csrf_token'][0] ?? '')) : $_SESSION['csrf_token'];

// Utilities
function _echo($message)
{
    echo $message . "<br />\n";
    @ob_flush();
    @flush();
}

// Handle actions (run in iframe target for streaming logs) via POST and CSRF validation
if (isset($_POST['action']) && $_POST['action'] === 'run') {
    // Basic header for output stream
    echo '<div style="font-family: monospace; padding: 10px;">';

    $post_csrf = is_array($_POST['csrf_token'] ?? null) ? '' : ($_POST['csrf_token'] ?? '');

    // Validate CSRF Token securely
    if (empty($post_csrf) || !hash_equals($session_csrf,$post_csrf)) {
        _echo('<span style="color:red">' . __('Invalid form submission token!') . '</span>');
        echo '</div>';
        exit();
    }

    // long running safety
    @set_time_limit(0);
    @ob_implicit_flush(true);

    $task = $_POST['task'] ?? '';

    _echo('<strong>' . __('Loan History Maintenance') . '</strong>');
    _echo(date('Y-m-d H:i:s'));
    _echo(str_repeat('-', 60));

    // Stats helper
    $stats = function () use ($dbs) {
        $loan_total = 0; $hist_total = 0; $missing = 0;
        $q = $dbs->query('SELECT COUNT(*) FROM loan');
        if ($q) { $r = $q->fetch_row(); $loan_total = (int)($r[0] ?? 0); }
        $q = $dbs->query('SELECT COUNT(*) FROM loan_history');
        if ($q) { $r = $q->fetch_row(); $hist_total = (int)($r[0] ?? 0); }
        $q = $dbs->query('SELECT COUNT(*) FROM loan l LEFT JOIN loan_history h ON h.loan_id=l.loan_id WHERE h.loan_id IS NULL');
        if ($q) { $r = $q->fetch_row(); $missing = (int)($r[0] ?? 0); }
        return [$loan_total, $hist_total, $missing];
    };

    // Common INSERT SELECT used to populate history
    $insert_sql_all = <<<SQL
INSERT IGNORE INTO loan_history 
  (`loan_id`, `item_code`, `biblio_id`, `member_id`, `loan_date`, `due_date`, `renewed`, `is_lent`, `is_return`, `return_date`, `input_date`, `last_update`, `title`, `call_number`, `classification`, `gmd_name`, `language_name`, `location_name`, `collection_type_name`, `member_name`, `member_type_name`)
SELECT l.loan_id,
       l.item_code,
       b.biblio_id,
       l.member_id,
       l.loan_date,
       l.due_date,
       l.renewed,
       l.is_lent,
       l.is_return,
       l.return_date,
       IF(DAY(l.input_date) IS NULL, NULL, l.input_date),
       IF(DAY(l.last_update) IS NULL, NULL, l.last_update),
       b.title,
       IF(i.call_number IS NULL, b.call_number, i.call_number),
       b.classification,
       g.gmd_name,
       ml.language_name,
       mlc.location_name,
       mct.coll_type_name,
       m.member_name,
       mmt.member_type_name
FROM loan l
LEFT JOIN item i ON i.item_code = l.item_code
LEFT JOIN biblio b ON b.biblio_id = i.biblio_id
LEFT JOIN mst_gmd g ON g.gmd_id = b.gmd_id
LEFT JOIN mst_language ml ON ml.language_id = b.language_id
LEFT JOIN mst_location mlc ON mlc.location_id = i.location_id
LEFT JOIN member m ON m.member_id = l.member_id
LEFT JOIN mst_coll_type mct ON mct.coll_type_id = i.coll_type_id
LEFT JOIN mst_member_type mmt ON mmt.member_type_id = m.member_type_id
WHERE m.member_id IS NOT NULL AND b.biblio_id IS NOT NULL
SQL;

    // Same INSERT, but only for missing (anti-join to loan_history)
    $insert_sql_missing = $insert_sql_all . "\nAND l.loan_id NOT IN (SELECT loan_id FROM loan_history)";

    $start = microtime(true);

    switch ($task) {
        case 'stats':
            list($loan_total, $hist_total, $missing) = $stats();
            _echo('Total loan: ' . number_format($loan_total));
            _echo('Total loan_history: ' . number_format($hist_total));
            _echo('Missing loan_history rows: ' . number_format($missing));
            break;

        case 'empty':
            if (!($can_write)) {
                _echo('<span style="color:red">' . __('You are not authorized to perform this action') . '</span>');
                break;
            }
            _echo(__('Truncating loan_history ...'));
            $ok = $dbs->query('TRUNCATE TABLE loan_history');
            if ($ok !== false && !$dbs->error) {
                writeLog('staff', $_SESSION['uid'], 'Circulation', 'Loan History truncated', 'Maintenance', 'Delete');
                _echo('<span style="color:green">' . __('OK') . '</span>');
            } else {
                _echo('<span style="color:red">' . __('Failed') . ' : ' . $dbs->error . '</span>');
            }
            break;

        case 'generate_missing':
            if (!($can_write)) {
                _echo('<span style="color:red">' . __('You are not authorized to perform this action') . '</span>');
                break;
            }
            list($loan_total, $hist_total, $missing_before) = $stats();
            _echo('Loan: ' . number_format($loan_total) . ' | History: ' . number_format($hist_total) . ' | Missing: ' . number_format($missing_before));
            _echo(__('Generating missing loan_history rows ...'));
            $ok = $dbs->query($insert_sql_missing);
            if ($ok === false) {
                _echo('<span style="color:red">' . __('Failed') . ' : ' . $dbs->error . '</span>');
                break;
            }
            $affected = (int)$dbs->affected_rows;
            list($_lt, $_ht, $missing_after) = $stats();
            _echo('<span style="color:green">' . sprintf(__('Inserted %d rows'), $affected) . '</span>');
            _echo('Remaining missing: ' . number_format($missing_after));
            writeLog('staff', $_SESSION['uid'], 'Circulation', "Generate missing loan_history (inserted $affected)", 'Maintenance', 'Update');
            break;

        case 'rebuild_all':
            if (!($can_write)) {
                _echo('<span style="color:red">' . __('You are not authorized to perform this action') . '</span>');
                break;
            }
            _echo(__('Rebuilding all loan_history entries ...'));
            // Empty first
            $ok = $dbs->query('TRUNCATE TABLE loan_history');
            if ($ok === false) {
                _echo('<span style="color:red">' . __('Failed to truncate') . ' : ' . $dbs->error . '</span>');
                break;
            }
            // Reinsert all
            $ok = $dbs->query($insert_sql_all);
            if ($ok === false) {
                _echo('<span style="color:red">' . __('Rebuild failed') . ' : ' . $dbs->error . '</span>');
                break;
            }
            $affected = (int)$dbs->affected_rows;
            list($loan_total, $hist_total, $missing) = $stats();
            _echo('<span style="color:green">' . sprintf(__('Done. Inserted %d rows'), $affected) . '</span>');
            _echo('Loan: ' . number_format($loan_total) . ' | History: ' . number_format($hist_total) . ' | Missing: ' . number_format($missing));
            writeLog('staff', $_SESSION['uid'], 'Circulation', "Rebuild all loan_history (inserted $affected)", 'Maintenance', 'Re-create');
            break;

        default:
            _echo(__('No task specified.'));
            break;
    }

    $elapsed = microtime(true) - $start;
    _echo(str_repeat('-', 60));
    _echo(sprintf(__('Finished in %.2f second(s)'), $elapsed));
    echo '</div>';
    exit;
}

// Initial stats for page
$loan_total = $hist_total = $missing = 0;
$q = $dbs->query('SELECT COUNT(*) FROM loan');
if ($q) { $r = $q->fetch_row(); $loan_total = (int)($r[0] ?? 0); }
$q = $dbs->query('SELECT COUNT(*) FROM loan_history');
if ($q) { $r = $q->fetch_row(); $hist_total = (int)($r[0] ?? 0); }
$q = $dbs->query('SELECT COUNT(*) FROM loan l LEFT JOIN loan_history h ON h.loan_id=l.loan_id WHERE h.loan_id IS NULL');
if ($q) { $r = $q->fetch_row(); $missing = (int)($r[0] ?? 0); }
?>

<div class="menuBox">
    <div class="menuBoxInner circulationIcon">
        <div class="per_title">
            <h2><?php echo __('Loan History Maintenance'); ?></h2>
        </div>
        <div class="sub_section mb-2">
            <form method="post" action="<?php echo MWB; ?>circulation/loan_history_maintenance.php?action=run" target="progress" style="display:inline-block;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($session_csrf); ?>">
                <input type="hidden" name="action" value="run">
                <div class="btn-group">
                    <button type="submit" name="task" value="generate_missing" class="btn btn-default"><?php echo __('Generate Missing'); ?></button>
                    <button type="submit" name="task" value="rebuild_all" class="btn btn-danger" onclick="return confirm('<?php echo __('This will rebuild all loan history. Continue?'); ?>')"><?php echo __('Rebuild All'); ?></button>
                    <button type="submit" name="task" value="empty" class="btn btn-warning" onclick="return confirm('<?php echo __('This will empty loan history table. Continue?'); ?>')"><?php echo __('Empty History'); ?></button>
                    <button type="submit" name="task" value="stats" class="btn btn-default"><?php echo __('Refresh Stats'); ?></button>
                </div>
            </form>
        </div>
        <div class="infoBox">
            <?php echo __('Re-indexes and/or regenerates loan history data by rebuilding history records from current loan entries'); ?>
            <div class="mt-2">
                <strong><?php echo __('Current Stats'); ?>:</strong>
                <span class="ml-2"><?php echo __('Loan'); ?>: <strong><?php echo number_format($loan_total); ?></strong></span>
                <span class="ml-2"><?php echo __('Loan History'); ?>: <strong><?php echo number_format($hist_total); ?></strong></span>
                <span class="ml-2"><?php echo __('Missing'); ?>: <strong><?php echo number_format($missing); ?></strong></span>
            </div>
        </div>
    </div>
</div>

<iframe name="progress" id="progress" class="w-100" style="height: 320px; border: 1px solid #ddd;"></iframe>