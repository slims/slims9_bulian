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
            // Mark the session cookie Secure whenever the request is served
            // over HTTPS, so the cookie is never sent back over plain HTTP.
            // Previously this read session.cookie_secure, which sysconfig
            // forces to false, leaving admin/member cookies without the
            // Secure attribute even on HTTPS deployments.
            'secure' => $this->isSecureRequest(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];
    }

    /**
     * Determine whether the current request is being served over HTTPS,
     * following the same conventions SLiMS already uses elsewhere
     * (direct TLS, a TLS-terminating proxy, force_https / load-balanced
     * config, and the https_enable system setting).
     *
     * The failure mode is deliberately safe: a plain HTTP request resolves
     * to false so the session cookie keeps working, and the worst a spoofed
     * forwarding header can do is set Secure on the spoofer's own cookie.
     */
    private function isSecureRequest(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') return true;
        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
        if (strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
        if (strtolower($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') return true;

        if (function_exists('config')) {
            if (config('url.force_https')) return true;
            $loadBalance = config('loadbalanced');
            if ($loadBalance && !empty($loadBalance['env']) && ($loadBalance['options']['scheme'] ?? '') === 'https') return true;
        }

        global $sysconf;
        if (!empty($sysconf['https_enable'])) return true;

        return false;
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
