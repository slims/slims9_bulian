<?php
/**
 * Copyright (C) 2014 Arie Nugraha (dicarve@gmail.com), Hendro Wicaksono (hendrowicaksono@gmail.com)
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

/* File Viewer */

// get file ID
$fileID = isset($_GET['fid'])?(integer)$_GET['fid']:0;
// get biblioID
$biblioID = isset($_GET['bid'])?(integer)$_GET['bid']:0;

// query file to database
$sql_q = 'SELECT att.*, f.* FROM biblio_attachment AS att
  LEFT JOIN files AS f ON att.file_id=f.file_id
  WHERE att.file_id='.$fileID.' AND att.biblio_id='.$biblioID.' AND att.access_type=\'public\'';
$file_q = $dbs->query($sql_q);
$file_d = $file_q->fetch_assoc();

if ($file_q->num_rows > 0) {
  $file_loc_url = SWB.'index.php?p=fstream-pdf&fid='.$fileID.'&bid='.$biblioID;
  $file_loc = REPOBS.( $file_d['file_dir']?$file_d['file_dir'].'/':'' ).$file_d['file_name'];
  if (file_exists($file_loc)) {
    // check access limit
    if ($file_d['access_limit']) {
      if (utility::isMemberLogin()) {
        $allowed_mem_types = @unserialize($file_d['access_limit']);
        if (!in_array($_SESSION['m_member_type_id'], $allowed_mem_types)) {
          # Access to file restricted
          # Member logged in but doesnt have privilege to download
          header("location:index.php?p=error&errnum=601");
          exit();
        }
      } else {
        $referto = SWB.'index.php?p=member&destination=index.php?p=fstream&fid='.$fileID.'&bid='.$biblioID;
        header("location:$referto");
        exit();
        }
      }

      if ($file_d['mime_type'] == 'application/pdf') {
        if ($sysconf['pdf']['viewer'] == 'pdfjs') {
          // header('Location: ./js/pdfjs/web/viewer.php?file=../../../repository/'.$file_d['file_dir'].'/'.$file_d['file_name']);
          require './js/pdfjs/web/viewer.php';
          exit();
        }
      } else if (preg_match('@(image)/.+@i', $file_d['mime_type'])) {
        header('Content-Disposition: inline; filename="'.basename($file_loc).'"');
        header('Content-Type: '.$file_d['mime_type']);
        readfile($file_loc);
        exit();
      } else {
        header('Content-Disposition: Attachment; filename="'.basename($file_loc).'"');
        header('Content-Type: '.$file_d['mime_type']);
        readfile($file_loc);
        exit();
      }
    }
    exit();
} else {
  die('<div class="errorBox">File Not Found!</div>');
}
exit();