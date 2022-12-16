<?php
/**
 *
 * Member SESSION Settings
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 * Taken and modified from phpMyAdmin's Session library
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

use SLiMS\Session\Factory as SessionFactory;
use SLiMS\Session\Driver\Files;

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// use session factory to handle session based on default SLiMS or user handler
SessionFactory::use(config('customSession', Files::class))->start('memberArea');
