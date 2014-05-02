<?php
/**
 * Copyright (C) 2009 Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

/*
 * This file demonstrate HTDIG integration with SENAYAN
 */

// HTDIG base config config
$server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
$htdigSearch = 'http://'.$server_addr.'/htdig/search.html';
$htdigBin = 'http://'.$server_addr.'/cgi-bin/htsearch';

/* HTDIG Options */
$method = isset($_GET['method'])?$_GET['method']:'';
$format = isset($_GET['format'])?$_GET['format']:'';
$sort = isset($_GET['sort'])?$_GET['sort']:'';
$config = isset($_GET['config'])?$_GET['config']:'';
$restrict = isset($_GET['restrict'])?$_GET['restrict']:'';
$exclude = isset($_GET['exclude'])?$_GET['exclude']:'';
$p = isset($_GET['p'])?$_GET['p']:'';
$page = isset($_GET['page'])?$_GET['page']:'1';
$words = isset($_GET['words'])?trim($_GET['words']):'';

// create file URL handle
if ($words) {
    $handle = @fopen($htdigSearch, 'r');
    if (!$handle) {
        echo '<div style="margin: 3px; padding: 5px; border: 1px dotted #FF0000; color: #FF0000;">Can\'t Open HTDIG Search.'
            .'Please make sure that HTDIG installed correctly.</div>';
    }
} else {
    // htdig cgi binary search URL
    $url = $htdigBin.'?config='.$config.'&restrict='.$restrict.'&exclude='.$exclude.'&method='.$method.'&format='.$format.'&sort='.$sort.'&words='.$words.'&page='.$page;
    $handle = @fopen($url, 'r');
    if (!$handle) {
        echo '<div style="margin: 3px; padding: 5px; border: 1px dotted #FF0000; color: #FF0000;">Can\'t Open HTDIG for search.'
            .'Please make sure that HTDIG installed correctly.</div>';
    }
}

// output
if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        if (preg_match('/PAGING_SEARCH_RESULT/i', $buffer)) {
            $buffer = preg_replace('/cgi-bin/i', str_replace('/', '', SWB), $buffer);
            $buffer = preg_replace('/htsearch/', 'index.php', $buffer);
            $buffer = preg_replace('/\?/', '', $buffer);
            $buffer = preg_replace('/index.php/', 'index.php?p=htdig&', $buffer);
            $buffer = preg_replace('/;/', '&', $buffer);
        }
        echo $buffer;
    }
    fclose($handle);
}
