<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 07:54:25
 * @modify date 2022-11-15 17:56:25
 * @license GPLv3
 * @desc easy library to interact with Guzzlehttp/Client
 */

namespace SLiMS\Http;

use Countable;
use IteratorAggregate;
use Exception;
use GuzzleHttp\Client as CoreClient;

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
    private $content = null;
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
        }
        
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

            $http->request = $http->client->{$method}($url, ...(array_slice($options, 1)??[]));
            $http->content = $http->request->getBody()->getContents();
            
        } catch (Exception $e) {
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
        return self::magicCaller($method, $options);
    }

    public function __call($method, $options)
    {
        return self::magicCaller($method, $options);
    }
}