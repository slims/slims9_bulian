<?php

/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/*
A Handler script for AJAX Lookup
Database
Arie Nugraha 2007
*/

// key to authenticate
define('INDEX_AUTH', '1');

require_once '../sysconfig.inc.php';
// session checking
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// receive json data if $_POST data empty
$rawInput = false;
if (empty($_POST)) {
	$rawInput = true;
	$_POST = json_decode(file_get_contents('php://input'), true);
}

/**
 * Helper to abort the request with a format-aware message.
 */
$lookup_abort = function (string $message) use ($rawInput) {
	if ($rawInput == false) exit('<option value="0">' . $message . '</option>');
	exit(json_encode(array('id' => 0, 'text' => $message)));
};

/**
 * Privilege gate.
 *
 * This is a shared, cross-module autocomplete endpoint, so it does not map
 * to a single module privilege. Require read access on at least one module
 * that legitimately uses lookups; the super administrator (uid 1) is always
 * allowed. Without this, any authenticated staff account could reach the
 * query below regardless of their assigned privileges.
 */
if (($_SESSION['uid'] ?? 0) != 1) {
	$lookup_modules = ['bibliography', 'master_file', 'circulation', 'serial_control', 'membership'];
	$has_lookup_privilege = false;
	foreach ($lookup_modules as $lookup_module) {
		if (utility::havePrivilege($lookup_module, 'r')) { $has_lookup_privilege = true; break; }
	}
	if (!$has_lookup_privilege) {
		$lookup_abort(__('You don\'t have enough privileges to access this area!'));
	}
}

// list limit
$limit = 20;

$table_state = explode(':', trim($_POST['tableName'] ?? ''));
$raw_table_name = trim($table_state[1] ?? $table_state[0]);
$table_fields = trim($_POST['tableFields'] ?? '');

if (!preg_match('/^[a-zA-Z0-9_]+$/', $raw_table_name)) {
    if ($rawInput == false) exit('<option value="0">' . __('Invalid table name format') . '</option>');
    else exit(json_encode(array('id' => 0, 'text' => __('Invalid table name format'))));
}
$table_name = $raw_table_name;

$fields_array = explode(':', $table_fields);
$validated_fields = [];

foreach ($fields_array as $field) {
    $field = trim($field);
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $field)) {
        if ($rawInput == false) exit('<option value="0">' . __('Invalid field name format') . '</option>');
        else exit(json_encode(array('id' => 0, 'text' => __('Invalid field name format'))));
    }
    $validated_fields[] = $field;
}

if (empty($validated_fields)) {
    if ($rawInput == false) exit('<option value="0">' . __('Fields cannot be empty') . '</option>');
    else exit(json_encode(array('id' => 0, 'text' => __('Fields cannot be empty'))));
}

/**
 * Allowlist of tables and the columns that may be read through this
 * endpoint.
 *
 * The regular expressions above only validate the *format* of the table and
 * field names, not that they are legitimate lookup targets. Because this
 * endpoint builds `SELECT <fields> FROM <table>` from client input, format
 * validation alone still allows reading any table/column (e.g. user.passwd
 * or member PII). The allowlist restricts both the table and every requested
 * column to the master-file data the autocomplete UI actually needs.
 *
 * Installations with custom master files can extend the list through the
 * `lookup_handler_whitelist` config key (table => [columns]).
 */
$lookup_whitelist = [
    'biblio'        => ['biblio_id', 'title', 'edition', 'publish_year'],
    'mst_author'    => ['author_id', 'author_name', 'author_year', 'authority_type'],
    'mst_topic'     => ['topic_id', 'topic', 'topic_type', 'classification'],
    'mst_place'     => ['place_id', 'place_name'],
    'mst_publisher' => ['publisher_id', 'publisher_name'],
];
foreach ((array) config('lookup_handler_whitelist', []) as $wl_table => $wl_columns) {
    $lookup_whitelist[$wl_table] = array_merge($lookup_whitelist[$wl_table] ?? [], (array) $wl_columns);
}

if (!isset($lookup_whitelist[$table_name])) {
    $lookup_abort(__('Table is not allowed'));
}
foreach ($validated_fields as $field) {
    if (!in_array($field, $lookup_whitelist[$table_name], true)) {
        $lookup_abort(__('Field is not allowed'));
    }
}

$table_fields = implode(':', $validated_fields);
$fields = implode(', ', $validated_fields);

if (isset($_POST['keywords']) and !empty($_POST['keywords'])) {
	$keywords = urldecode(ltrim($_POST['keywords']));
} else {
	if ($rawInput == false) exit('<option value="0">' . __('Keyword can\'t be empty') . '</option>');
	else exit(json_encode(array('id' => 0, 'text' => __('Keyword can\'t be empty'))));
}

$where_parts = [];
$params = [];
$types = '';

foreach ($validated_fields as $field) {
	$where_parts[] = " $field LIKE ? ";
	$types .= 's';
	$params[] = '%' . $keywords . '%';
}

$criteria_string = implode(' OR ', $where_parts);

$sql_string = "SELECT $fields ";

$sql_string .= " FROM $table_name ";
if ($criteria_string) {
	$sql_string .= " WHERE $criteria_string LIMIT $limit";
}

$error = '';
$query = null;

if ($stmt = $dbs->prepare($sql_string)) {

    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);

	if (!$stmt->execute()) {
        $error = $stmt->error;
    }

    $query = $stmt->get_result();

} else {
	$error = $dbs->error;
}

$data = array();

$headers = function_exists('getallheaders') ? getallheaders() : $_SERVER;
$contentType = $headers['Content-Type']??$headers['content-type']??$headers['CONTENT_TYPE']??'';

if ($contentType == 'application/json') {
	header('Content-Type: application/json');

	if ($error) {
		echo json_encode(array('id' => 0, 'text' => $error));
	}
	if ($query && $query->num_rows > 0) {
		$items = [];
		while ($row = $query->fetch_row()) {
			$data[] = array('id' => $row[0], 'text' => $row[1] . (isset($row[2]) ? ' - ' . $row[2] : '') . (isset($row[3]) ? ' - ' . $row[3] : ''));
			array_walk($row, function ($i) use (&$items) {
				$items[] = strtolower(trim($i));
			});
		}
		if (isset($_GET['allowNew']) && !in_array(strtolower(trim($keywords)), $items)) {
			$data = [['id' => 'NEW:' . $keywords, 'text' => $keywords . ' &lt;' . __('Add New') . '&gt;'], ...$data];
		}
	} else {
		if ($table_state[0] === 'new') {
			$data[] = array('id' => 'NEW:' . $keywords, 'text' => $keywords . ' &lt;' . __('Add New') . '&gt;');
		} else {
			$data[] = array('id' => 'NONE', 'text' => 'NO DATA FOUND');
		}
	}
    if (isset($stmt)) $stmt->close();
	exit(json_encode($data));
} else {
	if ($error) {
		echo '<option value="0">' . $error . '</option>';
	}
	if (!$query || $query->num_rows < 1) {
		echo '<option value="0">NO DATA FOUND</option>' . "\n";
	} else {
		while ($row = $query->fetch_row()) {
			echo '<option value="' . $row[0] . '">' . $row[1] . (isset($row[2]) ? ' - ' . $row[2] : '') . (isset($row[3]) ? ' - ' . $row[3] : '') . '</option>' . "\n";
		}
	}
    if (isset($stmt)) $stmt->close();
	exit();
}