<?php
/**
 * simbio class
 * Simbio Main Abstract class
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

abstract class simbio
{
    public $error = '';
    private $version = '2.0';

    /**
     * Method to show an error
     *
     * @param   boolean $bool_die
     * @return  void
     */
    public function showError($bool_die = false)
    {
        echo '<div style="padding: 5px; border: 1px dotted #FF0000; color: #FF0000;">';
        echo 'ERROR : '.nl2br($this->error).'</div>'."\n";
        if ($bool_die) {
            die();
        }
    }


    /**
     * Static method to colorized SQL string
     *
     * @param   string  $sql_string
     * @return  string
     */
    public static function colorSQLstring($sql_string = '')
    {
        // list of mysql reserved words
        $reservedwords = array(
            "\bDATABASE\b",
            "\bTABLE\b",
            "\bAND\b",
            "\bOR\b",
            "\bSELECT\b",
            "\bINSERT\b",
            "\bUPDATE\b",
            "\bDELETE\b",
            "\bALTER\b",
            "\bFROM\b",
            "\bWHERE\b",
            "\bLIKE\b",
            "\bORDER BY\b",
            "\bLIMIT\b",
            "\bUSE\b",
            "\bDESCRIBE\b",
            "\bJOIN\b",
            "\bLEFT\b",
            "\bRIGHT\b",
            "\bINNER\b",
            "\b=\b",
            "\b!=\b",
            "\bON\b",
            "\bIN\b",
            "\bAS\b",
            "\bNULL\b",
            "\bNOT\b",
            "\bIS\b",
            "\bINTO\b");

        // colorized the sql string
        $matches_str = array();
        preg_match_all("/'[^']*'/i", $sql_string, $matches_str, PREG_SET_ORDER);
        if ($matches_str) {
            foreach ($matches_str as $sql_str) {
                $sql_string = preg_replace("/".$sql_str[0]."/i", '<strong style="color: green;">'.$sql_str[0].'</strong>', $sql_string);
            }
        }

        // colorized brackets
        $sql_string = str_replace(array('(',')'), array('<strong style="color: red;">(</b>', '<b style="color: red;">)</strong>'), $sql_string);

        // colorized the SQL reserved words
        foreach ($reservedwords as $words) {
            $sql_string = preg_replace("/$words/i", '<strong style="color: navy;">'.$words.'</strong>', $sql_string);
        }

        // remove regex special chars
        $sql_string = str_replace(array('\b'), '', $sql_string);
        return $sql_string;
    }
}
?>
