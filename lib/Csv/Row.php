<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-06-24 14:22:22
 * @modify date 2023-07-09 14:34:56
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Csv;

use Closure;
use ArrayIterator;
use Countable;
use IteratorAggregate;

class Row implements IteratorAggregate,Countable
{
    /**
     * output type
     */
    const KEY_BASED = 1;
    const VALUE_BASED = 2;

    /**
     * \SLiMS\Csv\Columns
     *
     * @var object
     */
    private object $columns;

    /**
     * CSV standart
     *
     * @var array
     */
    private array $standart;

    /**
     * Custom formatter
     *
     * @var Closure|null
     */
    private ?Closure $customFormatter = null;

    private array $columnModifierList = [];

    /**
     * @param array $data
     * @param array|null $standart
     * @param Closure|null $callback
     */
    public function __construct(array $data, ?array $standart = null, ?Closure $callback = null)
    {
        $this->columns = new Columns($data);
        $this->standart = $standart??config('csv');
        if (is_callable($this->customFormatter)) $this->customFormatter = $callback;
    }

    /**
     * Make a content based on \SLiMS\Csv\Columns
     *
     * @param int $type
     * @return string
     */
    public function generate(int $type = self::VALUE_BASED):string
    {
        // Return as column name or data?
        if (isset($this->standart['key_based'])) $type = self::KEY_BASED;
        
        $contents = '';
        foreach ($this->columns as $column) {
            $columnValue = $column->getValue();
            $columnName = $column->getName();
            // if column has modifier
            if ($this->hasModifier($columnName)) $columnValue = $this->callModifier($columnName, $columnValue);
            // concating data based on column name or value
            $contents .= $this->formatter($type === self::KEY_BASED ? $columnName : $columnValue);
        }
        
        return trim($contents, $this->standart['separator']) . $this->standart['record_separator']['newline'];
    }

    /**
     * Format csv content
     *
     * @param string $content
     * @return string
     */
    private function formatter(string $content):string 
    {
        $encloser = $this->standart['enclosed_with'];
        $separator = $this->standart['separator'];
        
        if (is_callable($this->customFormatter)) $content = $this->customFormatter($content);
        return $encloser . str_replace($encloser, $encloser.$encloser, $content) . $encloser . $separator;
    }

    /**
     * Get all columns data
     *
     * @return Columns
     */
    public function all(): Columns
    {
        return $this->columns;
    }

    /**
     * Add new column data
     *
     * @param string $columnName
     * @param string $columnValue
     * @return void
     */
    public function add(string $columnName, string $columnValue): void
    {
        $this->columns->add($columnName, $columnValue);
    }

    /**
     * Modify column content based on
     * user function
     *
     * @param string $columnName
     * @param Closure $callback
     * @return void
     */
    public function registerColumnModifier(string $columnName, Closure $callback):void
    {
        $this->columnModifierList[$columnName] = $callback;
    }

    private function hasModifier(string $columnName)
    {
        return isset($this->columnModifierList[$columnName]);
    }

    private function callModifier(string $columnName, $content)
    {
        return $this->columnModifierList[$columnName]($content);
    }

    /**
     * Set row standart
     *
     * @param array|string $key
     * @param string $value
     * @return Row
     */
    public function setStandart(array|string $key, string $value = ''): Row
    {
        if (is_array($key)) $this->standart = array_merge($this->standart, $key);
        else $this->standart[$key] = $value;
        return $this;
    }

    public function toArray()
    {
        return (array)$this->columns;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->columns);
    }

    public function count(): int
    {
        return count($this->columns);
    }
}