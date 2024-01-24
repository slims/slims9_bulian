<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-30 11:07:17
 * @modify date 2024-01-21 15:07:00
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Table;

use Closure;
use PDO;
use PDOException;
use RuntimeException;
use SLiMS\DB;

class Schema
{
    use Utils,Detail;
    
    private string $table = '';
    private string $column = '';
    public static $debug = false;
    private static $instance = null;
    private static $connectionName = '';
    private static $connection = null;
    private static $connectionProfile = [];
    
    /**
     * Create Information Schema database connection
     */
    private function __construct()
    {
        try {
            if (empty(self::$connectionName)) self::$connectionName = config('database.default_profile');
            self::$connection = DB::connection(self::$connectionName);
            self::$connectionProfile = DB::getCredential(self::$connectionName);
        } catch (RuntimeException $e) {
            die($e->getMessage());
        }
    }

    public static function connection(string $connectionName)
    {
        self::$instance = null;
        self::$connectionName = $connectionName;
        return self::getInstance();
    }

    public static function getConnectionName()
    {
        return self::$connectionName;
    }

    /**
     * Use singleton pattern
     *
     * @return void
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) self::$instance = new Schema;

        return self::$instance;
    }

    /**
     * Create and table schema 
     * with Blueprint as designer based on
     * rdbms grammar
     *
     * @param string $tableName
     * @param Closure $callBack
     * @return void
     */
    public function structureCreate(string $tableName, Closure $callBack)
    {
        if (self::hasTable($tableName)) return;
        
        $bluePrint = new Blueprint;
        $callBack($bluePrint);
        
        try {
            $createQuery = $bluePrint->create($tableName);
            $this->verbose = $createQuery;
            if (!self::$debug) self::$connection->query($createQuery);
        } catch (PDOException $e) {
            // debuging
            debug($e->getMessage(), $e->getTrace());
        }

        return self::getInstance();
    }

    /**
     * Modify existing table
     *
     * @param string $tableName
     * @param Closure $callBack
     * @return Schema|String
     */
    public function structureTable(string $tableName, $callBack = '')
    {
        if (!self::hasTable($tableName)) return "Table {$tableName} is not found!";

        self::getInstance()->table = $tableName;
        if (!is_callable($callBack)) return self::getInstance();

        $bluePrint = new Blueprint;
        $callBack($bluePrint);

        try {
            $alterQuery = $bluePrint->alter($tableName);
            $this->verbose = $alterQuery;
            if (!self::$debug) self::$connection->query($alterQuery);
        } catch (PDOException $e) {
            // debuging
            debug($e->getMessage(), $e->getTrace());
        }

        return self::getInstance();
    }

    /**
     * Drop table
     * just type table name and drop it.
     *
     * @param string $tableName
     * @return void
     */
    public function structureDrop(string $tableName)
    {
        self::$connection->query('DROP TABLE `' . $tableName . '`');
    }

    /**
     * Drop column table
     * just type table and column name then drop it.
     *
     * @param string $tableName
     * @return void
     */
    public function structureDropColumn(string $tableName, string $columnName)
    {
        self::$connection->query('ALTER TABLE `' . $tableName . '` DROP COLUMN `' . $columnName . '`');
    }

    /**
     * Emptying table
     *
     * @param string $tableName
     * @return void
     */
    public function structureTruncate(string $tableName)
    {
        self::$connection->query('TRUNCATE TABLE `' . $tableName . '`');
    }

    /**
     * Magic method to call data via scoped property or structure method
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
        else if (method_exists($this, $method = 'structure' . ucfirst($method)))
        {
            return $this->$method(...$arguments);
        }
    }

    public static function __callStatic($method, $arguments)
    {
        if (method_exists(self::getInstance(), $method = 'structure' . ucfirst($method))) {
            return self::getInstance()->$method(...$arguments);
        }
    }
}