<?php
/**
 * SENAYAN application bootstrap files
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some modifications & patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

// key to authenticate
define('INDEX_AUTH', '1');

// required file
require 'sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('opac');
// member session params
require LIB.'member_session.inc.php';
// start session
session_start();
if ($sysconf['template']['base'] == 'html') {
  require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
}

// page title
$page_title = $sysconf['library_subname'].' | '.$sysconf['library_name'];

// default library info
$info = __('Web Online Public Access Catalog - Use the search options to find documents quickly');
// total opac result page
$total_pages = 1;
// default header info
$header_info = '';
// HTML metadata
$metadata = '';
// searched words for javascript highlight
$searched_words_js_array = '';

// member login information
if (utility::isMemberLogin()) {
  $header_info .= '<div class="alert alert-info alert-member-login" id="memberLoginInfo">'.__('You are currently Logged on as member').': <strong>'.$_SESSION['m_name'].' (<em>'.$_SESSION['m_email'].'</em>)</strong> <a id="memberLogout" href="index.php?p=member&logout=1">'.__('LOGOUT').'</a></div>';
}

// start the output buffering for main content
ob_start();
require LIB.'contents/common.inc.php';
if (isset($_GET['p'])) {
    $path = utility::filterData('p', 'get', false, true, true);
    // some extra checking
    $path = preg_replace('@^(http|https|ftp|sftp|file|smb):@i', '', $path);
    $path = preg_replace('@\/@i','',$path);
    // check if the file exists
    if (file_exists(LIB.'contents/'.$path.'.inc.php')) {
        include LIB.'contents/'.$path.'.inc.php';
        if ($path != 'show_detail') {
          $metadata = '<meta name="robots" content="noindex, follow">';
        }
    } else {
        // get content data from database
        $metadata = '<meta name="robots" content="index, follow">';
        include LIB.'content.inc.php';
        $content = new Content();
        $content_data = $content->get($dbs, $path);
        if ($content_data) {
          $page_title = $content_data['Title'];
          echo $content_data['Content'];
          unset($content_data);
        } else {
          header ("location:index.php");
        }
    }
} else {
    $metadata = '<meta name="robots" content="index, follow">';
    // homepage header info
    if (!isset($_GET['p'])) {
        if ((!isset($_GET['keywords'])) AND (!isset($_GET['page'])) AND (!isset($_GET['title'])) AND (!isset($_GET['author'])) AND (!isset($_GET['subject'])) AND (!isset($_GET['location']))) {
            // get content data from database
            include LIB.'content.inc.php';
            $content = new Content();
            $content_data = $content->get($dbs, 'headerinfo');
            if ($content_data) {
                //$header_info .= '<div id="headerInfo">'.$content_data['Content'].'</div>';
                unset($content_data);
            }
        }
    }
    include LIB.'contents/default.inc.php';
}
// main content grab
$main_content = ob_get_clean();

// template output
require $sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/index_template.inc.php';