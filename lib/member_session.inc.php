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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}


// always use session cookies
@ini_set('session.use_cookies', true);
// use more secure session ids
@ini_set('session.hash_function', 1);
// no cache
@session_cache_limiter('nocache');
// set session name and start the session
@session_name(MEMBER_COOKIES_NAME);
// set session cookies params
@session_set_cookie_params(43200, SWB);
