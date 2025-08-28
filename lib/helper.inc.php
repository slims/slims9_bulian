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
use SLiMS\DB;
use SLiMS\Ip;
use SLiMS\Number;
use SLiMS\Currency;
use SLiMS\Log\Factory;
use SLiMS\Json;
use SLiMS\Jquery;
use SLiMS\Url;
use SLiMS\Http\Redirect;
use SLiMS\Session\Flash;
use SLiMS\Polyglot\Memory;
use SLiMS\Debug\VarDumper;

if (!function_exists('config')) {
    /**
     * Helper to get config with dot separator keys
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    function config($key = '', $default = null) {
        if (!empty($key)) {
            return Config::getInstance()->get($key, $default);
        }

        return Config::getInstance();
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

if (!function_exists('pluginUrl'))
{
    /**
     * Generate URL with plugin_container.php?id=<id>&mod=<mod> + custom query
     *
     * @param array $data
     * @param boolean $reset
     * @return string
     */
    function pluginUrl(array $data = [], bool $reset = false): string
    {
        // back to base uri
        if ($reset) return Url::getSelf(fn($self) => $self . '?mod=' . $_GET['mod'] . '&id=' . $_GET['id']);
        
        return Url::getSelf(function($self) use($data) {
            return $self . '?' . http_build_query(array_merge($_GET,$data));
        });
    }
}

if (!function_exists('pluginNavigateTo'))
{
    /**
     * Create url based on registered plugin menu
     * to navigate from current page to another page
     * without pain 😁
     *
     * @param string $filepath
     * @return string
     */
    function pluginNavigateTo(string $filepath): string
    {
        $fileInfo = pathinfo($filepath);
        $trace = debug_backtrace(limit:1)[0];
        $currentPath = dirname($trace['file']) . DS;

        if ($fileInfo['dirname'] != './' || '.\\')
        {
            $optionalPath = realpath($currentPath . $fileInfo['dirname']) . DS;
            // make sure path is only inside plugin/ 
            if ($optionalPath !== false) $currentPath = $optionalPath;
        }

        $path = $currentPath . $fileInfo['filename'] . '.' . ($fileInfo['extension']??'php');
        if (!file_exists($path)) return pluginUrl(['id' => 'notfound']);
        
        return pluginUrl(['id' => md5($path)]);
    }
}

if (!function_exists('commonList'))
{
    function commonList(string $type)
    {
        $dbs = \SLiMS\DB::getInstance('mysqli');
        ob_start();
        switch ($type) {
            case 'location':
                echo '<option value="0">'.__('All Locations').'</option>';
                $loc_q = $dbs->query('SELECT location_name FROM mst_location LIMIT 50');
                while ($loc_d = $loc_q->fetch_row()) {
                    echo '<option value="'.$loc_d[0].'">'.$loc_d[0].'</option>';
                }
                break;

            case 'collection':
                echo '<option value="0">'.__('All Collections').'</option>';
                $colltype_q = $dbs->query('SELECT coll_type_name FROM mst_coll_type LIMIT 50');
                while ($colltype_d = $colltype_q->fetch_row()) {
                    echo '<option value="'.$colltype_d[0].'">'.$colltype_d[0].'</option>';
                }
                break;
            
            case 'gmd':
                echo '<option value="0">'.__('All GMD/Media').'</option>';
                $gmd_q = $dbs->query('SELECT gmd_name FROM mst_gmd LIMIT 50');
                while ($gmd_d = $gmd_q->fetch_row()) {
                    echo '<option value="'.$gmd_d[0].'">'.$gmd_d[0].'</option>';
                }
                break;
        }
        
        return ob_get_clean();

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

/**
 * Translatation method
 * part of SLiMS\Polyglot
 */
if (!function_exists('__'))
{
    function __(string $content)
    {
        return Memory::find($content);
    }
}

/**
 * Write log with easiest way.
 * This helper let you not only write
 * log into database, you can write SLiMS
 * System log to another service
 */
if (!function_exists('writeLog')) {
    function writeLog(string $type, string $value_id, string $location, string $message, string $submod = '', string $action = '')
    {
        Factory::write($type, $value_id, $location, $message, $submod, $action);
    }
}

/**
 * The beauty php dumper
 */
if (!function_exists('dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $label = ($trace['file']??'') . ':' . ($trace['line']);
        VarDumper::dump($var, $label);

        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }

        if (1 < func_num_args()) {
            return func_get_args();
        }

        return $var;
    }
}

/**
 * DD is stand for "Dump and Die!".
 * SLiMS will be dump your process and make
 * next proses is not execute.
 */
if (!function_exists('dd')) {
    /**
     * @return never
     */
    function dd(...$vars)
    {
        if (!in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $label = ($trace['file']??'') . ':' . ($trace['line']);
        foreach ($vars as $seq => $v) {
            VarDumper::dump($v, ($seq === 0 ? $label : ''));
        }

        exit(1);
    }
}

if (!function_exists('debug'))
{
    /**
     * Helper to verbosing 
     * debug process
     * @return void
     */
    function debug(string $title, ...$moreVars)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        $debugLocation = ($trace['file']??'') . ':' . ($trace['line']);
        
        if (isDev()) {
            VarDumper::dump($title, $debugLocation);

            foreach($moreVars as $anotherVar) {
                VarDumper::dump(
                    ...(
                        is_array($anotherVar) ? [
                            $anotherVar[1]??'', $anotherVar[0]??'' // var to dump, label
                        ] : [$anotherVar]) // only var without label
                );
            }
        }
    }
}

if (!function_exists('debugBox')) {
    function debugBox(Closure|string $content)
    {
        if (isDev()) {
            $debug_box = '<details class="debug debug-empty">' . PHP_EOL;
            $debug_box .= '<summary><strong>#</strong>&nbsp;<span>Debug Box</span></summary>' . PHP_EOL;
            ob_start();
            is_callable($content) ? $content() : print($content);
            $debug_box .= '<section>'.ob_get_clean().'</section>' . PHP_EOL;
            $debug_box .= '</details>' . PHP_EOL;
            echo $debug_box;
        }
    }
}

/**
 * A shortcut to remove xss char
 */
if (!function_exists('xssFree')) {
    function xssFree(array|string $content)
    {
        return simbio_security::xssFree($content);
    }
}

/**
 * Have problem with multidimension array?
 * e.g :
 * with 
 * 
 * $record['biblio']['items']['detail'] = 'Ok';
 * 
 * generally every people will be do this
 * 
 * if (isset($record['biblio'])) {
 *    if (isset($record['biblio']['items']) {
 *       if (isset($record['biblio']['items']['detail']) {
 *          // you got here. : ( so nested
*        }
 *    }
 * }
 * 
 * // just do it and your are not waste your time
 * $result = getArrayData(map: 'biblio.items.detail', data: $record);
 * 
 * // output : ok pr if not exists will be give you empty result. 
 * 
 * Need another data type as default result? just define your $default output
 * at 3rd argument or default argument.
 * 
 * this function inspired from SLiMS\Config,
 */
if (!function_exists('getArrayData')) {
    function getArrayData(?array $data, string $map, $default = '')
    {
        if (!is_array($data)) return $default;

        $result = $default;
        foreach (explode('.', trim($map, '.')) as $key) {
            if (isset($data[$key]) && empty($result)) {
                $result = $data[$key];
                continue;
            }

            if (isset($result[$key])) {
                $result = $result[$key];
                continue;
            }
        }

        return $result;
    }
}

/**
 * @param string $connectionName
 * @param string $type
 */
if (!function_exists('db')) {
    function db(string $connectionName = 'SLiMS', string $type = 'pdo', string $extension = '', array $extensionParams = [])
    {
        if (!empty($extension)) {
            $extensionInstance = DB::$extension(...$extensionParams);
            if (method_exists($extensionInstance, 'setConnection')) $extensionInstance->setConnection($connectionName);
            return $extensionInstance;
        }
        if ($connectionName === 'SLiMS') return DB::getInstance($type);

        return DB::connection($connectionName, $type);
    }
}

if (!function_exists('isSerialized')) {
    /**
     * Check if the given string is serialized
     *
     * @param string $data
     * @return bool
     */
    function isSerialized($data) {
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen($data) < 4) {
            return false;
        }

        if ($data[1] !== ':') {
            return false;
        }

        $lastc = substr($data, -1);
        if ($lastc !== ';' && $lastc !== '}') {
            return false;
        }

        try {
            $result = @unserialize($data);
            if ($result === false && $data !== serialize(false)) {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}