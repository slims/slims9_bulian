<?php
/**
 * admin_logon class
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

class admin_logon
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
     * Method to check user validity
     *
     * @param   object  $obj_db
     * @return  void
     */
    public function adminValid($obj_db) {
        $this->obj_db = $obj_db;
        $_check_login = call_user_func(array($this, $this->auth_method.'Login'));
        // check if the user exist in database
        if (!$_check_login) {
            return false;
        }

        // if the ip checking is enabled
        if ($this->ip_check) {
            if (!in_array(ip(), $this->ip_allowed)) {
                $this->errors = 'IP not allowed to login';
                return false;
            }
        }

        // update the last login time
        $obj_db->query("UPDATE user SET last_login='".date("Y-m-d H:i:s")."',
            last_login_ip='".ip()."'
            WHERE user_id=".$this->user_info['user_id']);

        return true;
    }

    function setupSession($obj_db)
    {
        global $sysconf;
        $this->real_name = $this->user_info['realname'];
        // fill all sessions var
        $_SESSION['uid'] = $this->user_info['user_id'];
        $_SESSION['uname'] = $this->user_info['username'];
        $_SESSION['realname'] = $this->user_info['realname'];
        //modified by Eddy Subratha
        if (!empty($this->user_info['user_image'])) {
            $_SESSION['upict'] = $this->user_info['user_image'];                    
        } else {
            $_SESSION['upict'] = 'person.png';        
        }
        if (!empty($this->user_info['groups'])) {
            $_SESSION['groups'] = @unserialize($this->user_info['groups']);
            // fetch group privileges
            foreach ($_SESSION['groups'] as $group_id) {
                $_priv_q = $obj_db->query("SELECT ga.*,mdl.module_path FROM group_access AS ga
                    LEFT JOIN mst_module AS mdl ON ga.module_id=mdl.module_id WHERE ga.group_id=$group_id");
                while ($_priv_d = $_priv_q->fetch_assoc()) {
                    if ($_priv_d['r']) {
                        $_SESSION['priv'][$_priv_d['module_path']]['r'] = true;
                    }
                    if ($_priv_d['w']) {
                        $_SESSION['priv'][$_priv_d['module_path']]['w'] = true;
                    }
                    if ($_priv_d['menus']) {
                        $submenus = json_decode($_priv_d['menus'], true);
                        if (isset($_SESSION['priv'][$_priv_d['module_path']]['menus'])) {
                            $_SESSION['priv'][$_priv_d['module_path']]['menus'] = array_unique(array_merge($submenus, $_SESSION['priv'][$_priv_d['module_path']]['menus']));
                        } else {
                            $_SESSION['priv'][$_priv_d['module_path']]['menus'] = $submenus;
                        }
                    }
                }
            }
        } else {
            $_SESSION['groups'] = null;
        }

        $_SESSION['logintime'] = time();
        // session vars needed by some application modules
        $_SESSION['temp_loan'] = array();
        $_SESSION['biblioAuthor'] = array();
        $_SESSION['biblioTopic'] = array();
        $_SESSION['biblioAttach'] = array();

        if (!defined('UCS_VERSION')) {
            // load holiday data from database
            $_holiday_dayname_q = $obj_db->query('SELECT holiday_dayname FROM holiday WHERE holiday_date IS NULL');
            $_SESSION['holiday_dayname'] = array();
            while ($_holiday_dayname_d = $_holiday_dayname_q->fetch_row()) {
                $_SESSION['holiday_dayname'][] = $_holiday_dayname_d[0];
            }

            $_holiday_date_q = $obj_db->query('SELECT holiday_date FROM holiday WHERE holiday_date IS NOT NULL
                ORDER BY holiday_date DESC LIMIT 365');
            $_SESSION['holiday_date'] = array();
            while ($_holiday_date_d = $_holiday_date_q->fetch_row()) {
                $_SESSION['holiday_date'][$_holiday_date_d[0]] = $_holiday_date_d[0];
            }
        }

        // save md5sum of  current application path
        if (config('loadbalanced.env', false)) {
            $server_addr = ip()->getProxyIp();
        } else {
            $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
        }
        $_SESSION['checksum'] = defined('UCS_BASE_DIR')?md5($server_addr.UCS_BASE_DIR.'admin'):md5($server_addr.SB.'admin');
    }

    function setUserInfo($user_info)
    {
        $this->user_info = $user_info;
    }

    function getUserInfo($key = null)
    {
        if (!is_null($key)) return $this->user_info[$key] ?? null;
        return $this->user_info;
    }


    /**
     * LDAP/Active directory login
     *
     * @return  boolean
     */
    protected function ldapLogin() {
        $ldap_configs = config('auth.user');
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

        // binding
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']),
            $this->password);

        if (!$_bind) {
            $this->errors = 'Failed to bind to directory server!';
            return false;
        }

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_search_filter']);

        // run query
        // $_search = @ldap_search($_ds, $ldap_configs['ldap_base_dn'], $_filter);
        $_search = @ldap_search($_ds, str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']), $_filter);
        if (!$_search) {
            $this->errors = 'LDAP search failed because of error!';
            return false;
        }

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $_username = $_entries[0][$ldap_configs['userid_field']][0];
            // check if User data exists in database
            $_check_q = $this->obj_db->query("SELECT u.user_id, u.username, u.realname, u.groups
                FROM user AS u WHERE u.username='".$_username."'");
            if ($_check_q->num_rows < 1) {
                $this->errors = 'You don\'t have enough privileges to enter this section!';
                return false;
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
    protected function nativeLogin() {
        /*
        $_sql_librarian_login = sprintf("SELECT
            u.user_id, u.username,
            u.realname, u.groups, u.user_image
            FROM user AS u
            WHERE u.username='%s'
                AND u.passwd=MD5('%s')", $this->obj_db->escape_string($this->username), $this->obj_db->escape_string($this->password));
        */
        $_sql_librarian_login = sprintf("SELECT
            u.user_id, u.username, u.passwd,
            u.realname, u.groups, u.user_image, u.2fa
            FROM user AS u
            WHERE u.username='%s'", $this->obj_db->escape_string($this->username));
        $_user_q = $this->obj_db->query($_sql_librarian_login);
    
        // error check
        if ($this->obj_db->error) {
            $this->errors = 'Failed to query user data from database with error: '.$this->obj_db->error;
            return false;
        }
        
        // result check
        if ($_user_q->num_rows < 1) {            
            $this->errors = 'Username not exists in database!';
            return false;
        }
        
        // get user info
        $this->user_info = $_user_q->fetch_assoc();
        // verify password hash
        $verified = password_verify($this->password, $this->user_info['passwd']);
        if (!$verified) {
            // maybe still use md5 encryption
            if ($this->nativeLoginMd5()) {
                $this->errors = array('status' => 'md5_encryption', 'uname' => $this->user_info['username']);
            } else {
                $this->errors = 'Username or Password not exists in database!';
            }
            return false;
        }
        return true;
    }

    /**
     * Native database checking login method with md5 encryption
     *
     * @return  boolean
     */
    protected function nativeLoginMd5() {
        $_sql_librarian_login = sprintf("SELECT
            u.user_id, u.username,
            u.realname, u.groups, u.2fa
            FROM user AS u
            WHERE u.username='%s'
                AND u.passwd=MD5('%s')", $this->obj_db->escape_string($this->username), $this->obj_db->escape_string($this->password));
        $_user_q = $this->obj_db->query($_sql_librarian_login);
        // error check
        if ($this->obj_db->error) {
            $this->errors = 'Failed to query user data from database with error: '.$this->obj_db->error;
            return false;
        }
        // result check
        if ($_user_q->num_rows < 1) {
            $this->errors = 'Username or Password not exists in database!';
            return false;
        }
        return true;
    }

    /**
     * Update password if still use md5 encryption
     *
     * @return  boolean
     */
    public function changePasswd($obj_db, $new_passwd)
    {
        $this->obj_db = $obj_db;

        if ($this->nativeLoginMd5()) {
            $_sql_update_password = sprintf("UPDATE user SET passwd = '%s', last_update = CURDATE() WHERE username = '%s'",
                password_hash($new_passwd, PASSWORD_BCRYPT), $this->username);
            $_update_q = $this->obj_db->query($_sql_update_password);
            // error check
            if ($this->obj_db->error) {
                $this->errors = 'Failed to query user data from database with error: '.$this->obj_db->error;
                return false;
            }
            return true;
        }
        $this->errors = 'Incorect current password!';
        return false;
    }
}
