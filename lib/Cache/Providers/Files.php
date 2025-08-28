<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2023-05-03 09:34:46
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache\Providers;

class Files extends \SLiMS\Cache\Contract
{
    private string $directory = '';

    /**
     * Register all options
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
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
        return file_put_contents($this->directory . basename($cacheName), json_encode($contents));
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
        $content = json_decode(file_get_contents($this->directory . basename($cacheName)));
        return is_callable($callBack) ? $callBack($content) : $content;
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
        if (file_exists($this->directory . basename($cacheName))) return $this->set($cacheName, $contents);
    }

    /**
     * Delete cache
     *
     * @param string $cacheName
     * @return void
     */
    public function destroy(string $cacheName)
    {
        unlink($this->directory . basename($cacheName));
    }

    /**
     * Make cache clean as soon as posible
     *
     * @return void
     */
    public function purge()
    {
        foreach ($this->getList() as $file) {
            unlink($this->directory . $file);
        }
    }

    /**
     * Get path or key of cache
     *
     * @return string
     */
    public function getPath()
    {
        return $this->directory;
    }

    /**
     * Get cache as list
     *
     * @return array
     */
    public function getList()
    {
        return array_values(array_diff(scandir($this->directory), ['.gitkeep', '.', '..','index.php','index.html']));
    }

    /**
     * @return boolean
     */
    public function isExists(string $cacheName)
    {
        return file_exists($this->directory . basename($cacheName));
    }
}