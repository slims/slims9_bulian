<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some ajax security patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

/* Bibliographic items listing */

// key to authenticate
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
}

// required file
require '../../sysconfig.inc.php';

if (isset($_POST['ajaxsec_user'])) {
    $ajaxsec_user = $_POST['ajaxsec_user'];
}

if (isset($_POST['ajaxsec_passwd'])) {
    $ajaxsec_passwd = $_POST['ajaxsec_passwd'];
}

if (($ajaxsec_user == $sysconf['ajaxsec_user']) AND ($ajaxsec_passwd == $sysconf['ajaxsec_passwd'])) {
    if ($sysconf['ajaxsec_ip_enabled'] == '1') {
		$server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
        if ($server_addr == $sysconf['ajaxsec_ip_allowed']) {
            die();
        }
    }
    if (isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $copy_q = $dbs->query('SELECT i.item_code, i.call_number, loc.location_name, stat.*, i.site FROM item AS i
            LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id
            LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id
            WHERE i.biblio_id='.$id);
        if ($copy_q->num_rows < 1) {
            echo '<strong style="color: red; font-weight: bold;">'.__('There is no item/copy for this title yet').'</strong>';
        } else {
            echo '<table width="100%" class="itemList" cellpadding="3" cellspacing="0">';
            while ($copy_d = $copy_q->fetch_assoc()) {
                // check if this collection is on loan
                $loan_stat_q = $dbs->query('SELECT due_date FROM loan AS l
                    LEFT JOIN item AS i ON l.item_code=i.item_code
                    WHERE l.item_code=\''.$copy_d['item_code'].'\' AND is_lent=1 AND is_return=0');
                echo '<tr>';
                echo '<td width="10%"><strong>'.$copy_d['item_code'].'</strong></td>';
                echo '<td width="20%">'.$copy_d['call_number'].'</td>';
                echo '<td width="40%">'.$copy_d['location_name'];
                if (trim($copy_d['site']) != "") {
                    echo ' ('.$copy_d['site'].')';
                }
                echo '</td>';
                echo '<td width="30%">';
                /* DEPRECATED
                $_rules = @unserialize($copy_d['rules']);
                */
                if ($loan_stat_q->num_rows > 0) {
                    $loan_stat_d = $loan_stat_q->fetch_row();
                    echo '<strong width="50%" style="color: red;">'.__('Currently On Loan (Due on').date($sysconf['date_format'], strtotime($loan_stat_d[0])).')</strong>'; //mfc
                } else if ($copy_d['no_loan']) {
                    echo '<strong width="50%" style="color: red;">'.__('Available but not for loan').' - '.$copy_d['item_status_name'].'</strong>';
                } else {
                    echo '<strong width="50%" style="color: navy;">'.__('Available').(trim($copy_d['item_status_name'])?' - '.$copy_d['item_status_name']:'').'</strong>';
                }
                $loan_stat_q->free_result();
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
}
