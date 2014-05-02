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

class content
{
    public $strip_html = false;
    public $allowed_tags = null;

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
        $_sql_check = sprintf('SELECT COUNT(*) FROM content WHERE content_path=\'%s\'', $obj_db->escape_string($_path_lang));
        $_check_q = $obj_db->query($_sql_check);
        $_check_d = $_check_q->fetch_row();
        if ($_check_d[0] > 0) {
          $_path = $_path_lang;
        }

        // query content
        $_sql_content = sprintf('SELECT * FROM content WHERE content_path=\'%s\'', $obj_db->escape_string($_path));
        $_content_q = $obj_db->query($_sql_content);
        // get content data
        $_content_d = $_content_q->fetch_assoc();
        if (!$_content_d['content_title'] OR !$_content_d['content_path']) {
            return false;
        } else {
            $_content['Title'] = $_content_d['content_title'];
            $_content['Path'] = $_content_d['content_path'];
            $_content['Content'] = $_content_d['content_desc'];
            // strip html
            if ($this->strip_html) {
                $_content['Content'] = strip_tags($_content['Content'], $this->allowed_tags);
            }

            return $_content;
        }
    }
}
