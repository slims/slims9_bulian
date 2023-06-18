<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-04 07:31:30
 * @modify date 2022-12-04 08:38:20
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Session;

use SLiMS\Session\Driver\Contract;

final class Factory
{
    private static $instance = null;
    private object $driver;
    private array $availableDriver = [];
    
    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Factory;
        return self::$instance;
    }

    public static function use(string $driverClass)
    {
        return self::getInstance()->setDriver($driverClass);
    }

    public function registerDriver(string $driverClass)
    {
        $this->availableDriver[] = $driverClass;
    }

    public function setDriver(string $driver)
    {
        if (!class_exists($driver)) throw new \Exception("$driver not found!");
        $this->driver = new $driver;
        if (!$this->driver instanceof Contract) throw new \Exception("$driver is not instance of " . Contract::class);
        return $this;
    }

    public function getCurrentDriver()
    {
        return get_class($this->driver);
    }

    public function start(string $sessionSection)
    {
        if (!method_exists($this->driver, $sessionSection)) throw new \Exception("Section fro $sessionSection is not available!");
        $this->driver->{$sessionSection}();
        // start session
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
}