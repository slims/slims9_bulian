<?php
/**
 * Member data custom fields processing.
 * 
 * @author Original code by Waris Agung Widodo (ido.alit@gmail.com).
 * @package SLiMS
 * @subpackage Membership
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

use SLiMS\Table\{Schema,Blueprint};

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

global $dbs;

$_q = $dbs->query("SELECT * FROM mst_custom_field WHERE `primary_table`='member'");
if(isset($_q->num_rows)){
	while($_d = $_q->fetch_assoc()){
		$dbfield[] = $_d;
		
		if (!Schema::hasColumn('member_custom', $_d['dbfield']))
		{
			$type = $_d['type']=='date'?'date':($_d['type']=='numeric'?['number', 11]:'text');
			Schema::table('member_custom', function(Blueprint $table) use($_d,$type) {
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
		$member_custom_fields = $dbfield;
	}
}