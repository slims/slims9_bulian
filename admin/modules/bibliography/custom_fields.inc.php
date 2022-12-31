<?php
/**
 * Bibliographic custom fields
 *
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
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

use SLiMS\Table\{Schema,Blueprint};

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

global $dbs;

$_q = $dbs->query("SELECT * FROM mst_custom_field WHERE `primary_table`='biblio'");
if(isset($_q->num_rows)){
	while($_d = $_q->fetch_assoc()){
		$dbfield[] = $_d;
		
		if (!Schema::hasColumn('biblio_custom', $_d['dbfield']))
		{
			$type = $_d['type']=='date'?'date':($_d['type']=='numeric'?['number', 11]:'text');
			Schema::table('biblio_custom', function(Blueprint $table) use($_d,$type) {
				// Set $table parametter
				$params = [$_d['dbfield']]; // if $type didn't have constraint

				// Need constraint?
				if (is_array($type)) {
					$params[] = $type[1];
					$type = $type[0];
				}

				// Register coloumn into $table
				$table->{$type}(...$params)->nullable()->comment('field for ' . $_d['label'])->add();
			});
		}
	  	$biblio_custom_fields = $dbfield;
	}
}