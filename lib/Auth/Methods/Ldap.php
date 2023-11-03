<?php
namespace SLiMS\Auth\Methods;

use SLiMS\Auth\Exception;
use SLiMS\DB;

class Ldap extends Contract
{
    /**
     * Member authentication
     *
     * @return Native
     */
    protected function memberAuthenticate() 
    {
        $ldap_configs = config('auth.member');
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
        if (!$_ds) throw new Exception(__('Failed to connect to LDAP server'), 500);

        // LDAP binding
        // for Active Directory Server login active line below
        // $_bind = ldap_bind($_ds, ( $ldap_configs['ldap_suffix']?$this->username.'@'.$ldap_configs['ldap_suffix']:$this->username ), $this->password);
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']),
            $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_search_filter']);

        // run query
        $_search = @ldap_search($_ds, $ldap_configs['ldap_base_dn'], $_filter);
        if (!$_search) throw new Exception(__('LDAP search failed because of error!'), 500);

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $this->data['member_id'] = $_entries[0][$ldap_configs['userid_field']][0];
            // check member in database
            $_check_q = DB::query('SELECT m.member_id, m.member_name, m.inst_name,
                m.member_email, m.expire_date, m.register_date, m.is_pending,
                m.member_type_id, mt.member_type_name, mt.enable_reserve, mt.reserve_limit
                FROM member AS m LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
                WHERE m.member_id=?', [$this->data['member_id']]);

            if ($_check_q->count() < 1) {
                $_curr_date = date('Y-m-d H:i:s');
                $_userid_field = strtolower($ldap_configs['userid_field']);
                $_fullname_field = strtolower($ldap_configs['fullname_field']);
                // insert member data to database
                $this->data['member_id'] = $_entries[0][$_userid_field][0];
                $this->data['member_name'] = $_entries[0][$_fullname_field][0];
                $this->data['gender'] = '1';
                $this->data['inst_name'] = 'New registered member';
                $this->data['member_email'] = $_entries[0][$ldap_configs['mail_field']][0];
                $this->data['expire_date'] = '0000-00-00';
                $this->data['register_date'] = '0000-00-00';
                $this->data['is_pending'] = '1';
                $this->data['member_type_id'] = '1';
                $this->data['input_date'] = $_curr_date;
                $this->data['last_update'] = $_curr_date;

                // include database operation library
                require_once SIMBIO.'simbio_DB/simbio_dbop.inc.php';
                $_dbop = new simbio_dbop($this->obj_db);
                $_insert = $_dbop->insert('member', $this->data);
                if (!$_insert) throw new Exception(__('Member insertion error because of:') . ' ' . $_dbop->error, 500);
                $this->data['member_type_name'] = 'None';
            } else {
                $this->data = $_check_q->first();
            }
        } else {
            throw new Exception(__('LDAP Record not found!'), 404);
        }

        // closing connection
        ldap_close($_ds);
        return $this;
    }

    /**
     * Administrator authentication
     *
     * @return void
     */
    protected function adminAuthenticate() 
    {
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
        if (!$_ds) throw new Exception(__('Failed to connect to LDAP server'), 500);

        // binding
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']),
            $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_search_filter']);

        // run query
        // $_search = @ldap_search($_ds, $ldap_configs['ldap_base_dn'], $_filter);
        $_search = @ldap_search($_ds, str_ireplace('#loginUserName', $this->username, $ldap_configs['ldap_bind_dn']), $_filter);
        if (!$_search) throw new Exception(__('LDAP search failed because of error!'), 404);

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $_username = $_entries[0][$ldap_configs['userid_field']][0];
            // check if User data exists in database
            $_check_q = DB::query("SELECT u.user_id, u.username, u.realname, u.groups
                FROM user AS u WHERE u.username=?", [$_username]);

            if ($_check_q->count() < 1) throw new Exception(__('You don\'t have enough privileges to enter this section!'), 403);
                
            $this->data = $_check_q->first();
        } else {
            throw new Exception(__('LDAP Record not found!'), 403);
        }

        // closing connection
        ldap_close($_ds);
        return $this;
    }
}