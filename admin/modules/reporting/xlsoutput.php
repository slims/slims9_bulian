<?php

/**
 *
 * Modified 2010  by Wardiyono (wynerst@gmail.com)
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

// key to authenticate
define('INDEX_AUTH', '1');

// load SLiMS main system configuration
require '../../../sysconfig.inc.php';

// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
// privileges checking
$can_read = utility::havePrivilege('reporting', 'r');
$can_write = utility::havePrivilege('reporting', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

// Make some functions for writing out an excel file. These functions do some hex writing and to be honest I got
// them from some where else but hey it works so I am not going to question it just reuse

// This one makes the beginning of the xls file
function xlsBOF() {
	echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
	return;
}

// This one makes the end of the xls file
function xlsEOF() {
	echo pack("ss", 0x0A, 0x00);
	return;
}

// this will write text in the cell you specify
function xlsWriteLabel($Row, $Col, $Value ) {
	$L = strlen($Value);
	echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
	echo $Value;
	return;
}

/**
* Get sql query stored in session
* Get table output name from session too
*/

if (isset($_SESSION["xlsquery"])) {
	$q = $_SESSION["xlsquery"];
	$dbtable = $_SESSION["tblout"];
	$qr = $dbs->query($q);
	if (!$qr) {
	    echo "Could not successfully run query ($q) from DB: " . mysql_error();
	    exit;
	}

	// send some headers so that this
	// thing that we are going make comes out of browser
	// as an xls file.
	//

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");

	//this line is important its makes the file name
	header("Content-Disposition: attachment;filename=export_".$dbtable.".xls ");

	header("Content-Transfer-Encoding: binary ");

	// start the file
	xlsBOF();

	// these will be used for keeping things in order.
	$col = 0;
	$row = 0;

	// This tells us that we are on the first row
	$first = true;
	while( $qrow = $qr->fetch_assoc() )
	{
		// Ok we are on the first row
		// lets make some headers of sorts
		if( $first ) 
		{
			foreach( $qrow as $k => $v )
			{
				// take the key and make label
				// make it uppper case and replace _ with ' '
				xlsWriteLabel( $row, $col, strtoupper( ereg_replace( "_" , " " , $k ) ) );
				$col++;
			}

			// prepare for the first real data row
			$col = 0;
			$row++;
			$first = false;
		}

		// go through the data
		foreach( $qrow as $k => $v )
		{
			// write it out
			xlsWriteLabel( $row, $col, $v );
			$col++;
		}

		// reset col and goto next row
		$col = 0;
		$row++;
	}

	xlsEOF();
	exit();
} else {
	$q = $_SESSION["xlsdata"];
	$dbtable = $_SESSION["tblout"];
	// send some headers so that this
	// thing that we are going make comes out of browser
	// as an xls file.
	//

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");

	//this line is important its makes the file name
	header("Content-Disposition: attachment;filename=export_".$dbtable.".xls ");

	header("Content-Transfer-Encoding: binary ");

	// start the file
	xlsBOF();

	// these will be used for keeping things in order.
	$col = 0;
	$row = 0;

	// This tells us that we are on the first row
	$first = true;

	// Ok we are on the first row
	// lets make some headers of sorts
	if( $first ) 
	{
		$qcol = $q[$row];
		foreach( $qcol as $k => $v )
		{
			// take the key and make label
			// make it uppper case and replace _ with ' '
			xlsWriteLabel( $row, $col, strtoupper( ereg_replace( "_" , " " , $v ) ) );
			$col++;
		}
		// prepare for the first real data row
		$col = 0;
		$first = false;
	}

	for ($i=1; $i < count($q); $i++) {
		$qcol = $q[$i];
		// go through the data
		foreach( $qcol as $k => $v )
		{
			// write it out
			xlsWriteLabel( $i, $col, $v );
			$col++;
		}

		// reset col and goto next row
		$col = 0;

	}

	xlsEOF();
	exit();
}
