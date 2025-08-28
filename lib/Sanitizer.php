<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-03-01 20:22:50
 * @modify date 2023-04-14 07:22:00
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS;

use Utility;

class Sanitizer 
{
    private $variableToClean = [];
    
    public static function fromGlobal(array $variableToClean = []): self
    {
        return new self($variableToClean);
    }

    public function __construct(array $variableToClean = [])
    {
        $this->variableToClean = $variableToClean;
    }

    private function setNewValue(string $type, string $key, $value)
    {
        switch ($type) {
            case 'get':
                $_GET[$key] = $value;
                break;

            case 'post':
                $_POST[$key] = $value;
                break;
            
            case 'server':
                $_SERVER[$key] = $value;
                break;

            case 'cookie':
                $_COOKIE[$key] = $value;
                break;

            case 'session':
                $_SESSION[$key] = $value;
                break;
        }
    }

    public function quoteFree(array $exception = [])
    {
        foreach ($this->variableToClean as $type => $globalVariable) {
            foreach ($globalVariable as $key => $value) {
                if (in_array($key, $exception)) continue;
                if (is_array($value)) continue;
                $this->setNewValue($type, $key, str_replace(['\'','"','`'],'',$value));
            }
        }
    }

    public function cleanUp(array $exception = [], array $filter = [true, true, true] /* escape_sql, trim, strip_tag */): void
    {
        foreach ($this->variableToClean as $type => $globalVariable) {
            foreach ($globalVariable as $key => $value) {
                if (in_array($key, $exception)) continue;
                if (is_array($value)) continue;
                $this->setNewValue($type, $key, utility::filterData($key, $type, ...$filter));
            }
        }
    }
}