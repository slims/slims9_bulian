<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 17:20:28
 * @modify date 2022-11-15 17:52:59
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;

use Closure;

trait Download
{
    /**
     * Download file via URL
     *
     * @param string $url
     * @return anonymousClass
     */
    public static function download(string $url)
    {
        self::reset();
        $http = self::init($url);
        return new Class($url, $http) {
            private $url = '';
            private $top = null;
            private $options = [];

            public function __construct($url, $top)
            {
                $this->url = $url;
                $this->top = $top;
            }

            /**
             * Make http get request and
             * sink it into some path
             *
             * @param string $pathToSaveFile
             * @return void
             */
            public function to(string $pathToSaveFile)
            {
                try {
                    $this->top->get($this->url, array_merge(['sink' => $pathToSaveFile], $this->options));
                } catch (\Exception $e) {
                    $this->top->setError($e->getMessage());
                }
            }

            /**
             * Download file with progress
             *
             * @param string $pathToSaveFile
             * @param Closure $callback
             * @return void
             */
            public function withProgress(string $pathToSaveFile, Closure $callback)
            {
                $this->options['progress'] = $callback;
                $this->to($pathToSaveFile);
            }
        };
    }

    /**
     * Stream file from URL
     *
     * @param string $url
     * @param array $optionalHeader
     * @return void
     */
    public static function stream(string $url, array $optionalHeader = [])
    {
        self::reset();
        $http = self::init($url);
        $http->get($url);

        $mime = $http->getHeader('Content-Type')[0]??'text';
        
        foreach ($optionalHeader as $header) header($header);
        header('Content-Type: ' . $mime);
        exit($http->getContent());
    }
}
