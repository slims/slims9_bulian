<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-25 09:18:33
 * @modify date 2022-12-25 10:52:03
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use SLiMS\Url;

class Redirect
{
    /**
     * Store Redirect property
     */
    private static $instance = null;
    private string $url = '';
    
    /**
     * @return Redirect
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new static;
        return self::$instance;
    }

    /**
     * Back to previous page
     *
     * @return void
     */
    public static function back()
    {
        self::getInstance()->url = Url::getReferer();
        return self::getInstance()->to();
    }

    /**
     * Redirect to SLiMS Path
     *
     * @param string $path
     * @return void
     */
    public static function toPath(string $path)
    {
        self::getInstance()->to(Url::getSlimsBaseUri('?p=' . $path));
    }

    /**
     * Setup custom Header
     *
     * @param splat ...$arguments
     * @return Redirect
     */
    public static function withHeader(...$arguments)
    {
        if (count($arguments) === 2) 
        {
            list($headerRule, $headerValue) = $arguments;
            header($headerRule, $headerValue);
        }
        else
        {
            foreach ($arguments[0] as $argument) header($argument[0], $argument[1]);
        }

        return self::getInstance();
    }

    /**
     * With flash message
     *
     * @param string $key
     * @param string $message
     * @return Redirect
     */
    public static function withMessage(string $key, string $message)
    {
        flash($key, $message);
        return self::getInstance();
    }

    /**
     * Refresh current page or to another page
     *
     * @param string $anotherPage
     * @return void
     */
    public function refresh(string $anotherPage = '')
    {
        header(trim("Refresh:0" . (!empty($anotherPage) ? '; url=' . $anotherPage : '')));
        exit;
    }

    /**
     * Redirect to another page
     *
     * @param string $urlOrPath
     * @return void
     */
    public function to(string $urlOrPath = '')
    {
        header('Location: ' . (empty($urlOrPath) ? $this->url : $urlOrPath));
        exit;
    }
}