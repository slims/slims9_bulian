<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-10 22:51:38
 * @modify date 2022-10-15 12:07:31
 * @license GPLv3
 * @desc Number manipulation tool
 */

namespace SLiMS;

class Number
{
    private $input;
    
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Set an input value
     *
     * @param $input
     * @return Number
     */
    public static function set($input)
    {
        return new static($input);
    }

    /**
     * Convert number into currency format
     *
     * @return string
     */
    public function toCurrency()
    {
        return (new Currency($this->input))->get();
    }

    /**
     * Convert input into integer
     *
     * @return integer
     */
    public function toInteger()
    {
        return (int)$this->input;
    }

    /**
     * Convert input to float data
     *
     * @return float
     */
    public function toFloat()
    {
        return floatval($this->input);
    }

    /**
     * format number into decimal with some criteria
     */
    public function toDecimal(int $decimalNumber = 2, string $decimalSeparator = ',', string $thousand = ',')
    {
        return number_format($this->toFloat(), $decimalNumber, $decimalSeparator, $thousand);
    }

    /**
     * Get number by position number
     *
     * @param integer|null $startPosition
     * @param integer|null $endPosition
     * @return string
     */
    public function get(?int $startPosition, ?int $endPosition = 0)
    {
        return substr($this, $startPosition, ($endPosition === 0 ? 1 : $endPosition));
    }

    /**
     * Get length of number
     *
     * @return string
     */
    public function len()
    {
        return strlen($this);
    }

    /**
     * Chunk a number with some
     * character
     *
     * @param string $delimiter
     * @return array
     */
    public function chunk(string $delimiter)
    {
        return explode($delimiter, $this);
    }

    /**
     * Round some number
     *
     * @param integer $precision
     * @return void
     */
    public function round(int $precision = 0)
    {
        return (int)round($this->toFloat(), $precision);
    }

    /**
     * Ceil number
     *
     * @return value
     */
    public function ceil()
    {
        return (int)ceil($this->toFloat());
    }

    /**
     * Modulus
     *
     * @param integer $modNumber
     * @return void
     */
    public function mod(int $modNumber)
    {
        return $this->toInteger() % $modNumber;
    }

    public function __toString()
    {
        return (string)$this->input;
    }
}