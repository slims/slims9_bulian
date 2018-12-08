<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Modified by Waris Agung Widodo (ido.alit@gmail.com)
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
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// list limit
$limit = 20;

$table_name = $dbs->escape_string(trim($_POST['tableName']));
$table_fields = trim($_POST['tableFields']);

if (isset($_POST['keywords']) AND !empty($_POST['keywords'])) {
  $keywords = $dbs->escape_string(urldecode(ltrim($_POST['keywords'])));
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

$sql_string = "SELECT $fields ";

// append table name
$sql_string .= " FROM $table_name ";
if ($criteria) { $sql_string .= " WHERE $criteria LIMIT $limit"; }

// send query to database
$query = $dbs->query($sql_string);
$error = $dbs->error;
$data = array();

if (isset($_GET['format'])) {
  if ($_GET['format'] == 'json') {
	  if ($error) { echo json_encode(array('id' => 0, 'text' => $error)); }
	  if ($query->num_rows > 0) {
	    while ($row = $query->fetch_row()) {
	  	$data[] = array('id' => $row[0], 'text' => $row[1].(isset($row[2])?' - '.$row[2]:'').(isset($row[3])?' - '.$row[3]:''));
	    }
	  } else {
		  if (isset($_GET['allowNew'])) {
			  $data[] = array('id' => 'NEW:'.$keywords, 'text' => $keywords.' &lt;'.__('Add New').'&gt;');
			} else {
		    $data[] = array('id' => 'NONE', 'text' => 'NO DATA FOUND');
			}
	  }
	  echo json_encode($data);
  }
	exit();
} else {
	if ($error) { echo '<option value="0">'.$error.'</option>'; }
	if ($query->num_rows < 1) {
	  // output the SQL string
	  // echo '<option value="0">'.$sql_string.'</option>';
	  echo '<li>NO DATA FOUND</li>'."\n";
	} else {
	  while ($row = $query->fetch_row()) {

	  	$output  = '<li>';
	  	$output .= '<span class="voc" style="font-style:italic;padding: 0 5px; border-radius:2px;">'.$row[1].(isset($row[2])?'</span> - '.$row[2]:'').(isset($row[3])?' - '.$row[3]:'');
	  	$output .= '<ul style="padding-left:20px; list-style:none;">';
	  	$li = '';
	  	$scope = '';

	  	$voc_q = $dbs->query('SELECT rt_id, related_topic_id, scope FROM mst_voc_ctrl WHERE topic_id='.$row[0]);
	  	while ($voc_d = $voc_q->fetch_row()) {
	  		if (is_null($voc_d[2])) {
	  			$topic_q = $dbs->query('SELECT topic, topic_type FROM mst_topic WHERE topic_id='.$voc_d[1]);
		  		$topic_d = $topic_q->fetch_row();
		  		$li .= '<li><strong style="margin-right:10px;">'.__($voc_d[0]).'</strong><span style="padding: 0 5px; border-radius:2px;" class="voc">'.$topic_d[0].' - '.$topic_d[1].'</span></li>';
	  		} else {
	  			$scope = $voc_d[2];
	  		}
	  	}

	  	$output .= $scope.$li;

	  	$output .= '</ul>';
	  	$output .= '</li>';

	  	echo $output;
	  }
	}
	exit();
}
