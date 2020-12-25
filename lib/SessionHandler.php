<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2020-12-25 16:21:44
 * @modify date 2020-12-25 16:21:44
 * @desc SessionHandler
 */

namespace SLiMS;

class SessionHandler
{
    public static function set($driver)
    {
        global $sysconf;

        if (method_exists('SLiMS\\SessionHandler', $driver.'Session'))
        {
            $loadDriver = $driver.'Session';
            $config = $sysconf['session'][$driver];
            self::$loadDriver($config);
        }
        else if ($driver !== 'files')
        {
            die('Session Error : handler is not found! Please check your configuration!');
        }
    }

    private static function filesSession($config)
    {
        global $sysconf;

        if (!empty($config['path']))
        {
            try {
                if (file_exists($config['path']) && is_writable($config['path']))
                {
                    ini_set('session.save_path', $config['path']);
                }
                else
                {
                    throw new \ErrorException($config['path'].' is not exists or not writeable!');
                }
            } catch (\ErrorException $e) {
                die('Sessions : '.$e->getMessage());
            }
        }
    }

    private static function redisSession($config)
    {
        global $sysconf;
        if (class_exists('Redis'))
        {   
            // check connection
            $Redis = new \Redis();
            try {
                // make connection
                $connection = $Redis->connect($config['host'], $config['port'], $sysconf['session_timeout']);
                // set authentication
                $authentication = true;
                if (!empty($config['options']['auth']))
                {
                    $authentication = $Redis->auth($config['options']['auth']);
                }

                 // set options
                $options = '';
                foreach ($config['options'] as $key => $value) {
                    $options .= $key.'='.$value.'&';
                }
                $options = '?'.substr_replace($options, '', -1);
                // set path
                $path = 'tcp://'.$config['host'].':'.$config['port'].$options;
                // set ini setting
                ini_set('session.save_handler', 'redis');
                ini_set('session.save_path', $path);
                ini_set('session.gc_maxlifetime', $sysconf['session_timeout']);

                // close redis
                $Redis->close();
            } catch (\RedisException $e) {
                die('Redis Error : '.$e->getMessage());
            }
        }
        else
        {
            die('Redis Error : extension is not loaded! Please check your configuration!');
        }
    }

    // other driver
}