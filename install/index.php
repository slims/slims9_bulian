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

include "settings.php";    
if (file_exists($config_file_path)) {        
    header("location: ".$application_start_file);
    exit;
}

ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
foreach($matches as $match)
{
    if(strlen($match[1]))
        $phpinfo[$match[1]] = array();
    elseif(isset($match[3]))
		@$phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
    else
        @$phpinfo[end(array_keys($phpinfo))][] = $match[2];
}
?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Start | Slims Installer</title>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="wrapper">
	<div class="title">
	    <h2>Welcome to SLiMS Installer</h2>	    
	</div>
	<p class="message">Before getting started, you must get some information of following items</p>
	<div class="content">	    
	    <ul class="list">
		<li>Database Name (must be set before running this installation)</li>
		<li>Database Username</li>
		<li>Database Password</li>
		<li>Username Login (optional)</li>
		<li>Password Login (optional)</li>
	    </ul>
	    <br/>
	    <div class="toright">
	    <input type="button" class="button" value="Let's Start The Installation" name="submit" title="Click to start installation" onclick="document.location.href='install.php'">	    
	    </div>
	</div>
	    <?php include_once("footer.php"); ?>

    </div>    
                  
</body>
</html>

