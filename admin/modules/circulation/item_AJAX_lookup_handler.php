<?php
/**
 * Handler script for Item data AJAX Lookup.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// key to authenticate
define('INDEX_AUTH', '1');

require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-circulation');
// session checking
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

$table_fields = trim($_POST['tableFields']);

if (isset($_POST['keywords']) AND !empty($_POST['keywords'])) {
    $keywords = $dbs->escape_string(urldecode(trim($_POST['keywords'])));
} else {
    $keywords = '';
}

// explode table fields data
$fields = str_replace(':', ', ', $table_fields);
// set where criteria
$criteria = '';
foreach (explode(':', $table_fields) as $field) {
    $criteria .= " $field LIKE '%$keywords%' OR";
}
// remove the last OR
$criteria = substr_replace($criteria, '', -2);

// sql string
$sql_string = "SELECT DISTINCT i.biblio_id, b.title, i.item_code FROM item AS i
    INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id WHERE ".$criteria." LIMIT 5";

// send query to database
$query = $dbs->query($sql_string);
$error = $dbs->error;
if ($error) {
    die('<option value="0">SQL ERROR : '.$error.'</option>');
}

if ($query->num_rows > 0) {
    while ($row = $query->fetch_row()) {
        echo '<option value="'.$row[2].'">'.$row[2].' - '.$row[1].'</option>'."\n";
    }
} else {
    // output the SQL string
    // echo '<option value="0">'.$sql_string.'</option>';
    echo '<option value="0">NO DATA FOUND</option>';
}
