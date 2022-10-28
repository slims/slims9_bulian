<?php
/**
 * SLiMS Library for limiting access to modules by IP address
 *
 * Copyright (C) 2011  Hendro Wicaksono (hendrowicaksono@yahoo.com)
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
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

if (!function_exists('do_checkIP')) {
    function do_checkIP ($module = 'general')
    {
        global $sysconf;
        if (isset($sysconf['ipaccess'][''.$module.''])) {
            $accmod = $sysconf['ipaccess'][''.$module.''];
        } else {
            die ('Invalid access module');
        }
        #$accmod = $sysconf['ipaccess'][''.$module.''];
        $is_allowed = false;
        $remote_addr = ip();
        if (($accmod != 'all') AND (is_array($accmod))) {
            foreach ($accmod as $value) {
                $pattern = "/^".$value."/i";
                if (preg_match($pattern, $remote_addr)) {
                    $is_allowed = true;
                }
            }
        } elseif ($accmod == 'all') {
            $is_allowed = true;
        } else {
            $is_allowed = false;
        }
        if (!$is_allowed) {
            echo 'Stop here! Access not allowed.';
            exit();
        }
    }
}

do_checkIP();
