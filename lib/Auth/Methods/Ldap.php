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

use SLiMS\Auth\Exception;
use SLiMS\DB;

class Ldap extends Native
{
    /**
     * Retrieve member data from database
     *
     * @param string $memberId
     * @return void
     */
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
     * @return Ldap
     */
    protected function memberAuthenticate()
    {
        $this->fetchRequest(['memberID','memberPassWord']);
        $this->type = parent::MEMBER_LOGIN;

        $ldap_configs = config('auth.options.ldap');
        $member_ldap_configs = $ldap_configs['member'];
        $member_ldap_configs['bind_dn'] = str_replace(
            ['{base_dn}','{ou}'], 
            [$ldap_configs['base_dn'], 'ou=' . $member_ldap_configs['ou']], 
            $member_ldap_configs['bind_dn']
        );

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
            str_ireplace('#loginUserName', $this->username, $member_ldap_configs['bind_dn']), $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $member_ldap_configs['search_filter']);

        // run query
        $base = 'ou=' . $member_ldap_configs['ou'] . ',' . $ldap_configs['base_dn'];
        $_search = @ldap_search($_ds, $base, $_filter);
        if (!$_search) throw new Exception(__('LDAP search failed because of error!'), 500);

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        if ($_entries) {
            $memberId = $_entries[0][$member_ldap_configs['userid_field']][0];
            $this->getMemberDetail($memberId);            

            if (count($this) < 1) {
                $_curr_date = date('Y-m-d H:i:s');
                $_fullname_field = strtolower($member_ldap_configs['fullname_field']);
                // insert member data to database
                $entryData['member_id'] = $memberId;
                $entryData['member_name'] = $_entries[0][$member_ldap_configs['fullname_field']][0];
                $entryData['gender'] = '1';
                $entryData['inst_name'] = 'New registered member';
                $entryData['member_email'] = $_entries[0][$member_ldap_configs['mail_field']][0];
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
     * @return Ldap
     */
    protected function adminAuthenticate() 
    {
        $this->fetchRequest(['userName','passWord']);
        $this->type = parent::LIBRARIAN_LOGIN;

        $ldap_configs = config('auth.options.ldap');
        $user_ldap_configs = $ldap_configs['user'];
        $user_ldap_configs['bind_dn'] = str_replace(
            ['{base_dn}','{ou}'], 
            [$ldap_configs['base_dn'], 'ou=' . $user_ldap_configs['ou']], 
            $user_ldap_configs['bind_dn']
        );

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
            str_ireplace('#loginUserName', $this->username, $user_ldap_configs['bind_dn']),
            $this->password);

        if (!$_bind) throw new Exception(__('Failed to bind to directory server!'), 500);

        $_filter = str_ireplace('#loginUserName', $this->username, $user_ldap_configs['search_filter']);

        // run query
        $base = 'ou=' . $user_ldap_configs['ou'] . ',' . $ldap_configs['base_dn'];
        $_search = @ldap_search($_ds, str_ireplace('#loginUserName', $this->username, $base), $_filter);
        if (!$_search) throw new Exception(__('LDAP search failed because of error!'), 404);

        // get query entry
        $_entries = @ldap_get_entries($_ds, $_search);
        
        if ($_entries) {
            // check if User data exists in database
            $username = $_entries[0][$user_ldap_configs['userid_field']][0];
            $realname = $_entries[0][$user_ldap_configs['fullname_field']][0];
            
            DB::query("INSERT IGNORE INTO `user` SET `username` = ?, `realname` = ?", [
                $username,
                $realname
            ])->run();

            // throw new Exception(__('You don\'t have enough privileges to enter this section!'), 403);

            $_check_q = DB::query(<<<SQL
            SELECT
                u.user_id as uid, u.username as uname, u.passwd,
                u.realname as realname, u.groups, u.user_image as upict, u.2fa
                FROM user AS u
                WHERE u.username=?
            SQL, [$username]);
            
            $this->data = $_check_q->first();
            $this->updateInfo();
        } else {
            throw new Exception(__('LDAP Record not found!'), 403);
        }

        // closing connection
        ldap_close($_ds);
        return $this;
    }
}