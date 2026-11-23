<?php

// key to authenticate
define('INDEX_AUTH', '1');

/*
A Handler script for AJAX ID checking
Arie Nugraha 2007
*/

require_once '../sysconfig.inc.php';
// session checking
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privilege gate: this endpoint only serves the item and membership forms
if (($_SESSION['uid'] ?? 0) != 1
  && !utility::havePrivilege('bibliography', 'r')
  && !utility::havePrivilege('membership', 'r')) {
  die('<strong style="color: #FF0000;">' . __('You don\'t have enough privileges to access this area!') . '</strong>');
}

/**
 * Allowlist of table => ID columns that may be checked through this endpoint.
 * Table and column names are SQL identifiers, so they can not be protected by
 * escaping; only known pairs are accepted. Extend it through the
 * `check_id_whitelist` config key (table => [columns]) when needed.
 */
$check_id_whitelist = [
  'item'   => ['item_code'],
  'member' => ['member_id'],
];
foreach ((array) config('check_id_whitelist', []) as $wl_table => $wl_columns) {
  if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$wl_table)) continue;
  foreach ((array) $wl_columns as $wl_column) {
    if (preg_match('/^[a-zA-Z0-9_]+$/', (string)$wl_column)) $check_id_whitelist[$wl_table][] = $wl_column;
  }
}

$table_name = trim((string)($_POST['tableName'] ?? ''));
$table_fields = trim((string)($_POST['tableFields'] ?? ''));
if (!isset($check_id_whitelist[$table_name]) || !in_array($table_fields, $check_id_whitelist[$table_name], true)) {
  die('<strong style="color: #FF0000;">' . __('Table is not allowed') . '</strong>');
}

if (isset($_POST['id']) AND !empty($_POST['id'])) {
  $id = $dbs->escape_string(trim($_POST['id']));
} else {
  die('<strong style="color: #FF0000;">' . __('No ID Supplied!') . '</strong>');
}

// sql string
$sql_string = "SELECT `$table_fields` FROM `$table_name` WHERE `$table_fields`='$id' LIMIT 1";

// send query to database
$query = $dbs->query($sql_string);
$error = $dbs->error;
if ($error) {
  die('SQL ERROR : '.$error);
}

if ($query->num_rows > 0) {
  echo '<strong style="color: #FF0000;">' . __('ID Already exists! Please use another ID') . '</strong>';
} else {
  // output the SQL string
  echo '<strong>' . __('ID Available') . '</strong>';
}
