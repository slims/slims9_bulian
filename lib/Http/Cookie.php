<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-11-03 11:32:14
 * @modify date 2023-11-03 11:32:14
 * @license GPL-3.0 
 * @desc Create a cookie
 */

namespace SLiMS\Http;

final class Cookie
{
    private array $options  = [
        'expires' => 0,
        'path' => '',
        'domain' => '',
        'secure' => false,
        'httponly' => false,
        'samesite' => ''
    ];

    /**
     * register a new cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set(string $name, string $value):void
    {
        if (empty($this->options['samesite'])) unset($this->options['samesite']);
        setcookie($name, $value, $this->options);
    }

    /**
     * Unregister cookie
     *
     * @param string $name
     * @return void
     */
    public function unset(string $name)
    {
        if (empty($this->options['samesite'])) unset($this->options['samesite']);
        unset($_COOKIE[$name]);
        setcookie($name, '', $this->options);
    }

    public static function get(string $name):mixed
    {
        return $_COOKIE[$name]??null;
    }

    /**
     * Mutating option with new value
     *
     * @param string $option
     * @param string $value
     * @return void
     */
    public function with(string $option, $value = '')
    {
        if (isset($this->$option)) $this->$option = empty($value) ? true : $value;
    }

    public function __isset(string $key)
    {
        return isset($this->options[$key]);
    }

    public function __set(string $option, $value)
    {
        if (isset($this->$option)) $this->options[$option] = $value;
    }

    public function __call(string $method, array $arguments)
    {
        $prefix = substr($method, 0,4);
        $property = lcfirst(substr($method, 4, strlen($method)));

        if ($prefix === 'with') $this->with($property, ...$arguments);

        return $this;
    }

    public static function __callStatic(string $method, array $arguments)
    {
        $instance = new static;
        $prefix = substr($method, 0,4);
        $option = lcfirst(substr($method, 4, strlen($method)));

        if ($prefix === 'with') $instance->with($option, ...$arguments);

        return $instance;
    }

    public function __toString()
    {
        return json_encode($this->options);
    }
}