<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-02-12 10:31:29
 * @modify date 2023-02-12 10:49:18
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use SLiMS\Json;

class Response 
{
    private $result = null;

    public static function asJson(mixed $data)
    {
        exit(Json::stringify($data)->withHeader());
    }

    public static function asPlain(string $content)
    {
        header('Content-Type: text/plain');
        exit($content);
    }

    public static function asHtml(string $content)
    {
        header('Content-Type: text/html; charset=utf-8');
        exit($content);
    }

    public static function asJs(string $content)
    {
        header('Content-Type: text/javascript');
        exit($content);
    }

    public function __set($key, $value)
    {
        if (is_null($this->result)) $this->result = new \stdClass;
        $this->result->$key = $value;
    }

    public function __get($key)
    {
        return property_exists($this->result, $key) ? $this->result->$key : null;
    }
}