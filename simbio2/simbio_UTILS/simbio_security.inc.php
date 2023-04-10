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
        #@setcookie($str_session_name, '', time()-86400, $str_cookie_path);
        #@setcookie($str_session_name, '', time()-86400, $str_cookie_path, "", FALSE, TRUE);

        @setcookie($str_session_name, '', [
            'expires' => time()-86400,
            'path' => $str_cookie_path,
            'domain' => '',
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    

        // destroy all session
        $_SESSION = null;
        session_destroy();

        /**
         * Check request content-type
         */
        // From getallhreaders()
        $contentType = isset(getallheaders()['Content-Type']) && getallheaders()['Content-Type'] == 'application/json';
        $accept = isset(getallheaders()['Accept']) && getallheaders()['Accept'] == 'application/json';

        // from $_SERVER
        $serverContentType = isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] == 'application/json';

        // from $_GET
        $queryFormat = isset($_GET['format']) && $_GET['format'] == 'json';

        // From $_POST
        if (count($_POST) === 0) $_POST = json_decode(file_get_contents('php://input'), TRUE);
        $postJson = isset($_POST['format']) && $_POST['format'] == 'json';

        $isJson = $contentType || $accept || $serverContentType || $queryFormat || $postJson;

        // bring back response
        if ($isJson) die(\SLiMS\Json::stringify(['status' => false, 'message' => __('Your Login session has timed out.'), 'code' => 401])->withHeader());

        if ($bool_die === true) {
            // shutdown current script
            die($str_msg);
        } else {
            if ($str_msg) { echo $str_msg; }
        }
    }


    /**
     * Static method to clean all string character
     * from html element and attributes
     *
     * @param string $str_char
     * @return string
     */
    public static function xssFree($str_char)
    {
        return str_replace(['\'', '"'], '', strip_tags($str_char));
    }
}
