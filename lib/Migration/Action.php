<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-07-12 10:51:52
 * @modify date 2023-07-12 11:28:05
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Migration;

class Action
{
    private string $error = '';
    private string $type = '';
    private static string $directory = '';

    public static function init()
    {
        return new static;
    }

    public static function setDirectory(string $directory)
    {
        self::$directory = $directory;
    }

    /**
     * Run some job after parent process done
     * if post.php exists
     *
     * @return void
     */
    private function post()
    {
        if (!file_exists($path = self::$directory . DS . 'post.php')) return;
        require $path;
        (new \Post)->{$this->type}();
    }

        /**
     * Run some job before parent process done
     * if pre.php exists
     *
     * @return void
     */
    private function pre()
    {
        if (!file_exists($path = self::$directory . DS . 'pre.php')) return;
        require $path;
        (new \Pre)->{$this->type}();
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * Manage pre && post process
     * based on "enable" or "Disable"
     *
     * @param string $method
     * @param array $aguments
     * @return void
     */
    public static function __callStatic(string $method, array $aguments)
    {
        $action = self::init();
        
        try {
            $action->type = strtolower(str_replace(['pre','post'], '', $method));
            $method = strtolower(str_replace(['Disable','Enable'], '', $method));

            $action->{$method}();
        } catch (\Exception $e) {
            $action->error = $e->getMessage();
        }

        return $action;
    }
}