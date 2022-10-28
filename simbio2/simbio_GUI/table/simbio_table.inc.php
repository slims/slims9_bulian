<?php
/**
 * simbio_table class
 * Class for creating HTML table
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

class simbio_table_field
{
    public $attr;
    public $value;

    /**
     * Class Constructor
     *
     * @param   string  $str_attr
     */
    public function __construct($str_attr = '')
    {
        $this->attr = $str_attr;
    }
}


class simbio_table_row
{
    public $attr;
    public $fields = array();
    public $all_cell_attr;

    /**
     * Class Constructor
     *
     * @param   string  $str_attr
     */
    public function __construct($array_field_content, $str_attr = '')
    {
        $this->attr = $str_attr;
        $this->addFields($array_field_content);
    }


    /**
     * Method to create simbio_table_field array from array
     *
     * @param   array   $array_field_content
     * @return  array
     */
    public function addFields($array_field_content)
    {
        foreach ($array_field_content as $idx => $fld_content) {
            $_field_obj = new simbio_table_field();
            $_field_obj->value = $fld_content;
            $this->fields[$idx] = $_field_obj;
        }
    }
}


class simbio_table
{
    public $table_ID = 'datatable';
    public $table_attr = '';
    public $table_header_attr = '';
    public $table_content_attr = '';
    public $table_row = array();
    public $row_attr = array();
    public $cell_attr = array();
    public $highlight_row = false;
    private $have_header = false;

    /**
     * Class Constructor
     *
     * @param   string  $str_table_attr
     */
    public function __construct($str_table_attr = '')
    {
        $this->table_attr = $str_table_attr;
    }


    /**
     * Method to set table headers
     *
     * @param   array   $array_column_value
     * @return  void
     */
    public function setHeader($array_column_content)
    {
        if (!is_array($array_column_content)) {
            // do nothing
            return;
        } else {
            $this->have_header = true;
            $this->table_row[0] = new simbio_table_row($array_column_content);
        }
    }


    /**
     * Method to append row/record to table
     *
     * @param   array   $array_column_content
     * @return  void
     */
    public function appendTableRow($array_column_content)
    {
        // row content must be an array
        if (!is_array($array_column_content)) {
            // do nothing
            return;
        } else {
            // records row must start with index 1 not 0
            // index 0 is reserved for table header row
            $_row_cnt = count($this->table_row);
            // create instance of simbio_table_row
            $_row_obj = new simbio_table_row($array_column_content);
            if ($_row_cnt < 1) {
                $this->table_row[1] = $_row_obj;
            } else {
                // if header row exists
                if (isset($this->table_row[0])) {
                    $this->table_row[$_row_cnt] = $_row_obj;
                } else {
                    $this->table_row[$_row_cnt+1] = $_row_obj;
                }
            }
        }
    }


    /**
     * Method to set content of specific column
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_content
     * @return  void
     */
    public function setColumnContent($int_row, $int_column, $str_column_content)
    {
        if (!isset($this->table_row[$int_row]->fields[$int_column])) {
           // do nothing
           return;
        } else {
           $this->table_row[$int_row]->fields[$int_column]->value = $str_column_content;
        }
    }



    /**
     * Method to get content of specific column
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_content
     * @return  mixed
     */
    public function getColumnContent($int_row, $int_column, $str_column_content)
    {
        if (isset($this->table_row[$int_row]->fields[$int_column])) {
            return $this->table_row[$int_row]->fields[$int_column]->value;
        } else {
            return null;
        }
    }


    /**
     * Method to set specific column attribute
     *
     * @param   integer $int_row
     * @param   integer $int_column
     * @param   string  $str_column_attr
     * @return  void
     */
    public function setCellAttr($int_row = 0, $int_column = null, $str_column_attr = null)
    {
        if (is_null($int_column)) {
            $this->row_attr[$int_row] = $str_column_attr;
        } else {
            $this->cell_attr[$int_row][$int_column] = $str_column_attr;
        }
    }


    /**
     * Method to print out table
     *
     * @return string
     */
    public function printTable()
    {
        $_buffer = '<table '.$this->table_attr.'>'."\n";

        // check if the array have a records
        if (count($this->table_row) < 1) {
            $_buffer .= '<tr><td align="center" class="s-table__no-data">'.__('No Data').'</td></tr>';
        } else {
            // set header style if exist
            $this->setCellAttr(0, null, $this->table_header_attr);

            // records
            $_record_row = 0;
            foreach ($this->table_row as $_row_idx => $_row) {
                if (!$_row instanceof simbio_table_row) {
                    continue;
                }
                // print out the row objects
                $_buffer .= '<tr '.( isset($this->row_attr[$_row_idx])?$this->row_attr[$_row_idx]:'' ).'>';
                foreach ($_row->fields as $_field_idx => $_field) {
                    if (isset($this->cell_attr[$_row_idx][$_field_idx])) {
                        $_field->attr = $this->cell_attr[$_row_idx][$_field_idx];
                    }
                    $_buffer .= '<td '.$_field->attr.'>'.$_field->value.'</td>';
                }
                $_buffer .= '</tr>'."\n";
                $_record_row++;
            }
        }

        $_buffer .= '</table>'."\n";
        return $_buffer;
    }
}
?>
