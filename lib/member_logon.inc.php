<?php
/**
 * Member Login class
 * Class for user authentication
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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
} else if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class member_logon
{
    private $obj_db = false;
    protected $username = '';
    protected $password = '';
    protected $auth_method = 'native';
    protected $user_info = array();
    public $real_name = '';
    public $ip_check = false;
    public $ip_allowed = array();
    public $errors = '';


    /**
     * Class Constructor
     *
     * @param   string  $str_username
     * @param   string  $str_password
     * @param   string  $str_auth_method
     * @return  void
     */
    public function __construct($str_username, $str_password, $str_auth_method = 'native') {
        $this->username = trim($str_username);
        $this->password = trim($str_password);
        $this->auth_method = $str_auth_method;
    }


 /**
     * LDAP/Active directory login
     *
     * @return  boolean
     */
    protected function ldapLogin() {
        global $ldap_configs;
        if (!function_exists('ldap_connect')) {
            $this->errors = 'LDAP library is not installed yet!';
            return false;
        }
        // connect to Directory Server
        $_ds = $ldap_configs['ldap_port']?ldap_connect($ldap_configs['ldap_server'], $ldap_configs['ldap_port']):ldap_connect($ldap_configs['ldap_server']);

        // check LDAP options
        if ($ldap_configs['ldap_options']) {
            foreach ($ldap_configs['ldap_options'] as $_opt) {
                @ldap_set_option($_ds, $_opt[0], $_opt[1]);
            }
        }

        // LDAP Connection check
        if (!$_ds) {
            $this->errors = 'Failed to connect to LDAP server';
            return false;
        }

        // LDAP binding
        // for Active Directory Server login active line below
        // $_bind = ldap_bind($_ds, ( $ldap_configs['ldap_suffix']?$this->username.'@'.$ldap_configs['ldap_suffix']:$this->username ), $this->password);
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']),
            $this->password);

        if (!$_bind) {
            $this->errors = 'Failed to bind to directory server!';
            return false;
        }

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_search_filter']);

        // run query
        $_search = @ldap_search($_ds, $ldap_configs['ldap_base_dn'], $_filter);
        if (!$_search) {
            $this->errors = 'LDAP search failed because of error!';
            return false;
        }

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $this->user_info['member_id'] = $_entries[0][$ldap_configs['userid_field']][0];
            // check member in database
            $_check_q = $this->obj_db->query('SELECT m.member_id, m.member_name, m.inst_name,
                m.member_email, m.expire_date, m.register_date, m.is_pending,
                m.member_type_id, mt.member_type_name, mt.enable_reserve, mt.reserve_limit
                FROM member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
                WHERE m.member_id=\''.$this->user_info['member_id'].'\'');
            if ($_check_q->num_rows < 1) {
                $_curr_date = date('Y-m-d H:i:s');
                $_userid_field = strtolower($ldap_configs['userid_field']);
                $_fullname_field = strtolower($ldap_configs['fullname_field']);
                // insert member data to database
                $this->user_info['member_id'] = $_entries[0][$_userid_field][0];
                $this->user_info['member_name'] = $_entries[0][$_fullname_field][0];
                $this->user_info['gender'] = '1';
                $this->user_info['inst_name'] = 'New registered member';
                $this->user_info['member_email'] = $_entries[0][$ldap_configs['mail_field']][0];
                $this->user_info['expire_date'] = '0000-00-00';
                $this->user_info['register_date'] = '0000-00-00';
                $this->user_info['is_pending'] = '1';
                $this->user_info['member_type_id'] = '1';
                $this->user_info['input_date'] = $_curr_date;
                $this->user_info['last_update'] = $_curr_date;

                // include database operation library
                require_once SIMBIO.'simbio_DB/simbio_dbop.inc.php';
                $_dbop = new simbio_dbop($this->obj_db);
                $_insert = $_dbop->insert('member', $this->user_info);
                if (!$_insert) {
                    $this->errors = 'Member insertion error because of: '.$_dbop->error;
                }
                $this->user_info['member_type_name'] = 'None';
            } else {
                $this->user_info = $_check_q->fetch_assoc();
            }
        } else {
            $this->errors = 'LDAP Record not found!';
            return false;
        }

        // closing connection
        ldap_close($_ds);
        return true;
    }


    /**
     * Native database checking login method
     *
     * @return  boolean
     */
    public function nativeLogin() {
        /*
        $_sql_member_login = sprintf("SELECT m.member_id, m.member_name, m.inst_name,
            m.member_email, m.expire_date, m.register_date, m.is_pending,
            m.member_type_id, mt.member_type_name, mt.enable_reserve, mt.reserve_limit
            FROM member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
            WHERE m.member_id='%s'
                AND m.mpasswd=MD5('%s')", $this->obj_db->escape_string($this->username), $this->obj_db->escape_string($this->password));
        */
        $_sql_member_login = sprintf("SELECT m.member_id, m.member_name, m.mpasswd, m.inst_name,
            m.member_email, m.expire_date, m.register_date, m.is_pending,
            m.member_type_id, mt.member_type_name, mt.enable_reserve, mt.reserve_limit
            FROM member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
            WHERE m.member_id='%s'", $this->obj_db->escape_string($this->username));
        $_member_q = $this->obj_db->query($_sql_member_login);

        // error check
        if ($this->obj_db->error) {
            $this->errors = 'Failed to query member data from database with error: '.$this->obj_db->error;
            return false;
        }
        
        // result check
        if ($_member_q->num_rows < 1) {
            $this->errors = 'Username or Password not exists in database!';
            return false;
        }
        
        // get user info
        $this->user_info = $_member_q->fetch_assoc();
        // verify password hash
        $verified = password_verify($this->password, $this->user_info['mpasswd']);
        if (!$verified) {
            $this->errors = 'Username or Password not exists in database!';
            return false;
        }
        
        return true;
    }


    /**
     * Method to check user validity
     *
     * @param   object  $obj_db
     * @return  void
     */
    public function valid($obj_db) {
        global $sysconf;
        $this->obj_db = $obj_db;
        $_check_login = call_user_func(array($this, $this->auth_method.'Login'));
        // check if the user exist in database
        if (!$_check_login) {
          return false;
        }

        // fill all sessions var
        $_SESSION['mid'] = $this->user_info['member_id'];
        $_SESSION['m_name'] = $this->user_info['member_name'];
        $_SESSION['m_email'] = $this->user_info['member_email'];
        $_SESSION['m_institution'] = $this->user_info['inst_name'];
        $_SESSION['m_logintime'] = time();
        $_SESSION['m_expire_date'] = $this->user_info['expire_date'];
        $_SESSION['m_member_type_id'] = $this->user_info['member_type_id'];
        $_SESSION['m_member_type'] = $this->user_info['member_type_name'];
        $_SESSION['m_register_date'] = $this->user_info['register_date'];
        $_SESSION['m_membership_pending'] = intval($this->user_info['is_pending'])?true:false;
        $_SESSION['m_is_expired'] = false;
        $_SESSION['m_mark_biblio'] = array();
        $_SESSION['m_can_reserve'] = $this->user_info['enable_reserve'];
        $_SESSION['m_reserve_limit'] = $this->user_info['reserve_limit'];
        // check member expiry date
        require_once SIMBIO.'simbio_UTILS/simbio_date.inc.php';
        $_curr_date = date('Y-m-d');
        if (simbio_date::compareDates($this->user_info['expire_date'], $_curr_date) == $_curr_date) {
            $_SESSION['m_is_expired'] = true;
        }

        // update the last login time
        $obj_db->query("UPDATE member SET last_login='".date("Y-m-d H:i:s")."',
            last_login_ip='".$_SERVER['REMOTE_ADDR']."'
            WHERE member_id='".$obj_db->escape_string($this->user_info['member_id'])."'");

        return true;
    }
}
