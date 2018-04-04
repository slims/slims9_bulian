<?php
/**
 *
 * Member Area/Information
 * Copyright (C) 2009  Arie Nugraha (dicarve@yahoo.com)
 * Patched by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// IP based access limitation
do_checkIP('opac');
do_checkIP('opac-member');
// required file
require LIB.'member_logon.inc.php';
// check if member already logged in
$is_member_login = utility::isMemberLogin();

$info = __('Welcome to Member\'s Area where you can view your current loan information and view your membership status.');

// member's password changing flags
define('CURR_PASSWD_WRONG', -1);
define('PASSWD_NOT_MATCH', -2);
define('CANT_UPDATE_PASSWD', -3);

if (isset($_GET['destination'])) {
    $destination = $_GET['destination'];
    if (isset($_GET['fid'])) {
        $destination .= '&fid='.$_GET['fid'];
    }
    if (isset($_GET['bid'])) {
        $destination .= '&bid='.$_GET['bid'];
    }
} else {
    $destination = FALSE;
}

// if member is logged out
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    // write log
    utility::writeLogs($dbs, 'member', $_SESSION['email'], 'Login', $_SESSION['member_name'].' Log Out from address '.$_SERVER['REMOTE_ADDR']);
    // completely destroy session cookie
    simbio_security::destroySessionCookie(null, MEMBER_COOKIES_NAME, SWB, false);
    header('Location: index.php?p=member');
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    header('Pragma: no-cache');
    exit();
}

// if there is member login action
if (isset($_POST['logMeIn']) && !$is_member_login) {
    $username = trim(strip_tags($_POST['memberID']));
    $password = trim(strip_tags($_POST['memberPassWord']));
    // check if username or password is empty
    if (!$username OR !$password) {
        echo '<div class="errorBox">'.__('Please fill your Username and Password to Login!').'</div>';
    } else {
        # <!-- Captcha form processing - start -->
        if ($sysconf['captcha']['member']['enable']) {
            if ($sysconf['captcha']['member']['type'] == 'recaptcha') {
                require_once LIB.$sysconf['captcha']['member']['folder'].'/'.$sysconf['captcha']['member']['incfile'];
                $privatekey = $sysconf['captcha']['member']['privatekey'];
                $resp = recaptcha_check_answer ($privatekey,
                    $_SERVER["REMOTE_ADDR"],
                    $_POST["g-recaptcha-response"]);

                if (!$resp->is_valid) {
                    // What happens when the CAPTCHA was entered incorrectly
                    session_unset();
                    header("location:index.php?p=member&captchaInvalid=true");
                    die();
                }
            } else if ($sysconf['captcha']['member']['type'] == 'others') {
                # other captchas here
            }
        }
        # <!-- Captcha form processing - end -->

        // regenerate session ID to prevent session hijacking
        session_regenerate_id(true);
        // create logon class instance
        $logon = new member_logon($username, $password, $sysconf['auth']['member']['method']);
        if ($sysconf['auth']['member']['method'] === 'LDAP') {
          $ldap_configs = $sysconf['auth']['member'];
        }
        if ($logon->valid($dbs)) {
            // write log
            utility::writeLogs($dbs, 'member', $username, 'Login', 'Login success for member '.$username.' from address '.$_SERVER['REMOTE_ADDR']);
            if ($destination) {
                header("location:$destination");
            } else {
                header('Location: index.php?p=member');
            }
            exit();
        } else {
            $_member_sql = sprintf('SELECT member_name FROM member
            WHERE mpasswd=MD5(\'%s\') AND member_id=\'%s\'',
            $dbs->escape_string(trim($password)), $dbs->escape_string(trim($username)));
            $_member_q = $dbs->query($_member_sql);
            if ($_member_q->num_rows > 0) {
                $_member_d = $_member_q->fetch_row();
                $msg  = '';
                $msg .= '<div class="panel panel-danger">';
                $msg .= '<div class="panel-heading">Hi, '. $_member_d[0] .'! '.__('Please update your password!').'</div>';
                $msg .= '<div class="panel-body">';
                $msg .= '<form method="post" action="index.php?p=member">';
                $msg .= '<div class="form-group">';
                $msg .= '<label for="isusername">'.__('Username').'</label>';
                $msg .= '<input type="text" class="form-control" id="isusername" name="isusername" placeholder="'.__('Username').'">';
                $msg .= '</div>';
                $msg .= '<div class="form-group">';
                $msg .= '<label for="isoldpassword">'.__('Current Password').'</label>';
                $msg .= '<input type="password" class="form-control" id="isoldpassword" name="isoldpassword" placeholder="'.__('Current Password').'">';
                $msg .= '</div>';
                $msg .= '<div class="form-group">';
                $msg .= '<label for="isnewpassword">'.__('New Password').'</label>';
                $msg .= '<input type="password" class="form-control" id="isnewpassword" name="isnewpassword" placeholder="'.__('New Password').'">';
                $msg .= '</div>';
                $msg .= '<div class="form-group">';
                $msg .= '<label for="isconfirmnewpassword">'.__('Confirm New Password').'</label>';
                $msg .= '<input type="password" class="form-control" id="isconfirmnewpassword" name="isconfirmnewpassword" placeholder="'.__('Confirm New Password').'">';
                $msg .= '</div>';
                $msg .= '</div>';
                $msg .= '<div class="panel-footer">';
                $msg .= '<button type="submit" name="renewPass" class="btn btn-success">'.__('Update').'</button>';
                $msg .= '</form></div></div>';
                simbio_security::destroySessionCookie($msg, MEMBER_COOKIES_NAME, SWB, false);                
            } else {
                // write log
                utility::writeLogs($dbs, 'member', $username, 'Login', 'Login FAILED for member '.$username.' from address '.$_SERVER['REMOTE_ADDR']);
                // message
                $msg = '<div class="errorBox">'.__('Login FAILED! Wrong username or password!').'</div>';
                simbio_security::destroySessionCookie($msg, MEMBER_COOKIES_NAME, SWB, false);                
            }
        }
    }
}

// check if member already login
if (!$is_member_login) {

    function procChangePasswordNew($str_user, $str_curr_pass, $str_new_pass, $str_conf_new_pass)
    {
        global $dbs;
        // current password checking
        $_sql_pass_check = sprintf('SELECT member_id FROM member
            WHERE mpasswd=MD5(\'%s\') AND member_id=\'%s\'',
            $dbs->escape_string(trim($str_curr_pass)), $dbs->escape_string(trim($str_user)));
        $_pass_check = $dbs->query($_sql_pass_check);
        if ($_pass_check->num_rows == 1) {
            $str_new_pass = trim($str_new_pass);
            $str_conf_new_pass = trim($str_conf_new_pass);
            // password confirmation check
            if ($str_new_pass && $str_conf_new_pass && ($str_new_pass === $str_conf_new_pass)) {
                $_new_password = password_hash($str_conf_new_pass, PASSWORD_BCRYPT);
                $_sql_update_mpasswd = sprintf('UPDATE member SET mpasswd=\'%s\'
                    WHERE member_id=\'%s\'', $dbs->escape_string($_new_password), $dbs->escape_string(trim($str_user)));
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

    // if there is change md5 password request
    if (isset($_POST['renewPass'])) {
        $change_pass = procChangePasswordNew($_POST['isusername'], $_POST['isoldpassword'], $_POST['isnewpassword'], $_POST['isconfirmnewpassword']);
        
        if ($change_pass === true) {
            $info = '<span style="font-size: 120%; font-weight: bold;">'.__('Your password have been changed successfully.').'</span>';
            $info_class = 'alert-success';
        } else {
            if ($change_pass === CURR_PASSWD_WRONG) {
                $info = __('Current password entered WRONG! Please insert the right password!');
            } else if ($change_pass === PASSWD_NOT_MATCH) {
                $info = __('Password confirmation FAILED! Make sure to check undercase or uppercase letters!');
            } else {
                $info = __('Password update FAILED! ERROR ON DATABASE!');
            }
            $info_class = 'alert-danger';
        }
        $msg = '<div class="alert '.$info_class.'"><span>'.$info.'</span></div>';
        simbio_security::destroySessionCookie($msg, MEMBER_COOKIES_NAME, SWB, false);
    }

?>
    <div class="tagline"><?php echo __('Library Member Login'); ?></div>
	<?php
	// captcha invalid warning
	if (isset($_GET['captchaInvalid']) && $_GET['captchaInvalid'] === 'true') {
		echo '<div class="errorBox">'.__('Wrong Captcha Code entered, Please write the right code!').'</div>';
	}
	?>
    <div class="loginInfo"><?php echo __('Please insert your member ID and password given by library system administrator. If you are library\'s member and don\'t have a password yet, please contact library staff.'); ?></div>
    <div class="loginInfo">
    <form action="index.php?p=member&destination=<?php echo $destination; ?>" method="post">
    <div class="fieldLabel"><?php echo __('Member ID'); ?></div>
        <div class="login_input"><input type="text" name="memberID" /></div>
    <div class="fieldLabel marginTop"><?php echo __('Password'); ?></div>
        <div  class="login_input"><input type="password" name="memberPassWord" /></div>
    <!-- Captcha in form - start -->
    <div>
    <?php if ($sysconf['captcha']['member']['enable']) { ?>
      <?php if ($sysconf['captcha']['member']['type'] == "recaptcha") { ?>
      <div class="captchaMember">
      <?php
        require_once LIB.$sysconf['captcha']['member']['folder'].'/'.$sysconf['captcha']['member']['incfile'];
        $publickey = $sysconf['captcha']['member']['publickey'];
        echo recaptcha_get_html($publickey);
      ?>
      </div>
      <!-- <div><input type="text" name="captcha_code" id="captcha-form" style="width: 80%;" /></div> -->
    <?php
      } elseif ($sysconf['captcha']['member']['type'] == "others") {

      }
      #debugging
      #echo SWB.'lib/'.$sysconf['captcha']['folder'].'/'.$sysconf['captcha']['webfile'];
    } ?>
    </div>
    <!-- Captcha in form - end -->
    <input type="submit" name="logMeIn" value="<?php echo __('Login'); ?>" class="memberButton" />
    </form>
    </div>
    </div>
<?php
} else {
    /*
     * Function to show member change password form
     *
     * @return      string
     */
    function changePassword()
    {
        // show the member information
        $_form = '<form id="memberChangePassword" method="post" action="index.php?p=member">'."\n";
        $_form .= '<table class="memberDetail" cellpadding="5" cellspacing="0">'."\n";
        $_form .= '<tr>'."\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>'.__('Current Password').'</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="currPass" /></td>';
        $_form .= '</tr>'."\n";
        $_form .= '<tr>'."\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>'.__('New Password').'</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="newPass" /></td>';
        $_form .= '</tr>'."\n";
        $_form .= '<tr>'."\n";
        $_form .= '<td class="key alterCell" width="20%"><strong>'.__('Confirm Password').'</strong></td>';
        $_form .= '<td class="value alterCell2"><input type="password" name="newPass2" /></td>';
        $_form .= '</tr>'."\n";
        $_form .= '<tr>'."\n";
        $_form .= '<td class="alterCell2" colspan="2"><input type="submit" id="loginButton" name="changePass" value="'.__('Change Password').'" /></td>';
        $_form .= '</tr>'."\n";
        $_form .= '</table>'."\n";
        $_form .= '</form>'."\n";

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
     * Function to send reservation e-mail for titles in basket
     *
     * @return      array
     */
    function sendReserveMail()
    {
        if (count($_SESSION['m_mark_biblio']) > 0) {
            $_ids = '(';
            foreach ($_SESSION['m_mark_biblio'] as $_biblio) {
                $_ids .= (integer)$_biblio.',';
            }
            $_ids = substr_replace($_ids, '', -1);
            $_ids .= ')';
        } else {
            return array('status' => 'ERROR', 'message' => 'No Titles to reserve');
        }

        global $dbs, $sysconf;
        require LIB.'phpmailer/class.phpmailer.php';

        $_mail = new PHPMailer(false);
        $_mail->IsSMTP();

        // get message template
        $_msg_tpl = @file_get_contents(SB.'template/reserve-mail-tpl.html');

        // date
        $_curr_date = date('Y-m-d H:i:s');

        // query
        $_biblio_q = $dbs->query("SELECT biblio_id, title FROM biblio WHERE biblio_id IN $_ids");

        // compile reservation data
        $_data = '<table width="100%" border="1">'."\n";
        $_data .= '<tr><th>Titles to reserve</th></tr>'."\n";
        while ($_title_d = $_biblio_q->fetch_assoc()) {
            $_data .= '<tr>';
            $_data .= '<td>'.$_title_d['title'].'</td>'."\n";
            $_data .= '</tr>';
        }
        $_data .= '</table>';


        // message
        $_message = str_ireplace(array('<!--MEMBER_ID-->', '<!--MEMBER_NAME-->', '<!--DATA-->', '<!--DATE-->'),
            array($_SESSION['mid'], $_SESSION['m_name'], $_data, $_curr_date), $_msg_tpl);

        // e-mail setting
        // $_mail->SMTPDebug = 2;
        $_mail->SMTPAuth = $sysconf['mail']['auth_enable'];
        $_mail->Host = $sysconf['mail']['server'];
        $_mail->Port = $sysconf['mail']['server_port'];
        $_mail->Username = $sysconf['mail']['auth_username'];
        $_mail->Password = $sysconf['mail']['auth_password'];
        $_mail->SetFrom($sysconf['mail']['from'], $sysconf['mail']['from_name']);
        $_mail->AddReplyTo($sysconf['mail']['reply_to'], $sysconf['mail']['reply_to_name']);
        // send carbon copy off reserve e-mail to member/requester
        $_mail->AddCC($_SESSION['m_email'], $_SESSION['m_name']);
        // send reservation e-mail to librarian
        $_mail->AddAddress($sysconf['mail']['from'], $sysconf['mail']['from_name']);
        // additional recipient
        if (isset($sysconf['mail']['add_recipients'])) {
          foreach ($sysconf['mail']['add_recipients'] as $_recps) {
            $_mail->AddAddress($_recps['from'], $_recps['from_name']);
          }
        }
        $_mail->Subject = 'Reservation request from Member '.$_SESSION['m_name'].' ('.$_SESSION['m_email'].')';
        $_mail->AltBody = strip_tags($_message);
        $_mail->MsgHTML($_message);

        $_sent = $_mail->Send();
        if (!$_sent) {
            return array('status' => 'ERROR', 'message' => $_mail->ErrorInfo);
            utility::writeLogs($this->obj_db, 'member', isset($_SESSION['mid'])?$_SESSION['mid']:'0', 'membership', 'FAILED to send reservation e-mail to '.$_SESSION['m_email'].' ('.$_mail->ErrorInfo.')');
        } else {
            return array('status' => 'SENT', 'message' => 'Overdue notification E-Mail have been sent to '.$_SESSION['m_email']);
            utility::writeLogs($this->obj_db, 'member', isset($_SESSION['mid'])?$_SESSION['mid']:'0', 'membership', 'Reservation notification e-mail sent to '.$_SESSION['m_email']);
        }
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
        $_loan_list->setSQLColumn('b.biblio_id AS \''.__('Remove').'\'', 'b.title AS \''.__('Title').'\'');
        $_loan_list->setSQLorder('b.last_update DESC');
        $_criteria = 'biblio_id = 0';
        if (count($_SESSION['m_mark_biblio']) > 0) {
            $_ids = '';
            foreach ($_SESSION['m_mark_biblio'] as $_biblio) {
                $_ids .= (integer)$_biblio.',';
            }
            $_ids = substr_replace($_ids, '', -1);
            $_criteria = "b.biblio_id IN ($_ids)";
        }
        $_loan_list->setSQLCriteria($_criteria);
        $_loan_list->column_width[0] = '5%';
        $_loan_list->modifyColumnContent(0, '<input type="checkbox" name="basket[]" class="basketItem" value="{column_value}" />');

        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberBasketList" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = '<form name="memberBasketListForm" id="memberBasketListForm" action="index.php?p=member" method="post">'."\n";
        $_datagrid = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        if ($_loan_list->num_rows > 0) {
            $_actions = '<div class="memberBasketAction">';
            $_actions .= '<a href="index.php?p=member" class="basket reserve">'.__('Reserve title(s) on Basket').'</a> ';
            $_actions .= '<a href="index.php?p=member" class="basket clearAll" postdata="clear_biblio=1">'.__('Clear Basket').'</a> ';
            $_actions .= '<a href="index.php?p=member" class="basket clearOne">'.__('Remove selected title(s) from Basket').'</a> ';
            $_actions .= '</div>';
            $_result .= '<div class="memberBasketInfo">'.$_loan_list->num_rows.' '.__('title(s) on basket').$_actions.'</div>'."\n".$_datagrid;
        }
		$_result .= "\n</form>";

        return $_result;
    }


    /*
     * Function to show membership detail of logged in member
     *
     * @return      string
     */
    function showMemberDetail()
    {
        // show the member information
        $_detail = '<table class="memberDetail" cellpadding="5" cellspacing="0">'."\n";
        // member notes and pending information
        if ($_SESSION['m_membership_pending'] || $_SESSION['m_is_expired']) {
            $_detail .= '<tr>'."\n";
            $_detail .= '<td class="key alterCell" width="15%"><strong>Notes</strong></td><td class="value alterCell2" colspan="3">';
            if ($_SESSION['m_is_expired']) {
                $_detail .= '<div style="color: #f00;">'.__('Your Membership Already EXPIRED! Please extend your membership.').'</div>';
            }
            if ($_SESSION['m_membership_pending']) {
                $_detail .= '<div style="color: #f00;">'.__('Membership currently in pending state, no loan transaction can be made yet.').'</div>';
            }
            $_detail .= '</td>';
            $_detail .= '</tr>'."\n";
        }
        $_detail .= '<tr>'."\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Member Name').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['m_name'].'</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Member ID').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['mid'].'</td>';
        $_detail .= '</tr>'."\n";
        $_detail .= '<tr>'."\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Member Email').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['m_email'].'</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Member Type').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['m_member_type'].'</td>';
        $_detail .= '</tr>'."\n";
        $_detail .= '<tr>'."\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Register Date').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['m_register_date'].'</td>';
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Expiry Date').'</strong></td><td class="value alterCell2" width="30%">'.$_SESSION['m_expire_date'].'</td>';
        $_detail .= '</tr>'."\n";
        $_detail .= '<tr>'."\n";
        $_detail .= '<td class="key alterCell" width="15%"><strong>'.__('Institution').'</strong></td>'
            .'<td class="value alterCell2" colspan="3">'.$_SESSION['m_institution'].'</td>';
        $_detail .= '</tr>'."\n";
        $_detail .= '</table>'."\n";


        return $_detail;
    }


    /* callback function to show overdue */
    function showOverdue($obj_db, $array_data)
    {
        $_curr_date = date('Y-m-d');
        if (simbio_date::compareDates($array_data[3], $_curr_date) == $_curr_date) {
            return '<strong style="color: #f00;">'.$array_data[3].' '.__('OVERDUED').'</strong>';
        } else {
            return $array_data[3];
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

        // modify column value
        $_loan_list->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_list->table_attr = 'align="center" class="memberLoanList" cellpadding="5" cellspacing="0"';
        $_loan_list->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_list->using_AJAX = false;
        // return the result
        $_result = $_loan_list->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanListInfo">'.$_loan_list->num_rows.' '.__('item(s) currently on loan').' | <a href="?p=download_current_loan">' . __('Download All Current Loan') . '</a></div>'."\n".$_result;
        return $_result;
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
        $_loan_hist->setSQLColumn('l.item_code AS \''.__('Item Code').'\'',
            'b.title AS \''.__('Title').'\'',
            'l.loan_date AS \''.__('Loan Date').'\'',
            'l.return_date AS \''.__('Return Date').'\'');
        $_loan_hist->setSQLorder('l.loan_date DESC');
        $_criteria = sprintf('m.member_id=\'%s\' AND l.is_lent=1 AND is_return=1 ', $_SESSION['mid']);
        $_loan_hist->setSQLCriteria($_criteria);

        // modify column value
        #$_loan_hist->modifyColumnContent(3, 'callback{showOverdue}');
        // set table and table header attributes
        $_loan_hist->table_attr = 'align="center" class="memberLoanList" cellpadding="5" cellspacing="0"';
        $_loan_hist->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_loan_hist->using_AJAX = false;
        // return the result
        $_result = $_loan_hist->createDataGrid($dbs, $_table_spec, $num_recs_show);
        $_result = '<div class="memberLoanHistInfo"> &nbsp;'.$_loan_hist->num_rows.' '.__('item(s) loan history').' | <a href="?p=download_loan_history">' . __('Download All Loan History') . '</a></div>'."\n".$_result;
        return $_result;
    }
    /* Experimental Loan History - end */

    // if there is change password request
    if (isset($_POST['changePass']) && $sysconf['auth']['member']['method'] == 'native') {
        $change_pass = procChangePassword($_POST['currPass'], $_POST['newPass'], $_POST['newPass2']);
        if ($change_pass === true) {
            $info = '<span style="font-size: 120%; font-weight: bold;">'.__('Your password have been changed successfully.').'</span>';
        } else {
            if ($change_pass === CURR_PASSWD_WRONG) {
                $info = __('Current password entered WRONG! Please insert the right password!');
            } else if ($change_pass === PASSWD_NOT_MATCH) {
                $info = __('Password confirmation FAILED! Make sure to check undercase or uppercase letters!');
            } else {
                $info = __('Password update FAILED! ERROR ON DATABASE!');
            }
            $info = '<span style="font-size: 120%; font-weight: bold; color: red;">'.$info.'</span>';
        }
    }

    // send reserve e-mail
    if (isset($_POST['sendReserve'])) {
        $mail = sendReserveMail();
        // die();
        if ($mail['status'] != 'ERROR') {
            $info = __('Reservation e-mail sent successfully!');
        } else {
            $info = '<span style="font-size: 120%; font-weight: bold; color: red;">'.__(sprintf('Reservation e-mail FAILED to sent with error: %s Please contact administrator!', $mail['message'])).'</span>';
        }
    }

    // biblio basket add process
    if (isset($_POST['biblio'])) {
        if (!is_array($_POST['biblio']) && is_scalar($_POST['biblio'])) {
            $_tmp_biblio = $_POST['biblio']; unset($_POST['biblio']);
            $_POST['biblio'][] = $_tmp_biblio;
        }
        // check reserve limit
        if ( (count($_SESSION['m_mark_biblio'])+count($_POST['biblio'])) > $sysconf['max_biblio_mark'] ) {
            $info = '<span style="font-size: 120%; font-weight: bold; color: red;">Maximum '.$sysconf['max_biblio_mark'].' titles can be added to basket!</span>';
        } else {
            foreach ($_POST['biblio'] as $biblio) {
                $biblio = (integer)$biblio;
                $_SESSION['m_mark_biblio'][$biblio] = $biblio;
            }
        }
    }

    // biblio basket remove process
    if (isset($_GET['rm_biblio'])) {
        if (!is_array($_GET['rm_biblio']) && is_scalar($_GET['rm_biblio'])) {
            $_tmp_biblio = $_GET['rm_biblio']; unset($_GET['rm_biblio']);
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

    // show all
	echo '<div class="tagline">';
    echo '<div class="memberInfoHead">'.__('Member Detail').'</div>'."\n";
    echo '</div>';
    echo '<div class="collection-list"><div class="item_list">';
    echo showMemberDetail();
    echo '</div>';
	echo '<div class="tagline">';
    echo '<div class="memberInfoHead">'.__('Your Current Loan').'</div>'."\n";
    echo '</div>';
    echo '<div class="collection-list"><div class="item_list">';
    echo showLoanList();
    echo '</div>';
	echo '<div class="tagline">';
    echo '<div class="memberInfoHead">'.__('Your Loan History').'</div>'."\n";
    echo '</div>';
    echo showLoanHist();
    echo '</div>';

    // default is to show the title basket
    if (!isset($sysconf['enable_mark']) || $sysconf['enable_mark']) {
	echo '<div class="tagline">';
        echo '<div class="memberInfoHead">'.__('Your Title Basket').'</div><a name="biblioBasket"></a>'."\n";
        echo showBasket();
        echo '</div>';
    }
    // change password only form NATIVE authentication, not for others such as LDAP
    if ($sysconf['auth']['member']['method'] == 'native') {
	echo '<div class="tagline">';
        echo '<div class="memberInfoHead">'.__('Change Password').'</div>'."\n";
	    echo '</div>';
        echo changePassword();
    }
    ?>
    <script type="text/javascript">
    $(document).ready( function() {
        $('.clearAll').click(function(evt) {
            evt.preventDefault();
            var anchor = $(this);
            // get anchor href
            var aHREF = anchor.attr('href');
            var postData = anchor.attr('postdata');
            if (confirm('Clear your title(s) basket?')) {
                // send ajax
                $.ajax({ type: 'POST',
                  url: aHREF, cache: false, data: postData, async: false,
                  success: function(ajaxRespond) {
                    alert('Basket data cleared!');
                    window.location.href = aHREF;
                  }
                });
            }
        });

		$('.clearOne').click(function(evt) {
            evt.preventDefault();
			var basketForm = $('#memberBasketListForm');
			var basketData = basketForm.serialize() + '&basketRemove=1';
            // get anchor href
            var basketAction = basketForm.attr('action');
            if (confirm('Remove selected title(s) from basket?')) {
                // send ajax
                $.ajax({ type: 'POST',
                  url: basketAction, cache: false, data: basketData, async: false,
                  success: function(ajaxRespond) {
                    alert('Selected basket data removed!');
                    window.location.href = 'index.php?p=member';
                  }
                });
            }
		});

        $('.reserve').click(function(evt) {
            evt.preventDefault();
            var anchor = $(this);
            // get anchor href
            var aHREF = anchor.attr('href');
            // send ajax
            $.ajax({ type: 'POST',
              url: aHREF, cache: false, data: 'sendReserve=1', async: false,
                success: function(ajaxRespond) { alert('Reservation e-mail sent'); window.location.href = aHREF; }
            });
        });
    }
    );
    </script>
    <?php
}
