<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-25 06:30:45
 * @modify date 2022-12-25 11:12:27
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Session;

use simbio_security;

class Flash
{
    /**
     * @return Flash
     */
    public static function init()
    {
        return new Static;
    }

    /**
     * Set flash message
     *
     * @param string $key
     * @param string $value
     * @param string $type
     * @return Flash
     */
    public static function register(string $key, string $value, string $type = 'info')
    {
        if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
        $_SESSION['flash']['messages'][$key] = ['message' => $value, 'type' => $type];
        return self::init();
    }

    /**
     * Print out flash message
     * based on key
     *
     * @param [type] $key
     * @return void
     */
    public static function show($key)
    {
        global $sysconf;
        
        if (!self::has($key)) return null;

        $alertTitle = '';
        $data = self::get($key);
        $alertMessage = $data['message'];

        switch ($data['type']) {
            case 'danger':
                $alertType = 'alert-danger';
                break;

            case 'warning':
                $alertType = 'alert-warning';
                break;

            case 'success':
                $alertType = 'alert-success';
                break;
            
            default:
                $alertType = 'alert-info';
                break;
        }

        include SB . 'template/alert.php';

        self::delete($key);
    }

    /**
     * Method to check flash message
     * value
     *
     * @return boolean
     */
    public static function isEmpty()
    {
        return !(isset($_SESSION['flash']) && isset($_SESSION['flash']['messages']) && count($_SESSION['flash']['messages']));
    }

    /**
     * Check if key is exists or not
     *
     * @param string $key
     * @return boolean
     */
    public static function has(string $key)
    {
        return (bool)self::get($key);
    }

    /**
     * Check key and return it
     *
     * @param [type] ...$arguments
     * @return void
     */
    public static function includes(...$arguments)
    {
        foreach ($arguments as $argument) {
            if (self::has($argument)) return $argument;
        }

        return false;
    }

    /**
     * Retrieve data from session
     *
     * @param string $key
     * @return array|null
     */
    public static  function get(string $key = '')
    {
        return !self::isEmpty() ? (empty($key) ? $_SESSION['flash']['messages'] : $_SESSION['flash']['messages'][$key]??null) : null;
    }

    /**
     * @param string $key
     * @return void
     */
    public static function delete(string $key)
    {
        unset($_SESSION['flash']['messages'][$key]);
    }

    /**
     * @return void
     */
    public static function flush()
    {
        $_SESSION['flash']['messages'] = [];
    }

    public function __call($method, $arguments)
    {
        if (!self::isEmpty() && in_array($method, ['info', 'danger', 'warning', 'success']))
        {
            if (count($arguments)) $key = $arguments[0];
            else $key = array_key_last($_SESSION['flash']['messages']);
            $_SESSION['flash']['messages'][$key]['type'] = $method;
            self::show($key);
        }
    }
}