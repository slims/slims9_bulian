<?php

/**
 * contentFromDb class
 * Class for getting content
 *
 * Copyright (C) 2010  Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

class content_custom
{
    protected $content_path = NULL;
    protected $link;
    protected $db;
    protected $sql = '';
    protected $query;
    protected $url = NULL;
    protected $content_title = NULL;
    protected $content_desc = NULL;
    protected $page = '';
    protected $page_assigned = array('all');

    function do_content_custom($content_path)
    {
        $this->link = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
        $this->db = mysql_select_db(DB_NAME);
        $this->sql = "SELECT * FROM content WHERE content_path='$content_path'";
        $this->query = mysql_query($this->sql);
        while ($result = mysql_fetch_array($this->query)) {
            $this->content_title = $result['content_title'];
            $this->content_desc = $result['content_desc'];
            $this->content_path = $result['content_path'];
            $this->url = SWB.'index.php?p='.$this->content_path;
        }
    }

    public function get_between($start, $end)
    {
        $r = explode($start, $this->content_desc);
        if (isset($r[1])){
            $r = explode($end, $r[1]);
            return $r[0];
        }
        return '';
    }


    public function is_page($dest = 'frontpage')
    {
        if (!isset($_GET['p'])) {
            if ((!isset($_GET['keywords'])) AND (!isset($_GET['page'])) AND (!isset($_GET['title'])) AND (!isset($_GET['author'])) AND (!isset($_GET['subject'])) AND (!isset($_GET['location']))) {
                $page = 'frontpage';
            } else {
                $page = 'detail';
            }
        } else {
            $page = $_GET['p'];
        }
        if ($dest === $page) {
            return 1;
        } elseif ($dest === 'all') {
            return 1;
        } else {
            return 0;
        }
    }


    public function get_url()
    {

        return $this->url;
    }

    public function get_content_title()
    {
        return $this->content_title;
    }

    public function get_content_desc()
    {
        return $this->content_desc;
    }

}
