<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-30 11:07:17
 * @modify date 2022-12-19 12:34:46
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
    use Utils;
    
    public static $debug = false;
    private static $instance = null;
    private static $connection = null;
    private static $connectionProfile = [];
    
    /**
     * Create Information Schema database connection
     */
    private function __construct()
    {
        try {
            self::$connectionProfile = config('database.nodes.SLiMS', []);
            self::$connection = new PDO("mysql:host=".self::$connectionProfile['host'].';port='.self::$connectionProfile['port'].';dbname=information_schema', self::$connectionProfile['username'], self::$connectionProfile['password']);
        } catch (RuntimeException $e) {
            die($e->getMessage());
        }
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
    public static function create(string $tableName, Closure $callBack)
    {
        if (self::hasTable($tableName)) return;
        
        $bluePrint = new Blueprint;
        $callBack($bluePrint);
        
        $static = new static;
        try {
            $createQuery = $bluePrint->create($tableName);
            $static->verbose = $createQuery;
            if (!self::$debug) DB::getInstance()->query($createQuery);
        } catch (PDOException $e) {
            // debuging
            debug($e->getMessage(), $e->getTrace());
        }

        return $static;
    }

    /**
     * Modify existing table
     *
     * @param string $tableName
     * @param Closure $callBack
     * @return void
     */
    public static function table(string $tableName, Closure $callBack)
    {
        if (!self::hasTable($tableName)) return "Table {$tableName} is not found!";

        $bluePrint = new Blueprint;
        $callBack($bluePrint);

        $static = new static;
        try {
            $alterQuery = $bluePrint->alter($tableName);
            $static->verbose = $alterQuery;
            if (!self::$debug) DB::getInstance()->query($alterQuery);
        } catch (PDOException $e) {
            // debuging
            debug($e->getMessage(), $e->getTrace());
        }

        return $static;
    }

    /**
     * Drop table
     * just type table name and drop it.
     *
     * @param string $tableName
     * @return void
     */
    public static function drop(string $tableName)
    {
        DB::getInstance()->query('DROP TABLE `' . $tableName . '`');
    }

    /**
     * Drop column table
     * just type table and column name then drop it.
     *
     * @param string $tableName
     * @return void
     */
    public static function dropColumn(string $tableName, string $columnName)
    {
        DB::getInstance()->query('ALTER TABLE `' . $tableName . '` DROP COLUMN `' . $columnName . '`');
    }

    /**
     * Emptying table
     *
     * @param string $tableName
     * @return void
     */
    public static function truncate(string $tableName)
    {
        DB::getInstance()->query('TRUNCATE TABLE `' . $tableName . '`');
    }

    /**
     * CHeck if table is exists or not
     *
     * @param string $tableName
     * @return boolean
     */
    public static function hasTable(string $tableName):bool
    {
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT * FROM `TABLES` WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = :table_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database'], 'table_name' => $tableName]);

        return (bool) $tableState->rowCount();
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
        // set database instance
        self::getInstance();

        // Create table state
        $tableState = self::$connection->prepare('SELECT * FROM `COLUMNS` WHERE `TABLE_SCHEMA` = :database_name AND `TABLE_NAME` = :table_name AND `COLUMN_NAME` = :column_name');
        $tableState->execute(['database_name' => self::$connectionProfile['database'], 'table_name' => $tableName, ':column_name' => $columnName]);

        return (bool) $tableState->rowCount();
    }
}