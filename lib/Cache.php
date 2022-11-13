<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-13 11:33:08
 * @modify date 2022-11-13 11:33:08
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

    public function __construct(string $provider, array $options)
    {
        if (!$this->isProviderExists($provider)) throw new Exception("Provider {$provider} is not exists!");

        $this->provider = new $provider(...$options);

        if (!$this->isProviderUseStandart()) throw new Exception("Provider {$provider} is not use Contract class");
    }

    public function isProviderExists(string $provider)
    {
        return class_exists($provider);
    }

    public function isProviderUseStandart()
    {
        return $this->provider instanceof Contract;
    }

    public static function __callStatic($method, $arguments)
    {
        $cacheConfig = config('cache');

        if (is_null($cacheConfig)) throw new Exception("Cache config isn't exists!");

        $cacheProviderName = $cacheConfig['default']??'Files';
        $cacheProviderDetail = $cacheConfig['providers'][$cacheProviderName];
        $cache = new Static($cacheProviderDetail['class'], $cacheProviderDetail['options']);
        
        return $cache->provider->{$method}(...$arguments);
    }
}