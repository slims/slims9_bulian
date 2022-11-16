<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2022-11-13 23:52:13
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache\Providers;

class Redis extends \SLiMS\Cache\Contract
{
    private string $prefix = '';

    /**
     * Register all options
     *
     * @param string $directory
     */
    public function __construct(){}

    /**
     * Create a new cache files/value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return void
     */
    public function set(string $cacheName, $contents){}

    /**
     * Get cache value
     *
     * @param string $cacheName
     * @param string $callBack
     * @return mixed
     */
    public function get(string $cacheName, $callBack = ''){}

    /**
     * Update cache value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return bool
     */
    public function put(string $cacheName, $contents){}

    /**
     * Delete cache
     *
     * @param string $cacheName
     * @return void
     */
    public function destroy(string $cacheName){}

    /**
     * Make cache clean as soon as posible
     *
     * @return void
     */
    public function purge(){}

    /**
     * Get path or key of cache
     *
     * @return string
     */
    public function getPath(){}

    /**
     * Undocumented function
     *
     * @return array
     */
    public function getList(){}
}