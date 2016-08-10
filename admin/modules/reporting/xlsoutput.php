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

if (isset($_SESSION["xlsquery"])) {
	$q = $_SESSION["xlsquery"];
	$dbtable = $_SESSION["tblout"];
	$qr = $dbs->query($q);
	if (!$qr) {
	    echo "Could not successfully run query ($q) from DB: " . $dbs->error;
	    exit;
	}

	$filename = "excelfilename";         //File Name
	$file_ending = "xls";

	//header info for browser
	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=$dbtable.xls");  
	header("Pragma: no-cache"); 
	header("Expires: 0");
	/*******Start of Formatting for Excel*******/   
	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character
	//start of printing column names as names of MySQL fields
	$columns = $qr->fetch_fields();
	foreach ($columns as $val) {
		echo $val->name . "\t";
	}
	print("\n");    

	//end of printing column names  
	//start while loop to get data
    while($row = $qr->fetch_row())
    {
        $schema_insert = "";
        for($j=0; $j<count($columns); $j++)
        {
            if(!isset($row[$j]))
                $schema_insert .= "NULL".$sep;
            elseif ($row[$j] != "")
                $schema_insert .= "$row[$j]".$sep;
            else
                $schema_insert .= "".$sep;
        }
        $schema_insert = str_replace($sep."$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
    }
	exit();

} else {
	$q = $_SESSION["xlsdata"];
	$dbtable = $_SESSION["tblout"];
	$sep = "\t"; //tabbed character
	//header info for browser
	header("Content-Type: application/xls");    
	header("Content-Disposition: attachment; filename=$dbtable.xls");  
	header("Pragma: no-cache"); 

	// This tells us that we are on the first row

	foreach($q as $row => $cols) {
		$schema_insert = "";
		for($j=0; $j<count($cols); $j++) {
			if(!isset($cols[$j])) 
				$schema_insert .= "NULL".$sep;
			elseif ($cols[$j] != "")
				$schema_insert .= "$cols[$j]".$sep;
			else
				$schema_insert .= "".$sep;
		}	
		$schema_insert = str_replace($sep."$", "", $schema_insert);
		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
		$schema_insert .= "\t";
		print(trim($schema_insert));
		print "\n";
	}

	exit();
}
