<?php
/**
 * Copyright (C) 2014  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

use SLiMS\Filesystems\Storage;

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

if (isset($sysconf['allow_pdf_download'])) {
  // do nothing
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
    $file_loc = str_ireplace('/', DS, $file_d['file_dir']).DS.$file_d['file_name'];
    $repository = Storage::repository();
    if ($repository->isExists($file_loc)) {
        if ($file_d['access_limit']) {
            if (utility::isMemberLogin()) {
                $allowed_mem_types = @unserialize($file_d['access_limit']);
                if (!in_array($_SESSION['m_member_type_id'], $allowed_mem_types)) {
                    header("location:index.php?p=error&errnum=601");
                    //~ continue;
                }
            } else {
                $referto = SWB.'index.php?p=member&destination=index.php?p=fstream-pdf&fid='.$fileID.'&bid='.$biblioID;
                header("location:$referto");
                //~ continue;
            }
        }
        $repository->streamFile($file_loc);
    } else {
      die('<div class="errorBox">File Not Found!</div>');
    }
} else {
  die('<div class="errorBox">File Not Found!</div>');
}
exit();
