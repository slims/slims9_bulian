<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com), Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

// key to authenticate
define('INDEX_AUTH', '1');

/* File Viewer */

require '../sysconfig.inc.php';
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
if (!$can_read) {
    die('<div class="errorBox">You dont have enough privileges to view this section</div>');
}

// get file ID
$fileID = isset($_GET['fid'])?(integer)$_GET['fid']:0;
$memberID = isset($_SESSION['mid']) ? $_SESSION['mid'] : 0;
$userID = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;
// query file to database
$file_q = $dbs->query('SELECT * FROM files WHERE file_id='.$fileID);
$file_d = $file_q->fetch_assoc();

if ($file_q->num_rows > 0) {
    $file_loc = str_ireplace('/', DS, $file_d['file_dir']).DS.$file_d['file_name'];
    $repository = Storage::repository();
    if ($repository->isExists($file_loc)) {
		utility::dlCount($dbs, $fileID, $memberID, $userID);
        $repository->streamFile($file_loc);
    } else {
        if ($file_d['mime_type'] == 'text/uri-list') {
            header('Location: '.$file_d['file_url']);
            exit();
        }
        die('<div class="errorBox">File Metadata exists in database BUT '.$file_loc.' does\'t exists in repository!</div>');
    }
} else {
  die('<div class="errorBox">File Not Found!</div>');
}
