<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-12-29 10:02:23
 * @modify date 2024-04-17 11:26:09
 * @license GPL-3.0 
 * @desc [description]
 */
namespace SLiMS;

final class Error
{
    private array $traces = [];
    private array $info = [];
    private string $buffer = '';
    private string $templateLocation = __DIR__ . '/../template/';

    private array $templateTypes = [
        'development' => 'exception.php',
        'production' => 'errorproduction.php'
    ];

    const DEV_REPORT = -1;
    const PROD_REPORT = E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED;

    /**
     * @param array $info
     * @param array $traces
     * @return Error
     */
    public static function set(array $info, array $traces): Error
    {
        $static = new static;
        $static->info = $info;
        $static->traces = $traces;

        return $static;
    }

    /**
     * Determine current request is need
     * json response or not
     *
     * @return boolean
     */
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

    /**
     * Determine current request is based on
     * simbioaAJAX request or not
     *
     * @return bool
     */
    private function withSimbioAJAXRequest(bool $outputWithHeader = true)
    {
        $headers = getallheaders();

        if (isset($headers['X-Requested-With']) && $headers['X-Requested-With'] == 'XMLHttpRequest') {
            if ($outputWithHeader) header('Content-Type: application/json');
            return true;
        }

        return false;
    }

    /**
     * Environment detector
     *
     * @return boolean
     */
    private function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * Throw back a error response as JSON
     */
    private function sendAsJson(bool $withReport)
    {
        return Json::stringify($withReport ? [
            'status' => false,
            'message' => $this->info['message']??'-',
            'file' => $this->info['path']??'-',
            'line' => $this->info['line']??'-',
            'traces' => $this->traces
        ] : ['status' => false, 'message' => 'Something error']);
    }

    /**
     * Parse error response
     *
     * @return void
     */
    private function parse()
    {
        global $sysconf;

        if ($this->isJsonRequest() || ($isCli = $this->isCli())) return $this;

        $title = $this->info['message'];
        $traces = $this->traces;
        $headers = getallheaders();

        extract($this->info);
        ob_get_clean();
        if (!ob_get_level()) ob_start();
        include $this->templateLocation . $this->templateTypes['development'];
        $this->buffer = ob_get_clean();

        return $this;
    }

    public function send(string $inEnv) {
        global $sysconf;

        ini_set('display_errors', ($withReport = $inEnv === 'development'));
        error_reporting($withReport ? self::DEV_REPORT : self::PROD_REPORT);

        if ($this->isJsonRequest() || ($isCli = $this->isCli())) {
            $json = $this->sendAsJson($withReport);

            if (!isset($isCli)) $json->withHeader();
            else $json->prettyPrint();

            exit($json);
        }

        if ($withReport) {
            $output = $this->buffer;
        } else {
            ob_get_clean();
            ob_start();
            include $this->templateLocation . $this->templateTypes['production'];
            $output = ob_get_clean();
        }

        exit($output??'');
    }

    public function render()
    {
        if(!headers_sent()) header('HTTP/1.1 500 Error', true, 500);
        return $this->parse();
    }
}