<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2023-05-03 09:13:45
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache\Providers;

use SLiMS\DB;
use Carbon\Carbon;

class Database extends \SLiMS\Cache\Contract
{
    private $db = null;
    private $expire = null;

    /**
     * Register all options
     *
     * @param string $directory
     */
    public function __construct(array $expire)
    {
        $this->expire = $expire;
        $this->db = DB::getInstance();
    }

    /**
     * Create a new cache files/value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return void
     */
    public function set(string $cacheName, $contents)
    {
        $data = [
            'name' => $cacheName,
            'contents' => (is_string($contents) ? $contents : json_encode($contents)),
            'created_at' => date('Y-m-d H:i:s'),
            'expired_at' => ($this->expire['time'] == 0 ? null : date('Y-m-d H:i:s', strtotime('+' . $this->expire['time'] . ' seconds')))
        ];
        
        $insert = $this->db->prepare('INSERT IGNORE INTO `cache` (`name`,`contents`,`created_at`,`expired_at`) VALUES (:name,:contents,:created_at,:expired_at)');
        $insert->execute($data);

        return $insert->rowCount();
    }

    /**
     * Get cache value
     *
     * @param string $cacheName
     * @param string $callBack
     * @return mixed
     */
    public function get(string $cacheName, $callBack = '')
    {
        $cache = $this->db->prepare('SELECT `contents` FROM `cache` WHERE `name` = :name');
        $cache->execute(['name' => $cacheName]);
        $data = $cache->rowCount() ? json_decode($cache->fetchObject()->contents) : null;

        return is_callable($callBack) ? $callBack($data) : $data;
    }

    public function isExpire(string $cacheName)
    {
        $cache = $this->db->prepare('SELECT `expired_at` FROM `cache` WHERE `name` = :name');
        $cache->execute(['name' => $cacheName]);

        if ($cache->rowCount() < 1) return;
        $expired_at =  $cache->fetchObject()->expired_at;

        $expire = Carbon::parse($expired_at??date('Y-m-d H:i:s'));
        $now = Carbon::now();
        
        // $expire->diffInHours()
        $criteriaOperator = $this->expire['criteria'][0]??'>';
        $criteriaNum = $this->expire['criteria'][1]??1;
        $diff = call_user_func([$expire, 'diffIn' . ucfirst($this->expire['diffIn'])]);

        $result = $diff > $criteriaNum;
        if ($criteriaOperator == '>=') $result = $diff >= $criteriaNum;
        
        return $result;
    }

    /**
     * Update cache value
     *
     * @param string $cacheName
     * @param mixed $contents
     * @return bool
     */
    public function put(string $cacheName, $contents)
    {
        $data = [
            'contents' => (is_string($contents) ? $contents : json_encode($contents)),
            'updated_at' => date('Y-m-d H:i:s'),
            'name' => $cacheName,
            'expired_at' => ($this->expire['time'] == 0 ? null : date('Y-m-d H:i:s', strtotime('+' . $this->expire['time'] . ' seconds')))
        ];
        
        $update = $this->db->prepare('UPDATE `cache` SET `contents` = :contents, `updated_at` = :updated_at, `expired_at` = :expired_at WHERE `name` = :name');
        $update->execute($data);

        return (bool)$update->rowCount();
    }

    /**
     * Update|Create data if expired
     *
     * @param string $cacheName
     * @param [type] $contents
     * @return void
     */
    public function putIfExpire(string $cacheName, $contents)
    {
        if (!$this->isExpire($cacheName)) return;

        return $this->put($cacheName, $contents);
    }

    /**
     * Delete cache
     *
     * @param string $cacheName
     * @return void
     */
    public function destroy(string $cacheName)
    {
        $delete = $this->db->prepare('DELETE FROM `cache` WHERE `name` = :name');
        $delete->execute(['name' => $cacheName]);
        
        return $delete;
    }

    /**
     * Make cache clean as soon as posible
     *
     * @return void
     */
    public function purge()
    {
        $this->db->prepare('TRUNCATE TABLE `cache`');
    }

    /**
     * Get path or key of cache
     *
     * @return string
     */
    public function getPath()
    {
        return 'cache';
    }

    /**
     * Get cache as list
     *
     * @return array
     */
    public function getList()
    {
        $state = $this->db->query('SELECT `contents` FROM `cache`');
        
        $result = [];
        while ($data = $state->fetchObject()) {
            $result[] = $data;
        }

        return $result;
    }

    /**
     * @return boolean
     */
    public function isExists(string $cacheName)
    {
        $state = $this->db->prepare('SELECT `contents` FROM `cache` WHERE `name` = :name');
        $state->execute(['name' => $cacheName]);

        return (bool)$state->rowCount();
    }
}