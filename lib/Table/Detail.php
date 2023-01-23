<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-20 09:41:31
 * @modify date 2023-01-23 11:56:13
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Table;

use PDO;

trait Detail
{
    /**
     * Get column data in 
     * INFORMATION_SCHEMA.TABLES base on key
     *
     * @var array
     */
    private array $detailTableScope = [
        'engine' => 'ENGINE',
        'rowcount' => 'TABLE_ROWS',
        'collation' => 'TABLE_COLLATION',
        'autoincrement' => 'AUTO_INCREMENT',
        'comment' => 'TABLE_COMMENT',
        'temporary' => 'TEMPORARY',
        'createtime' => 'CREATE_TIME',
        'updatetime' => 'UPDATE_TIME',
        'checktime' => 'CHECK_TIME'
    ];

    /**
     * Get column data in 
     * INFORMATION_SCHEMA.COLUMNS base on key
     *
     * @var array
     */
    private array $detailColumnScope = [
        'type' => 'DATA_TYPE',
        'position' => 'ORDINAL_POSITION',
        'null' => 'IS_NULLABLE',
        'autoincrement' => 'EXTRA',
        'comment' => 'COLUMN_COMMENT',
        'collation' => 'COLLATION_NAME',
        'default' => 'COLUMN_DEFAULT',
        'maxlength' => 'CHARACTER_MAXIMUM_LENGTH',
        'key' => 'COLUMN_KEY',
        ''
    ];

    /**
     * @param string $tableName
     * @return PDOStatement
     */
    public function getTableDetail(string $tableName)
    {
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT * FROM `TABLES` WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = :table_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database'], 'table_name' => $tableName]);

        return $tableState;
    }

    /**
     * @param string $tableName
     * @param string $columnName
     * @return PDOStatement
     */
    public function getColumnDetail(string $tableName, string $columnName)
    {
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT * FROM `COLUMNS` WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = :table_name AND `COLUMN_NAME` = :column_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database'], 'table_name' => $tableName, ':column_name' => $columnName]);

        return $tableState;
    }

    /**
     * @param string $tableName
     * @param boolean $fetchAll
     * @return array
     */
    public function getTableColumn(string $tableName, bool $fetchAll = false)
    {
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT * FROM `COLUMNS` WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = :table_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database'], 'table_name' => $tableName,]);
        
        $data = [];
        while ($result = $tableState->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $fetchAll ? $result : $result['COLUMN_NAME'];
        }

        return $data;
    }

    /**
     * CHeck if table is exists or not
     *
     * @param string $tableName
     * @return boolean
     */
    public static function hasTable(string $tableName):bool
    {
        return (bool) self::getInstance()->getTableDetail($tableName)->rowCount();
    }

    /**
     * Check if column exists or not
     *
     * @param string $tableName
     * @param string $columnName
     * @return boolean
     */
    public static function hasColumn(string $tableName, string $columnName):bool
    {
        return (bool) self::getInstance()->getColumnDetail($tableName, $columnName)->rowCount();
    }

    /**
     * This method relate with Schema::table('<table_name>')
     *
     * @param string $column
     * @return Schema
     */
    public function column(string $column)
    {
        if (!empty($this->table)) $this->column = $column;
        return $this;
    }

    /**
     * Same as column, but this
     * method will give you more raw information
     * about column in some table
     *
     * @param boolean $fetchAll
     * @return array
     */
    public function columns(bool $fetchAll = false)
    {
        return !empty($this->table) ? $this->getTableColumn($this->table, $fetchAll) : [];
    }

    /**
     * Get all table data from current database
     *
     * @return array
     */
    public static function tables()
    {
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT `TABLE_NAME` FROM `TABLES` WHERE `TABLE_SCHEMA` = :database_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database']]);

        $table = [];
        while ($data = $tableState->fetch(PDO::FETCH_ASSOC)) {
            $table[] = $data['TABLE_NAME'];
        }

        return $table;
    }

    /**
     * Get boolean result about
     * table and column Auto Increment status
     *
     * @return boolean
     */
    public function isAutoIncrement()
    {
        if (empty($this->column)) return (bool)$this->getAutoIncrement();
        else return $this->getAutoIncrement() === 'auto_increment';
    }

    /**
     * @return boolean
     */
    public function isTemporary()
    {
        return empty($this->column) ? ($this->getTemporary() !== 'N') : false;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->column) ? (!(bool)$this->getRowCount()) : false;
    }

    /**
     * @return boolean
     */
    public function isNull()
    {
        return !empty($this->column) ? ($this->getNull() === 'YES') : false;
    }

    /**
     * @return boolean
     */
    public function isUnique()
    {
        return !empty($this->column) ? ($this->getKey() === 'UNI') : false;
    }

    /**
     * @return boolean
     */
    public function isPrimary()
    {
        return !empty($this->column) ? ($this->getKey() === 'PRI') : false;
    }

    /**
     * @return boolean
     */
    public function isFullText()
    {
        return !empty($this->column) ? ($this->getKey() === 'MUL') : false;
    }

    /**
     * @return boolean
     */
    public function isExists()
    {
        if (!empty($this->column)) return !is_null($this->getType());
        else return (bool)$this->getTableDetail($this->table)->rowCount();
    }

    /**
     * Magic method to call data via scoped property
     *
     * @param [type] $method
     * @param [type] $arguments
     * @return string
     */
    public function __call($method, $arguments)
    {
        $escapeMethod = strtolower(str_replace('get', '', $method));
        
        if (isset($this->detailTableScope[$escapeMethod]) && empty($this->column) && !empty($this->table)) 
        {
            $data = $this->getTableDetail($this->table)->fetchObject();
            $column = $this->detailTableScope[$escapeMethod];
            return $data && property_exists($data, $column) ? $data->{$column} : null;
        }
        else if (!empty($this->column) && !empty($this->table))
        {
            $data = $this->getColumnDetail($this->table, $this->column)->fetchObject();
            $column = $this->detailColumnScope[$escapeMethod];
            return $data && property_exists($data, $column) ? $data->{$column} : null;
        }
    }
}