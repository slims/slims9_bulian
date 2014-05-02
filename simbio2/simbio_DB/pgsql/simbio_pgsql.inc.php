<?php
/**
 * simbio_pgsql class
 * Simbio PostgreSQL connection object class
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

require_once 'simbio_pgsql_result.inc.php';

class simbio_pgsql extends simbio
{
    private $db_host = '';
    private $db_port = '';
    private $db_name = '';
    private $db_username = '';
    private $db_passwd = '';
    private $res_conn = 0;
    public $affected_rows = 0;
    public $insert_id = 0;
    public $errno = 0;

    /**
     * Simbio PostgreSQL Class Constructor
     *
     * @param   string  $str_host
     * @param   string  $str_username
     * @param   string  $str_passwd
     * @param   string  $str_dbname
     * @param   integer $int_port
     */
    public function __construct($str_host, $str_username, $str_passwd, $str_dbname, $int_port = 5432)
    {
        $this->db_host = $str_host;
        $this->db_port = $int_port;
        $this->db_name = $str_dbname;
        $this->db_username = $str_username;
        $this->db_passwd = $str_passwd;
        // execute connection
        $this->connect();
    }


    /**
     * Method to invoke connection to RDBMS
     *
     * @return  void
     */
    private function connect()
    {
        $this->res_conn = @pg_connect("host=".$this->db_host." dbname=".$this->db_name.
            " user=".$this->db_username.
            " password=".$this->db_passwd.
            " port=".$this->db_port);
        if (!$this->res_conn) {
            $this->error = "Database fail to connected. \n".pg_last_error($this->res_conn);
            parent::showError(true);
        }
    }


    /**
     * Method to create/send query to RDBMS
     *
     * @param   string  $str_query
     * @return  object
     */
    public function query($str_query = '')
    {
        if (empty($str_query)) {
            $this->error = 'Cant send query because query was empty';
            parent::showError(true);
        } else {
            $_result = new simbio_pgsql_result($str_query, $this->res_conn);
            // get any properties from result object
            $this->affected_rows = $_result->affected_rows;
            $this->errno = $_result->errno;
            $this->error = $_result->error;
            $this->insert_id = $_result->insert_id;
            // return the result object
            if ($this->error) {
                return false;
            } else {
                return $_result;
            }
        }
    }


    /**
     * Method to escape SQL string
     *
     * @param   string  $str_data
     * @return  string
     */
    public function escape_string($str_data)
    {
        return pg_escape_string($this->res_conn, $str_data);
    }


    /**
     * Method to close RDBMS connection
     *
     * @return  void
     */
    public function close()
    {
        pg_close($this->res_conn);
    }
}
?>
