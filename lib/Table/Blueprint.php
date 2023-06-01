<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-30 11:08:37
 * @modify date 2023-04-14 07:45:53
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Table;

use RuntimeException;

/**
 * @package SLiMS\Table
 * 
 * @property string $engine
 * @property string $charset
 * @property string $collation
 * 
 * @method SLiMS\Table\Blueprint string(string $column, int $length)
 * @method void autoIncrement(string $column)
 * @method void id()
 * @method void timestamps()
 * @method void fulltext(string $column)
 * @method void index(string $column)
 * @method void primary(string $column)
 * @method void nullable()
 * @method void notNull()
 * @method void after(string $column)
 * @method void first()
 * @method void change(string $newColumn)
 * @method void add()
 * @method void drop(string $column)
 * @method void default(string $value)
 */
class Blueprint
{
    /**
     * A collection for table data
     *
     * @var array
     */
    private $data = [
        'columns' => [],
        'rdbmsOpt' => ['engine' => '', 'charset' => '', 'collation' => ''],
        'options' => [],
    ];

    /**
     * Grammar supported
     *
     * @var string
     */
    private $rdbmsGrammar = 'Mysql';

    /**
     * Grammar class
     *
     * @var [type]
     */
    private $grammarClass = null;

    /**
     * Mode type
     */
    private $type = '';

    /**
     * Set grammar class for static 
     * call
     *
     * @return void
     */
    private function setGrammarClass()
    {
        $rdbmsGrammarClass = '\SLiMS\Table\Grammar\\' . $this->rdbmsGrammar;

        if (!class_exists($rdbmsGrammarClass)) throw new RuntimeException("Error : grammar {$rdbmsGrammarClass} not found!");

        if (is_null($this->grammarClass)) $this->grammarClass = $rdbmsGrammarClass;
    }

    /**
     * Get detail table data
     *
     * @return void
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Magic  method to processing
     * method call in this object or in the
     * grammar
     *
     * @param string $method
     * @param array $params
     * @return void
     */
    public function __call(string $method = '', array $params = [])
    {
        $this->setGrammarClass();

        /**
         * Column type checking
         */
        if (!is_null($type = $this->grammarClass::getType($method)))
        {
            $columnName = $params[0];
            $constraint = isset($params[1]) ? '(' . $params[1] . ')' : '';

            $this->data['columns'][] = trim(<<<SQL
                `{$columnName}` {$type}$constraint
            SQL);
            return $this;
        }

        /**
         * Special method in grammar
         */
        if (method_exists($this->grammarClass, $method))
        {
            $this->data['columns'][] = trim($this->grammarClass::$method($params[0], $params[1]));
            return $this;
        }

        /**
         * If method not exists in Grammar, chekck in scope
         */
        $scopeMethod = 'scope' . ucfirst($method);
        if (method_exists($this, $scopeMethod) && !is_null($value = call_user_func_array([$this, $scopeMethod], $params)))
        {
            $this->data['columns'][] = $value;
            return $this;
        }

        return $this;
    }

    /**
     * Set a property for rdmsOpt
     *
     * @param string $key
     * @param [type] $value
     */
    public function __set(string $key, $value)
    {
        if (isset($this->data['rdbmsOpt'][$key])) $this->data['rdbmsOpt'][$key] = $value;
    }

    /**
     * Default migrations table architecture
     *
     * @return void
     */
    public static function migrationArchitecture()
    {
        $static = new static;
        $static->engine = 'MyISAM';
        $static->autoIncrement('id');
        $static->string('filehash', 256)->nullable();
        $static->string('filepath', 256)->nullable();
        $static->string('class', 256)->nullable();
        $static->string('version', 50)->nullable();
        $static->index('class');
        $static->timestamps();
        return $static->grammarClass::create('migrations', $static->getData(), true);
    }

    /**
     * Compile a data with grammar compiler and created
     * SQL text
     *
     * @param string $tableName
     * @return void
     */
    public function create(string $tableName)
    {
        if (!class_exists($this->grammarClass)) return;
        
        return $this->grammarClass::create($tableName, $this->getData());
    }

    /**
     * Alter existing table
     *
     * @param string $tableName
     * @return void
     */
    public function alter(string $tableName)
    {
        if (!class_exists($this->grammarClass)) return;

        return $this->grammarClass::alter($tableName, $this->getData());
    }

    /**
     * Scope for id column
     *
     * @return void
     */
    private function scopeId()
    {
        return '`id` int(11) NOT NULL';
    }

    /**
     * Scope for autoincrement column
     *
     * @param string $columnName
     * @return void
     */
    private function scopeAutoIncrement(string $columnName)
    {
        return '`' . $columnName . '` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY';
    }

    /**
     * Create timestamps column
     * by default created_at and updated_at as
     * default column name
     *
     * @return void
     */
    private function scopeTimestamps()
    {
        return '`created_at` timestamp NULL, `updated_at` timestamp DEFAULT NOW()';
    }

    /**
     * Create fulltext key
     *
     * @param string $columnName
     * @return void
     */
    private function scopeFulltext(string $columnName)
    {
        $this->type = 'option';
        $this->data['options'][] = 'FULLTEXT KEY `' . $columnName . '_fulltext` (`' . $columnName . '`)';
    }

    /**
     * Create index key
     *
     * @param string $columnName
     * @return void
     */
    private function scopeIndex(string $columnName)
    {
        $this->type = 'option';
        $this->data['options'][] = 'KEY `' . $columnName . '_index` (`' . $columnName . '`)';
    }

    /**
     * Create unique key
     *
     * @param string $columnName
     * @return void
     */
    private function scopeUnique(string $columnName)
    {
        $this->type = 'option';
        $this->data['options'][] = 'UNIQUE `' . $columnName . '_unq` (`' . $columnName . '`)';
    }

        /**
     * Delete existing index
     *
     * @param string $indexName
     * @return void
     */
    private function scopeDropIndex(string $indexName, string $suffix = '')
    {
        $this->type = 'option';
        $this->data['options'][] = 'DROP INDEX `' . trim($indexName . $suffix) . '`';
    }


    /**
     * Set primarykey
     *
     * @param string $columnName
     * @return void
     */
    private function scopePrimary($columnName = '')
    {
        $this->type = 'option';
        if (empty($columnName) && !is_array($columnName))
        {
            $lastIndexData = array_key_last($this->data['columns']);

            $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' PRIMARY KEY';
        }
        else if (is_array($columnName))
        {
            
            $this->data['options'][] = 'PRIMARY KEY (`' . implode('`,`', $columnName) . '`)';
        }
        else
        {
            $this->data['options'][] = 'PRIMARY KEY (`' . $columnName . '`)';
        }
    }

    /**
     * Set null as default value
     *
     * @return void
     */
    private function scopeNullable()
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' DEFAULT NULL';
    }

    /**
     * Detail information for a column
     *
     * @param sring $comment
     * @return void
     */
    private function scopeComment(string $comment)
    {
        $lastIndexData = array_key_last($this->data['columns']);
        
        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' COMMENT \'' . $comment . '\' ';
    }

    /**
     * Set not null as default value
     *
     * @return void
     */
    private function scopeNotNull()
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' NOT NULL';
    }

    /**
     * Set column after existsting column
     *
     * @param string $columnName
     * @return void
     */
    private function scopeAfter(string $columnName)
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' AFTER `' . $columnName . '`';
    }

    /**
     * Set a columnt at first order of table
     *
     * @return void
     */
    private function scopeFirst()
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' FIRST';
    }

    /**
     * Change column attribute
     *
     * @return void
     */
    private function scopeChange(string $newColumnName = '')
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $perWord = explode(' ', $this->data['columns'][$lastIndexData]); // 0 as column name
        $this->data['columns'][$lastIndexData] = 'CHANGE ' . $perWord[0]  . ' ' . (!empty($newColumnName) ? $newColumnName : $this->data['columns'][$lastIndexData]);
    }

    /**
     * Add column into existing table
     */
    private function scopeAdd()
    {
        $type = empty($this->type) ? 'columns' : 'options';
        $lastIndexData = array_key_last($this->data[$type]);

        $perWord = explode(' ', $this->data[$type][$lastIndexData]??''); // 0 as column name
        $this->data[$type][$lastIndexData] = 'ADD ' . ($this->data[$type][$lastIndexData]??'');
        $this->type = '';
    }

    /**
     * Delete existing column
     *
     * @param string $columnName
     * @return void
     */
    private function scopeDrop(string $columnName)
    {
        $lastIndexData = array_key_last($this->data['columns']);

        if (count($this->data['columns']) === 0) $lastIndexData = 0;
        if (count($this->data['columns']) > 0) ++$lastIndexData;
        
        $this->data['columns'][$lastIndexData] = 'DROP ' . $columnName;
    }

    /**
     * Set default value with specific data
     *
     * @param string $value
     * @return void
     */
    private function scopeDefault(string $value)
    {
        $lastIndexData = array_key_last($this->data['columns']);

        $this->data['columns'][$lastIndexData] = $this->data['columns'][$lastIndexData] . ' DEFAULT \'' . $value . '\'';
    }
}