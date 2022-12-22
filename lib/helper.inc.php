<?php
/**
 * @CreatedBy          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date               : 2020-11-28  20:07:48
 * @FileName           : helper.inc.php
 * @Project            : slims9_bulian
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

use SLiMS\{Config,Ip,Number,Currency};

if (!function_exists('config')) {
    /**
     * Helper to get config with dot separator keys
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    function config($key, $default = null) {
        return Config::getInstance()->get($key, $default);
    }
}

if (!function_exists('ip'))
{
    /**
     * Helper to get client Ip Address
     * and check if app is behind proxy or not
     */
    function ip()
    {
        return Ip::getInstance();
    }
}

if (!function_exists('number'))
{
    /**
     * function to call number instance
     *
     * @param mixed $input
     * @return Number
     */
    function number($input)
    {
        return Number::set($input);
    }
}

if (!function_exists('currency'))
{
    /**
     * function to call currency instance
     *
     * @param mixed $input
     * @return Number
     */
    function currency($input)
    {
        return new Currency($input);
    }
}

if (!function_exists('debug'))
{
    /**
     * Helper to verbosing 
     * debug process
     * @return void
     */
    function debug()
    {
        if (ENVIRONMENT == 'development') dump(...func_get_args());
    }
}

if (!function_exists('isDev')) 
{
    function isDev()
    {
        return ENVIRONMENT === 'development' ? true : false;
    }
}

// set version on static files
if (!function_exists('v'))
{
  function v($filename)
  {
    global $sysconf;
    $version = substr(isDev() ? md5(date('this')) : md5(SENAYAN_VERSION_TAG . $sysconf['static_file_version']), 0,5);
    return  $filename . '?v=' . $version;
  }
}

if (!function_exists('toastr'))
{
    /**
     * Helper to call toastrJS
     * alert template as function
     * 
     * usage: 
     * 
     * toastr('your message')->{template}('Toastr alert title leave empty for default title');
     * 
     * example:
     * 
     * toastr('Success insert data')->success();
     * 
     * Available template
     * - error, info, success, warning
     * 
     * if you use outside template, by default this function
     * will you use native browser alert.
     * 
     * @param string $message
     * @return void
     */
    function toastr(string $message)
    {
        // Anonymous class
        return new Class($message)
        {
            private $message = '';

            public function __construct($message)
            {
                $this->message = $message;
            }

            /**
             * use magic method to identifiy which
             * template user use.
             */
            public function __call($method, $parameters)
            {
                // Call toastrJS on utility class
                if (in_array($method, ['error','info','success','warning'])) utility::jsToastr($parameters[0]??__(ucfirst($method)), $this->message, $method);
                // native as default if template not available
                else utility::jsAlert($this->message);
            }
        };
    }
}