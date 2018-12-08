<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-04 15:27:14
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 15:19:06
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

require 'AltoRouter.php';

class Router extends AltoRouter
{

    private $sysconf;
    private $db;
    
    function __construct($sysconf, $obj_db)
    {
        parent::__construct();
        $this->sysconf = $sysconf;
        $this->db = $obj_db;
    }

    public function match($requestUrl = null, $requestMethod = null)
    {
        $params = array();
        $match = false;

        // set Request Url if it isn't passed as parameter
        if($requestUrl === null) {
            $path = explode('/', $_GET['p']);
            if ($path[0] == $this->basePath) {
                $requestUrl = $_GET['p'];
            } else {
                $requestUrl = '/';
            }
        }

        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        // set Request Method if it isn't passed as a parameter
        if($requestMethod === null) {
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }

        foreach($this->routes as $handler) {
            list($methods, $route, $target, $name) = $handler;

            $method_match = (stripos($methods, $requestMethod) !== false);

            // Method did not match, continue to next route.
            if (!$method_match) continue;

            if ($route === '*') {
                // * wildcard (matches all)
                $match = true;
            } elseif (isset($route[0]) && $route[0] === '@') {
                // @ regex delimiter
                $pattern = '`' . substr($route, 1) . '`u';
                $match = preg_match($pattern, $requestUrl, $params) === 1;
            } elseif (($position = strpos($route, '[')) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                // Compare longest non-param string with url
                if (strncmp($requestUrl, $route, $position) !== 0) {
                    continue;
                }
                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $requestUrl, $params) === 1;
            }

            if ($match) {

                if ($params) {
                    foreach($params as $key => $value) {
                        if(is_numeric($key)) unset($params[$key]);
                    }
                }

                return array(
                    'target' => $target,
                    'params' => $params,
                    'name' => $name
                );
            }
        }
        return false;
    }

    public function makeCallable($string)
    {
        $method = explode(':', $string);
        if (isset($method[1]) && class_exists($method[0])) {
            $instance = new $method[0]($this->sysconf, $this->db);
            if (method_exists($instance, $method[1])) {
                return array($instance, $method[1]);
            }
        }
        return false;
    }

    public function run()
    {
        // match current request url
        $match = $this->match();
        // call closure or throw 404 status
        if( $match && is_callable( $match['target'] ) ) {
            call_user_func_array( $match['target'], $match['params'] ); 
        } else {
            if ($callable = $this->makeCallable($match['target'])) {
                call_user_func_array($callable, $match['params']);
            } else {
                // no route was matched
                // header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
                // include $this->sysconf['template']['dir'].'/'.$this->sysconf['template']['theme'].'/404.php';
                header ("location:index.php");
            }
        }
    }
}