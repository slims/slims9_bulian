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

if ($file_q->num_rows == 0) throw new Exception('Data not found!' . trim((isDev() ? '&nbsp;: ' . $dbs->error : '')));

$file_d = $file_q->fetch_assoc();


\SLiMS\Plugins::getInstance()->execute('fstream_all_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
$file_loc_url = SWB . 'index.php?p=fstream&fid=' . $fileID . '&bid=' . $biblioID;
$file_loc = REPOBS . ($file_d['file_dir'] ? $file_d['file_dir'] . '/' : '') . $file_d['file_name'];

if (!file_exists($file_loc)) throw new Exception('File not found!' . trim((isDev() ? '&nbsp;: ' . $file_loc : '')));

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
    $referto = SWB . 'index.php?p=member&destination=index.php?p=fstream&fid=' . $fileID . '&bid=' . $biblioID;
    header("location:$referto");
    exit();
  }
}

if ($file_d['mime_type'] == 'application/pdf') {
  if ($sysconf['pdf']['viewer'] == 'pdfjs') {
    $file_loc_url = SWB . 'index.php?p=fstream-pdf&fid=' . $fileID . '&bid=' . $biblioID;
    $uuid = Uuid::uuid4()->toString();
    $_SESSION[$uuid] = base64_encode($file_d['file_key']??'');
    $loader_init = $uuid;
    \SLiMS\Plugins::getInstance()->execute('fstream_pdf_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);

    if (utility::isMobileBrowser()) {
        require SB . 'js/pdfjs/mobile/index.php';
    } else {
        require SB . 'js/pdfjs/web/viewer.php';
    }

    \SLiMS\Plugins::getInstance()->execute('fstream_pdf_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
    utility::dlCount($dbs, $fileID, $memberID, $userID);
    exit();
  }
} else if (preg_match('@(image)/.+@i', $file_d['mime_type'])) {
  \SLiMS\Plugins::getInstance()->execute('fstream_img_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
  utility::dlCount($dbs, $fileID, $memberID, $userID);
  header('Content-Disposition: inline; filename="' . basename($file_loc) . '"');
  header('Content-Type: ' . $file_d['mime_type']);
  echo file_get_contents($file_loc);
  \SLiMS\Plugins::getInstance()->execute('fstream_img_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
  exit();
} else {
  if (strpos($file_d['mime_type'], 'video') !== false) {
    require_once LIB . 'VideoStream.php';
    $stream = new VideoStream($file_loc, $file_d['mime_type']);
    $stream->start();
  } else {
    \SLiMS\Plugins::getInstance()->execute('fstream_oth_before_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
    header('Content-Disposition: Attachment; filename="' . basename($file_loc) . '"');
    header('Content-Type: ' . $file_d['mime_type']);
    echo file_get_contents($file_loc);
    \SLiMS\Plugins::getInstance()->execute('fstream_oth_after_download', ['data' => array('fileID' => $fileID, 'memberID' => $memberID, 'userID' => $userID, 'biblioID' => $biblioID, 'file_d' => $file_d)]);
  }
  exit();
}
