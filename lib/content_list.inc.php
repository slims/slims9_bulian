<?php
/**
 * content_list class
 * Class for generating a list of records
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

class content_list
{
    protected $list_template;
    protected $paging_enable = false;
    protected $criteria;
    protected $order;
    public $num_rows = 0;


    /**
     * Method to set list template
     *
     * @param   string  $str_template
     * @return  void
     */
    public function setListTemplate($str_template)
    {
        $this->list_template = $str_template;
    }


    /**
     * Method to set SQL criteria of list
     *
     * @param   string  $str_criteria
     * @return  void
     */
    public function setSQLcriteria($str_criteria)
    {
        $this->criteria = $str_criteria;
    }


    /**
     * Method to set SQL field ordering of list
     *
     * @param   string  $str_order
     * @return  void
     */
    public function setSQLorder($str_order)
    {
        $this->order = $str_order;
    }


    /**
     * Method to enable or disable list paging
     *
     * @param   boolean $bool_enable
     * @return  void
     */
    public function pagingEnable($bool_enable)
    {
        $this->paging_enable = $bool_enable;
    }


    /**
     * Method to parse list template
     *
     * @param   array   $array_associative_data
     * @return  string
     */
    protected function parseListTemplate($array_associative_data)
    {
        if (!$this->list_template) {
            echo 'There is no template for content list yet';
            return;
        }

        // get the template markers from regular expression
        preg_match_all("/\{[a-z_]+?\}/", $this->list_template, $matches);
        $_markers = $matches[0];

        // result buffer var
        $_result = $this->list_template;
        foreach ($_markers as $_each_marker) {
            $_index = str_replace(array('{','}'), '', $_each_marker);
            if (isset($array_associative_data[$_index]) AND $array_associative_data[$_index]) {
                $_result = str_replace($_each_marker, $array_associative_data[$_index], $_result);
            } else {
                $_result = str_replace($_each_marker, '', $_result);
            }
        }

        return $_result;
    }
}
