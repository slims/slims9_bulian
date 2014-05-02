<?php
/**
 *
 * Download CSV for member loan history
 * Copyright (C) 2009  Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * Based on member.inc.php
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

// required file
require LIB.'member_logon.inc.php';
// check if member already logged in
$is_member_login = utility::isMemberLogin();


// check if member already login
if (!$is_member_login) {
    header ("location:index.php");
} else {

    /* Experimental Loan History - start */
    function showLoanList($num_recs_show = 1000000)
    {
        global $dbs;
        require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
        require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
        require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
        require SIMBIO.'simbio_UTILS/simbio_date.inc.php';

        // table spec
        $_table_spec = 'loan AS l
            LEFT JOIN member AS m ON l.member_id=m.member_id
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

        // create datagrid
        $_loan_list = new simbio_datagrid();
        $_loan_list->disable_paging = true;
        $_loan_list->table_ID = 'loanlist';
        $_loan_list->setSQLColumn('l.item_code AS \''.__('Item Code').'\'',
            'b.title AS \''.__('Title').'\'',
            'l.loan_date AS \''.__('Loan Date').'\'',
            'l.due_date AS \''.__('Due Date').'\'');
        $_loan_list->setSQLorder('l.loan_date DESC');
        $_criteria = sprintf('m.member_id=\'%s\' AND l.is_lent=1 AND is_return=0 ', $_SESSION['mid']);
        $_loan_list->setSQLCriteria($_criteria);

        /* callback function to show overdue */
        function showOverdue($obj_db, $array_data)
        {
            $_curr_date = date('Y-m-d');
            if (simbio_date::compareDates($array_data[3], $_curr_date) == $_curr_date) {
                #return '<strong style="color: #f00;">'.$array_data[3].' '.__('OVERDUED').'</strong>';
            } else {
                return $array_data[3];
            }
        }

        // modify column value
        $_loan_list->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberLoanList" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanListInfo">'.$_loan_list->num_rows.' '.__('Current Loan item(s)').'</div>'."\n".$_result;
        return $_result;
    }
    /* Experimental Loan History - end */


    // show all
    #echo '<h3 class="memberInfoHead">'.__('Your Current Loan').'</h3>'."\n";
    #echo showLoanList();
    $download = '<h3 class="memberInfoHead">'.__('Your Current Loan').'</h3>'."\n";
    $download .= showLoanList();

    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=current_loan.html");
    header("Content-Type: text/html");
    #header("Content-Transfer-Encoding: binary");
    echo $download;
    exit();
}
