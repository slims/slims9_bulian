<?php
/**
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

/* Send ALL Catalog data to UCS server */

// check if we are running from command line or not
if (php_sapi_name() != 'cli') {
  die('This script intended to be run on command line interface only!');
}

set_time_limit(0);

define('INDEX_AUTH', 1);

// main system configuration
require '../../../sysconfig.inc.php';

// load settings
utility::loadSettings($dbs);

// check if UCS is enabled or not
if (!$sysconf['ucs']['enable']) {
  die(__('UCS is not enabled! Change global system configuration to enable UCS'));
}

require LIB.'http_request.inc.php';

// fetch all data from biblio table
$sql = "SELECT
    b.biblio_id, b.title, b.spec_detail_info, gmd.gmd_code, gmd.gmd_name, b.edition,
    b.isbn_issn, publ.publisher_name, b.publish_year,
    b.collation, b.series_title, b.call_number, lang.language_id,
    lang.language_name, pl.place_name, b.classification, b.notes, fr.frequency
    FROM biblio AS b
    LEFT JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
    LEFT JOIN mst_publisher AS publ ON b.publisher_id=publ.publisher_id
    LEFT JOIN mst_language AS lang ON b.language_id=lang.language_id
    LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id
    LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id";

// invoke query
$q = @$dbs->query($sql);
// die(json_encode(array('status' => 'DEBUG_SQL', 'message' => $sql)));
if ($dbs->error) {
    die('Error on database: '.$dbs->error);
}

echo "Starting send data to UCS server... \n"; flush();

$data = array();
// loop record and poll it in an array
while ($d = $q->fetch_assoc()) {
    $id = (integer)$d['biblio_id'];
    $data[$id] = $d;
    $data[$id]['authors'] = array();
    $data[$id]['subjects'] = array();
    // author
    $author_q = @$dbs->query("SELECT a.author_name, ba.level, a.authority_type, a.auth_list FROM biblio_author AS ba
        LEFT JOIN mst_author AS a ON ba.author_id=a.author_id
        WHERE ba.biblio_id=$id ORDER BY level ASC");
    while ($author_d = $author_q->fetch_row()) { $data[$id]['authors'][] = array('name' => $author_d[0], 'level' => $author_d[1], 'type' => $author_d[2], 'auth_list' => $author_d[3]); }
    // subject
    $topic_q = @$dbs->query("SELECT t.topic, bt.level, t.topic_type, t.auth_list FROM biblio_topic AS bt
        LEFT JOIN mst_topic AS t ON bt.topic_id=t.topic_id
        WHERE bt.biblio_id=$id ORDER BY level ASC");
    while ($topic_d = $topic_q->fetch_row()) { $data[$id]['subjects'][] = array('name' => $topic_d[0], 'level' => $topic_d[1], 'type' => $topic_d[2], 'auth_list' => $topic_d[3]); }
}

// encode array to json format
if ($data) {
    $to_sent['node_info'] = $sysconf['ucs'];
    $to_sent['node_data'] = $data;
    // create HTTP request
    $http_request = new http_request();
    // send HTTP POST request
    $http_request->send_http_request($sysconf['ucs']['serveraddr'].'/ucpoll.php', @gethostbyaddr(), $to_sent, 'POST', 'text/json');
    // below is for debugging purpose only
	// die($http_request->body());

	// check for http request error
	if ($req_error = $http_request->error()) {
		echo $req_error['message']."\n";
        exit(6);
	}

    // print out body of request result
    $response = json_decode($http_request->body(), true);
    echo $response['message']."\n";
    exit(0);
} else {
    exit(0);
}
?>
