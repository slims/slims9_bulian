<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-08-24 07:55:33
 * @modify date 2023-08-24 07:55:33
 * @desc :
 * 
 * Manage file or directory between two or more storages, such as
 * migrating from one storage to another storage
 * 
 */

namespace SLiMS\Filesystems;

use ArrayIterator;
use IteratorAggregate;
use SLiMS\Filesystems\Providers\Contract;
use League\Flysystem\MountManager;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;

class Mount implements IteratorAggregate
{
    private MountManager $manager;
    private array $storages = [];
    private array $action = [];
    private string $path = '';

    public function __construct(array $filesystems)
    {
        $this->manager = new MountManager($filesystems);
    }

    /**
     * Register storage into mount manager instance
     *
     * @param Contract $storage
     * @param Contract $anotherStorage
     * @return Mount
     */
    public static function registerStorages(Contract $storage, Contract $anotherStorage)
    {
        // need more then 2 storage?
        if (func_num_args() > 2) {
            $storages = self::compabilityCheck(func_get_args());
            $instance = new static($storages);
            $instance->storages = array_merge($instance->storages, array_keys($storages));
        } else {
            $instance = new static([
                $storage->getDiskname() => $storage->getFileSystem(), 
                $anotherStorage->getDiskName() => $anotherStorage->getFilesystem()
            ]);

            $instance->storages = [
                $storage->getDiskname(), $anotherStorage->getDiskName()
            ];
        }
        
        return $instance;
    }

    /**
     * Instance compability check
     *
     * @param array $storages
     * @return array $result
     */
    private static function compabilityCheck(array $storages)
    {
        $result = [];
        foreach (array_slice($storages, 2) as $order => $storage) {
            $order = $order + 1;
            if (!$storage instanceof Contract) throw new Exception("Argument #{$order} is not instance of Provider contract.");
            else $result[$storage->getDiskname()] = $storage->getFilesystem();
        }

        return $result;
    }

    public function getIterator(): ArrayIterator
    {
        $list = $this->manager->listContents($this->path, true)->toArray();
        return new ArrayIterator($list);
    }

    private function process()
    {
        $path = $this->action['arguments'][0];
        $this->action['arguments'][0] = $this->pathResolver($this->path, $path);

        if (in_array($this->action['method'], ['copy','move'])) {
            return $this;
        }

        
        return $this->manager->{$this->action['method']}(...$this->action['arguments']);
    }

    public function pathResolver(string $storageName, string $path)
    {
        $path = substr($path, -1) === '/' ? $path : $path . '/';
        return strpos($path, '://') === false ? $storageName . '://' . $path : $path;
    }

    /**
     * Set destionation place
     *
     * @param string $storage
     * @param string $path
     * @return mixed
     */
    public function to(string $storage, string $path)
    {
        if (!in_array($storage, $this->storages)) throw new \Exception("Error : $storage not exists");
        $sourcePath = $this->pathResolver($this->path, $this->action['arguments'][0]);
        $destPath = $this->pathResolver($storage, trim($path));
        return $this->manager->{$this->action['method']}($sourcePath, $destPath);
    }

    
    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        // Method is an storage name
        if (in_array($method, $this->storages)) {
            $path = @array_pop(explode('://', ($arguments[0]??'')));
            $this->path = $this->pathResolver($method, $path);
            return $this;
        }

        // Method as MountManager method
        if (method_exists($this->manager, $method)) {
            // capture method and arguments
            $this->action = [
                'method' => $method,
                'arguments' => $arguments
            ];

            return $this->process();
        }

        // Transfer process copy or move process only
        if (substr($method, 0,2) === 'to' && $this->action) {
            $storage = substr($method, 2, strlen($method));
            return $this->to(strtolower($storage), ...$arguments);
        }
    }
}