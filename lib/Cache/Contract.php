<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2022-11-16 09:04:20
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache;

abstract class Contract
{
    private string $error = '';
    
    /**
     * Create a new cache files/value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return void
     */
    abstract public function set(string $cacheName, $contents);

    /**
     * Get cache value
     *
     * @param string $cacheName
     * @param string $callBack
     * @return mixed
     */
    abstract public function get(string $cacheName, $callBack = '');

    /**
     * Update cache value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return bool
     */
    abstract public function put(string $cacheName, $contents);

    /**
     * Delete cache
     *
     * @param string $cacheName
     * @return void
     */
    abstract public function destroy(string $cacheName);

    /**
     * Make cache clean as soon as posible
     *
     * @return void
     */
    abstract public function purge();

    /**
     * Undocumented function
     *
     * @return array
     */
    abstract public function getList();

    /**
     * Get path or key of cache
     *
     * @return string
     */
    abstract public function getPath();

    /**
     * @return boolean
     */
    abstract public function isExists(string $cacheName);

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}