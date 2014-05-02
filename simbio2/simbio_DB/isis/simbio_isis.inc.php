<?php
/**
 * simbio_isis class
 * Simbio ISIS connection object class
 * This class still using PHP4 class style for backward compability
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

require 'simbio_isis_result.inc.php';

class simbio_isis extends simbio
{
    var $db_name = '';
    var $res_conn = 0;
    var $isis_opt = '-v error -format aligned -encoding ISO8859_1';

    /**
     * Simbio ISIS Class Constructor
     *
     * @param   string  $str_dbname
     */
    function simbio_isis($str_dbname)
    {
        $this->db_name = $str_dbname;
        // execute connection
        $this->connect();
    }


    /**
     * Method to invoke connection to ISIS database
     *
     * @return  void
     */
    function connect()
    {
        $open = @isis_open($this->db_name, $this->isis_opt);
        if ($open) {
            $this->res_conn = $open;
        } else {
            $this->error = "Can't make connection to ".strtoupper($this->isis_db)." ISIS database";
            parent::showError(true);
        }
    }


    /**
     * Method to create/send query to RDBMS
     *
     * @param   string  $str_query
     * @param   string  $str_query_type
     * @return  object
     */
    function query($str_query = '', $str_query_type = 'query')
    {
        if (empty($str_query)) {
            $this->error = 'Cant send ISIS query because query was empty';
            parent::showError(true);
        } else {
            $_result = new simbio_isis_result($str_query, $str_query_type, $this->res_conn);
            // return the result object
            if ($this->error) {
                return false;
            } else {
                return $_result;
            }
        }
    }


    /**
     * Method to close RDBMS connection
     *
     * @return  void
     */
    public function close()
    {
        @isis_close($this->res_conn);
    }
}
?>
