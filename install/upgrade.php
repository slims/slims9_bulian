<?php
/**
 * Slims Installer files
 *
 * Copyright © 2006 - 2012 Advanced Power of PHP
 * modification by Waris Agung Widodo (ido.alit@gmail.com)
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
require 'settings.php';

$slimsold = array(
	'1' => array('version' => 'stable3', 'name' => 'Senayan 3 - Stable 3'),
	'2' => array('version' => 'stable4', 'name' => 'Senayan 3 - Stable 4'),
	'3' => array('version' => 'stable5', 'name' => 'Senayan 3 - Stable 5'),
	'4' => array('version' => 'stable6', 'name' => 'Senayan 3 - Stable 6'),
	'5' => array('version' => 'stable7', 'name' => 'Senayan 3 - Stable 7'),
	'6' => array('version' => 'stable8', 'name' => 'Senayan 3 - Stable 8'),
	'7' => array('version' => 'stable9', 'name' => 'Senayan 3 - Stable 9'),
	'8' => array('version' => 'stable10', 'name' => 'Senayan 3 - Stable 10'),
	'9' => array('version' => 'stable11', 'name' => 'Senayan 3 - Stable 11'),
	'10' => array('version' => 'stable12', 'name' => 'Senayan 3 - Stable 12'),
	'11' => array('version' => 'stable13', 'name' => 'Senayan 3 - Stable 13'),
	'12' => array('version' => 'stable14', 'name' => 'Senayan 3 - Stable 14 | Seulanga'),
	'13' => array('version' => 'stable15', 'name' => 'Senayan 3 - Stable 15 | Matoa'),
	'14' => array('version' => 'slims5_meranti', 'name' => 'SLiMS 5 | Meranti'),
	'15' => array('version' => 'slims7_cendana', 'name' => 'SLiMS 7 | Cendana'),
	'16' => array('version' => 'slims8_akasia', 'name' => 'SLiMS 8 | Akasia'),
	'17' => array('version' => 'slims8.2_akasia', 'name' => 'SLiMS 8.2 | Akasia'),
	'18' => array('version' => 'slims8.3_akasia', 'name' => 'SLiMS 8.3 | Akasia')
	);

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Step 2 | Slims Installer</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="styles.css">
	<link rel="shortcut icon" href="img/webicon.ico" type="image/x-icon"/>
	<link rel="stylesheet" type="text/css" href="fonts/font-awesome/css/font-awesome.css">
</head>
<body>
    <div class="wrapper">
	<div class="content hastable">
		<div class="title">
		    <h2>Step 2 - Upgrade</h2>
		</div>
		<p class="message">Please complete following form with your database connection information</p>
		<form method="post" action="install2.php">

			<input type="hidden" name="submit" value="step2" />
	        <table class=text width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text">
	            <tr>
	                <td>&nbsp;Database Host</td>
	                <td>
	                    <input type="text" class="form_text" name="database_host" value='localhost' size="30">&nbsp; <em>default : localhost</em>
	                </td>
	            </tr>
	            <tr>
	                <td>&nbsp;Database Name</td>
	                <td>
	                    <input type="text" class="form_text" name="database_name" size="30" value="">
	                </td>
	            </tr>
	            <tr>
	                <td>&nbsp;Database Username</td>
	                <td>
	                    <input type="text" class="form_text" name="database_username" size="30" value="">
	                </td>
	            </tr>
	            <tr>
	                <td>&nbsp;Database Password</td>
	                <td>
	                    <input type="password" class="form_text" name="database_password" size="30" value="">
	                </td>
	            </tr>
	        </table>
	        <p class="message">Please select your current SLiMS version</p>
		    <div>
		    	<table class=text width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text">
		            <tr>
		                <td>&nbsp;Your SLiMS Version</td>
		                <td>
		                    <select class="form_text" name="indexdbupgrade">
		                    <option value="0">-- Select Version --</option>
							<?php 
							foreach ($slimsold as $key => $value) { ?>
								
								<option value="<?php echo $key; ?>"><?php echo $value['name']; ?></option>

							<?php }	?>
							</select>
		                </td>
		            </tr>
		        </table>
			    <hr>
				<div class="toright">
					<input type="button" class="button upgrade" name="btn_cancel" value="Back" title="Click to start upgrade" onclick="document.location.href='check_system.php'">
					<input type="submit" class="button" name="btn_submit" value="Next">
			    </div>
		    </div>
	    </div>
		</form>
	<?php include_once("footer.php"); ?>
</div>
</body>
</html>