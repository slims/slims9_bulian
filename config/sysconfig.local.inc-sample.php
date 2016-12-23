<?php
/**
 * SENAYAN application local configuration file
 *
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com), Wardiyono (wynerst@gmail.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/* LOCAL DATABASE CONNECTION config */
// database constant
// change below setting according to your database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'senayandb');
define('DB_USERNAME', 'senayanuser');
define('DB_PASSWORD', 'password_senayanuser');

// define any other sysconfig variables below
$sysconf['index']['type'] = 'index';

/**
 * UCS settings
 */
$sysconf['ucs']['enable'] = false;
// auto delete same record on UCS?
$sysconf['ucs']['auto_delete'] = true;
// auto insert new record to UCS?
$sysconf['ucs']['auto_insert'] = true;

// additional e-mail recipients for library administrator
/*
$sysconf['mail']['add_recipients'][] = array('from' => 'senayan.slims@slims.web.id', 'from_name' => 'Librarian 2');
$sysconf['mail']['add_recipients'][] = array('from' => 'wynerst@gmail.com', 'from_name' => 'Librarian 3');
*/
