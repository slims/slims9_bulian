<?php
/**
 * simbio_paging_ajax
 * Paging Generator class
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

class simbio_paging
{
    /**
     * Static Method to print out the paging list
     *
     * @param   integer $int_all_recs_num
     * @param   integer $int_recs_each_page
     * @param   integer $int_pages_each_set
     * @param   string  $str_fragment
     * @param   string  $str_target_frame
     * @return  string
     */
    public static function paging($int_all_recs_num, $int_recs_each_page, $int_pages_each_set = 10, $str_fragment = '', $str_target_frame = '_self')
    {
        // check for wrong arguments
        if ($int_recs_each_page > $int_all_recs_num) {
            return;
        }

        // total number of pages
        $_num_page_total = ceil($int_all_recs_num/$int_recs_each_page);

        if ($_num_page_total < 2) {
            return;
        }

        // total number of pager set
        $_pager_set_num = ceil($_num_page_total/$int_pages_each_set);

        // check the current page number
        if (isset($_GET['page']) AND $_GET['page'] > 1) {
            $_page = (integer)$_GET['page'];
        } else {$_page = 1;}

        // check the query string
        if (isset($_SERVER['QUERY_STRING']) AND !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $arr_query_var);
            // renew csrf token
            if (isset($arr_query_var['csrf_token'])) $arr_query_var['csrf_token'] = $_SESSION['csrf_token']??'';
            // rebuild query str without "page" var
            $_query_str_page = '';
            foreach ($arr_query_var as $varname => $varvalue) {
                if (is_string($varvalue)) {
                    $varvalue = urlencode($varvalue);
                    if ($varname != 'page') {
                        $_query_str_page .= simbio_security::xssFree($varname).'='.simbio_security::xssFree($varvalue).'&';
                    }
                } else if (is_array($varvalue)) {
                    $_query_str_page .= http_build_query(xssFree($varvalue));
                }
            }
            // append "page" var at the end
            $_query_str_page .= 'page=';
            // create full URL
            $_current_page = $_SERVER['PHP_SELF'].'?'.$_query_str_page;
        } else {
            $_current_page = $_SERVER['PHP_SELF'].'?page=';
        }

        // target frame
        $str_target_frame = 'target="'.$str_target_frame.'"';

        // init the return string
        $_buffer = '<span class="pagingList">';
        $_stopper = 1;

        // count the offset of paging
        if (($_page > 5) AND ($_page%5 == 1)) {
            $_lowest = $_page-5;
            if ($_page == $_lowest) {
                $_pager_offset = $_lowest;
            } else {
                $_pager_offset = $_page;
            }
        } else if (($_page > 5) AND (($_page*2)%5 == 0)) {
            $_lowest = $_page-5;
            $_pager_offset = $_lowest+1;
        } else if (($_page > 5) AND ($_page%5 > 1)) {
            $_rest = $_page%5;
            $_pager_offset = $_page-($_rest-1);
        } else {
            $_pager_offset = 1;
        }

        // Previous page link
				$_first = __('First Page');

				$_prev = __('Previous');

        if ($_page > 1) {
            $_buffer .= '<a href="'.$_current_page.(1).$str_fragment.'" '.$str_target_frame.' class="first_link">'.$_first.'</a>'."\n";
            $_buffer .= '<a href="'.$_current_page.($_page-1).$str_fragment.'" '.$str_target_frame.' class="prev_link">'.$_prev.'</a>'."\n";
        }

        for ($p = $_pager_offset; ($p <= $_num_page_total) AND ($_stopper < $int_pages_each_set+1); $p++) {
            if ($p == $_page) {
                $_buffer .= '<b>'.$p.'</b>'."\n";
            } else {
                $_buffer .= '<a href="'.$_current_page.$p.$str_fragment.'" '.$str_target_frame.'>'.$p.'</a>'."\n";
            }

            $_stopper++;
        }

        // Next page link
				$_next = __('Next');

        if (($_pager_offset != $_num_page_total-4) AND ($_page != $_num_page_total)) {
            $_buffer .= '<a href="'.$_current_page.($_page+1).$str_fragment.'" '.$str_target_frame.' class="next_link">'.$_next.'</a>'."\n";
        }

        // Last page link
				$_last = __('Last Page');

        if ($_page < $_num_page_total) {
            $_buffer .= '<a href="'.$_current_page.($_num_page_total).$str_fragment.'" '.$str_target_frame.' class="last_link">'.$_last.'</a>'."\n";
        }

        $_buffer .= '</span>';

        return $_buffer;
    }
}
