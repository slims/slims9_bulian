<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-24 17:27:51
 * @modify date 2023-06-28 17:16:55
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Csv;

class Column
{
    private string $name;
    private ?string $value;

    public function __construct(string $name, ?string $value = null)
    {
        $this->name = $name;
        $this->value = $value;    
    }

    public function getName()
    {
        return $this->name??'';
    }

    public function getValue()
    {
        return $this->value??'';
    }
}