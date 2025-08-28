<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 11/09/20 18.13
 * @File name           : Cache.php
 */

class Cache
{
    /**
     * @param $name string
     * @param $data string
     */
    static function set($name, $data) {
        $path = __DIR__ . '/../../../files/cache/cache_' . $name . '.json';
        //Check if the directory already exists.
        if(!is_dir(dirname($path))){
            //Directory does not exist, so lets create it.
            mkdir(dirname($path), 0755);
        }
        file_put_contents($path, $data);
    }

    /**
     * @param $name string
     * @return false|string|null
     */
    static function get($name) {
        $path = __DIR__ . '/../../../files/cache/cache_' . $name . '.json';
        if (file_exists($path) && time() - 18000 < filemtime($path)) {
            return file_get_contents($path);
        }
        return null;
    }

    /**
     * @param $name string
     * @return void
     */
    static function destroy($name)
    {
        $path = __DIR__ . '/../../../files/cache/cache_' . $name . '.json';
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}