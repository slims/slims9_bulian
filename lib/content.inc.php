<?php
/**
 * Content class
 * Class for showing content from database
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

class Content
{
    public $strip_html = false;
    public $allowed_tags = null;

    public static function createSummary($text, $max_chars = 300)
    {
        $summary = strip_tags($text);
        // $summary = substr($summary, 0, $max_chars);

        // making sure substr finishes on a word
        if (preg_match('/^.{1,'.$max_chars.'}\b/s', $summary, $match)) {
            $summary= $match[0];
        }
        
        return $summary;
    }
    
    public function getContents($obj_db, $max_each_page = 10, &$total = 0, $search_query = '')
    {
        global $sysconf;
        $contents = array();
        $page = 1;
        $offset = 0;
        if (isset($_GET['page'])) {
            $page = (integer)$_GET['page'];
        }
        if ($page > 1) {
            $offset = ($page*$max_each_page)-$max_each_page;
        }
        
        // language
        $_lang = strtolower($sysconf['default_lang']);

        // query content
        $_sql_content = "SELECT SQL_CALC_FOUND_ROWS * FROM content WHERE is_news=1 AND is_draft=0";
        if ($search_query) {
            $search_query = $obj_db->escape_string(trim($search_query));
            $_sql_content .= " AND MATCH(`content_title`, `content_desc`) AGAINST('$search_query' IN BOOLEAN MODE)";
        }
        $_sql_content .= " ORDER BY `last_update` DESC";
        $_sql_content .= " LIMIT $max_each_page OFFSET $offset";
        
        $_content_q = $obj_db->query($_sql_content);
        // echo $_sql_content;
        
        // get total rows
        $_total_rows = $obj_db->query('SELECT FOUND_ROWS()');
        $_total_rows_d = $_total_rows->fetch_row();
        $total = $_total_rows_d[0];
        
        // get content data
        while ($_content_d = $_content_q->fetch_assoc()) {
            $contents[] = $_content_d;
        }
        
        return $contents;
    }
    
    public function get($obj_db, $str_path = '')
    {
        global $sysconf;
        $_path = strtolower(trim($str_path));
        if (!$_path) {
            return;
        }

        if (preg_match('@^admin.+@i', $_path)) {
            $_unauthorized = !isset($_SESSION['uid']) AND !isset($_SESSION['uname']) AND !isset($_SESSION['realname']);
            if ($_unauthorized) {
                return;
            }
        }

        // language
        $_lang = strtolower($sysconf['default_lang']);
        $_path_lang = $_path.'_'.$_lang;

        // check for language
        $_sql_check = sprintf('SELECT COUNT(*) FROM content WHERE content_path=\'%s\' AND is_draft = 0', $obj_db->escape_string($_path_lang));
        $_check_q = $obj_db->query($_sql_check);
        $_check_d = $_check_q->fetch_row();
        if ($_check_d[0] > 0) {
          $_path = $_path_lang;
        }

        // query content
        $_sql_content = sprintf('SELECT * FROM content WHERE content_path=\'%s\' AND is_draft = 0', $obj_db->escape_string($_path));
        $_content_q = $obj_db->query($_sql_content);
        // get content data
        $_content_d = $_content_q->fetch_assoc();
        if (!isset($_content_d['content_title']) OR !isset($_content_d['content_path'])) {
            return false;
        } else {
            $_content['Title'] = $_content_d['content_title'];
            $_content['Path'] = $_content_d['content_path'];
            $_content['Content'] = '<div class="ck-content p-5">' . $_content_d['content_desc'] . '</div>';
            // strip html
            if ($this->strip_html) {
                $_content['Content'] = '<div class="ck-content p-5">' . strip_tags($_content['Content'], $this->allowed_tags) . '</div>';
            }

            return $_content;
        }
    }
}
