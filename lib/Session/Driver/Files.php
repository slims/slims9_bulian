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
    private function cookieOptions(int $lifetime, string $path): array
    {
        return [
            'lifetime' => $lifetime,
            'path' => $path,
            'secure' => filter_var(ini_get('session.cookie_secure'), FILTER_VALIDATE_BOOLEAN),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }
 
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
        @session_set_cookie_params($this->cookieOptions(86400, SWB.'admin/'));
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
        @session_set_cookie_params($this->cookieOptions(43200, SWB));
    }
}
