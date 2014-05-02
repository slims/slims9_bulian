<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some ajax security patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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
 * You should have received a attachment of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Bibliographic attachment listing */

// key to authenticate
if (!defined('INDEX_AUTH')) {
  define('INDEX_AUTH', '1');
}

// required file
require '../../sysconfig.inc.php';
require '../member_session.inc.php';
session_start();

if (isset($_POST['ajaxsec_user'])) {
  $ajaxsec_user = $_POST['ajaxsec_user'];
}

if (isset($_POST['ajaxsec_passwd'])) {
  $ajaxsec_passwd = $_POST['ajaxsec_passwd'];
}

if (($ajaxsec_user == $sysconf['ajaxsec_user']) AND ($ajaxsec_passwd == $sysconf['ajaxsec_passwd'])) {
  if ($sysconf['ajaxsec_ip_enabled'] == '1') {
    $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
    if ($server_addr == $sysconf['ajaxsec_ip_allowed']) {
      die();
    }
  }
  if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $attachment_q = $dbs->query('SELECT att.*, f.* FROM biblio_attachment AS att
      LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$id.' AND att.access_type=\'public\' LIMIT 20');
    if ($attachment_q->num_rows < 1) {
      echo '<strong style="color: red; font-weight: bold;">'.__('No Attachment').'</strong>';
    } else {
      echo '<ul class="attachList">';
      while ($attachment_d = $attachment_q->fetch_assoc()) {
        // check member type privileges
        if ($attachment_d['access_limit']) {
          if (utility::isMemberLogin()) {
            $allowed_mem_types = @unserialize($attachment_d['access_limit']);
            if (!in_array($_SESSION['m_member_type_id'], $allowed_mem_types)) {
              continue;
            }
          } else {
            continue;
          }
        }
        #if (preg_match('@(video|audio|image)/.+@i', $attachment_d['mime_type'])) {
        if ($attachment_d['mime_type'] == 'application/pdf') {
          echo '<li style="list-style-image: url(images/labels/ebooks.png)"><strong><a class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'&fname='.$attachment_d['file_name'].'" width="780" height="520">'.$attachment_d['file_title'].'</a></strong>';
          echo '<div><i>'.$attachment_d['file_desc'].'</i></div>';
          if (trim($attachment_d['file_url']) != '') { echo '<div><a href="'.trim($attachment_d['file_url']).'" title="Other Resource related to this book" target="_blank">Other Resource Link</a></div>'; }
          echo '</li>';
        } else if (preg_match('@(video|audio)/.+@i', $attachment_d['mime_type'])) {
          echo '<li style="list-style-image: url(images/labels/auvi.png)"><strong><a class="openPopUp" title="'.$attachment_d['file_title'].'" href="./index.php?p=multimediastream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="640" height="480">'.$attachment_d['file_title'].'</a></strong>';
          echo '<div><i>'.$attachment_d['file_desc'].'</i></div>';
          if (trim($attachment_d['file_url']) != '') { echo '<div><a href="'.trim($attachment_d['file_url']).'" title="Other Resource Link" target="_blank">Other Resource Link</a></div>'; }
          echo '</li>';
        } else if ($attachment_d['mime_type'] == 'text/uri-list') {
          echo '<li style="list-style-image: url(images/labels/url.png)"><strong><a href="'.trim($attachment_d['file_url']).'" title="Click to open link" target="_blank">'.$attachment_d['file_title'].'</a><div>'.$attachment_d['file_desc'].'</div></strong></li>';
        } else if (preg_match('@(image)/.+@i', $attachment_d['mime_type'])) {
          #echo '<li style="list-style-image: url(images/labels/ebooks.png)"><strong><a title="Click To View File" href="index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" target="_blank">'.$attachment_d['file_title'].'</a></strong>';
          $file_loc = REPOBS.'/'.$attachment_d['file_dir'].'/'.$attachment_d['file_name'];
          $imgsize = GetImageSize($file_loc);
          $imgwidth = $imgsize[0] + 16;
          if ($imgwidth > 600) {
            $imgwidth = 600;
          }
          $imgheight = $imgsize[1] + 16;
          if ($imgheight > 400) {
            $imgheight = 400;
          }
          #echo '<li style="list-style-image: url(images/labels/ebooks.png)"><strong><a href="#" title="Click To View File" onclick="openHTMLpop(\'index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'\', 600, 400, \''.$attachment_d['file_title'].'\')">'.$attachment_d['file_title'].'</a></strong>';
          echo '<li style="list-style-image: url(images/labels/ebooks.png)"><strong><a class="openPopUp" title="'.$attachment_d['file_title'].'" href="index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" width="'.$imgwidth.'" height="'.$imgheight.'">'.$attachment_d['file_title'].'</a></strong>';
          if (trim($attachment_d['file_url']) != '') { echo ' [<a href="'.trim($attachment_d['file_url']).'" title="Other Resource related to this file" target="_blank" style="font-size: 90%;">Other Resource Link</a>]'; }
          echo '<div><i>'.$attachment_d['file_desc'].'</i></div></li>';
        } else {
          echo '<li style="list-style-image: url(images/labels/ebooks.png)"><strong><a title="Click To View File" href="index.php?p=fstream&fid='.$attachment_d['file_id'].'&bid='.$attachment_d['biblio_id'].'" target="_blank">'.$attachment_d['file_title'].'</a></strong>';
          if (trim($attachment_d['file_url']) != '') { echo ' [<a href="'.trim($attachment_d['file_url']).'" title="Other Resource related to this file" target="_blank" style="font-size: 90%;">Other Resource Link</a>]'; }
          echo '<div><i>'.$attachment_d['file_desc'].'</i></div></li>';
        }
      }
      echo '</ul>';
    }
  }
}
