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

use Ramsey\Uuid\Uuid;
use SLiMS\Plugins;
use SLiMS\Filesystems\Storage;

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

if (isset($_POST['init'])) {
  echo base64_encode($_SESSION[$_POST['init']].$_POST['init']) ?? '';
  exit;
}

/* File Viewer */

// get file ID
$fileID = isset($_GET['fid']) ? (integer)$_GET['fid'] : 0;
// get biblioID
$biblioID = isset($_GET['bid']) ? (integer)$_GET['bid'] : 0;
$memberID = isset($_SESSION['mid']) ? $_SESSION['mid'] : 0;
$userID = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;

// query file to database
$sql_q = 'SELECT att.*, f.*, b.title FROM biblio_attachment AS att
  LEFT JOIN files AS f ON att.file_id=f.file_id
  LEFT JOIN biblio b on att.biblio_id = b.biblio_id
  WHERE att.file_id=' . $fileID . ' AND att.biblio_id=' . $biblioID . ' AND att.access_type=\'public\'';
$file_q = $dbs->query($sql_q);

if ($file_q->num_rows == 0) throw new Exception('Data attachment not found!' . trim((isDev() ? '&nbsp;: ' . $dbs->error : '')));

$file_d = $file_q->fetch_assoc();


Plugins::getInstance()->execute('fstream_all_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
$file_loc_url = SWB . 'index.php?p=fstream&fid=' . $fileID . '&bid=' . $biblioID;
$file_loc = ($file_d['file_dir'] ? $file_d['file_dir'] . '/' : '') . $file_d['file_name'];
$repository = Storage::repository();


if (!$repository->isExists($file_loc)) throw new Exception('File attachment is not found!');

// check access limit
if ($file_d['access_limit']) {
  if (utility::isMemberLogin()) {
    if (!in_array($_SESSION['m_member_type_id'], @unserialize($file_d['access_limit']))) 
      throw new Exception(__('Access to file restricted. Member logged in but doesnt have privilege to download'));
  } else {
    $referto = SWB . 'index.php?p=member&destination=index.php?p=fstream&fid=' . $fileID . '&bid=' . $biblioID;
    header("location:$referto");
    exit();
  }
}

/**
 * PDF Stream
 */
if ($file_d['mime_type'] == 'application/pdf') {
  if ($sysconf['pdf']['viewer'] == 'pdfjs') {
    $file_loc_url = SWB . 'index.php?p=fstream-pdf&fid=' . $fileID . '&bid=' . $biblioID;
    $uuid = Uuid::uuid4()->toString();
    $_SESSION[$uuid] = base64_encode($file_d['file_key']??'');
    $loader_init = $uuid;
    Plugins::getInstance()->execute('fstream_pdf_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);

    if (utility::isMobileBrowser()) {
        require SB . 'js/pdfjs/mobile/index.php';
    } else {
        require SB . 'js/pdfjs/web/viewer.php';
    }

    Plugins::getInstance()->execute('fstream_pdf_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
    utility::dlCount($dbs, $fileID, $memberID, $userID);
    exit;
  }
} 

/**
 * Video strema file
 */
if (strpos($file_d['mime_type'], 'video') === true) {
  require_once LIB . 'VideoStream.php';
  $stream = new VideoStream($repository, $file_loc, $file_d['mime_type']);
  $stream->start();
  exit;
}

/**
 * Image stream
 */
if (preg_match('@(image)/.+@i', $file_d['mime_type'])) {
    Plugins::getInstance()->execute('fstream_img_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
    utility::dlCount($dbs, $fileID, $memberID, $userID);
    $repository->streamFile($file_loc);
    Plugins::getInstance()->execute('fstream_img_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
    exit();
}

/**
 * Other stream file
 */
Plugins::getInstance()->execute('fstream_oth_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
$repository->streamFile($file_loc);
Plugins::getInstance()->execute('fstream_oth_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
exit();  