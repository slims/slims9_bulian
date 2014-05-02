<?php
/**
 * simbio_security class
 * A Collection of static function for web security
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
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

class simbio_security
{
    /**
     * Static Method to redirect page to https equivalent
     *
     * @param   integer $int_https_port
     * @return  void
     */
    public static function doCheckHttps($int_https_port)
    {
        $server_https_port = $_SERVER['SERVER_PORT'];
        if ($server_https_port != $int_https_port) {
            $host =  $_SERVER['SERVER_NAME'];
            $https_url = 'https://'.$host.$_SERVER['PHP_SELF'];
            // send HTTP header
            header("location: $https_url");
        }
    }


    /**
     * Static Method to completely destroy session and its cookies
     *
     * @param   string  $str_msg
     * @param   boolean $bool_die
     * @return  void
     */
    public static function destroySessionCookie($str_msg, $str_session_name = '', $str_cookie_path = '/', $bool_die = false)
    {
        if (!$str_session_name) { $str_session_name = session_name(); }
        // deleting session browser cookie
        @setcookie($str_session_name, '', time()-86400, $str_cookie_path);
        // destroy all session
        $_SESSION = null;
        session_destroy();
        if ($bool_die === true) {
            // shutdown current script
            die($str_msg);
        } else {
            if ($str_msg) { echo $str_msg; }
        }
    }
}
?>
