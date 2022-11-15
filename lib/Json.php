<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-07-29 13:05:34
 * @modify date 2022-11-15 17:07:17
 * @license GPLv3
 * @desc : 
 * - Manage & manipulation JSON en|de-code process
 * - easy iterate with foreach at decoding/parsing process
 */

namespace SLiMS;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use JsonException;

class Json implements IteratorAggregate,Countable
{
    /**
     * Instance
     *
     * @var Json|null
     */
    private static $instance = null;

    /**
     * Property for inputed json string
     *
     * @var string
     */
    private string $original;

    /**
     * Error
     *
     * @var string
     */
    private string $error = '';

    /**
     * An option for json output
     *
     * @var boolean
     */
    private bool $assoc = false;

    /**
     * Json number option
     *
     * @var integer
     */
    private int $option = 0;

    /**
     * Json depth
     *
     * @var integer
     */
    private int $depth = 512;

    /**
     * Magic method mapping for JSON option
     *
     * @var array
     */
    private array $scope = ['prettyPrint' => JSON_PRETTY_PRINT];

    /**
     * Attributes
     *
     * @var Object|Array
     */
    private $attributes;

    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Json;
        return self::$instance;
    }

    /**
     * Register json string
     * 
     * @param string $jsonString
     * @param boolean $error
     * @return Json | Array via ArrayIterator
     */
    public static function parse(string $jsonString, bool $error = false): Json
    {
        if ($error) self::getInstance()->option = JSON_THROW_ON_ERROR;
        self::getInstance()->original = $jsonString;
        return self::getInstance();
    }

    /**
     * Register data to prepare json_encode process
     *
     * @param mix $data
     * @return Json
     */
    public static function stringify($data): Json
    {
        self::getInstance()->attributes = $data;
        return self::getInstance();
    }

    /**
     * Implement IteratorAggregate interface
     *
     * Use for iterate attributes at foreach()
     * 
     * @param Undocumented function value
     * @return value
     */
    public function getIterator(): ArrayIterator
    {
        $this->toArray();
        return new ArrayIterator($this->attributes);
    }

    /**
     * Counting attributes data
     *
     * @return int
     */
    public function count(): int
    {
        $this->process();
        return count((array)$this->attributes);
    }

    /**
     * JSON en|de-code process
     *
     * @param string $type
     * @return Json
     */
    private function process($type = 'decode'): Json
    {
        try {
            $this->attributes = $type === 'decode' ? 
                                    json_decode($this->original, $this->assoc, $this->depth, $this->option) : 
                                    json_encode($this->attributes, $this->option);
        } catch (JsonException $e) {
            $this->error = $e->getMessage();
        }
        return $this;
    }

    /**
     * Convert attributes to array
     *
     * @param Undocumented function value
     * @return value
     */
    public function toArray(): array
    {
        $this->assoc = true;
        $this->process();

        if (!is_array($this->attributes)) 
        {
            $this->error = '$attributes property is not valid json_decode result';
            $this->attributes = [];
        }

        return $this->attributes;
    }

    /**
     * Set up json option
     *
     * @param [type] $option
     * @return void
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * JSON Output with header
     *
     * @return Json
     */
    public function withHeader(): Json
    {
        header('Content-Type: application/json');
        return $this;
    }

    /**
     * Get attributes
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->process()->attributes;
    }

    /**
     * Get JSON error
     *
     * @return string
     */
    public static function getError()
    {
        return self::getInstance()->error;
    }

    /**
     * Get value from attributes based on magic property
     *
     * @param [type] $key
     * @return mixed
     */
    public function __get($key)
    {
        return self::getInstance()->process()->toArray()[$key]??null;
    }

    /**
     * Call Json scope for optional json process
     * as magic method
     *
     * @param [type] $method
     * @param [type] $params
     * @return void
     */
    public function __call($method, $params)
    {
        if (isset($this->scope[$method])) $this->option = $this->scope[$method];
        return $this;
    }

    /**
     * Main output for encoding process
     *
     * @param Undocumented function value
     * @return value
     */
    public function __toString()
    {
        $this->process('encode');
        $output = '';
        if ($this->option === JSON_PRETTY_PRINT) $output .= '<pre>';
        $output .= $this->attributes;
        if ($this->option === JSON_PRETTY_PRINT) $output .= '</pre>';
        
        return $output;
    }
}