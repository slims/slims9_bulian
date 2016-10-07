<?php
/**
 * Slims Installer files
 *
 * Copyright © 2006 - 2012 Advanced Power of PHP
 * Some modifications & patches by Eddy Subratha (eddy.subratha@gmail.com)
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
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Step 2 | Slims Installer</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link rel="shortcut icon" href="img/webicon.ico" type="image/x-icon"/>
</head>
<body>
    <div class="wrapper">
	<div class="content hastable">
    <div class="title">
        <h2>Step 2 - Generate the database </h2>
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
            <tr>
				<td>Generate Sample Data</td>
                                <td>
				    <input type="radio" name="install_sample" value="yes" /> Yes
				    <input type="radio" name="install_sample" value="no" checked="checked" /> No
				</td>
            </tr>
        </table>
        <p class="message">Please complete following form with user login and password (Optional)</p>
        <div class="hastable">
            <table class=text width="100%" border="0" cellspacing="0" cellpadding="2" class="main_text">
                <tr>
                    <td>&nbsp;Username</td>
                    <td>
                        <input type="text" class="form_text" name="username" size="30" value="admin">&nbsp; <em>default : admin</em>
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input type="password" class="form_text" name="password" size="30" value="">&nbsp; <em>default : admin</em>
                    </td>
                </tr>
                <tr>
                    <td>Retype Password</td>
                    <td>
                        <input type="password" class="form_text" name="retype_password" size="30" value="">
                    </td>
                </tr>
            </table>
            <hr/>
            <div class="toright">
                <input type="button" class="button" name="btn_cancel" value="Back" onclick="document.location.href='check_system.php'">
                <input type="submit" class="button" name="btn_submit" value="Next">
            </div>
        </div>
    </div>
    </form>
    <?php include_once("footer.php"); ?>
</div>
</body>
</html>
