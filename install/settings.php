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

$config_file_default = "config.default";
$config_file_directory = "../config/";
$config_file_name = "sysconfig.local.inc.php";
$config_file_path = $config_file_directory.$config_file_name;
$application_name = "SLiMS Easy Installer";
$application_start_file = "../index.php";
$license_agreement_page = "";
$sql_dump = "senayan.sql.php";
$sql_sample	= "sampledata.sql";
$sql_upgrade = array(
	'1'  => 'upgrade_stable3.sql',
	'2'  => 'upgrade_stable4.sql',
	'3'  => 'upgrade_stable5.sql',
	'4'  => 'upgrade_stable6.sql',
	'5'  => 'upgrade_stable7.sql',
	'6'  => 'upgrade_stable8.sql',
	'7'  => 'upgrade_stable9.sql',
	'8'  => 'upgrade_stable10.sql',
	'9'  => 'upgrade_stable11.sql',
	'10' => 'upgrade_stable12.sql',
	'11' => 'upgrade_stable13.sql',
	'12' => 'upgrade_stable14.sql',
	'13' => 'upgrade_stable15.sql',
	'14' => 'upgrade_slims5_meranti.sql',
	'15' => 'upgrade_slims7_cendana.sql',
	'16' => 'upgrade_slims8_akasia.sql'
);