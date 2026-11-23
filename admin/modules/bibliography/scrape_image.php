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
 * MERCHANTABILITY and FITNESS FOR A PARTICULAR PURPOSE.  See the
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
    if (!isset($_POST['imageURL']) || empty($_POST['imageURL'])) throw new Exception(__('URL can\'t empty!'));
    // make sure it is http request only
    $parsed_url = parse_url($_POST['imageURL']);
    if (!isset($parsed_url['scheme']) || !in_array(strtolower($parsed_url['scheme']), ['http','https'], true)) {
        throw new Exception(__('Must be http/https URL!'));
    }

    // imageURL must be a valid URL format
    $url = $_POST['imageURL'];
    if (!filter_var($url, FILTER_VALIDATE_URL)) throw new Exception(__('URL not valid!'));

    /**
     * Check one URL before it is requested: http/https only and the host must
     * resolve to a public address. Returns [host, port, ip] so the request can
     * be pinned to the address that was checked.
     */
    $checkTarget = function (string $target) {
        $parts = parse_url($target);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new Exception(__('Must be http/https URL!'));
        }
        // no private ip addresses
        $ip = gethostbyname($parts['host']);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new Exception('Access to addresses is not allowed');
        }
        return [$parts['host'], $parts['port'] ?? ($scheme === 'https' ? 443 : 80), $ip];
    };

    // Get image from another service using native cURL / file_get_contents to bypass SLiMS Client option compiling bugs
    try {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        $image = '';

        // redirects are followed by hand so every hop goes through the same check.
        // letting the HTTP client follow them would allow a public URL to
        // redirect the request to an internal address
        $maxRedirects = 5;
        for ($hop = 0; ; $hop++) {
            if ($hop > $maxRedirects) throw new Exception(__('URL not valid!'));
            [$targetHost, $targetPort, $targetIp] = $checkTarget($url);
            $location = null;

            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
                // connect to the address that was validated above
                curl_setopt($ch, CURLOPT_RESOLVE, [$targetHost . ':' . $targetPort . ':' . $targetIp]);
                curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                $image = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('Curl error: ' . curl_error($ch));
                }
                $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                if ($status >= 300 && $status < 400) {
                    $location = curl_getinfo($ch, CURLINFO_REDIRECT_URL) ?: null;
                }
                curl_close($ch);
            } else {
                $opts = [
                    'http' => [
                        'method' => 'GET',
                        'header' => "User-Agent: " . $userAgent . "\r\n",
                        'timeout' => 15,
                        'follow_location' => 0,
                        'ignore_errors' => true
                    ]
                ];
                $context = stream_context_create($opts);
                $image = @file_get_contents($url, false, $context);
                if ($image === false) {
                    throw new Exception('file_get_contents failed to fetch image');
                }
                $status = 0;
                foreach ($http_response_header ?? [] as $headerLine) {
                    if (preg_match('@^HTTP/\S+\s+(\d{3})@', $headerLine, $m)) $status = (int)$m[1];
                    if (preg_match('@^Location:\s*(\S+)@i', $headerLine, $m)) $location = $m[1];
                }
                if ($status < 300 || $status >= 400) $location = null;
            }

            if ($location === null) break;
            // only absolute redirect targets are followed
            if (!filter_var($location, FILTER_VALIDATE_URL)) throw new Exception(__('URL not valid!'));
            $url = $location;
        }
    } catch (\Exception $e) {
        $message = $e->getMessage();
        if (strpos($message, 'Curl error') !== false || strpos($message, 'timed out') !== false) {
             throw new Exception(__('Failed to connect to image source or connection timed out. Check the URL and server connectivity.'));
        }
        throw new Exception($message);
    }

    // Get image info from string
    $imageInfo = getimagesizefromstring($image);

    if (!$imageInfo) throw new Exception(__('Image is not valid!'));

    $mime = $imageInfo['mime'] ?? '';
    $ext = $mime ? ltrim(strtolower(image_type_to_extension($imageInfo[2], false)), '.') : '';
    $allowedMimes = array_merge($sysconf['allowed_images_mimetype'], ['image/jpg', 'image/gif', 'image/webp']);
    // $sysconf['allowed_images'] uses dotted format (e.g. ".jpg"), $ext has no dot — add it for comparison
    if (!$mime || !in_array($mime, $allowedMimes, true) || ($ext && !in_array('.' . $ext, $sysconf['allowed_images'], true))) {
        throw new Exception(__('Image type not allowed!'));
    }
    
    if (strlen($image) > 1 * 512 * 512) { // 512kb limit
        throw new Exception('Image larger than expected');
    }

    if (!$imageInfo || strpos($imageInfo['mime'], 'image/') !== 0) {
        throw new Exception('Invalid image');
    }

    if (extension_loaded('gd') && $img = @imagecreatefromstring($image)) {
        $mime = $imageInfo['mime'];
        $gd_failed = false;

        ob_start();
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($img, null, 100);
                break;
            case 'image/png':
                imagealphablending($img, false);
                imagesavealpha($img, true);
                imagepng($img, null, 9);
                break;
            case 'image/gif':
                imagegif($img, null);
                break;
            default:
                ob_end_clean();
                $gd_failed = true;
                break;
        }

        if (!$gd_failed) {
            $cleaned_image = ob_get_clean();
            if (!empty($cleaned_image)) {
                $image = $cleaned_image;
            }
        }

        imagedestroy($img);
    }

    $src = 'data:' . $imageInfo['mime'] . ';base64,'. ($encodedImage = base64_encode($image));
    $type = str_replace('image/', '',$imageInfo['mime']);
    $result = $encodedImage. '#image/type#' . $type;
    exit(Json::stringify(['status' => 'VALID', 'message' => $src, 'image' => $result])->withHeader());

} catch (Exception $e) {
    $response = Json::stringify(['status' => 'INVALID', 'message' => $e->getMessage()])->withHeader();
    exit($response);
}
