<?php
/**
 * Copyright (C) 2017  Arie Nugraha (dicarve@gmail.com)
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

/* Update or Delete Catalog data on UCS server */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require LIB.'http_request.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

// sent HTTP header
header('Content-type: text/json');

if (!($can_read && $can_write)) {
    die(json_encode(array('status' => 'UNAUTHORIZED', 'message' => 'Unauthorized Access!')));
}

// load settings
utility::loadSettings($dbs);

if (isset($_POST['itemID']) && !empty($_POST['itemID']) && isset($_POST['nodeOperation'])) {
    $biblioIDS = '';
    // concat all ID
    if (is_array($_POST['itemID'])) {
	foreach ($_POST['itemID'] as $itemID) {
	    $biblioID = (integer)$itemID;
	    $biblioIDS .= $biblioID.',';
	}
	// remove last comma
	$biblioIDS = substr_replace($biblioIDS, '', -1);
    } else {
	$biblioIDS = (integer)$_POST['itemID'];
    }

    // node data
    if ($_POST['nodeOperation'] == 'delete') {
	$data = array(
	    'operation' => 'delete',
	    'biblio' => $biblioIDS
	);
    }

    // encode array to json format
    $to_sent['node_info'] = $sysconf['ucs'];
    $to_sent['node_data'] = $data;
    // create HTTP request
    $http_request = new \GuzzleHttp\Client();
    $http_param = ['body' => json_encode($to_sent), 'http_errors' => false];

    if (isset($sysconf['ucs']['serverversion']) && $sysconf['ucs']['serverversion'] < 3) {
        $request = $http_request->request('POST', $sysconf['ucs']['serveraddr'].'/uc-ops.php', $http_param);
    } else {
        $request = $http_request->request('POST', $sysconf['ucs']['serveraddr'].'/ucs.php', $http_param);
    }

    // below is for debugging purpose only
    // die(json_encode(array('status' => 'RAW', 'message' => $request->getBody())));

    // check for http request error
    if ($request->getStatusCode() != '200') {
        die(json_encode(array('status' => 'HTTP_REQUEST_ERROR', 'message' => 'HTTP Request error with code : ' . $request->getStatusCode())));
    }

    // print out body of request result
    echo $request->getBody();
    exit();
} else {
    die(json_encode(array('status' => 'NO_BIBLIO_SELECTED', 'message' => 'Please select bibliographic data to update!')));
}
