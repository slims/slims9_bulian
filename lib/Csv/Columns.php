<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-24 17:33:18
 * @modify date 2023-06-28 16:59:00
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Csv;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Columns implements IteratorAggregate,Countable
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = [];
        foreach ($data as $name => $value) $this->data[$name] = new Column($name, $value);
    }

    public function getData()
    {
        return $this->data;
    }

    public function add(string $name, $value)
    {
        $this->data[$name] = new Column($name, $value);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator(array_values($this->data));
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function __get(string $key)
    {
        return isset($this->$key) ? $this->$key : null;
    }

    public function __isset(string $key)
    {
        return isset($this->data[$key]);
    }
}