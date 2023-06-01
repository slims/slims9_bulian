<?php
/**
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

/* MEMBER BASE LIBRARY */
use SLiMS\{Mail,Plugins};

class member
{
    # class properties
    public $member_id = null;
    public $member_name = null;
    public $member_type_id = null;
    public $member_type_name = null;
    public $register_date = null;
    public $expire_date = null;
    public $member_email = null;
    public $inst_name = null;
    public $is_member = false;
    public $member_image = '';
    public $member_notes = null;
    protected $is_expire = true;
    protected $is_pending = true;
    protected $member_type_prop = array();
    protected $obj_db = false;

    # class constructor
    public function __construct($obj_db, $str_member_id)
    {
        // assign simbio database object to property
        $this->obj_db = $obj_db;
        // get member data from database
        $_member_q = $this->obj_db->query(sprintf("SELECT m.*,mt.* FROM member AS m
            LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
            WHERE member_id='%s'", $str_member_id));

        if ($_member_q->num_rows > 0) {
            // set the is_member property flag to TRUE
            $this->is_member = true;
            $_member_d = $_member_q->fetch_assoc();
            // assign database value to class properties
            $this->member_id = $_member_d['member_id'];
            $this->member_name = $_member_d['member_name'];
            $this->member_type_id = $_member_d['member_type_id'];
            $this->member_type_name = $_member_d['member_type_name'];
            $this->register_date = $_member_d['register_date'];
            $this->expire_date = $_member_d['expire_date'];
            $this->member_email = $_member_d['member_email'];
            $this->inst_name = $_member_d['inst_name'];
            $this->member_image = $_member_d['member_image'];
            $this->member_notes = $_member_d['member_notes'];
            $this->is_pending = (bool)$_member_d['is_pending'];
            $this->member_type_prop = array(
                    'loan_limit' => $_member_d['loan_limit'],
                    'loan_periode' => $_member_d['loan_periode'],
                    'enable_reserve' => $_member_d['enable_reserve'],
                    'reserve_limit' => $_member_d['reserve_limit'],
                    'member_periode' => $_member_d['member_periode'],
                    'reborrow_limit' => $_member_d['reborrow_limit'],
                    'fine_each_day' => $_member_d['fine_each_day'],
                    'grace_periode' => $_member_d['grace_periode']
                );

            // is membership expired ?
            // compare it with current date
            $_current_date = date('Y-m-d');
            $_expired_date = simbio_date::compareDates($_current_date, $_member_d['expire_date']);
            if ($_expired_date <= $_member_d['expire_date']) {
                $this->is_expire = false;
            }
        }
    }


    # checking wether member is valid or not
    # return : boolean
    public function valid()
    {
        return $this->is_member;
    }


    # checking wether membership is already expired
    # return : boolean
    public function isExpired()
    {
        return $this->is_expire;
    }


    # checking wether membership is pending
    # return : boolean
    public function isPending()
    {
        return $this->is_pending;
    }


    # get all member properties
    # retuen : array
    public function getMemberTypeProp()
    {
        // return all member information as an array
        return $this->member_type_prop;
    }


    # get info about item being lent by member
    # return : array
    protected function getItemLoan($int_loan_rules = 0)
    {
        $_arr_on_loan = array();
        // count how many items is in loan for this member
        if ($int_loan_rules) {
            $_sql_str = "SELECT item_code FROM loan AS l
                WHERE member_id='".$this->member_id."' AND is_lent=1 AND is_return=0 AND loan_rules_id=$int_loan_rules";
        } else {
            $_sql_str = "SELECT item_code FROM loan AS l
                WHERE member_id='".$this->member_id."' AND is_lent=1 AND is_return=0";
        }
        $_count_q = $this->obj_db->query($_sql_str);
        while ($_count_d = $_count_q->fetch_row()) {
            $_arr_on_loan[] = $_count_d[0];
        }

        return $_arr_on_loan;
    }


    # get an array of member's overdue data
    public static function getOverduedLoan($obj_db, $str_member_id)
    {   //      

        $_overdues = array();
        $_ovd_title_q = $obj_db->query(sprintf('SELECT l.loan_id, l.item_code, i.price, i.price_currency,
            b.title, l.loan_date,
            l.due_date, (TO_DAYS(DATE(NOW()))-TO_DAYS(due_date)) AS \'Overdue Days\', b.image
            FROM loan AS l
            LEFT JOIN item AS i ON l.item_code=i.item_code
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
            WHERE (l.is_lent=1 AND l.is_return=0 AND TO_DAYS(due_date) < TO_DAYS(\''.date('Y-m-d').'\')) AND l.member_id=\'%s\'', $str_member_id));
        while ($_ovd_title_d = $_ovd_title_q->fetch_assoc()) {
            $_overdues[] = $_ovd_title_d;
        }

        return $_overdues;
    }


    # send overdue notice e-mail
    public function sendOverdueNotice()
    {
        // execute registered hook
        Plugins::getInstance()->execute(Plugins::OVERDUE_NOTICE_INIT, ['member' => $this]);

        try {
            // Template
            include SB . 'admin/admin_template/overdueMail.php';
            $overdueTemplate = new overdueMail($this);
            $overdueTemplate->setMinify(true);
            $overdueTemplate->setCirculationData($this->obj_db, self::getOverduedLoan($this->obj_db, $this->member_id));
            
            // Send email to member
            Mail::to($this->member_email, $this->member_name)
                    ->subject(str_replace(['{member_name}', '{member_email}'], [$this->member_name, $this->member_email], __('Overdue Notice for Member {member_name} ({member_email})')))
                    ->loadTemplate($overdueTemplate)
                    ->send();
            
            return ['status' => 'SENT', 'message' => __('Overdue notification E-Mail have been sent to ') . $this->member_email];
        } catch (Exception $exception) {
            return ['status' => 'ERROR', 'message' => __('Message could not be sent. Mailer Error: ') . Mail::getInstance()->ErrorInfo];
        }
    }
}
