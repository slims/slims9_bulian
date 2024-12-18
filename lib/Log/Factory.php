<?php
/**
 * @author Drajat Hasan
 * @email <drajathasan20@gmail.com>
 * @create date 2023-10-21 17:29:20
 * @modify date 2023-10-21 19:34:54
 * @license GPLv3
 * @desc [description]
 */
namespace SLiMS\Log;

use Handler\Contract;

final class Factory
{
    private static ?object $instance = null;
    private static string $defaultHandler = \SLiMS\Log\Handler\Systemlog::class;
    private ?object $handler = null;

    private function __construct()
    {
        $this->handler = new self::$defaultHandler;
    }

    /**
     * @return Factory
     */
    public static function getInstance(): Factory
    {
        if (is_null(self::$instance)) self::$instance = new Factory;
        if (file_exists(SB . 'config/customlog.php')) {
            self::use(config('customlog.default_handler'));
        }
        return self::$instance;
    }

    /**
     * Use custom handler
     *
     * @param string $class
     * @param array $options
     * @return void
     */
    public static function use(string $class, array $options = [])
    {
        if (class_exists($class)) {
            self::getInstance()->handler = null;
            self::getInstance()->handler = new $class(...$options);
        }
    }

    /**
     * Write new log record
     *
     * @param string $type
     * @param string $value_id
     * @param string $location
     * @param string $message
     * @param string $submod
     * @param string $action
     * @return void
     */
    public static function write(string $type, string $value_id, string $location, string $message, string $submod='', string $action='')
    {
        self::getInstance()->handler->write($type, $value_id, $location, $message, $submod, $action);
    }

    /**
     * @return Contract
     */
    public static function read(?Object $formatter = null):string
    {
        self::getInstance()->handler->read($formatter);
        return self::getInstance()->handler;
    }

    public static function truncate()
    {
        self::getInstance()->handler->truncate();
    }

    public static function download()
    {
        self::getInstance()->handler->download();
    }
}