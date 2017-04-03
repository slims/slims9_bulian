<?php
/**
 *
 * Librarian login page
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

$errmsg = NULL;
if (isset($_GET['errnum'])) {
    if ($_GET['errnum'] === '601') {
        $errmsg = '<div class="alert alert-danger" role="alert">You have no authorization to download this file.</div>';
    } else {
        $errnum = FALSE;
    }
} else {
    $errnum = FALSE;
}

if ((!$errnum) AND (!is_null($errmsg))) {
    echo $errmsg;
} else {
    header("location:index.php");
}

