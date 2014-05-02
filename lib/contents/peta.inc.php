<?php
/**
 * Copyright (C) 2009, WARDIYONO (wynerst@gmail.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

$info = 'Library Location';
$lat= -6.2254549;
$long= 106.8023901;

?>
<html>
<head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<title><?php echo $info; ?></title>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(<?php echo $lat . ',' . $long; ?>);
    var myOptions = {
      zoom: 14,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);

	var marker = new google.maps.Marker({
        position: latlng,
        map: map,
        title:"<?php echo $sysconf['library_name'] . ' ' . $sysconf['library_subname']; ?>"
    });
  }
</script>
</head>
<body onload="initialize()">
<div id="map_canvas" style="float: left; width: 50%; height: 100%;"></div>
<div style="float: left; width: 2%; height: 100%;"></div>
<div style="float: left; width: 40%; height: 100%;">
	<h3>Contact Information</h3>
		<p>
		<strong>Address :</strong>
			<br />
			Jenderal Sudirman Road, Senayan, Jakarta, Indonesia
			<br />
			Postal Code : 10270
			<br />
		<strong>Phone Number :</strong>
			<br />
			(021) 5711144
			<br />
		<strong>Fax Number :</strong>
			<br />
			(021) 5711144
		</p>

	<h3>Opening Hours</h3>
		<p>
		<strong>Monday - Friday :</strong>
		<br />
		Open : 08.00 AM<br />
		Break : 12.00 - 13.00 PM<br />
		Close : 20.00 PM
		<br />
		<strong>Saturday  :</strong>
		<br />
		Open : 08.00 AM<br />
		Break : 12.00 - 13.00 PM<br />
		Close : 17.00 PM
		</p>

	<h3>Collections</h3>
		<p>
		We have many types of collections in our library, range from Fictions to Sciences Material,
		from printed material to digital collections such CD-ROM, CD, VCD and DVD. We also collect
		daily serials publications such as newspaper and also monthly serials such as magazines.
		</p>

	<h3>Library Membership</h3>
		<p>
		To be able to loan our library collections, you must first become library member. There is
		terms and conditions that you must obey.
		</p>
</div>
</body>
</html>
<?php
exit();
?>
