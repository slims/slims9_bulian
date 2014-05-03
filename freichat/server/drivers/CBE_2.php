<?php

require 'Joomla.php';

class CBE_2 extends Joomla {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function avatar_url($res) {
        $murl = str_replace($this->to_freichat_path, "", $this->url);
        $avatar_url = $murl . $res[$this->avatar_field_name];
        return $avatar_url;
    }

//------------------------------------------------------------------------------
    public function get_guests() {

        $query = "
           SELECT DISTINCT status_mesg,j.avatar,f.username,f.session_id,f.status,f.guest,f.username AS profile_iden,f.in_room
           FROM frei_session AS f
           LEFT JOIN " . DBprefix . "cbe_users AS j ON j.userid=f.session_id
           WHERE f.time>" . $this->online_time2 . "
           AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
           AND f.status!=2
           AND f.status!=0";

        //query;
        //echo $query;	
        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------
    public function linkprofile_url($result, $r_path, $def_avatar) {
        $id = $result['session_id'];
        
        if(!isset($result['profile_iden']))return '';
        
        $iden = $result['profile_iden'];
        
        $profile_url = $r_path . "index.php?option=com_cbe&view=profile&userid=".$id;
        
        if($this->sef_link_profile == 'enabled') 
            $profile_url = $r_path . "index.php/cbecommunity/" . $id . "-" . $iden . "/profile";
        
        return "<span id = 'freichat_profile_link_" . $id . "'  class='freichat_linkprofile_s'>
                <a href='" . $profile_url ."'>
                <img title = '" . $this->frei_trans['profilelink'] . "' class ='freichat_linkprofile' src='" . $def_avatar . "' alt='view' />
                </a></span>";
    }

//------------------------------------------------------------------------------    
    public function get_users() {

        $query = "
       SELECT DISTINCT f.status_mesg,j.avatar,f.username,f.session_id,f.status,f.guest , f.username AS profile_iden,f.in_room
            FROM frei_session AS f
            LEFT JOIN " . DBprefix . "cbe_users AS j ON j.userid=f.session_id
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


        $query = "SELECT DISTINCT f.status_mesg,f.status_mesg,u.avatar,f.username,f.session_id,f.status,f.guest,f.username AS profile_iden,f.in_room
            FROM frei_session as f
            LEFT JOIN " . DBprefix . "cbe_users AS u ON f.session_id=u.userid
            LEFT JOIN " . DBprefix . "cbe_connection AS c ON f.session_id=c.connect_to
            WHERE f.time>" . $this->online_time2 . "
            AND f.guest=0
            AND f.status!=2
            AND f.status!=0

            AND c.connect_from=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
            AND f.session_id!=c.connect_from
            AND c.status=1";


        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------    
}