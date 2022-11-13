<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2022-11-13 11:33:08
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache;

abstract class Contract
{
    abstract public function set(string $cacheName, $contents);
    abstract public function get(string $cacheName, $callBack = '');
    abstract public function put(string $cacheName, $contents);
    abstract public function destroy(string $cacheName);
    abstract public function purge();
    abstract public function getList();
    abstract public function getPath();
}