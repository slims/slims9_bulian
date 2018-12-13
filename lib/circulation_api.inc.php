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

class circapi
{
  public static function loan_load($obj_db, $member_id, $onloan = FALSE)
  {
    $s_loa = 'SELECT * ';
    $s_loa .= 'FROM loan AS l ';
    $s_loa .= 'WHERE l.member_id=\''.$member_id.'\'';
    if ($onloan) {
      $s_loa .= ' AND l.is_return=\'0\')';
    }
    $q_loa = $obj_db->query($s_loa);
    $_return = NULL;
    if (!$obj_db->errno) {
      $i = 0;
      while ($r_loa = $q_loa->fetch_assoc()) {
        $_return[$i]['loan_id'] = $r_loa['loan_id'];
        $_return[$i]['item_code'] = $r_loa['item_code'];
        $_return[$i]['member_id'] = $r_loa['member_id'];
        $_return[$i]['loan_date'] = $r_loa['loan_date'];
        $_return[$i]['due_date'] = $r_loa['due_date'];
        $_return[$i]['renewed'] = $r_loa['renewed'];
        $_return[$i]['loan_rules_id'] = $r_loa['loan_rules_id'];
        $_return[$i]['actual'] = $r_loa['actual'];
        $_return[$i]['is_lent'] = $r_loa['is_lent'];
        $_return[$i]['is_return'] = $r_loa['is_return'];
        $_return[$i]['return_date'] = $r_loa['return_date'];
        $i++;
      }
    }
    return api::to_object($_return);
  }

  public static function loan_extended($obj_db, $member_id, $loan_id)
  {
    $_sql_loaninfo = 'SELECT l.* FROM loan AS l
      WHERE loan_id=\''.$loan_id.'\'
      ';
    $_stmt_loaninfo = $obj_db->query($_sql_loaninfo);
    $_count_loaninfo = mysqli_num_rows($_stmt_loaninfo);
    if ($_count_loaninfo === 1) {
      while($row_loaninfo = $_stmt_loaninfo->fetch_assoc()) {
        $_loan_rules_id = $row_loaninfo['loan_rules_id'];
      }
    }
    $_today = date('Y-m-d');
    if ($_loan_rules_id === '0') {
      $_sql_llist = ' SELECT l.*, m.*, i.*, b.*, mmt.* 
        FROM loan AS l, member AS m, item AS i, biblio AS b, mst_member_type AS mmt
        WHERE
        l.member_id=m.member_id
        AND l.item_code=i.item_code 
        AND i.biblio_id=b.biblio_id
        AND m.member_type_id=mmt.member_type_id
        AND is_lent=1
        AND is_return=0
        AND l.loan_id=\''.$loan_id.'\'
        AND l.renewed < mmt.reborrow_limit 
        AND m.expire_date > '.$_today.'
        AND l.member_id=\''.$member_id.'\'';
    } else {
      $_sql_llist = ' SELECT l.*, m.*, i.*, b.*, mlr.* 
        FROM loan AS l, member AS m, item AS i, biblio AS b, mst_loan_rules AS mlr
        WHERE
        l.member_id=m.member_id
        AND l.item_code=i.item_code 
        AND i.biblio_id=b.biblio_id
        AND is_lent=1
        AND is_return=0
        AND l.loan_id=\''.$_POST['loan_id'].'\'
        AND l.renewed < mlr.reborrow_limit 
        AND m.expire_date > '.$vars['global']['today'].'
        AND l.member_id=\''.$_SESSION['member_id'].'\'';
    }
    $_stmt_llist = $obj_db->query($_sql_llist);
    $_count_llist = mysqli_num_rows($_stmt_llist);
    if ($_count_llist === 1) {
      while($row = $_stmt_llist->fetch_assoc()) {
        $_old_dd = $row['due_date'];
        $_loan_periode = $row['loan_periode'];
        $_renewed = $row['renewed'];
        $_fine_each_day = $row['fine_each_day'];
        $_member_id = $row['member_id'];
        $_title = $row['title'];
        $_item_code = $row['item_code'];
      }
      $_new_dd = date('Y-m-d', strtotime('+'.$_loan_periode.' day'));
      $_new_dd_name = strtolower(date('D', strtotime('+'.$_loan_periode.' day')));
      do {
        $_sql_holiday = 'SELECT * FROM holiday WHERE holiday_date=\''.$_new_dd.'\'';
        $_stmt_holiday = $obj_db->query($_sql_holiday);
        $_counter = mysqli_num_rows($_stmt_holiday);
        $_sql_aholiday = 'SELECT * FROM holiday WHERE holiday_date IS NULL AND holiday_dayname=\''.$_new_dd_name.'\'';
        $_stmt_aholiday = $obj_db->query($_sql_aholiday);
        $_countera = mysqli_num_rows($_stmt_aholiday);
        if ( ($_counter > 0) OR ($_countera > 0) ){
          $_loan_periode = $_loan_periode + 1;
          $_new_dd = date('Y-m-d', strtotime('+'.$_loan_periode.' day'));
          $_new_dd_name = strtolower(date('D', strtotime('+'.$_loan_periode.' day')));
        }
        $i = 0;
      } while ( ($_counter > 0) OR ($_countera > 0) );
      $_renewed = $_renewed + 1;
      $_sql_doextend = 'UPDATE loan SET due_date=\''.$_new_dd.'\', renewed=\''.$_renewed.'\' WHERE loan_id='.$loan_id;
      $_stmt_doextend = $obj_db->query($_sql_doextend);
      if ($_today > $_old_dd) {
        $_uts_duedate = DateTime::createFromFormat('Y-m-d', $_old_dd);
        $uts_duedate = (int) $_uts_duedate->format('U');
        $_uts_today = DateTime::createFromFormat('Y-m-d', $_today);
        $uts_today = (int) $_uts_today->format('U');
        $late_days = ($uts_today - $uts_duedate) / 86400;
        $total_fines = $late_days * $_fine_each_day;
        $_sql_fines = 'INSERT INTO fines VALUES (NULL, \''.$_today.'\', \''.$member_id.'\', \''.$total_fines.'\', \'0\', \'Overdue fines for item '.$_item_code.'\')';
        $_stmt_fines = $obj_db->query($_sql_fines);
      }
      if ($_stmt_doextend) {
        return TRUE;
      }
    }
    return FALSE;
  }

  public static function is_any_active_loanrules($obj_db, $loan_rules_id)
  {
    $s_lr = 'SELECT l.loan_id ';
    $s_lr .= 'FROM loan AS l ';
    $s_lr .= 'WHERE l.loan_rules_id=\''.$loan_rules_id.'\'';
    $s_lr .= ' AND ';
    $s_lr .= 'l.is_return=\'0\'';
    $s_lr .= ' AND ';
    $s_lr .= 'l.is_lent=\'1\'';
    $q_lr = $obj_db->query($s_lr);
    $c_lr = mysqli_num_rows($q_lr);
    if ($c_lr > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  public static function is_any_active_membershipType($obj_db, $member_type_id)
  {
    $s_lr = 'SELECT mmt.member_type_id, m.member_id, l.loan_id ';
    $s_lr .= 'FROM mst_member_type AS mmt, member AS m, loan AS l ';
    $s_lr .= 'WHERE ';
    $s_lr .= 'mmt.member_type_id=m.member_type_id ';
    $s_lr .= ' AND ';
    $s_lr .= 'm.member_id=l.member_id ';
    $s_lr .= ' AND ';
    $s_lr .= 'mmt.member_type_id=\''.$member_type_id.'\'';
    $s_lr .= ' AND ';
    $s_lr .= 'l.is_return=\'0\'';
    $s_lr .= ' AND ';
    $s_lr .= 'l.is_lent=\'1\'';
    $q_lr = $obj_db->query($s_lr);
    $c_lr = mysqli_num_rows($q_lr);
    if ($c_lr > 0) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}
