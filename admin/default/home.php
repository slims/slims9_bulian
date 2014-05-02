<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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
 * some patches by hendro
 */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    include_once '../../sysconfig.inc.php';
}
?>
<fieldset class="menuBox adminHome">
<div class="menuBoxInner">
	<div class="per_title">
    	<h2><?php echo __('Library Administration'); ?></h2>
	</div>
</div>
</fieldset>
<?php

// generate warning messages
$warnings = array();
// check GD extension
if (!extension_loaded('gd')) {
    $warnings[] = __('<strong>PHP GD</strong> extension is not installed. Please install it or application won\'t be able to create image thumbnail and barcode.');
} else {
    // check GD Freetype
    if (!function_exists('imagettftext')) {
        $warnings[] = __('<strong>Freetype</strong> support is not enabled in PHP GD extension. Rebuild PHP GD extension with Freetype support or application won\'t be able to create barcode.');
    }
}
// check for overdue
$overdue_q = $dbs->query('SELECT COUNT(loan_id) FROM loan AS l WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\''.date('Y-m-d').'\')) GROUP BY member_id');
$num_overdue = $overdue_q->num_rows;
if ($num_overdue > 0) {
    $warnings[] = str_replace('{num_overdue}', $num_overdue, __('There is currently <strong>{num_overdue}</strong> library members having overdue. Please check at <b>Circulation</b> module at <b>Overdues</b> section for more detail')); //mfc
    $overdue_q->free_result();
}
// check if images dir is writable or not
if (!is_writable(IMGBS) OR !is_writable(IMGBS.'barcodes') OR !is_writable(IMGBS.'persons') OR !is_writable(IMGBS.'docs')) {
    $warnings[] = __('<strong>Images</strong> directory and directories under it is not writable. Make sure it is writable by changing its permission or you won\'t be able to upload any images and create barcodes');
}
// check if file repository dir is writable or not
if (!is_writable(REPOBS)) {
    $warnings[] = __('<strong>Repository</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any bibliographic attachments.');
}
// check if file upload dir is writable or not
if (!is_writable(UPLOAD)) {
    $warnings[] = __('<strong>File upload</strong> directory is not writable. Make sure it is writable (and all directories under it) by changing its permission or you won\'t be able to upload any file, create report files and create database backups.');
}
// check mysqldump
if (!file_exists($sysconf['mysqldump'])) {
    $warnings[] = __('The PATH for <strong>mysqldump</strong> program is not right! Please check configuration file or you won\'t be able to do any database backups.');
}
// check installer directory
if (is_dir('../install/')) {
    $warnings[] = __('Installer folder is still exist inside your server. Please remove it or rename to another name for security reason.');
}


// check need to be repaired mysql database
$query_of_tables = $dbs->query('SHOW TABLES');
$num_of_tables = $query_of_tables->num_rows;
$prevtable = '';
$is_repaired = false;

if (isset ($_POST['do_repair'])) {
    if ($_POST['do_repair'] == 1) {
        while ($row = $query_of_tables->fetch_row()) {
            $sql_of_repair = 'REPAIR TABLE '.$row[0];
            $query_of_repair = $dbs->query ($sql_of_repair);
        }
    }
}

while ($row = $query_of_tables->fetch_row()) {
    $query_of_check = $dbs->query('CHECK TABLE '.$row[0]);
    while ($rowcheck = $query_of_check->fetch_assoc()) {
        if (!(($rowcheck['Msg_type'] == "status") && ($rowcheck['Msg_text'] == "OK"))) {
            if ($row[0] != $prevtable) {
                echo '<li class="warning">Table '.$row[0].' might need to be repaired.</li>';
            }
            $prevtable = $row[0];
            $is_repaired = true;
        }
    }
}
if (($is_repaired) && !isset($_POST['do_repair'])) {
     echo '<li class="warning"><form method="POST"><input type="hidden" name="do_repair" value="1"><input value="Repaire Tables" type="submit"></form></li>';
}

// if there any warnings
if ($warnings) {
    echo '<div class="message">';
    echo '<ul>';
    foreach ($warnings as $warning_msg) {
        echo '<li class="warning">'.$warning_msg.'</li>';
    }
    echo '</ul>';
    echo '</div>';
}

// admin page content
require LIB.'content.inc.php';
$content = new content();
$content_data = $content->get($dbs, 'adminhome');
if ($content_data) {
    echo '<div class="contentDesc">'.$content_data['Content'].'</div>';
    unset($content_data);
}
