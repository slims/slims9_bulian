<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-03-01 20:22:50
 * @modify date 2023-03-07 08:09:40
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

    public function cleanUp(): void
    {
        foreach ($this->variableToClean as $type => $globalVariable) {
            foreach ($globalVariable as $key => $value) {
                if (is_array($value)) continue;
                $newValue = utility::filterData($key, $type, true, true, true);

                switch ($type) {
                    case 'get':
                        $_GET[$key] = $newValue;
                        break;
                    
                    case 'server':
                        $_SERVER[$key] = $newValue;
                        break;

                    case 'cookie':
                        $_COOKIE[$key] = $newValue;
                        break;

                    case 'session':
                        $_SESSION[$key] = $newValue;
                        break;
                }
            }
        }
    }
}