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

$page_title = __('Library Location').' | '.$sysconf['library_name'];

?>
<html><head>
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
<title><?php echo $page_title; ?></title>
<script type="text/javascript" src="//maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(<?php echo $sysconf['location']['lat'] . ',' . $sysconf['location']['long']; ?>);
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
<body onload="initialize()" style="padding: 0; margin: 0;">
<div id="map_canvas" style="width: 100%; height: 100%;"></div>
</body>
</html>
<?php
exit();
?>
