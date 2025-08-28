<?php
/**
 * @author drajathasan20@gmail.com
 * @email drajathasan20@gmail.com
 * @create date 2022-09-11 12:22:46
 * @modify date 2022-09-11 14:47:13
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

class Ip
{
    /**
     * Class instance
     */
    private static $instance = null;

    /**
     * Array key of $_SERVER 
     * to get client IP
     */
    private $sourceRemoteIp = 'REMOTE_ADDR';

    /**
     * Default array key of $_SERVER 
     * to collect client ip from proxy server
     */
    private $proxyKey = 'HTTP_X_FORWARDED_FOR';

    /**
     * Variable to store $_SERVER
     */
    private array $server;

    /* Constructor */
    private function __construct()
    {
        $this->server = $_SERVER;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Ip;
        return self::$instance;
    }

    /**
     * Method to check the application is behind
     * proxy or not
     */
    public function isBehindProxy()
    {
        return array_key_exists($this->proxyKey, $this->server);
    }

    /**
     * Get proxy ip address
     */
    public function getProxyIp()
    {
        if ($this->isBehindProxy()) return $this->server['REMOTE_ADDR']??null;
    }

    /**
     * Get client IP
     */
    public function get()
    {
        return $this->server[$this->sourceRemoteIp]??$this->server['REMOTE_ADDR'];
    }

    /**
     * Setter for default $_SERVER key to get ip address of client
     */
    public function setSourceRemoteIp(string $serverKey)
    {
        $this->sourceRemoteIp = $serverKey;
    }

    /**
     * Set key to get real ip client at reverse proxy
     */
    public function setProxyKey(string $proxyKey)
    {
        $this->proxyKey = $proxyKey;
    }

    /**
     * short cut for access "get" method
     */
    public function __toString()
    {
        return $this->get();
    }
}