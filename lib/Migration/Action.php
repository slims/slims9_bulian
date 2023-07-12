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

    private function post()
    {
        require self::$directory . DS . 'post.php';
        (new \Post)->{$this->type}();
    }

    private function pre()
    {
        require self::$directory . DS . 'pre.php';
        (new \Pre)->{$this->type}();
    }

    public function getError()
    {
        return $this->error;
    }

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