<?php
/**
 * simbio_pgsql_result class
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/**
 * A Helper class to contain result fields information
 */
class simbio_pgsql_field_info
{
    public $name;
    public $type;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        // just do nothing
    }
}


class simbio_pgsql_result extends simbio
{
    /**
     * Private properties
     */
    private $res_result = false;
    private $sql_string = '';

    /**
     * Public properties
     */
    public $num_rows = 0;
    public $field_count = 0;
    public $affected_rows = 0;
    public $insert_id = 0;
    public $errno = 0;

    /**
     * Class Constructor
     *
     * @param   string      $str_query
     * @param   resource    $res_conn
     */
    public function __construct($str_query, $res_conn)
    {
        $this->sql_string = trim($str_query);
        $this->sendQuery($res_conn);
    }


    /**
     * Method to send SQL query
     *
     * @param   resource    $res_conn
     * @return  void
     */
    private function sendQuery($res_conn)
    {
        // checking query type
        // if the query return recordset or not
        if (preg_match("/^(SELECT)\s/i", $this->sql_string)) {
            $this->res_result = @pg_query($res_conn, $this->sql_string);
            // error checking
            if (!$this->res_result) {
                $this->errno = 1;
                $this->error = "Query failed to executed. Please check your query again. \n".pg_result_error($this->res_result);
            } else {
                // count number of rows
                $this->num_rows = @pg_num_rows($this->res_result);
            }
        } else {
            $_query = @pg_query($res_conn, $this->sql_string);
            // error checking
            if (!$_query) {
                $this->errno = 1;
                $this->error = "Query failed to executed. Please check your query again. \n".pg_last_error($res_conn);
            } else {
                // get number of affected row
                $this->affected_rows = @pg_affected_rows($_query);
                // get last OID if it is insert operation
                if (preg_match("/^(INSERT)\s/i", $this->sql_string)) {
                    $this->insert_id = @pg_last_oid($_query);
                }
            }
            // nullify query
            $_query = null;
        }
    }


    /**
     * Method to fetch record in associative  array
     *
     * @return  array
     */
    public function fetch_assoc()
    {
        return @pg_fetch_assoc($this->res_result);
    }


    /**
     * Method to fetch record in numeric array indexes
     *
     * @return  array
     */
    public function fetch_row()
    {
        return @pg_fetch_row($this->res_result);
    }


    /**
     * Method to fetch fields information of resultset
     *
     * @return  array
     */
    public function fetch_fields()
    {
        $_fields_info = array();
        $_f = 0;
        $_field_num = pg_num_fields($this->res_result);
        while ($_f < $_field_num) {
            $field_obj = new simbio_pgsql_field_info();
            $field_obj->name = pg_field_name($this->res_result, $_f);
            $field_obj->type = pg_field_type($this->res_result, $_f);
            $_fields_info[] = $field_obj;
            $_f++;
        }
        return $_fields_info;
    }


    /**
     * Method to free resultset memory
     *
     * @return  void
     */
    public function free_result()
    {
        if ($this->res_result) {
            @pg_free_result($this->res_result);
        }
    }
}
?>
