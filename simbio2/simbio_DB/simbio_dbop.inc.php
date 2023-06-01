<?php
/**
 * simbio_dbop class
 * SQL insert, update and delete operation wrapper class
 *
 * Copyright (C) 2007  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

class simbio_dbop extends simbio
{
    private $obj_db = false;
    private $sql_string = '';
    public $insert_id = 0;
    public $affected_rows = 0;
    public $error = '';

    /**
     * A Class Constructor
     * Provide an argument with mysqli or simbio_mysql RDBMS connection object
     *
     * @param   object  $obj_db
     */
    public function __construct($obj_db)
    {
        $this->obj_db = $obj_db;
    }


    /**
     * Method to insert a record
     *
     * @param   string  $str_table
     * @param   array   $array_data
     * @return  boolean
     */
    public function insert($str_table, $array_data)
    {
        if (!is_array($array_data) OR count($array_data) == 0) {
            return false;
        }

        // parse the array first
        $_str_columns = '';
        $_str_value = '';
        foreach ($array_data as $column => $value) {
            // concatenating column name
            $_str_columns .= ", `$column`";
            // concatenating value
            if ($value === 'NULL' OR $value === null) {
                // if the value is NULL or string NULL
                $_str_value .= ', NULL';
            } else if (is_string($value)) {
                if (preg_match("/^literal{.+}/i", $value)) {
                    $value = preg_replace("/literal{|}/i", '', $value);
                    $_str_value .= ", $value";
                } else {
                    // concatenating column value
                    $_str_value .= ", '$value'";
                }
            } else {
                // if the value is an integer or unknown data type
                $_str_value .= ", $value";
            }
        }

        // strip the first comma  of string
        $_str_columns = substr_replace($_str_columns, '', 0, 1);
        $_str_value = substr_replace($_str_value, '', 0, 1);

        try {
            // the insert query
            $this->sql_string = "INSERT INTO `$str_table` ($_str_columns) "
                ."VALUES ($_str_value)";
            $_insert = $this->obj_db->query($this->sql_string);
            
            // get last inserted record ID
            $this->insert_id = $this->obj_db->insert_id;
            $this->affected_rows = $this->obj_db->affected_rows;
        } catch (Exception $e) {
            // if an error occur
            $this->error = isDev() ? $e->getMessage() . ' : ' . $this->sql_string : ''; 
            return false; 
        }

        return true;
    }


    /**
     * Method to update table records based on $str_criteria
     *
     * @param   string  $str_table
     * @param   array   $array_update
     * @param   string  $str_criteria
     * @return  boolean
     */
    public function update($str_table, $array_update, $str_criteria)
    {
        // check if the first argumen is an array
        if (!is_array($array_update)) {
            return false;
        } else {
            $_set = '';
            // concat the update query string
            foreach ($array_update as $column => $new_value) {
                if ($new_value == '') {
                    $_set .= ", `$column` = ''";
                } else if ($new_value === 'NULL' OR $new_value == null) {
                    $_set .= ", `$column` = NULL";
                } else if (is_string($new_value)) {
                    if (preg_match("/^literal{.+}/i", $new_value)) {
                        $new_value = preg_replace("/literal{|}/i", '', $new_value);
                        $_set .= ", `$column` = $new_value";
                    } else {
                        $_set .= ", `$column` = '$new_value'";
                    }
                } else {
                    $_set .= ", `$column` = $new_value";
                }
            }

            // strip the first comma
            $_set = substr_replace($_set, '', 0, 1);
        }

        // update query
        try {
            $this->sql_string = "UPDATE $str_table SET $_set WHERE $str_criteria";
            $_update = $this->obj_db->query($this->sql_string);
            // number of affected rows
            $this->affected_rows = $this->obj_db->affected_rows;
        } catch (Exception $e) {
             // if an error occur
             $this->error = isDev() ? $e->getMessage() . ' : ' . $this->sql_string : ''; 
             return false; 
        }

        return true;
    }


    /**
     * Method to delete records based on $str_criteria
     *
     * @param   string  $str_table
     * @param   string  $str_criteria
     * @return  boolean
     */
    public function delete($str_table, $str_criteria)
    {
        try {
            // the delete query
            $this->sql_string = "DELETE FROM $str_table WHERE $str_criteria";
            $_delete = $this->obj_db->query($this->sql_string);
            // affected rows
            $this->affected_rows = $this->obj_db->affected_rows;
        } catch (Exception $e) {
            // if an error occur
            $this->error = isDev() ? $e->getMessage() . ' : ' . $this->sql_string : ''; 
            return false; 
        }

        return true;
    }

    /**
     * Method to get last sql string
     *
     * @return string
     */
    public function getSQL() {
        return $this->sql_string;
    }

}

