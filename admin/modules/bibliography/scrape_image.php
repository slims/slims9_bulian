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

use SLiMS\Json;
use SLiMS\Http\Client;

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
if (!($can_read && $can_write)) {
    $response = Json::stringify(['status' => 'UNAUTHORIZED', 'message' => 'Unauthorized Access!'])->withHeader();
    exit($response);
}

try {
    // field check
    if (!isset($_POST['imageURL']) && empty($_POST['imageURL'])) throw new Exception(__('URL can\'t empty!'));

    // imageURL must be a valid URL format
    $url = $_POST['imageURL'];
    if (!filter_var($url, FILTER_VALIDATE_URL)) throw new Exception(__('URL not valid!'));

    // Get image from another service
    $stream = Client::get($url, [
        'headers' => [
            'User-Agent' => $_SERVER['HTTP_USER_AGENT']
        ]
    ]);

    // Get image info from string
    $imageInfo = getimagesizefromstring($image = $stream->getContent());

    if (!$imageInfo) throw new Exception(__('Image is not valid!'));
    
    $src = 'data:' . $imageInfo['mime'] . ';base64,'. ($encodedImage = base64_encode($image));
    $type = str_replace('image/', '',$imageInfo['mime']);
    $result = $encodedImage. '#image/type#' . $type;
    exit(Json::stringify(['status' => 'VALID', 'message' => $src, 'image' => $result])->withHeader());

} catch (Exception $e) {
    $response = Json::stringify(['status' => 'INVALID', 'message' => $e->getMessage()])->withHeader();
    exit($response);
}
