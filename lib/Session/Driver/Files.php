<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-04 07:48:11
 * @modify date 2022-12-04 08:26:05
 * @license GPLv3
 * @desc [description]
 */

 namespace SLiMS\Session\Driver;

class Files extends Contract
{
    public function admin()
    {
        // always use session cookies
        @ini_set('session.use_cookies', true);
        // use more secure session ids
        @ini_set('session.hash_function', 1);
        // no cache
        @session_cache_limiter('nocache');
        // set session name and start the session
        @session_name(COOKIES_NAME);
        // set session cookies params
        @session_set_cookie_params(86400, SWB.'admin/');
    }

    public function memberArea()
    {
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
    }
}