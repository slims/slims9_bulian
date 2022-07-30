<?php
/**
 * SENAYAN application lightweight bootstrap files
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some modifications & patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

require '../sysconfig.inc.php';
// set cookie
$cookie_path = preg_replace('@m\/*@i', '', SENAYAN_WEB_ROOT_DIR);
// create cookies of lightweight mode
if (isset($_GET['fullsite'])) {
    #@setcookie('FULLSITE_MODE', 1, time()+43200, $cookie_path);
    #@setcookie('FULLSITE_MODE', 1, time()+43200, $cookie_path, "", FALSE, TRUE);

    @setcookie('FULLSITE_MODE', 1, [
        'expires' => time()+43200,
        'path' => $cookie_path,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);


} else {
	// remove cookies
	#@setcookie('FULLSITE_MODE', 0, time()-43200, $cookie_path);
	#@setcookie('FULLSITE_MODE', 0, time()-43200, $cookie_path, "", FALSE, TRUE);

    @setcookie('FULLSITE_MODE', 0, [
        'expires' => time()-43200,
        'path' => $cookie_path,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);



}
// redirect to main bootstrap
header('Location: ../index.php');
?>
