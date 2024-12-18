<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-04-06 07:59:09
 * @modify date 2023-06-06 05:35:28
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Parcel;

class Package implements \IteratorAggregate,\JsonSerializable,\Countable
{
    /**
     * Package instance
     */
    private static $instance = null;

    /**
     * File path of archive data
     */
    private string $filepath = '';
    
    /**
     * List of registered package
     *
     * @var array
     */
    private array $packages = [];

    /**
     * Zip is default option
     *
     * @var string
     */
    private static string $defaultPackager = \SLiMS\Parcel\Packager\Zip::class;
    private string $error = '';

    public static function getInstance()
    {
        if (self::$instance === null) self::$instance = new Package;
        return self::$instance;
    }

    /**
     * Register a package
     *
     * @param string $filepath
     * @param string $packager
     * @return null|object
     */
    private function registerPackage(string $filepath, string $packager)
    {
        try {
            $filepathInfo = pathinfo($filepath);
            if (!class_exists($packager)) throw new Exception("Packger $packager is not available!");
            if (!isset($this->packages[$filepathInfo['filename']])) $this->packages[$filepathInfo['filename']] = new $packager($filepath);
            return $this->packages[$filepathInfo['filename']];
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
    }

    public static function prepare(string $filepath, string $packager = '')
    {
        if (empty($packager)) $packager = self::$defaultPackager;
        return self::getInstance()->registerPackage($filepath, $packager);
    }

    public static function process(string $packageName)
    {
        return self::getInstance()->$packageName;
    }

    public function __get($key)
    {
        return isset($this->packages[$key]) ? $this->packages[$key] : null;
    }

    public function jsonSerialize(): mixed 
    {
        return $this->packages;
    }

    public function count(): int
    {
        return count($this->packages);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->packages);
    }

    public static function getError()
    {
        return self::getInstance()->error;
    }
}