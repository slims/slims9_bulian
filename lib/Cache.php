<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-13 11:33:08
 * @modify date 2022-11-13 23:53:33
 * @license GPLv3
 * @desc
 */

namespace SLiMS;

use Exception;
use SLiMS\Cache\Contract;

class Cache
{
    private object $provider;
    private array $options;
    private static $instance = null;

    /**
     * Cache initialization
     * with some provider like Files, Redis etc
     *
     * @param string $provider
     * @param array $options
     * @return void
     */
    private function __construct(string $provider, array $options)
    {
        if (!$this->isProviderExists($provider)) throw new Exception("Provider {$provider} is not exists!");

        $this->provider = new $provider(...array_values($options));

        if (!$this->isProviderUseStandart()) throw new Exception("Provider {$provider} is not use Contract class");
    }

    public static function getInstance(string $provider, array $options)
    {
        if (is_null(self::$instance)) self::$instance = new Cache($provider, $options);
        return self::$instance;
    }

    /**
     * @param string $provider
     * @return boolean
     */
    public function isProviderExists(string $provider)
    {
        return class_exists($provider);
    }

    /**
     * Make sure provider is use
     * cache contract
     *
     * @return bool
     */
    public function isProviderUseStandart()
    {
        return $this->provider instanceof Contract;
    }

    public static function init(string $cacheProviderName = '')
    {
        $cacheConfig = config('cache');

        if (is_null($cacheConfig)) throw new Exception("Cache config isn't exists!");

        if (empty($cacheProviderName)) $cacheProviderName = $cacheConfig['default']??'Files';
        
        $cacheProviderDetail = $cacheConfig['providers'][$cacheProviderName];
        
        return self::getInstance($cacheProviderDetail['class'], $cacheProviderDetail['options']);
    }

    /**
     * Magic function to communicated
     * with cache provider with static class call
     *
     * @param string $method
     * @param array $arguments
     * @return void
     */
    public static function __callStatic($method, $arguments)
    {
        return self::init()->provider->{$method}(...$arguments);
    }

    /**
     * Magic function to communicated
     * with cache provider with static class call
     *
     * @param string $method
     * @param array $arguments
     * @return void
     */
    public function __call($method, $arguments)
    {
        return self::init()->provider->{$method}(...$arguments);
    }
}