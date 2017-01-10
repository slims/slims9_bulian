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

$table_name = $dbs->escape_string(trim($_POST['tableName']));
$table_fields = $dbs->escape_string(trim($_POST['tableFields']));
if (isset($_POST['id']) AND !empty($_POST['id'])) {
  $id = $dbs->escape_string(trim($_POST['id']));
} else {
  die('<strong style="color: #FF0000;">' . __('No ID Supplied!') . '</strong>');
}

// sql string
$sql_string = "SELECT $table_fields FROM $table_name WHERE $table_fields='$id' LIMIT 1";

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
