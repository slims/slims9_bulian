<?php
/**
 * Session variables check
 *
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

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

// check session
$unauthorized = !isset($_SESSION['uid']) && !isset($_SESSION['uname']) && !isset($_SESSION['realname']);
if ($unauthorized) {
    $msg = '<script type="text/javascript">'."\n";
    $msg .= 'alert(\''.__('You are not authorized to view this section').'\');'."\n";
    $msg .= 'top.location.href = \''.SWB.'index.php?p=login\';'."\n";
    $msg .= '</script>'."\n";
    // unset cookie admin flag
    #setcookie('admin_logged_in', false, time()-86400, SWB);
    #setcookie('admin_logged_in', false, time()-86400, SWB, "", FALSE, TRUE);

    setcookie('admin_logged_in', FALSE, [
        'expires' => time()-86400,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);


    simbio_security::destroySessionCookie($msg, COOKIES_NAME, SWB.'admin', true);
}

// checking session checksum
if ($sysconf['load_balanced_env']) {
    $server_addr = ip();
} else {
    $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
}

$unauthorized = $_SESSION['checksum'] != md5($server_addr.SB.'admin');
if ($unauthorized) {
    $msg = '<div style="padding: 5px; border: 1px dotted #FF0000; color: #FF0000;">';
    $msg .= __('You are not authorized to view this section');
    $msg .= '</div>'."\n";
    // unset cookie admin flag
    #setcookie('admin_logged_in', true, time()-86400, SWB);
    #setcookie('admin_logged_in', true, time()-86400, SWB, "", FALSE, TRUE);

    setcookie('admin_logged_in', TRUE, [
        'expires' => time()-86400,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);


    simbio_security::destroySessionCookie($msg, COOKIES_NAME, SWB.'admin', true);
}

// check for session timeout
$curr_timestamp = time();
$timeout = ($curr_timestamp-$_SESSION['logintime']) >= $sysconf['session_timeout'];
if ($timeout && !isset($_SESSION['remember_me'])) {
    $msg = '<div style="font-family: Arial, sans-serif; text-align:center; padding: 20px; margin: 20px; border: 2px solid #F00; color: #F00;">';
    $msg .= __('Your Login session has timed out.').' <a target="_top" href="'.SWB.'index.php?p=login" style="text-decoration: underline; color: #000;">'.__('Click here to Login again').'</a>';
    $msg .= '</div>'."\n";
    // unset cookie admin flag
    #setcookie('admin_logged_in', true, time()-86400, SWB);
    #setcookie('admin_logged_in', true, time()-86400, SWB, "", FALSE, TRUE);

    setcookie('admin_logged_in', TRUE, [
        'expires' => time()-86400,
        'path' => SWB,
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);



    simbio_security::destroySessionCookie($msg, COOKIES_NAME, SWB.'admin', true);
} else {
    // renew session logintime
    $_SESSION['logintime'] = time();
}
