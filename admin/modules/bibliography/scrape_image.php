<?php
/**
 * Copyright (C) 2020  Eddy (eddy.subratha@gmail.com)
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

/* Image Processing */

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

if (isset($_POST['imageURL']) && !empty($_POST['imageURL'])) {
    $url = $_POST['imageURL'];
    $img = file_get_contents($url);
    $url_info = pathinfo($url);
    $src = 'data:image/jpg;base64,'. base64_encode($img);
    $image = base64_encode($img).'#image/type#jpg';
    echo json_encode(array('status' => 'VALID', 'message' => $src, 'image' => $image));
}
 else {
    echo json_encode(array('status' => 'NOT_VALID', 'message' => __('URL not valid!')));
}
