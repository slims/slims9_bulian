<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-05-30 11:42:38
 * @modify date 2022-09-28 17:47:03
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Table\Grammar;

class Mysql
{
    /**
     * Column type map
     *
     * @var array
     */
    private static array $types = [
        // Text
        'string' => 'varchar',
        'fixstring' => 'char',
        'text' => 'text',
        'json' => 'json',
        'longtext' => 'longtext',
        // number
        'number' => 'int',
        'smallnumber' => 'smallint',
        'tinynumber' => 'tinyint',
        'bignumber' => 'bigint',
        'decimal' => 'decimal',
        'float' => 'float',
        'double' => 'double',
        // Date
        'date' => 'date',
        'datetime' => 'datetime'
    ];
    
    /**
     * Compiler method to generate 
     * 'create' query
     *
     * @param string $tableName
     * @param array $attributes
     * @param boolean $ifNotExists
     * @return void
     */
    public static function create(string $tableName, array $attributes, bool $ifNotExists = false)
    {
        extract($attributes);

        // set state
        $state = $ifNotExists ? 'createItNotExists' : 'create';
        $columns = implode(',', $columns);
        $options = count($options) ? ', ' . implode(',', $options) : '';
        
        return self::compile($state, $tableName, $columns, $options, $rdbmsOpt);
    }

    /**
     * Compiler method to generate 
     * 'alter' query
     *
     * @param string $tableName
     * @param array $attributes
     * @param boolean $ifNotExists
     * @return void
     */
    public static function alter(string $tableName, array $attributes)
    {
        extract($attributes);

        // set state
        $columns = count($columns) ? implode(',', $columns) : '';
        $options = count($options) ? ', ' . implode(',', $options) : '';

        return self::compile('alter', $tableName, $columns, $options, $rdbmsOpt);
    }

    /**
     * Main method to generate 
     * query attribute like state, column,
     * engine etc.
     *
     * @param string $state
     * @param string $tableName
     * @param string $columns
     * @param string $options
     * @param array $rdbmsOpt
     * @return void
     */
    private static function compile(string $state, string $tableName, string $columns, string $options, array $rdbmsOpt)
    {
        $engineOpt = ';';

        if (count($rdbmsOpt) > 0)
        {
            $optionMap = ['engine' => 'ENGINE=', 'charset' => 'CHARSET=', 'collation' => 'COLLATE='];
            $engineOpt = [];
            foreach ($rdbmsOpt as $key => $value) {
                if (!empty($rdbmsOpt[$key]))
                {
                    $engineOpt[] = $optionMap[$key] . $value;
                }
            }

            $engineOpt = implode(' ', $engineOpt);
        }

        switch ($state) {
            case 'create':
                $SQL = <<<SQL
                    CREATE TABLE `{$tableName}` (
                        {$columns}
                        {$options}
                    ) {$engineOpt}
                SQL;
                break;

            case 'createItNotExists':
                $SQL = <<<SQL
                    CREATE TABLE IF NOT EXISTS `{$tableName}` (
                        {$columns}
                        {$options}
                    ) {$engineOpt}
                SQL;
                break;
            
            case 'alter':
                if (empty($columns)) $options = trim(substr_replace($options, '', 0,1));
                $SQL = <<<SQL
                    ALTER TABLE `{$tableName}`
                        {$columns}
                        {$options}
                    {$engineOpt}
                SQL;
                break;
                
            default:
                $SQL = '-- no state available';
                break;
        }

        return $SQL;
    }

    /**
     * Enum
     *
     * @param string $columname
     * @param array $value
     * @return void
     */
    public static function enum(string $columname, array $value)
    {
        return '`' . $columname . '` enum(\'' . implode("','", $value) . '\')';
    }

    /**
     * Getter for column type
     *
     * @param string $columnType
     * @return void
     */
    public static function getType(string $columnType)
    {
        return self::$types[$columnType]??null;
    }
}