<?php
/**
 *
 * Modified 2025  by Heru Subekti (heroe.soebekti@gmail.com)
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
 */

// key to authenticate
define('INDEX_AUTH', '1');

require_once __DIR__ . '/../../../sysconfig.inc.php';

use Mpdf\Mpdf;
use Mpdf\MpdfException;

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

$tempPath = SB . FLS . DS. '/cache';

$xlsdata = [];
if (isset($_SESSION['xlsquery'])) {
	$q = $_SESSION['xlsquery'];
	$q = $dbs->query($q);
	$xlsdata = [];
	$xlsheader = [];
  while($f = $q->fetch_field()) {
  	$xlsheader[] = $f->name;
  }
  $xlsdata[] = $xlsheader;
  while ($a = $q->fetch_row()) {
  	$xlsdata[] = $a;
  }
}
else if (isset($_SESSION['xlsdata'])) {
	$xlsdata = $_SESSION['xlsdata'];
}

try {
    $mpdf = new \Mpdf\Mpdf([
        'tempDir' => $tempPath
    ]);
} catch (MpdfException $e) {
    die('Mpdf error: ' . $e->getMessage());
}

$tblout = $_SESSION['tblout'];

$html = '<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>'.__(ucwords(str_replace('_',' ',$tblout))).'</h1>
    <table>
        <thead>
            <tr>';
            
if (!empty($xlsdata)) {
    $header = array_shift($xlsdata);
    foreach ($header as $col) {
        $html .= '<th>' . htmlspecialchars($col) . '</th>';
    }
}

$html .= '  </tr>
        </thead>
        <tbody>';

// Isi tabel
foreach ($xlsdata as $row) {
    $html .= '<tr>';
    foreach ($row as $cell) {
        $html .= '<td>' . htmlspecialchars($cell) . '</td>';
    }
    $html .= '</tr>';
}

$html .= '  </tbody>
    </table>
</body>
</html>';

$mpdf->WriteHTML($html);
$mpdf->Output("$tblout.pdf", 'D');
exit;
