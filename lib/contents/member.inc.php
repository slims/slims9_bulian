<?php
/**
 *
 * Member Area/Information
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 * Patched by Hendro Wicaksono (hendrowicaksono@yahoo.com)
 * Patched by Waris Agung Widodo (ido.alit@gmail.com)
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

use SLiMS\Url;
use SLiMS\DB;
use SLiMS\Json;
use SLiMS\Captcha\Factory as Captcha;
use Volnix\CSRF\CSRF;

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

if ($sysconf['baseurl'] != '') {
    $_host = $sysconf['baseurl'];
    header("Access-Control-Allow-Origin: $_host", FALSE);
}

// IP based access limitation
do_checkIP('opac');
do_checkIP('opac-member');

// Required flie
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
require LIB . 'member_logon.inc.php';

// Captcha initialize
$captcha = Captcha::section('memberarea');

// check if member already logged in
$is_member_login = utility::isMemberLogin();

$info = __('Welcome to Member\'s Area where you can view your current loan information and view your membership status.');
if(isset($_SESSION['info'])){
    $info .= PHP_EOL.'<div class="alert alert-'.$_SESSION['info']['status'].'">'.$_SESSION['info']['data'].'</div>';
    unset($_SESSION['info']);
    $_SESSION['m_mark_biblio'] = array();
}

// member's password changing flags
define('CURR_PASSWD_WRONG', -1);
define('PASSWD_NOT_MATCH', -2);
define('CANT_UPDATE_PASSWD', -3);

// if member is logged out
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    // write log
    utility::writeLogs($dbs, 'member', $_SESSION['email'], 'Login', $_SESSION['member_name'] . ' Log Out from address ' . ip());
    // completely destroy session cookie
    simbio_security::destroySessionCookie(null, MEMBER_COOKIES_NAME, SWB, false);
    redirect()->withHeader([
        ['Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0'],
        ['Expires', 'Sat, 26 Jul 1997 05:00:00 GMT'],
        ['Pragma', 'no-cache']
    ])->to('?p=member');
}

// if there is member login action
if (isset($_POST['logMeIn']) && !$is_member_login) {
    if (!CSRF::validate($_POST)) {
        session_unset();
        redirect()->withMessage('csrf_failed', __('Invalid login form!'))->back();
    }
    $username = trim(strip_tags($_POST['memberID']));
    $password = trim(strip_tags($_POST['memberPassWord']));
    
    // check if username or password is empty
    if (!$username OR !$password) redirect()->withMessage('empty_field', __('Please fill your Username and Password to Login!'))->back();
    
    # <!-- Captcha form processing - start -->
    if ($captcha->isSectionActive() && $captcha->isValid() === false) {
        // set error message
        $message = isDev() ? $captcha->getError() : __('Wrong Captcha Code entered, Please write the right code!'); 
        // What happens when the CAPTCHA was entered incorrectly
        session_unset();
        redirect()->withMessage('captchaInvalid', $message)->back();
    }
    # <!-- Captcha form processing - end -->

    // regenerate session ID to prevent session hijacking
    session_regenerate_id(true);

    // create logon class instance
    $logon = new member_logon($username, $password, $sysconf['auth']['member']['method']);
    if ($sysconf['auth']['member']['method'] === 'LDAP') $ldap_configs = $sysconf['auth']['member'];

    if ($logon->valid($dbs)) {
        // write log
        utility::writeLogs($dbs, 'member', $username, 'Login', sprintf(__('Login success for member %s from address %s'),$username,ip()));
        if (isset($_GET['destination']) && Url::isValid($_GET['destination']) && Url::isSelf($_GET['destination'])) {
            redirect($_GET['destination']);
        } else {
            redirect()->toPath('member');
        }
        exit();
    } else {
        // write log
        utility::writeLogs($dbs, 'member', $username, 'Login', sprintf(__('Login FAILED for member %s from address %s'),$username,ip()));
        // message
        //simbio_security::destroySessionCookie($msg, MEMBER_COOKIES_NAME, SWB, false);
        CSRF::generateToken();
        redirect()->withMessage('wrong_password', __('Login FAILED! Wrong Member ID or password!'))->to('?p=member');
    }
}

// biblio basket add process
if ($is_member_login) {
    if (isset($_POST['biblio'])) {
        if (!is_array($_POST['biblio']) && is_scalar($_POST['biblio'])) {
            $_tmp_biblio = $_POST['biblio'];
            unset($_POST['biblio']);
            $_POST['biblio'][] = $_tmp_biblio;
        }
        // check reserve limit
        if ((count($_SESSION['m_mark_biblio']) + count($_POST['biblio'])) > $sysconf['max_biblio_mark']) {
            $status = false;
            $message = 'Maximum ' . $sysconf['max_biblio_mark'] . ' titles can be added to basket!';
            $info = '<span style="font-size: 120%; font-weight: bold; color: red;">' . $message . '</span>';
        } else {
            foreach ($_POST['biblio'] as $biblio) {
                $biblio = (integer)$biblio;
                $_SESSION['m_mark_biblio'][$biblio] = $biblio;
            }
            $status = true;
            $message = __('Title has been added in the basket.');
        }

        if (isset($_POST['callback']) && $_POST['callback'] === 'json') {
            $res = [
                'status' => $status,
                'message' => $message,
                'count' => count($_SESSION['m_mark_biblio'])
            ];
            exit(Json::stringify($res)->withHeader());
        }
    }
    if (isset($_POST['bookmark_id']))
    {
        try {
            // switch to delete process
            if (isset($_POST['delete_bookmark']))
            {

                DB::getInstance()
                    ->prepare('DELETE FROM `biblio_mark` WHERE `biblio_id` = ? AND `member_id` = ?')
                    ->execute([$_POST['bookmark_id'], $_SESSION['mid']]);
                unset($_SESSION['bookmark'][$_POST['bookmark_id']]);
                exit(Json::stringify(['status' => true, 'message' => __('Data has been deleted')])->withHeader());    
            }

            // input biblio data to database
            DB::getInstance()
                ->prepare('INSERT IGNORE INTO `biblio_mark` SET `biblio_id` = ?, `member_id` = ?, `id` = ?')
                ->execute([$_POST['bookmark_id'], $_SESSION['mid'], md5($_POST['bookmark_id'] . $_SESSION['mid'])]);

            $_SESSION['bookmark'][$_POST['bookmark_id']] = $_POST['bookmark_id'];

            exit(Json::stringify(['status' => true, 'message' => __('Data has been saved'), 'label' => __('Bookmarked')])->withHeader());
        } catch (PDOException $e) {
            exit(Json::stringify(['status' => false, 'message' => isDev() ? $e->getMessage() : __('Data failed saved')])->withHeader());   
        } catch (Exception $e) {
            exit(Json::stringify(['status' => false, 'message' => isDev() ? $e->getMessage() : __('Data failed saved')])->withHeader());   
        } 
    }
} else {
    if (isset($_POST['callback']) && $_POST['callback'] === 'json') {
        $res = [
            'status' => false,
            'message' => __('Please, login first!'),
            'count' => 0
        ];
        http_response_code(401);
        exit(Json::stringify($res)->withHeader());
    }
}

// biblio basket remove process
if (isset($_GET['rm_biblio'])) {
    if (!is_array($_GET['rm_biblio']) && is_scalar($_GET['rm_biblio'])) {
        $_tmp_biblio = $_GET['rm_biblio'];
        unset($_GET['rm_biblio']);
        $_GET['rm_biblio'][] = $_tmp_biblio;
    }
    foreach ($_GET['rm_biblio'] as $biblio) {
        $biblio = (integer)$biblio;
        unset($_SESSION['m_mark_biblio'][$biblio]);
    }
}

// biblio basket item removal process
if (isset($_POST['basketRemove']) && isset($_POST['basket']) && count($_POST['basket']) > 0) {
    foreach ($_POST['basket'] as $basket_item) {
        unset($_SESSION['m_mark_biblio'][$basket_item]);
    }
}

// biblio basket clear process
if (isset($_POST['clear_biblio'])) {
    $_SESSION['m_mark_biblio'] = array();
}

if ($is_member_login) :

    if (filter_var($_SESSION['m_image'], FILTER_VALIDATE_URL)) {
        $member_image_url = $_SESSION['m_image'];
    } else {
        $member_image = $_SESSION['m_image'] && file_exists(IMGBS . 'persons/' . $_SESSION['m_image']) ? $_SESSION['m_image'] : 'person.png';
        $member_image_url = './images/persons/' . $member_image;
    }

    // require file
    require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
    require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
    require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
    require SIMBIO . 'simbio_UTILS/simbio_date.inc.php';

    /*
       * Function to show membership detail of logged in member
       *
       * @return      string
       */
    function showMemberDetail()
    {
        // show the member information
        $_detail = '<table class="memberDetail table table-striped" cellpadding="5" cellspacing="0">' . "\n";
        // member notes and pending information
        if ($_SESSION['m_membership_pending'] || $_SESSION['m_is_expired']) {
            $_detail .= '<tr>' . "\n";
            $_detail .= '<td class="key alterCell" width="15%"><strong>Notes</strong></td><td class="value alterCell2" colspan="3">';
            if ($_SESSION['m_is_expired']) {
                $_detail .= '<div style="color: #f00;">' . __('Your Membership Already EXPIRED! Please extend your membership.') . '</div>';
            }
            if ($_SESSION['m_membership_pending']) {
                $_detail .= '<div style="color: #f00;">' . __('Membership currently in pending state, no loan transaction can be made yet.') . '</div>';
            }
            $_detail .= '</td>';
            $_detail .= '</tr>' . "\n";
        }
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Name') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_name'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member ID') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['mid'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Email') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_email'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Member Type') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_member_type'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Register Date') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_register_date'] . '</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Expiry Date') . '</strong></td><td class="value alterCell2" width="30%">' . $_SESSION['m_expire_date'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '<tr>' . "\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>' . __('Institution') . '</strong></td>'
            . '<td class="value alterCell2" colspan="3">' . $_SESSION['m_institution'] . '</td>';
        $_detail .= '</tr>' . "\n";
        $_detail .= '</table>' . "\n";


        return $_detail;
    }

    /*
       * Function to show member change password form
       *
       * @return      string
       */
    function changePassword()
    {
        // show the member information
        $_form = '<form id="memberChangePassword" method="post" action="index.php?p=member&sec=my_account">' . "\n";
        $_form .= '<table class="memberDetail table table-striped" cellpadding="5" cellspacing="0">' . "\n";
        $_form .= '<tr>' . "\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>' . __('Current Password') . '</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="currPass" class="form-control" placeholder="Enter current password" /></td>';
        $_form .= '</tr>' . "\n";
        $_form .= '<tr>' . "\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>' . __('New Password') . '</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="newPass" class="form-control" placeholder="Enter new password" /></td>';
        $_form .= '</tr>' . "\n";
        $_form .= '<tr>' . "\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>' . __('Confirm Password') . '</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="newPass2" class="form-control" placeholder="Confirm new password" /></td>';
        $_form .= '</tr>' . "\n";
        $_form .= '<tr>' . "\n";
        $_form .= '<td class="alterCell2" colspan="2"><input class="btn btn-primary" type="submit" id="loginButton" name="changePass" value="' . __('Change Password') . '" /></td>';
        $_form .= '</tr>' . "\n";
        $_form .= '</table>' . "\n";
        $_form .= '</form>' . "\n";

        return $_form;
    }

    /*
       * Function to process member's password changes
       *
       * @param       string      $str_curr_pass = member's current password
       * @param       string      $str_new_pass = member's new password request
       * @param       string      $str_conf_new_pass = member's new password request confirmation
       * @return      boolean     true on success, false on failed
       */
    function procChangePassword($str_curr_pass, $str_new_pass, $str_conf_new_pass)
    {
        global $dbs;
        // get hash from db
        $_str_pass_sql = sprintf('SELECT mpasswd FROM member
            WHERE member_id=\'%s\'', $dbs->escape_string(trim($_SESSION['mid'])));
        $_str_pass_q = $dbs->query($_str_pass_sql);
        $_str_pass_d = $_str_pass_q->fetch_row();
        $verified = password_verify($str_curr_pass, $_str_pass_d[0]);
        // current password checking
        // $_sql_pass_check = sprintf('SELECT member_id FROM member
        //     WHERE mpasswd=MD5(\'%s\') AND member_id=\'%s\'',
        //     $dbs->escape_string(trim($str_curr_pass)), $dbs->escape_string(trim($_SESSION['mid'])));
        // $_pass_check = $dbs->query($_sql_pass_check);
        if ($verified) {
            $str_new_pass = trim($str_new_pass);
            $str_conf_new_pass = trim($str_conf_new_pass);
            // password confirmation check
            if ($str_new_pass && $str_conf_new_pass && ($str_new_pass === $str_conf_new_pass)) {
                $_new_password = password_hash($str_conf_new_pass, PASSWORD_BCRYPT);
                $_sql_update_mpasswd = sprintf('UPDATE member SET mpasswd=\'%s\'
                    WHERE member_id=\'%s\'', $dbs->escape_string($_new_password), $dbs->escape_string(trim($_SESSION['mid'])));
                @$dbs->query($_sql_update_mpasswd);
                if (!$dbs->error) {
                    return true;
                } else {
                    return CANT_UPDATE_PASSWD;
                }
            } else {
                return PASSWD_NOT_MATCH;
            }
        } else {
            return CURR_PASSWD_WRONG;
        }
    }

    /*
       * Function to show list of logged in member loan
       *
       * @param       int         number of loan records to show
       * @return      string
       */
    function showLoanList($num_recs_show = 20)
    {
        global $dbs;

        // table spec
        $_table_spec = 'loan AS l
            LEFT JOIN member AS m ON l.member_id=m.member_id
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

        // create datagrid
        $_loan_list = new simbio_datagrid();
        $_loan_list->disable_paging = true;
        $_loan_list->table_ID = 'loanlist';
        $_loan_list->setSQLColumn('l.item_code AS \'' . __('Item Code') . '\'',
            'b.title AS \'' . __('Title') . '\'',
            'l.loan_date AS \'' . __('Loan Date') . '\'',
            'l.due_date AS \'' . __('Due Date') . '\'');
        $_loan_list->setSQLorder('l.loan_date DESC');
        $_criteria = sprintf('m.member_id=\'%s\' AND l.is_lent=1 AND is_return=0 ', $_SESSION['mid']);
        $_loan_list->setSQLCriteria($_criteria);

        // modify column value
        $_loan_list->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberLoanList table table-striped" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanListInfo my-3">' . $_loan_list->num_rows . ' ' . __('item(s) currently on loan') . ' | <a href="?p=download_current_loan" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Download All Current Loan') . '</a></div>' . "\n" . $_result;
        return $_result;
    }

    function showBookmarkList($num_recs_show = 20)
    {
        global $dbs;

        // table spec
        $_table_spec = 'biblio_mark AS bm
            INNER JOIN biblio AS b ON b.biblio_id=bm.biblio_id';
        // create datagrid
        $_mark_list = new simbio_datagrid();
        $_mark_list->disable_paging = false;
        $_mark_list->table_ID = 'loanlist';
        $_mark_list->setSQLColumn('b.title AS \'' . __('Title') . '\'', 'bm.created_at AS \'' . __('Marked At') . '\'','bm.biblio_id AS \'' . __('Action') . '\'');
        $_mark_list->setSQLorder('bm.created_at DESC');
        // $_mark_list->invisible_fields = [2];
        $_criteria = sprintf('bm.member_id=\'%s\'', $_SESSION['mid']);
        $_mark_list->setSQLCriteria($_criteria);


        // modify column value
        $_mark_list->modifyColumnContent(0, 'callback{showBookCover}');
        $_mark_list->modifyColumnContent(1, 'callback{showDetailDate}');
        $_mark_list->modifyColumnContent(2, 'callback{showMarkDetail}');
        // set table and table header attributes
        $_mark_list->table_attr = 'align="center" class="memberBookmarkList table table-striped" cellpadding="5" cellspacing="0"';
        $_mark_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_mark_list->using_AJAX = false;
        // return the result
        $_result = $_mark_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanListInfo my-3">' . $_mark_list->num_rows . ' ' . __('title currently on list') . ' </div>' . "\n" . $_result;
        return $_result;
    }

    function showDetailDate($obj_db, $data)
    {
        global $sysconf;
        if (isset($_COOKIE['select_lang'])) $sysconf['default_lang'] = trim(strip_tags($_COOKIE['select_lang']));
        return \Carbon\Carbon::parse($data[1])->locale($sysconf['default_lang'])->isoFormat('dddd, LL');
    }

    function showBookCover($obj_db, $data)
    {
        $author = $obj_db->query('select ma.author_name from biblio_author as ba 
        inner join mst_author as ma on ba.author_id = ma.author_id where ba.biblio_id = ' . $obj_db->escape_string($data[2]));
        $list = [];

        if ($author->num_rows > 0)
        {
            while ($result = $author->fetch_row()) {
                $list[] = $result[0];
            }
        }

        return '<strong><a title="'.__('Click to view detail').'" href="'.Url::getSlimsBaseUri('?p=show_detail&id=' . $data[2]).'">'.$data[0].'</a></strong>
                <br>
                <div class="d-flex flex-row"><span class="text-muted text-sm">'.implode('</span>,<span class="text-muted text-sm">', $list).'</span></div>';
    }

    function showMarkDetail($obj_db, $data)
    {
        return '<button class="btn btn-danger btn-sm deleteBookmark" data-id="' . $data[2] . '"><i class="fa fa-trash"></i></button>';
    }

    /* callback function to show overdue */
    function showOverdue($obj_db, $array_data)
    {
        $_curr_date = date('Y-m-d');
        if (simbio_date::compareDates($array_data[3], $_curr_date) == $_curr_date) {
            return '<strong style="color: #f00;">' . $array_data[3] . ' ' . __('OVERDUED') . '</strong>';
        } else {
            return $array_data[3];
        }
    }

    /* Experimental Loan History - start */
    function showLoanHist($num_recs_show = 20)
    {
        global $dbs;

        // table spec
        $_table_spec = 'loan AS l
            LEFT JOIN member AS m ON l.member_id=m.member_id
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id';

        // create datagrid
        $_loan_hist = new simbio_datagrid();
        $_loan_hist->disable_paging = true;
        $_loan_hist->table_ID = 'loanhist';
        $_loan_hist->setSQLColumn('l.item_code AS \'' . __('Item Code') . '\'',
            'b.title AS \'' . __('Title') . '\'',
            'l.loan_date AS \'' . __('Loan Date') . '\'',
            'l.return_date AS \'' . __('Return Date') . '\'');
        $_loan_hist->setSQLorder('l.loan_date DESC');
        $_criteria = sprintf('m.member_id=\'%s\' AND l.is_lent=1 AND is_return=1 ', $_SESSION['mid']);
        $_loan_hist->setSQLCriteria($_criteria);

        // modify column value
        #$_loan_hist->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_hist->table_attr = 'align="center" class="memberLoanList table table-striped" cellpadding="5" cellspacing="0"';
        $_loan_hist->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_hist->using_AJAX = false;
        // return the result
        $_result = $_loan_hist->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanHistInfo my-3"> &nbsp;' . $_loan_hist->num_rows . ' ' . __('item(s) loan history') . ' | <a href="?p=download_loan_history" class="btn btn-sm btn-outline-primary"><i class="fa fa-download"></i>&nbsp;&nbsp;' . __('Download All Loan History') . '</a></div>' . "\n" . $_result;
        return $_result;
    }

    /*
       * Function to show member collection basket
       *
       * @param       int         number of loan records to show
       * @return      string
       */
    function showBasket($num_recs_show = 20)
    {
        global $dbs;

        // table spec
        $_table_spec = 'biblio AS b';

        // create datagrid
        $_loan_list = new simbio_datagrid();
        $_loan_list->table_ID = 'basket';
        $_loan_list->setSQLColumn('b.biblio_id AS \'' . __('Remove') . '\'', 'b.title AS \'' . __('Title') . '\'');
        $_loan_list->setSQLorder('b.last_update DESC');
        $_criteria = 'biblio_id = 0';
        if (count($_SESSION['m_mark_biblio']) > 0) {
            $_ids = '';
            foreach ($_SESSION['m_mark_biblio'] as $_biblio) {
                $_ids .= (integer)$_biblio . ',';
            }
            $_ids = substr_replace($_ids, '', -1);
            $_criteria = "b.biblio_id IN ($_ids)";
        }
        $_loan_list->setSQLCriteria($_criteria);
        $_loan_list->column_width[0] = '5%';
        $_loan_list->modifyColumnContent(1, 'callback{titleLink}');
        function titleLink($db, $data)
        {
            return '<a target="_blank" href="index.php?p=show_detail&id=' . $data[0] . '">' . $data[1] . '</a>';
        }
        $_loan_list->modifyColumnContent(0, '<input type="checkbox" name="basket[]" class="basketItem" value="{column_value}" />');

        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberBasketList table table-striped" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = '<form name="memberBasketListForm" id="memberBasketListForm" action="index.php?p=member" method="post">' . "\n";
        $_datagrid = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_actions = '<div class="memberBasketAction my-3">';
        $_actions .= '<a href="index.php?p=member&sec=title_basket" class="btn btn-sm btn-primary basket reserve"><i class="fa fa-save"></i>&nbsp;&nbsp;' . __('Reserve title(s) on Basket') . '</a> ';
        $_actions .= '<a href="index.php?p=member&sec=title_basket" class="btn btn-sm btn-secondary basket clearAll" postdata="clear_biblio=1"><i class="fa fa-eraser"></i>&nbsp;&nbsp;' . __('Clear Basket') . '</a> ';
        $_actions .= '<a href="index.php?p=member&sec=title_basket" class="btn btn-sm btn-secondary basket clearOne"><i class="fa fa-trash"></i>&nbsp;&nbsp;' . __('Remove selected title(s) from Basket') . '</a> ';
        $_actions .= '</div>';
        $_result .= '<div class="memberBasketInfo">' . $_loan_list->num_rows . ' ' . __('title(s) on basket') . $_actions . '</div>' . "\n" . $_datagrid;
        $_result .= "\n</form>";

        return $_result;
    }

    /*
       * Function to send reservation e-mail for titles in basket
       *
       * @return      array
       */
    function sendReserveMail()
    {
        global $dbs;

        if (count($_SESSION['m_mark_biblio']) === 0) return ['status' => 'ERROR', 'message' => 'No Titles to reserve'];
        
        $mail = \SLiMS\Mail::to(config('mail.from'), config('mail.from_name'));

        try {
            // additional recipient
            if (is_array(config('mail.add_recipients'))) {
                foreach (config('mail.add_recipients') as $recipients) {
                    \SLiMS\Mail::to($recipients['from'], $recipients['from_name']);
                }
            }

            // Template
            include SB . 'template/reserveMail.php';
            $reserveTemplate = new reserveMail;
            $reserveTemplate->setMinify(true);

            // CC
            $mail->addCC($_SESSION['m_email'], $_SESSION['m_name']);
            $mail->subject('Reservation request from Member ' . $_SESSION['m_name'] . ' (' . $_SESSION['m_email'] . ')')
                 ->loadTemplate($reserveTemplate)
                 ->send();

            // write to system log
            utility::writeLogs($dbs, 'member', isset($_SESSION['mid']) ? $_SESSION['mid'] : '0', 'membership', 'Reservation notification e-mail sent to ' . $_SESSION['m_email'], 'Reservation', 'Add');

            // sent response
            return ['status' => 'SENT', 'message' => 'Reservation notification e-mail sent to ' . $_SESSION['m_email']];
        } catch (Exception $exception) {
            // write to system log
            utility::writeLogs($dbs, 'member', isset($_SESSION['mid']) ? $_SESSION['mid'] : '0', 'membership', 'FAILED to send reservation e-mail to ' . $_SESSION['m_email'] . ' (' . $mail->ErrorInfo . ')');

            return ['status' => 'ERROR', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        }
    }

    function saveReserve($dbs, $sysconf)
    {

        if (count($_SESSION['m_mark_biblio']) > 0) {
            
            // cek dahulu, batas reservasi apakah sudah tercapai?
            if (($check = _isReserveAlowed($dbs)) !== true) return $check;

            $result = [];
            $sql_op = new simbio_dbop($dbs);
            $reserve['member_id'] = $_SESSION['mid'];

            foreach ($_SESSION['m_mark_biblio'] as $_index => $_biblio) {
                $id = (integer)$_biblio;
                $biblio = api::biblio_load($dbs, $id);

                // skip if already reseve this collection
                if(_isAlreadyReserved($dbs, $id)) {
                    $result[] = ['status' => 'SUCCESS', 'message' => sprintf(__('%s already reseved'), $biblio['title'])];
                    unset($_SESSION['m_mark_biblio'][$_index]);
                    continue;
                }

                // cek ketersediaan item,
                if(count($biblio['items'] ?? []) > 0) {
                    
                    if($sysconf['reserve_on_loan_only']) {
                        // ambil secara random dari koleksi yang dipinjam
                        $item_code = _getItemReserveFromLoan($dbs, $id);
                    } else {
                        // Semua item bisa direservasi
                        $item_code = _getItemReserve($dbs, $id);
                    }

                    if(is_null($item_code)) {
                        $result[] = ['status' => 'ERROR', 'message' => sprintf(__('Item for %s is available for loan'), $biblio['title'])];
                    } else {
                        $reserve['biblio_id'] = $id;
                        $reserve['item_code'] = $item_code;
                        $reserve['reserve_date'] = date('Y-m-d H:i:s');
                        if($sql_op->insert('reserve', $reserve)) {
                            $result[] = ['status' => 'SUCCESS', 'message' => sprintf(__('%s reserved successfully'), $biblio['title'])];
                            unset($_SESSION['m_mark_biblio'][$_index]);
                        } else {
                            $debug_message = ENVIRONMENT == 'development' ? $sql_op->error : '';
                            $result[] = ['status' => 'ERROR', 'message' => sprintf(__('Reserve %s failed. '), $biblio['title']) . $debug_message ];
                        }
                    }

                } else {
                    // jika tidak memiliki item, maka tidak dapat direservasi.
                    $result[] = ['status' => 'ERROR', 'message' => sprintf(__('No item available to be reserved for %s'), $biblio['title'])];
                }
                
            }

            return $result;
        } else {
            return array('status' => 'ERROR', 'message' => __('No Titles to reserve'));
        }
    }

    function _isReserveAlowed($dbs) {

        // cek apakah di keanggotaan diijikan untuk reservasi
        if ($_SESSION['m_can_reserve'] == '0') return ['status' => 'ERROR', 'message' => __('Reservation not allowed')];;

        // hitung yang sedang direservasi
        $sql = "SELECT COUNT(reserve_id) FROM reserve WHERE member_id='%s'";
        $query = $dbs->query(sprintf($sql, $_SESSION['mid']));
        $data = $query->fetch_row();
        
        // hitung tinggal berapa kesempatan untuk reservasinya
        return ($data[0]+count($_SESSION['m_mark_biblio'])) > (int)$_SESSION['m_reserve_limit'] ? ['status' => 'ERROR', 'message' => __('Reserve limit reached')] : 
            (count($_SESSION['m_mark_biblio']) > (int)$_SESSION['m_reserve_limit'] ? ['status' => 'ERROR', 'message' => sprintf(__('Maximum reserve limit is %d collection'), (int)$_SESSION['m_reserve_limit'])] : true);
    }

    function _getItemReserve($dbs, $biblio_id)
    {
        $sql = "SELECT item_code FROM item WHERE biblio_id='%s' ORDER BY RAND() ASC LIMIT 1";
        $query = $dbs->query(sprintf($sql, $biblio_id));
        $data = $query->fetch_row();
        return $data[0] ?? null;
    }

    function _getItemReserveFromLoan($dbs, $biblio_id)
    {
        $biblio_id = (int)$biblio_id;
        $sql = "SELECT l.item_code, l.due_date FROM loan AS l WHERE l.is_lent=1 AND l.is_return=0 AND l.item_code IN (SELECT i.item_code FROM item AS i WHERE i.biblio_id = $biblio_id) ORDER BY RAND() ASC LIMIT 1";
        $query = $dbs->query($sql);
        $data = $query->fetch_row();
        return $data[0] ?? null;
    }

    function _isAlreadyReserved($dbs, $biblio_id)
    {
        $sql = "SELECT member_id FROM reserve WHERE biblio_id='%s' AND member_id='%s'";
        $query = $dbs->query(sprintf($sql, $biblio_id, $_SESSION['mid']));
        return $query->num_rows > 0;
    }

    // if there is change password request
    if (isset($_POST['changePass']) && $sysconf['auth']['member']['method'] == 'native') {
        $change_pass = procChangePassword($_POST['currPass'], $_POST['newPass'], $_POST['newPass2']);
        if ($change_pass === true) {
            $info = '<span style="font-size: 120%; font-weight: bold; color: #28a745;">' . __('Your password have been changed successfully.') . '</span>';
        } else {
            if ($change_pass === CURR_PASSWD_WRONG) {
                $info = __('Current password entered WRONG! Please insert the right password!');
            } else if ($change_pass === PASSWD_NOT_MATCH) {
                $info = __('Password confirmation FAILED! Make sure to check undercase or uppercase letters!');
            } else {
                $info = __('Password update FAILED! ERROR ON DATABASE!');
            }
            $info = '<span style="font-size: 120%; font-weight: bold; color: red;">' . $info . '</span>';
        }
    }

    // send reserve e-mail
    if (isset($_POST['sendReserve']) && $_POST['sendReserve'] == 1) {
        // Make a notification for librarian or member
        \SLiMS\Plugins::getInstance()->execute('custom_reserve_notification');
        // save reservation to database
        if ($sysconf['reserve_direct_database'] ?? false) {
            header('content-type: application/json');
            echo json_encode(saveReserve($dbs, $sysconf));
            exit;
        } else {
            // by email
            $mail = sendReserveMail();
            if ($mail['status'] != 'ERROR') {
                $_SESSION['info']['data'] = __('Reservation e-mail sent successfully!. please contact librarians for further info.');
                $_SESSION['info']['status'] = 'success';
            } else {
                $_SESSION['info']['data'] = '<span style="font-size: 120%; font-weight: bold; color: red;">'.__(sprintf('Reservation e-mail FAILED to sent with error: %s Please contact administrator!', $mail['message'])).'</span>';
                $_SESSION['info']['status'] = 'danger';
            }
            exit;
        }

    }

    ?>

    <div class="d-flex">
        <div style="width: 16rem;" class="bg-grey-light p-4" id="member_sidebar">
            <div class="p-4">
                <img src="<?= $member_image_url ?>" alt="member photo" class="rounded shadow">
            </div>
            <a href="index.php?p=member&logout=1" class="btn btn-danger btn-block"><i
                        class="fas fa-sign-out-alt mr-2"></i><?php echo __('LOGOUT'); ?></a>
        </div>
        <div class="flex-grow-1 p-4" id="member_content">
            <div class="text-sm text-grey-dark">
                <?php
                if ($_SESSION['m_membership_pending']) :
                    $info = 'Your member status is pending state! Please contact system administrator for more detail.';
                    ?>
                    <i class="fas fa-lock mr-2 text-red"></i>Member status pending
                <?php
                elseif ($_SESSION['m_is_expired']) :
                    $info = 'Your member status is expired state! Please contact system administrator for more detail.';
                    ?>
                    <i class="far fa-calendar-times mr-2 text-red"></i>Member expired
                <?php else: ?>
                    <i class="far fa-user mr-2 text-green"></i><?php echo $_SESSION['m_member_type']; ?>
                <?php endif; ?>
            </div>
            <h1 class="mb-2">Hi, <?php echo $_SESSION['m_name']; ?></h1>
            <p id="info" class="w-75 mb-4">
                <?php echo $info; ?>
            </p>
            <div class="row"></div>
            <div class="my-4">
                <ul class="nav nav-tabs nav-fill">
                    <?php
                    $tabs_menus = [
                        'current_loan' => [
                            'text' => __('Current Loan'),
                            'link' => 'index.php?p=member'
                        ],
                        'bookmark' => [
                            'text' => __('Title Bookmark'),
                            'link' => 'index.php?p=member&sec=bookmark'
                        ],
                        'title_basket' => [
                            'text' => __('Title Basket'),
                            'link' => 'index.php?p=member&sec=title_basket'
                        ],
                        'loan_history' => [
                            'text' => __('Loan History'),
                            'link' => 'index.php?p=member&sec=loan_history'
                        ],
                        'my_account' => [
                            'text' => __('My Account'),
                            'link' => 'index.php?p=member&sec=my_account'
                        ]
                    ];
                    $section = isset($_GET['sec']) ? trim($_GET['sec']) : 'current_loan';
                    foreach ($tabs_menus as $km => $kv) {
                        $active = $section === $km ? 'active' : '';
                        $m = '<li class="nav-item">';
                        $m .= '<a class="nav-link ' . $active . '" href="' . $kv['link'] . '">' . $kv['text'] . '</a>';
                        $m .= '</li>';
                        echo $m;
                    }
                    ?>
                </ul>
                <div class="bg-white border-right border-bottom border-left p-4">
                    <?php
                    switch ($section) {
                        case 'current_loan':
                            echo '<div class="tagline">';
                            echo '<div class="memberInfoHead">' . __('My Current Loan') . '</div>' . "\n";
                            echo '</div>';
                            echo showLoanList();
                            break;
                        case 'bookmark':
                            echo '<div class="tagline">';
                            echo '<div class="memberInfoHead">' . __('My Title Bookmark') . '</div>' . "\n";
                            echo '</div>';
                            echo showBookmarkList();
                            break;
                        case 'title_basket':
                            echo '<div class="tagline">';
                            echo '<div class="memberInfoHead">' . __('My Title Basket') . '</div>' . "\n";
                            echo '</div>';
                            echo showBasket();
                            break;
                        case 'loan_history':
                            echo '<div class="tagline">';
                            echo '<div class="memberInfoHead">' . __('My Loan History') . '</div>' . "\n";
                            echo '</div>';
                            echo showLoanHist();
                            break;
                        case 'my_account':
                            echo '<div class="tagline">';
                            echo '<div class="memberInfoHead">' . __('Member Detail') . '</div>' . "\n";
                            echo '</div>';
                            echo showMemberDetail();
                            // change password only form NATIVE authentication, not for others such as LDAP
                            if ($sysconf['auth']['member']['method'] == 'native') {
                                echo '<div class="tagline">';
                                echo '<div class="memberInfoHead mt-8">' . __('Change Password') . '</div>' . "\n";
                                echo '</div>';
                                echo changePassword();
                            }
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
                $('.clearAll').click(function (evt) {
                    evt.preventDefault();
                    var anchor = $(this);
                    // get anchor href
                    var aHREF = anchor.attr('href');
                    var postData = anchor.attr('postdata');
                    if (confirm('Clear your title(s) basket?')) {
                        // send ajax
                        $.ajax({
                            type: 'POST',
                            url: aHREF, cache: false, data: postData, async: false,
                            success: function (ajaxRespond) {
                                alert('Basket data cleared!');
                                window.location.href = aHREF;
                            }
                        });
                    }
                });

                $('.clearOne').click(function (evt) {
                    evt.preventDefault();
                    var basketForm = $('#memberBasketListForm');
                    var basketData = basketForm.serialize() + '&basketRemove=1';
                    // get anchor href
                    var basketAction = basketForm.attr('action');
                    if (confirm('Remove selected title(s) from basket?')) {
                        // send ajax
                        $.ajax({
                            type: 'POST',
                            url: basketAction, cache: false, data: basketData, async: false,
                            success: function (ajaxRespond) {
                                alert('Selected basket data removed!');
                                window.location.href = 'index.php?p=member&sec=title_basket';
                            }
                        });
                    }
                });

                $('.reserve').click(function (evt) {
                    evt.preventDefault();
                    var anchor = $(this);
                    // get anchor href
                    var aHREF = anchor.attr('href');
                    // set alert to wait
                    $('#info').html('<div class="alert alert-info"><?= __('Please wait. your reservation is being sent') ?>...</div>');
                    // send ajax
                    $.ajax({
                        type: 'POST',
                        url: aHREF, cache: false, data: 'sendReserve=1', async: false,
                        success: function (ajaxRespond) {
                            console.log(ajaxRespond)

                            <?php if ($sysconf['reserve_direct_database'] ?? false): ?>

                                if(Array.isArray(ajaxRespond)) {

                                    for (let i = 0; i < ajaxRespond.length; i++) {
                                        const element = ajaxRespond[i];
                                        let message = element.message ?? '<?= __('Reservation request sent') ?>';
                                        if(element.status == 'ERROR') {
                                            toastr.error(message)
                                        } else {
                                            toastr.success(message)
                                            setTimeout(() => window.location.href = '?p=member&sec=title_basket', 2500);
                                        }
                                    }

                                } else {
                                    let message = ajaxRespond.message ?? '<?= __('Reservation request sent') ?>';
                                    if(ajaxRespond.status == 'ERROR') {
                                        toastr.error(message)
                                    } else {
                                        toastr.success(message)
                                        setTimeout(() => window.location.href = '?p=member&sec=title_basket', 2500);
                                    }
                                }

                            <?php else: ?>
                                toastr.success('<?= __('Reservation e-mail sent') ?>');
                                setTimeout(() => {
                                    window.location.href = aHREF; 
                                }, 5000);
                                
                            <?php endif ?>
                        }
                    });
                });
                
                $('.deleteBookmark').click(function(e){
                    e.preventDefault();
                    let id = $(this).data('id')
                    $.post('index.php?p=member', {bookmark_id:id,delete_bookmark: true}, function(res,state) {
                        if (!res.status)
                        {
                            toastr.error(res.message)    
                        }
                        else
                        {
                            toastr.success(res.message, '',{
                                timeOut: 2000,
                                onHidden: function() {
                                    window.location.replace('index.php?p=member&sec=bookmark')
                                }
                            })
                        }
                    }).fail(function(state){
                        console.log(state)
                        toastr.error('<?= __('Unexcpected error. Please tell it to the librarian') ?>')
                    })
                })
            }
        );
    </script>
<?php else: ?>
    <div>
        <div class="tagline"><?php echo __('Library Member Login'); ?></div>
        <div class="loginInfo">
            <?php 
            if (flash()->isEmpty())
            {
                echo __('Please insert your member ID and password given by library system administrator. If you are library\'s member and don\'t have a password yet, please contact library staff.'); 
            }
            elseif ($key = flash()->includes('wrong_password','csrf_failed','empty_field','captchaInvalid'))
            {
                flash()->danger($key);
            }
            ?>
        </div>
        <div class="loginInfo">
            <form action="index.php?p=member&destination=<?= urlencode(simbio_security::xssFree($_GET['destination'] ?? '')) ?>" method="post">
                <div class="fieldLabel"><?php echo __('Member ID'); ?></div>
                <div class="login_input"><input class="form-control" type="text" name="memberID"
                                                placeholder="Enter member ID" required/></div>
                <div class="fieldLabel marginTop"><?php echo __('Password'); ?></div>
                <div class="login_input"><input class="form-control" type="password" name="memberPassWord"
                                                placeholder="Enter password" required autocomplete="off"/></div>
                <?= \Volnix\CSRF\CSRF::getHiddenInputString() ?>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']??'' ?>">
                <!-- Captcha in form - start -->
                <div>
                    <?php 
                    if ($captcha->isSectionActive()) { ?>
                        <div class="captchaMember">
                            <?= $captcha->getCaptcha() ?>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <!-- Captcha in form - end -->
                <input type="submit" name="logMeIn" value="<?php echo __('Login'); ?>" class="memberButton"/>
            </form>
        </div>
    </div>
<?php endif; ?>
