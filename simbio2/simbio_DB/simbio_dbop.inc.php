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
     * Helper function needed for dynamic call_user_func_array with bind_param
     */
    private function refValues($arr) {
        if (strnatcmp(phpversion(),'5.3') >= 0) {
            $refs = array();
            foreach($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    /**
     * Helper function to determine the bind type for mysqli::bind_param
     *
     * @param   mixed   $value
     * @return  string  Type string ('s', 'i', 'd')
     */
    private function getBindType($value) {
        if (is_int($value) || is_float($value)) {
            return 's';
        } else {
            return 's';
        }
    }

    /**
     * Method to insert a record using Prepared Statements
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

        $_columns = array();
        $_placeholders = array();
        $_values = array();
        $_types = '';

        foreach ($array_data as $column => $value) {
            $_columns[] = "`$column`";
            $_placeholders[] = '?';
            $_values[] = $value;
            $_types .= $this->getBindType($value);
        }

        $_str_columns = implode(', ', $_columns);
        $_str_placeholders = implode(', ', $_placeholders);

        try {
            $this->sql_string = "INSERT INTO `$str_table` ($_str_columns) VALUES ($_str_placeholders)";
            $stmt = $this->obj_db->prepare($this->sql_string);
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $this->obj_db->errno . ") " . $this->obj_db->error);
            }

            if (count($_values) > 0) {
                $bind_params = array_merge(array($_types), $_values);
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bind_params));
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            $this->insert_id = $this->obj_db->insert_id;
            $this->affected_rows = $stmt->affected_rows;
            $stmt->close();

        } catch (Exception $e) {
            // if an error occur
            $this->error = isDev() ? $e->getMessage() . ' : ' . $this->sql_string : '';
            return false; 
        }

        return true;
    }


    /**
     * Method to update table records based on $str_criteria using Prepared Statements
     *
     * @param   string  $str_table
     * @param   array   $array_update
     * @param   string  $str_criteria
     * @param   array   $array_criteria_values
     * @return  boolean
     */
    public function update($str_table, $array_update, $str_criteria, $array_criteria_values = [])
    {
        if (!is_array($array_update) OR count($array_update) == 0) {
            return false;
        }

        $_set_clauses = array();
        $_values = array();
        $_types = '';

        foreach ($array_update as $column => $new_value) {
            $_set_clauses[] = "`$column` = ?";
            $_values[] = $new_value;
            $_types .= $this->getBindType($new_value);
        }

        foreach ($array_criteria_values as $c_value) {
            $_values[] = $c_value;
            $_types .= $this->getBindType($c_value);
        }

        $_set = implode(', ', $_set_clauses);

        try {
            // update query
            $this->sql_string = "UPDATE `$str_table` SET $_set WHERE $str_criteria";
            $stmt = $this->obj_db->prepare($this->sql_string);
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $this->obj_db->errno . ") " . $this->obj_db->error);
            }

            if (count($_values) > 0) {
                $bind_params = array_merge(array($_types), $_values);
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bind_params));
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            // number of affected rows
            $this->affected_rows = $stmt->affected_rows;
            $stmt->close();

        } catch (Exception $e) {
             // if an error occur
             $this->error = isDev() ? $e->getMessage() . ' : ' . $this->sql_string : ''; 
             return false; 
        }

        return true;
    }


    /**
     * Method to delete records based on $str_criteria using Prepared Statements
     *
     * @param   string  $str_table
     * @param   string  $str_criteria
     * @param   array   $array_criteria_values
     * @return  boolean
     */
    public function delete($str_table, $str_criteria, $array_criteria_values = [])
    {
        $_values = array();
        $_types = '';

        foreach ($array_criteria_values as $c_value) {
            $_values[] = $c_value;
            $_types .= $this->getBindType($c_value);
        }

        try {
            // the delete query
            $this->sql_string = "DELETE FROM `$str_table` WHERE $str_criteria";

            $stmt = $this->obj_db->prepare($this->sql_string);
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $this->obj_db->errno . ") " . $this->obj_db->error);
            }

            if (count($_values) > 0) {
                $bind_params = array_merge(array($_types), $_values);
                call_user_func_array(array($stmt, 'bind_param'), $this->refValues($bind_params));
            }

            if (!$stmt->execute()) {
                throw new Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            // affected rows
            $this->affected_rows = $stmt->affected_rows;
            $stmt->close();

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