<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-01 09:40:33
 * @modify date 2023-01-12 08:00:03
 * @license GPLv3
 * @desc 
 * 
 * Store file into "disk". Read filesystem.php in config directory.
 * 
 * How to use :
 * 
 * Pattern : \SLiMS\Storage::{diskProfile}() -> {method}
 * Example :
 * 
 * - \SLiMS\Storage::repository()->directories(); // list all contents in "repository disk" this operation for iterate process
 * 
 * Available method please read Contract.php in Provider directory
 */

namespace SLiMS\Filesystems;

use Exception;

class Storage
{
    /**
     * Default storage provider class
     *
     * @var string
     */
    private static $defaultProvider = '\SLiMS\Filesystems\Providers\Local';
    
    /**
     * Call disk provider instance
     *
     * @param string $Disk
     * @param array $arguments
     * @return disk class
     */
    public static function disk(string $Disk, array $arguments = [])
    {
        $class = config('filesystem.disks.' . $Disk, false) ? config('filesystem.disks.' . $Disk . '.provider') : self::$defaultProvider;
        return new $class(...array_merge($arguments, [$Disk]));
    }

    /**
     * Call disk instance based on
     * static method
     *
     * @param [type] $diskName
     * @param [type] $arguments
     * @return void
     */
    public static function __callStatic($diskName, $arguments)
    {
        $disks = config('filesystem.disks');

        if (array_key_exists($diskName, $disks))
        {
            $provider = $disks[$diskName]['provider']??'';
            if (!class_exists($provider)) throw new Exception("Class {$provider} not found!");
            
            return self::disk($diskName, array_merge(array_values($disks[$diskName]['options']), $arguments));
        }

        throw new Exception("Disk {$diskName} not registered in filesystem");
    }
}