<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-12-29 10:02:23
 * @modify date 2023-12-30 14:56:38
 * @license GPL-3.0 
 * @desc [description]
 */
namespace SLiMS;

final class Error
{
    private array $traces = [];
    private array $info = [];

    public static function set(array $info, array $traces)
    {
        $static = new static;
        $static->info = $info;
        $static->traces = $traces;

        return $static;
    }

    private function isJsonRequest()
    {
        $headers = getallheaders();
        $contentType = isset($headers['Content-Type']) && $headers['Content-Type'] === 'application/json';
        $responseWith = isset($headers['X-Response-With']) && $headers['X-Response-With'] === 'application/json';
    
        if ($contentType || $responseWith) {
            header('Content-Type: application/json');
            return true;
        }

        return false;
    }

    private function withSimbioAJAXRequest()
    {
        $headers = getallheaders();

        if (isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest') {
            header('Content-Type: application/json');
            return true;
        }

        return false;
    }

    private function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    private function sendAsJson()
    {
        return Json::stringify([
            'status' => false,
            'message' => $this->info['message']??'-',
            'file' => $this->info['path']??'-',
            'line' => $this->info['line']??'-',
            'traces' => $this->traces
        ]);
    }

    private function parse()
    {
        global $sysconf;

        if ($this->isJsonRequest() || ($isCli = $this->isCli())) {
            $json = $this->sendAsJson();

            if (!isset($isCli)) $json->withHeader();
            else $json->prettyPrint();
            
            exit($json);
        }

        $title = $this->info['message'];
        $traces = $this->traces;
        $headers = getallheaders();

        extract($this->info);
        if (!ob_get_level()) ob_start();

        if (ENVIRONMENT === 'development') 
        {
            @error_reporting(-1);
            @ini_set('display_errors', true);
            ob_get_clean();
            include __DIR__ . '/../template/exception.php';
        }
        else 
        {
            // Production mode
            @ini_set('display_errors', false);
            @error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
            ob_get_clean();

            if (!$this->withSimbioAJAXRequest()) 
            {
                include __DIR__ . '/../template/errorproduction.php';
            }
        }

        $content = ob_get_clean();
        exit($content);
    }

    public function render()
    {
        @header('HTTP/1.1 500 Error', false, 500);
        $this->parse();
    }
}