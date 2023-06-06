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

use SLiMS\Config;
use SLiMS\Ip;
use SLiMS\Number;
use SLiMS\Currency;
use SLiMS\Json;
use SLiMS\Jquery;
use SLiMS\Http\Redirect;
use SLiMS\Session\Flash;

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

if (!function_exists('isDev')) 
{
    function isDev()
    {
        return ENVIRONMENT === 'development';
    }
}

if (!function_exists('isCli')) 
{
    function isCli()
    {
        return php_sapi_name() === 'cli';
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

if (!function_exists('flash'))
{
    /**
     * Set flash session message
     *
     * @param string $key
     * @param string $message
     * @return Flash|AnonymousClass
     */
    function flash(string $key = '', string $message = '')
    {
        if (!empty($message) && !empty($key))  return Flash::register($key, $message);

        return new Class {
            public function __call($method, $arguments)
            {
                if (method_exists(Flash::class, $method)) 
                {
                    return Flash::{$method}(...$arguments);
                }
                else
                {
                    return Flash::init()->$method(...$arguments);
                }
            }
        };
    }
}

if (!function_exists('jQuery'))
{
    function jQuery(string $selector = '')
    {
        if (!empty($selector)) return Jquery::getInstance($selector);
        echo Jquery::getInstance('');
    }
}

if (!function_exists('redirect'))
{
    /**
     * Redirect page with many options
     *
     * @param string $urlOrPath
     * @return void
     */
    function redirect(string $urlOrPath = '')
    {
        if (!empty($urlOrPath))  Redirect::getInstance()->to($urlOrPath);

        return new Class {
            /**
             * Redirect html content via Simbio AJAX
             *
             * @param string $url
             * @param string $data
             * @param string $position
             * @param string $selector
             * @return void
             */
            public function simbioAJAX(string $url, string $data = '', string $position = 'top.', string $selector = '#mainContent', int $timeout = 0)
            {
                $params = empty($data) ? "'$url'" : "'$url', {method: 'post', addData: '$data'}";
                exit(jQuery($selector)->setPosition($position)->simbioAJAX($params)->delayIn($timeout));
            }

            public function __call($method, $arguments)
            {
                if (method_exists(Flash::class, $method)) 
                {
                    return Redirect::{$method}(...$arguments);
                }
                else
                {
                    return Redirect::getInstance()->$method(...$arguments);
                }
            }
        };
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