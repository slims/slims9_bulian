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

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// rest for a while
sleep(1);
require '../../sysconfig.inc.php';

header('Content-type: text/javascript');
// get search value
if (isset($_POST['inputSearchVal'])) {
    $searchVal = $dbs->escape_string(urldecode(trim($_POST['inputSearchVal'])));
} else {
    exit();
}
// query to database
if ($_POST['type'] == 'author') {
    $data_q = $dbs->query("SELECT author_name
        FROM mst_author WHERE author_name LIKE '%$searchVal%' LIMIT 5");
} else {
    $data_q = $dbs->query("SELECT topic
        FROM mst_topic WHERE topic LIKE '%$searchVal%' LIMIT 5");
}
if ($data_q->num_rows < 1) {
    exit();
}
$json_array = array();
// loop data
while ($data_d = $data_q->fetch_row()) {
    $json_array[] = $data_d[0];
}
// encode to JSON array
if (!function_exists('json_encode')) {
    die();
}
echo json_encode($json_array);
