<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 07:54:25
 * @modify date 2023-03-13 12:16:32
 * @license GPLv3
 * @desc easy library to interact with Guzzlehttp/Client
 */

namespace SLiMS\Http;

use Countable;
use IteratorAggregate;
use Exception;
use GuzzleHttp\Client as CoreClient;
use GuzzleHttp\Exception\ConnectException;

class Client implements IteratorAggregate,Countable
{
    use Utils,ArrayAble,Download;
    
    /**
     * Default property
     *
     * @var string
     */
    private $error = '';
    private $client = null;
    private $request = null;
    private $response = null;
    private $httpOptions = [];
    private static $instance = null;

    /**
     * HTTP Method list
     *
     * @var array
     */
    private static array $allowMethod = [
        'GET','HEAD','POST','PUT',
        'DELETE','OPTIONS','PATCH'
    ];

    private static $basicHttpOptions = [
        'withBody' => 'body',
        'withHeaders' => 'headers'
    ];

    /**
     * Initialization HTTP instance and Client instance
     *
     * @param string $url
     * @param array $options
     * @return void
     */
    public static function init(string $url = '', array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static;
            self::$instance->client = empty($url) ? new CoreClient() : new CoreClient(array_merge(['base_uri' => trim($url, '/') . '/'], $options));
            self::loadDefaultOptions();
        }
        
        return self::$instance;
    }

    /**
     * Default SLiMS Http Client
     *
     * @return void
     */
    private static function loadDefaultOptions()
    {
        foreach (config('http.client') as $option => $value) {
            if (!is_array($value)) self::withOption($option, $value);
            else self::withOptions($value);
        }
    }

    /**
     * Register guzzle option
     *
     * @param string $key
     * @param array|string $value
     * @return void
     */
    public static function withOption(string $key, array|string|bool $value)
    {
        self::init();
        self::$instance->httpOptions[$key] = $value;

        return self::$instance;
    }

    /**
     * Register massive guzzle option
     *
     * @param array $options
     * @return void
     */
    public static function withOptions(array $options)
    {
        self::init();
        foreach ($options as $key => $value) self::withOption($key, $value);

        return self::$instance;
    }

    /**
     * Reset previous instance
     *
     * @return void
     */
    public static function reset()
    {
        self::$instance = null;
    }

    /**
     * Manage static and non static call magic method
     *
     * @param string $method
     * @param array $options
     * @return Http
     */
    private static function magicCaller($method,$options)
    {
        $http = self::init();
        try {
            if (!in_array(strtoupper($method), static::$allowMethod)) 
                throw new Exception("{$method} : invalid HTTP method");

            $url = $options[0];

            $http->request = $http->client->{$method}($url, $http->compileOptions(array_slice($options, 1)??[]));
            $http->response = new Response;
            $http->response->headers = $http->request->getHeaders();
            $http->response->content = $http->request->getBody()->getContents();
            
        } catch (ConnectException $e) {
            $http->error = explode("\n", $e->getMessage())[0]??'Error';
        }        

        return $http;
    }

    /**
     * Implement Countable interface method
     * to count requested result data
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    public static function __callStatic($method, $options)
    {
        if (isset(self::$basicHttpOptions[$method])) return self::withOption(self::$basicHttpOptions[$method], ...$options);
        if (!method_exists(__CLASS__, $method)) return self::magicCaller($method, $options);
    }

    public function __call($method, $options)
    {
        if (isset(self::$basicHttpOptions[$method])) return self::withOption(self::$basicHttpOptions[$method], ...$options);
        if (!method_exists($this, $method)) return  self::magicCaller($method, $options);
    }
}