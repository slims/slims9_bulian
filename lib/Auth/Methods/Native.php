<?php

/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2023-12-05 15:26:50
 * @modify date 2023-12-05 15:26:50
 * @license GPL-3.0 
 * @desc some modification from lib/admin_logon.inc.php
 */

namespace SLiMS\Auth\Methods;

use SLiMS\DB;
use SLiMS\Auth\Exception;

class Native extends Contract
{
    /**
     * Member authentication
     *
     * @return Native
     */
    protected function memberAuthenticate()
    {
        $this->fetchRequest(['memberID', 'memberPassWord']);
        $this->type = parent::MEMBER_LOGIN;

        /**
         * Get data from database
         */
        $member = DB::query(<<<SQL
            SELECT 
                m.member_id as mid, 
                m.member_name as m_name, 
                m.mpasswd, 
                m.inst_name as m_institution,
                m.member_email as m_email, 
                m.expire_date as m_expire_date, 
                m.register_date as m_register_date, 
                m.is_pending as m_membership_pending,
                m.member_type_id as m_member_type_id, 
                mt.member_type_name as m_member_type, 
                mt.enable_reserve as m_can_reserve, 
                mt.reserve_limit as m_reserve_limit, 
                m.member_image as m_image
            FROM 
                member AS m 
                LEFT JOIN 
                    mst_member_type AS mt ON m.member_type_id=mt.member_type_id
            WHERE m.member_id=?
        SQL, [$this->username]);

        // not found?
        if ($member->count() < 1) throw new Exception(__('Username or Password not exists in database!'), 404);

        $this->data = $member->first();

        // verify password hash
        $verified = password_verify($this->password, $this->data['mpasswd'] ?? '');
        if (!$verified) {
            //check if md5
            if ($this->data['mpasswd'] == md5($this->password)) {
                $update_password = DB::query("UPDATE member SET mpasswd = ?, last_update = CURDATE() WHERE member_id = ?", [password_hash($this->password, PASSWORD_BCRYPT), $this->username]);
                // error check
                if (!$update_password->isAffected()) throw new Exception(__('Failed to query user data from database with error: ') . $update_password->getError(), 500);
            } else {
                throw new Exception(__('Username or Password not exists in database!'), 404);
            }
        }

        $this->setMemberData();

        return $this;
    }

    /**
     * Administrator authentication
     *
     * @return Native
     */
    protected function adminAuthenticate()
    {
        $this->fetchRequest(['userName', 'passWord']);
        $this->type = parent::LIBRARIAN_LOGIN;

        $user = DB::query(<<<SQL
        SELECT
            u.user_id as uid, u.username as uname, u.passwd,
            u.realname as realname, u.groups, u.user_image as upict, u.2fa
            FROM user AS u
            WHERE u.username=?
        SQL, [$this->username]);

        if ($user->count() < 1) throw new Exception(!empty($error = $user->getError()) ? $error : __('Username not exists in database!'), 404);

        $this->data = $user->first();

        // verify password hash
        $verified = password_verify($this->password, $this->data['passwd']);
        if (!$verified) {
            // MD5 wrong password
            if (md5($this->password) !== $this->data['passwd']) throw new Exception("Wrong Password", 401);

            // maybe still use md5 encryption
            $newPasswd = password_hash($this->password, PASSWORD_BCRYPT);
            $update_passwd = DB::query('UPDATE `user` SET `passwd` = ?, `last_update` = CURRENT_DATE() WHERE `user_id` = ?', [$newPasswd, $this->data['uid']]);
            if (!$update_passwd->isAffected()) throw new Exception(__('Failed to update user password with hash'), 500);
        }

        $this->setAdminData();

        return $this;
    }

    /**
     * Setup admin data to store in session
     *
     * @return void
     */
    function setAdminData(): Native
    {
        // $this->data['mid'] = *
        $this->data['logintime'] = time();
        // session vars needed by some application modules
        $this->data['temp_loan'] = array();
        $this->data['biblioAuthor'] = array();
        $this->data['biblioTopic'] = array();
        $this->data['biblioAttach'] = array();

        if (empty($this->data['upict'])) {
            $this->data['upict'] = 'person.png';
        }

        if (!empty($this->data['groups'])) {
            $this->data['groups'] = @unserialize($this->data['groups']);
            // fetch group privileges
            foreach ($this->data['groups'] as $group_id) {
                $_priv_q = DB::query("SELECT ga.*,mdl.module_path FROM group_access AS ga
                    LEFT JOIN mst_module AS mdl ON ga.module_id=mdl.module_id WHERE ga.group_id= ?", [$group_id]);

                foreach ($_priv_q as $_priv_d) {
                    if ($_priv_d['r']) {
                        $this->data['priv'][$_priv_d['module_path']]['r'] = true;
                    }
                    if ($_priv_d['w']) {
                        $this->data['priv'][$_priv_d['module_path']]['w'] = true;
                    }
                    if ($_priv_d['menus']) {
                        $submenus = json_decode($_priv_d['menus'], true);
                        if (isset($this->data['priv'][$_priv_d['module_path']]['menus'])) {
                            $this->data['priv'][$_priv_d['module_path']]['menus'] = array_unique(array_merge($submenus, $this->data['priv'][$_priv_d['module_path']]['menus']));
                        } else {
                            $this->data['priv'][$_priv_d['module_path']]['menus'] = $submenus;
                        }
                    }
                }
            }
        } else {
            $this->data['groups'] = null;
        }

        if (!defined('UCS_VERSION')) {
            // load holiday data from database
            $_holiday_dayname_q = DB::query();
            $_holiday_dayname_q->setDefaultOutput(\PDO::FETCH_NUM);
            $_holiday_dayname_q->prepare('SELECT holiday_dayname FROM holiday WHERE holiday_date IS NULL');

            $this->data['holiday_dayname'] = array();
            foreach ($_holiday_dayname_q as $_holiday_dayname_d) {
                $this->data['holiday_dayname'][] = $_holiday_dayname_d[0];
            }

            $_holiday_date_q = DB::query();
            $_holiday_date_q->setDefaultOutput(\PDO::FETCH_NUM);
            $_holiday_date_q->prepare('SELECT holiday_date FROM holiday WHERE holiday_date IS NOT NULL ORDER BY holiday_date DESC LIMIT 365');

            $this->data['holiday_date'] = array();
            foreach ($_holiday_date_q as $_holiday_date_d) {
                $this->data['holiday_date'][$_holiday_date_d[0]] = $_holiday_date_d[0];
            }
        }

        // save md5sum of  current application path
        if (config('loadbalanced.env', false)) {
            $server_addr = ip()->getProxyIp();
        } else {
            $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
        }
        $this->data['checksum'] = defined('UCS_BASE_DIR') ? md5($server_addr . UCS_BASE_DIR . 'admin') : md5($server_addr . SB . 'admin');

        unset($this->data['passwd']);
        return $this;
    }

    /**
     * Setup member data to store in session
     *
     * @return void
     */
    function setMemberData(): Native
    {
        $this->data['m_logintime'] = time();
        $this->data['m_is_expired'] = false;
        $this->data['m_mark_biblio'] = array();

        $this->data['m_membership_pending'] = intval($this->data['m_membership_pending']) ? true : false;

        // set bookmark
        $bookmarkStatement = DB::query('SELECT `biblio_id` FROM `biblio_mark` WHERE `member_id` = ?', [$this->data['mid']]);

        $this->data['m_mark_biblio'] = [];
        if ($bookmarkStatement->count()) {
            foreach ($bookmarkStatement as $bookmark) {
                $this->data['m_mark_biblio'][$bookmark['biblio_id']] = $bookmark['biblio_id'];
            }
        }

        // check member expiry date
        require_once SIMBIO . 'simbio_UTILS/simbio_date.inc.php';
        $_curr_date = date('Y-m-d');
        if (\simbio_date::compareDates($this->data['m_expire_date'], $_curr_date) == $_curr_date) {
            $this->data['m_is_expired'] = true;
        }

        // update the last login time
        $updateLastLogin = DB::query("UPDATE member SET last_login='" . date("Y-m-d H:i:s") . "', last_login_ip='" . ip() . "' WHERE member_id=?", [$this->data['mid']]);
        $updateLastLogin->run();

        unset($this->data['mpasswd']);
        return $this;
    }
}
