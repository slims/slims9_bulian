<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-13 11:33:34
 * @modify date 2022-11-13 11:33:08
 * @license GPLv3
 * @desc
 */

namespace SLiMS\Cache\Providers;

class Files extends \SLiMS\Cache\Contract
{
    private string $directory = '';

    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    public function set(string $cacheName, $contents)
    {
        return file_put_contents($this->directory . basename($cacheName), $contents);
    }

    public function get(string $cacheName, $callBack = '')
    {
        $content = file_get_contents($this->directory . basename($cacheName));
        return is_callable($callBack) ? $callBack($content) : $content;
    }

    public function put(string $cacheName, $contents)
    {
        if (file_exists($this->directory . basename($cacheName))) return $this->set($cacheName, $contents);
    }

    public function destroy(string $cacheName)
    {
        unlink($this->directory . basename($cacheName));
    }

    public function purge()
    {
        foreach ($this->getList() as $file) {
            unlink($this->directory . $file);
        }
    }

    public function getPath()
    {
        return $this->directory;
    }

    public function getList()
    {
        return array_values(array_diff(scandir($this->directory), ['.gitkeep', '.', '..']));
    }
}