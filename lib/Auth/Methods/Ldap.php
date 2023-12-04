<?php
namespace SLiMS\Auth\Methods;

use SLiMS\Auth\Exception;
use SLiMS\DB;

class Ldap extends Native
{
    private function getMemberDetail(string $memberId)
    {
        // check member in database
        $member = DB::query('SELECT m.member_id as mid, 
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
                    mst_member_type AS mt 
                    ON m.member_type_id=mt.member_type_id
            WHERE m.member_id=?', [$memberId]);

        $this->data = $member->first()??[];
    }

    /**
     * Member authentication
     *
     * @return Native
     */
    protected function memberAuthenticate() 
    {
        $this->fetchRequest(['memberID','memberPassWord']);

        $ldap_configs = config('auth.options.ldap');
        if (!function_exists('ldap_connect')) {
            $this->errors = 'LDAP library is not installed yet!';
            return false;
        }
        // connect to Directory Server
        $_ds = $ldap_configs['port']?ldap_connect($ldap_configs['server'], $ldap_configs['port']):ldap_connect($ldap_configs['server']);

        // check LDAP options
        if ($ldap_configs['options']) {
            foreach ($ldap_configs['options'] as $_opt) {
                @ldap_set_option($_ds, $_opt[0], $_opt[1]);
            }
        }

        // LDAP Connection check
        if (!$_ds) throw new Exception(__('Failed to connect to LDAP server'), 500);

        // LDAP binding
        // for Active Directory Server login active line below
        // $_bind = ldap_bind($_ds, ( $ldap_configs['suffix']?$this->username.'@'.$ldap_configs['suffix']:$this->username ), $this->password);
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['bind_dn']), $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['search_filter']);

        // run query
        $_search = @ldap_search($_ds, $ldap_configs['base_dn'], $_filter);
        if (!$_search) throw new Exception(__('LDAP search failed because of error!'), 500);

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $memberId = $_entries[0][$ldap_configs['userid_field']][0];
            $this->getMemberDetail($memberId);            

            if (count($this) < 1) {
                $_curr_date = date('Y-m-d H:i:s');
                $_fullname_field = strtolower($ldap_configs['fullname_field']);
                // insert member data to database
                $entryData['member_id'] = $memberId;
                $entryData['member_name'] = $_entries[0][$_fullname_field][0];
                $entryData['gender'] = '1';
                $entryData['inst_name'] = 'New registered member';
                $entryData['member_email'] = $_entries[0][$ldap_configs['mail_field']][0];
                $entryData['expire_date'] = '0000-00-00';
                $entryData['register_date'] = '0000-00-00';
                $entryData['is_pending'] = '1';
                $entryData['member_type_id'] = '1';
                $entryData['input_date'] = $_curr_date;
                $entryData['last_update'] = $_curr_date;

                // include database operation library
                require_once SIMBIO.'simbio_DB/simbio_dbop.inc.php';
                $_dbop = new \simbio_dbop(DB::getInstance('mysqli'));
                $_insert = $_dbop->insert('member', $entryData);
                if (!$_insert) throw new Exception(__('Member insertion error because of:') . ' ' . $_dbop->error, 500);
                $this->getMemberDetail($memberId);
                $this->data['member_type_name'] = 'None';
            }
        } else {
            throw new Exception(__('LDAP Record not found!'), 404);
        }

        $this->updateInfo();

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
        $_ds = $ldap_configs['port']?ldap_connect($ldap_configs['server'], $ldap_configs['port']):ldap_connect($ldap_configs['server']);

        // check LDAP options
        if ($ldap_configs['options']) {
            foreach ($ldap_configs['options'] as $_opt) {
                @ldap_set_option($_ds, $_opt[0], $_opt[1]);
            }
        }

        // LDAP Connection check
        if (!$_ds) throw new Exception(__('Failed to connect to LDAP server'), 500);

        // binding
        $_bind = @ldap_bind($_ds,
            str_ireplace('#loginUserName', $this->username, $ldap_configs['bind_dn']),
            $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $ldap_configs['search_filter']);

        // run query
        // $_search = @ldap_search($_ds, $ldap_configs['base_dn'], $_filter);
        $_search = @ldap_search($_ds, str_ireplace('#loginUserName', $this->username, $ldap_configs['bind_dn']), $_filter);
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