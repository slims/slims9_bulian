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

#var_dump($_SESSION);

// check if member already login
if (!$is_member_login) {
    header ("location:index.php");
} else {
    $profile = NULL;
    $profile .= '<table width="100%" cellpadding="5" cellspacing="0">
        <tbody>
            <tr>
                <td width="90%">
                    <table width="100%" cellpadding="5" cellspacing="0">
                        <tbody>
                            <tr>
                                <td width="15%"><strong>Member Name</strong></td>
                                <td width="30%">'.$_SESSION['m_name'].'</td>
                                <td width="15%"><strong>Member ID</strong></td>
                                <td width="30%">'.$_SESSION['mid'].'</td>
                            </tr>
                            <tr>
                                <td width="15%"><strong>Member Email</strong></td>
                                <td width="30%">'.$_SESSION['m_email'].'</td>
                                <td width="15%"><strong>Member Type</strong></td>
                                <td width="30%">'.$_SESSION['m_member_type'].'</td>
                            </tr>
                            <tr>
                                <td width="15%"><strong>Register Date</strong></td>
                                <td width="30%">'.$_SESSION['m_register_date'].'</td>
                                <td width="15%"><strong>Expiry Date</strong></td>
                                <td width="30%">'.$_SESSION['m_expire_date'].'</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <td width="10%">
                    <!--<img src="./lib/minigalnano/createthumb.php?filename=images/persons/'.$_SESSION['m_image'].'&width=100" />-->
                </td>
            </tr>
        </tbody>
    </table>';

    /* Current Loan History - start */
    function currentLoanList($num_recs_show = 1000000)
    {
        global $dbs;
        require SIMBIO.'simbio_UTILS/simbio_date.inc.php';
        $sql_current_loan = 'SELECT l.member_id, l.item_code, b.title, l.loan_date, l.due_date FROM loan AS l ';
        $sql_current_loan .= 'LEFT JOIN item AS i ON l.item_code=i.item_code ';
        $sql_current_loan .= 'LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id ';
        $sql_current_loan .= sprintf('WHERE l.member_id=\'%s\' AND l.is_lent=1 AND is_return=0', $_SESSION['mid']);
        $qry_current_loan = $dbs->query($sql_current_loan);
        $_result = '<table width="100%" cellpadding="5" cellspacing="0">';
        $_result .= '<tr>';
        $_result .= '<td><b>Item Code</b></td>';
        $_result .= '<td><b>Title</b></td>';
        $_result .= '<td><b>Loan Date</b></td>';
        $_result .= '<td><b>Due Date</b></td>';
        $_result .= '</tr>';
        $_curr_date = date('Y-m-d');
        #echo $_curr_date;
        while ($row = $qry_current_loan->fetch_row()) {
            #var_dump($row);
            $_result .= '<tr>';
            $_result .= '<td>'.$row[1].'</td>';
            $_result .= '<td>'.$row[2].'</td>';
            $_result .= '<td>'.$row[3].'</td>';
            if (simbio_date::compareDates($row[4], $_curr_date) == $_curr_date) {
                $_result .= '<td><strong style="color: #f00;">'.$row[4].' '.__('OVERDUED').'</strong></td>';
            } else {
                $_result .= '<td>'.$row[4].'</td>';
            }
            $_result .= '</tr>';
        }
        $_result .= '</table>';
        return $_result;
    }
    /* Current Loan History - end */

    /* Past Loan History - start */
    function pastLoanList($num_recs_show = 1000000)
    {
        global $dbs;
        require_once SIMBIO.'simbio_UTILS/simbio_date.inc.php';
        $sql_past_loan = 'SELECT l.member_id, l.item_code, b.title, l.loan_date, l.due_date, l.return_date FROM loan AS l ';
        $sql_past_loan .= 'LEFT JOIN item AS i ON l.item_code=i.item_code ';
        $sql_past_loan .= 'LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id ';
        $sql_past_loan .= sprintf('WHERE l.member_id=\'%s\' AND l.is_lent=1 AND is_return=1', $_SESSION['mid']);
        #echo $sql_past_loan; die();
        $qry_past_loan = $dbs->query($sql_past_loan);
        $_result = '<table width="100%" cellpadding="5" cellspacing="0">';
        $_result .= '<tr>';
        $_result .= '<td><b>Item Code</b></td>';
        $_result .= '<td><b>Title</b></td>';
        $_result .= '<td><b>Loan Date</b></td>';
        $_result .= '<td><b>Due Date</b></td>';
        $_result .= '<td><b>Returned Date</b></td>';
        $_result .= '</tr>';
        $_curr_date = date('Y-m-d');
        #echo $_curr_date;
        while ($row = $qry_past_loan->fetch_row()) {
            #var_dump($row);
            $_result .= '<tr>';
            $_result .= '<td>'.$row[1].'</td>';
            $_result .= '<td>'.$row[2].'</td>';
            $_result .= '<td>'.$row[3].'</td>';
            $_result .= '<td>'.$row[4].'</td>';
            $_result .= '<td>'.$row[5].'</td>';
            #if (simbio_date::compareDates($row[4], $_curr_date) == $_curr_date) {
            #    $_result .= '<td><strong style="color: #f00;">'.$row[4].' '.__('OVERDUED').'</strong></td>';
            #} else {
            #    $_result .= '<td>'.$row[4].'</td>';
            #}
            $_result .= '</tr>';
        }
        $_result .= '</table>';
        return $_result;
    }
    /* Past Loan History - end */


    // show all
    $download = NULL;
    $download .= $profile;
    $download .= '<h3 class="memberInfoHead">'.__('Your Current Loan').'</h3>'."\n";
    $download .= currentLoanList();
    $download .= '<h3 class="memberInfoHead">'.__('Your Past Loan').'</h3>'."\n";
    $download .= pastLoanList();
    #$download .= pastLoanList();

    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=".$_SESSION['mid']."_all_loan_report.html");
    header("Content-Type: text/html");
    #header("Content-Transfer-Encoding: binary");

?>

<?php

    echo $download;
    exit();
}
