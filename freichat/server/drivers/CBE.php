<?php

require 'Joomla.php';

class CBE extends Joomla {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }
//------------------------------------------------------------------------------
    public function avatar_url($res) {
        $murl = str_replace($this->to_freichat_path, "", $this->url);
        $avatar_url = $murl . "images/cbe/" . $res[$this->avatar_field_name];
        return $avatar_url;
    }

//------------------------------------------------------------------------------
    public function get_guests() {

        $query = "
           SELECT DISTINCT status_mesg,j.avatar,f.username,f.session_id,f.status,f.guest,f.in_room
           FROM frei_session AS f
           LEFT JOIN " . DBprefix . "cbe AS j ON j.id=f.session_id
           WHERE f.time>" . $this->online_time2 . "
           AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
           AND f.status!=2
           AND f.status!=0";

        //query;
        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------     
    public function get_users() {

        $query = "
       SELECT DISTINCT f.status_mesg,j.avatar,f.username,f.session_id,f.status,f.guest,f.in_room
            FROM frei_session AS f
            LEFT JOIN " . DBprefix . "cbe AS j ON j.id=f.session_id
            WHERE f.time>" . $this->online_time2 . "
            AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
            AND f.status!=2
            AND f.status!=0
            AND f.guest=0";


        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

    //------------------------------------------------------------------------------
    public function get_buddies() {

        $query = "SELECT DISTINCT f.status_mesg,f.username, f.session_id, f.status, f.guest, c.avatar,f.in_room
                    FROM " . DBprefix . "cbe_buddylist AS b
                    LEFT JOIN frei_session AS f ON b.userid = f.session_id
                    LEFT JOIN " . DBprefix . "cbe AS c ON c.user_id = f.session_id
                    INNER JOIN " . DBprefix . "cbe_buddylist AS d ON d.userid = b.buddyid

                    WHERE f.time>" . $this->online_time2 . "
                    AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                    AND f.guest=0
                    AND f.status!=2
                    AND f.status!=0
                    AND c.confirmed=1
                    AND c.banned=0
                    AND b.buddyid = " . $_SESSION[$this->uid . 'usr_ses_id'] . "
                    AND b.buddy = 1
                    AND b.status = 0
                    AND d.buddy = 1
                    AND d.status = 0";


        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

}