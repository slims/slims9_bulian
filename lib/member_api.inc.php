<?php
/**
 * API class
 * A Collection of API static utility methods
 *
 * Copyright (C) 2016  Hendro Wicaksono (hendrowicaksono@gmail.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class membershipApi
{
  public static function getMembershipType($obj_db)
  {
    $sMtype = 'SELECT * FROM mst_member_type';
    $qMtype = $obj_db->query($sMtype);
    $_return = array ();
    $_return[0]['member_type_name'] = 'All';

    if (!$obj_db->errno) {
      $i = 0;
      while ($rMtype = $qMtype->fetch_assoc()) {
        #$_return[$i]['member_type_id'] = $rMtype['member_type_id'];
        $member_type_id = $rMtype['member_type_id'];
        #$_return[$i]['member_type_name'] = $rMtype['member_type_name'];
        $_return[$member_type_id]['member_type_name'] = $rMtype['member_type_name'];
        $i++;
      }
    }
    #return api::to_object($_return);
    return ($_return);
  }

}
