<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/* Stock Take */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-stocktake');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

if (isset($_POST['resync'])) {
    // update stock item data against bibliographic and item data
    $update_q = $dbs->query('UPDATE stock_take_item AS sti
        LEFT JOIN item AS i ON sti.item_code=i.item_code
            LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
            LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
                LEFT JOIN mst_gmd AS g ON b.gmd_id=g.gmd_id
        SET sti.title=b.title, sti.gmd_name=g.gmd_name,
            sti.classification=b.classification, sti.call_number=b.call_number,
            sti.coll_type_name=ct.coll_type_name');
    if (!$dbs->error) {
        $aff_rows = $dbs->affected_rows;
        // record to log
        utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'stock_take', 'Stock Take Re-Synchronization');
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#resyncInfo\').html(\''.$aff_rows.' Stock Take\\\'s Item Data Successfully Synchronized!\');'."\n";
        echo 'parent.$(\'#resyncInfo\').css( {\'display\': \'block\'} );'."\n";
        echo '</script>';
    } else {
        echo '<script type="text/javascript">'."\n";
        echo 'parent.$(\'#resyncInfo\').html(\'Stock Take\\\'s Item Data FAILED to Synchronized!\');'."\n";
        echo 'parent.$(\'#resyncInfo\').css( {\'color\': \'red\', \'display\': \'block\'} );'."\n";
        echo '</script>';
    }
    exit();
}

echo '<div class="infoBox">'.__('Re-synchronize will only update current stock take\'s item data. It won\'t update any new bibliographic or item data that were inserted in the middle of stock take proccess')."\n";
echo '<hr size="1" />'."\n";
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post" target="resyncSubmit">'."\n";
echo '<input type="submit" name="resync" value="'.__('Resynchronize Now').'" class="btn btn-default" />'."\n";
echo '</form>'."\n";
echo '<iframe name="resyncSubmit" style="width: 0; height: 0; visibility: hidden;"></iframe>'."\n";
echo '</div>';
echo '<div id="resyncInfo" style="display: none; padding: 5px; font-weight: bold; border: 1px solid #999;">&nbsp;</div>';
