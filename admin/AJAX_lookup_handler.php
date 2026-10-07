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