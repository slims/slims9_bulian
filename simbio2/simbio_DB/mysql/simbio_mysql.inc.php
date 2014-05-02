<?php
/**
 * simbio_mysql class
 * Simbio MySQL connection object class
 * Simbio MySQL try to emulates mysqli object behaviour
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

require 'simbio_mysql_result.inc.php';

class simbio_mysql extends simbio
{
    private $db_host = '127.0.0.1';
    private $db_port = 3306;
    private $db_socket = '';
    private $db_name = '';
    private $db_username = '';
    private $db_passwd = '';
    private $res_conn = false;
    public $affected_rows = 0;
    public $insert_id = 0;
    public $errno = false;

    /**
     * Simbio MySQL Class Constructor
     *
     * @param   string  $str_host
     * @param   string  $str_username
     * @param   string  $str_passwd
     * @param   string  $str_dbname
     * @param   integer $int_port
     * @param   string  $str_socket
     */
    public function __construct($str_host, $str_username, $str_passwd, $str_dbname, $int_port = 3306, $str_socket = '')
    {
        $this->db_host = $str_host;
        $this->db_port = $int_port;
        $this->db_socket = $str_socket;
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
        if ($this->db_socket) {
            $this->res_conn = @mysql_connect($this->db_host.":".$this->db_socket, $this->db_username, $this->db_passwd);
        } else {
            $this->res_conn = @mysql_connect($this->db_host.":".$this->db_port, $this->db_username, $this->db_passwd);
        }
        // check the connection status
        if (!$this->res_conn) {
            $this->error = 'Error Connecting to Database. Please check your configuration';
            parent::showError(true);
        } else {
            // select the database
            $db = @mysql_select_db($this->db_name, $this->res_conn);
            if (!$db) {
                $this->error = 'Error Opening Database';
                parent::showError(true);
            }
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
            $this->error = "Error on simbio_mysql::query() method : query empty";
            parent::showError(true);
        } else {
            // create simbio_mysql_result object
            $result = new simbio_mysql_result($str_query, $this->res_conn);
            // get any properties from result object
            $this->affected_rows = $result->affected_rows;
            $this->errno = $result->errno;
            $this->error = $result->error;
            $this->insert_id = $result->insert_id;
            // return the result object
            if ($this->error) {
                return false;
            } else {
                return $result;
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
        return mysql_real_escape_string($str_data, $this->res_conn);
    }


    /**
     * Method to close RDBMS connection
     *
     * @return  void
     */
    public function close()
    {
        mysql_close($this->res_conn);
    }
}
?>
