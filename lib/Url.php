<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-17 07:14:29
 * @modify date 2023-01-12 11:34:58
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

class Url
{
    /**
     * If SLiMS behind reverse proxy
     * you must set this property to true
     *
     * @var boolean
     */
    public static $forceHttps = false;

    /**
     * This property is used to make
     * some magic method to generate result
     * based on availibility method
     *
     * Example:
     * - Url::getSlimsBaseUri(); 
     * * it will be return combination value from Url::getScheme(), Url::getDomain(), Url::getPath()
     * 
     * @var array
     */
    private static $scopes = [
        'slimsBaseUri' => ['scheme','domain', 'path'],
        'slimsFullUri' => ['scheme','domain','self','query']
    ];

    /**
     * Result from scope method process
     *
     * @var string
     */
    private string $scopeResult = '';

    /**
     * Retrieve server name from $_SERVER
     *
     * @return string
     */
    public static function getDomain(bool $strict = true)
    {
        if (!in_array(self::getPort(), [80,443]) && $strict) return $_SERVER['SERVER_NAME'] . ':' . self::getPort();
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Retrieve server port from $_SERVER
     *
     * @return string
     */
    public static function getPort()
    {
        return $_SERVER['SERVER_PORT'];
    }

    /**
     * Retrieve url scheme from from $_SERVER
     *
     * @return string
     */
    public static function getScheme()
    {
        $urlConfig = Config::getInstance()->get('url.force_https', false);
        return (self::$forceHttps || $urlConfig ? 'https' : $_SERVER['REQUEST_SCHEME']) . '://';
    }

    /**
     * Path is string after domain without filename
     *
     * @return string
     */
    public static function getPath($callBack = '')
    {
        if (is_callable($callBack)) return $callBack(SWB);
        return SWB;
    }

    /**
     * Path with file
     *
     * @return string
     */
    public static function getSelf($callBack = '')
    {
        if (is_callable($callBack)) return $callBack($_SERVER['PHP_SELF']);
        return $_SERVER['PHP_SELF'];
    }

    /**
     * $_GET is QueryString 
     *
     * @param string $callBack
     * @return string
     */
    public static function getQuery($callBack = '')
    {
        if (is_callable($callBack)) return $callBack($_SERVER['QUERY_STRING']);
        return '?' . $_SERVER['QUERY_STRING'];
    }

    /**
     * @return string
     */
    public static function getReferer()
    {
        return $_SERVER['HTTP_REFERER']??self::getSlimsFullUri();
    }

    /**
     * @param string $url
     * @return boolean
     */
    public static function isValid(string $url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * Sometimes we must check inputed url
     * is our selfUrl (same domain) or not to protected our System
     * from RCE etc, via URL.
     *
     * @param string $url
     * @return boolean
     */
    public static function isSelf(string $url)
    {
        return self::parse($url)->getDomain() === self::getDomain($strict = false);
    }

    /**
     * Parsing URL with style!
     *
     * @param string $url
     * @return string|null
     */
    public static function parse(string $url)
    {
        return new Class($url) {
            private $url;
            private $methods = [
                'domain' => PHP_URL_HOST,
                'scheme' => PHP_URL_SCHEME,
                'user' => PHP_URL_USER,
                'password' => PHP_URL_PASS,
                'port' => PHP_URL_PORT,
                'path' => PHP_URL_PATH,
                'query' => PHP_URL_QUERY,
                'anchor' => PHP_URL_FRAGMENT
            ];

            public function __construct(string $url)
            {
                $this->url = $url;
            }

            public function __call($method, $arguments)
            {
                $method = strtolower(str_replace('get', '', $method));
                if (!isset($this->methods[$method])) return;
                
                return parse_url($this->url, $this->methods[$method]);
            }
        };
    }
    
    /**
     * Megic method to process
     * scopes property
     *
     * @param string $method
     * @param array $arguments
     * @return string
     */
    public static function __callStatic($method, $arguments)
    {
        if (php_sapi_name() === 'cli') return;
        
        $static = new Static;
        $method = lcfirst(str_replace('get', '', $method));
        if (!isset(self::$scopes[$method])) return;

        $url = '';
        foreach (self::$scopes[$method] as $mixedMethods) {
            $url .= self::{'get' . ucfirst($mixedMethods)}();
        }

        $static->scopeResult = trim($url . ($arguments[0]??''));
        return $static;
    }

    /**
     * @return string
     */
    public function decode()
    {
        return urldecode($this->scopeResult);
    }

    /**
     * @return string
     */
    public function encode()
    {
        return urlencode($this->scopeResult);
    }

    public function __toString()
    {
        return $this->scopeResult;
    }
}