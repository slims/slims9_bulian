<?php
/**
 * simbio_isis_result class
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

class simbio_isis_result extends simbio
{
    # private properties
    var $res_result = false;
    var $query_string = '';
    var $query_type = '';
    var $num_rows = 0;


    /**
     * Class Constructor
     *
     * @param   string      $str_query
     * @param   string      $str_query_type
     * @param   resource    $res_conn
     */
    function simbio_isis_result($str_query, $str_query_type, $res_conn)
    {
        $this->query_string = $str_query;
        $this->query_type = $str_query_type;
        $this->sendQuery($res_conn);
    }


    /**
     * Method to send ISIS DB query
     *
     * @param   resource    $res_conn
     * @return  void
     */
    function sendQuery($res_conn)
    {
        if (($this->query_type == 'search') OR ($this->keywords == '$')) {
            $this->res_result = @isis_search($this->query_string, $res_conn);
        } else {
            $this->res_result = @isis_query($this->query_string, $res_conn);
        }

        if ($this->res_result) {
            // set the num rows property
            $this->num_rows = @isis_num_rows($this->res_result);
            return true;
        } else {
            return false;
        }
    }


    /**
     * Method to fetch record
     *
     * @return  array
     */
    function fetch_assoc()
    {
        if (!$this->res_result) {
            $this->error = "No Resultset can be fetched from query";
            return null;
        } else {
            $_rec = @isis_fetch_flat_array($this->res_result);
        }

        return $_rec;
    }


    /**
     * Method to fetch record
     *
     * @return  array
     */
    public function fetch_row()
    {
        return $this->fetch_assoc();
    }


    /**
     * Method to free resultset memory
     *
     * @return  void
     */
    function free_result()
    {
        if ($this->res_result) {
            @isis_free_result($this->res_result);
        }
    }
}
?>
