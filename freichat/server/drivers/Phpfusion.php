<?php

require 'base.php';

class Phpfusion extends driver_base {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {
        if ($session_id == 0) {
            $_SESSION[$this->uid . 'is_guest'] = 1;
        } else {
            $_SESSION[$this->uid . 'is_guest'] = 0;
        }
        if ($_SESSION[$this->uid . 'is_guest'] == 0) {
            if ($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') { //To consume less resources , now the query is made only once in 15 seconds
                $query = "SELECT user_id,user_name FROM " . DBprefix . "users WHERE user_id=?  LIMIT 1";
            $res_obj = $this->db->prepare($query);                       
            $res_obj->execute( array($session_id) );// var_dump($res_obj);
            $res = $res_obj->fetchAll();

                if ($res == null) {
                    $this->freichat_debug("Incorrect Query :  " . $query . " \n session id:  ". $session_id ."\n PDO error: ".print_r($this->db->errorInfo(),true));
                }

                foreach ($res as $result) {
                    if (isset($result['user_name'])) { //To avoid undefined index error. Because empty results were shown sometimes
                        $_SESSION[$this->uid . 'usr_name'] = $result['user_name'];
                        $_SESSION[$this->uid . 'usr_ses_id'] = $session_id;
                    }
                }
            }
        } else {
            $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
            $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
        }
    }
//------------------------------------------------------------------------------
    public function linkprofile_url($result,$r_path,$def_avatar) {
        
        $id = $result['session_id'];
        $profile_url =  $r_path . "profile.php?lookup=" . $id;
                
        return "<span id = 'freichat_profile_link_" . $id . "'  class='freichat_linkprofile_s'>
                <a href='" . $profile_url . "'>
                <img title = '" . $this->frei_trans['profilelink'] . "' class ='freichat_linkprofile' src='" . $def_avatar . "' alt='view' />
                </a></span>";
    }
//------------------------------------------------------------------------------
    public function avatar_url($res) {
        $murl = str_replace($this->to_freichat_path, "", $this->url);
        $avatar_url = $murl . "images/avatars/" . $res[$this->avatar_field_name];
        return $avatar_url;
    }

//------------------------------------------------------------------------------
    public function getList() {

        $user_list = null;

        if ($this->show_name == 'guest') {
            $user_list = $this->get_guests();
        } else if ($this->show_name == 'user') {
            $user_list = $this->get_users();
        } else {
            $this->freichat_debug('USER parameters for show_name are wrong.');
        }
        return $user_list;
    }
//------------------------------------------------------------------------------   
    public function get_guests() {

        $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,
                    u.user_avatar as avatar
                   FROM frei_session AS f
                   LEFT JOIN ".DBprefix."users AS u ON f.session_id=u.user_id
                   WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id<>" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status<>2
                   AND f.status<>0";

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------ 
    public function get_users() {

        $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room,
                    u.user_avatar as avatar
                   FROM frei_session AS f
                   LEFT JOIN ".DBprefix."users AS u ON f.session_id=u.user_id
                   WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id<>" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status<>2
                   AND f.status<>0
                   AND f.guest=0";

        $list = $this->db->query($query)->fetchAll();

        return $list;
    }
//------------------------------------------------------------------------------ 
    public function load_driver() {

        //define("DBprefix", $this->db_prefix);
        $session_id = $this->options['id'];
        $custom_mesg = $this->options['custom_mesg'];
        $first = $this->options['first'];

// 1. Connect The DB
//      DONE
// 2. Basic Build the blocks        
        $this->createFreiChatXsession();
// 3. Get Required Data from client DB
        $this->getDBdata($session_id, $first);
        $this->check_ban();
// 4. Insert user data in FreiChatX Table Or Recreate Him if necessary
        $this->createFreiChatXdb();
// 5. Update user data in FreiChatX Table
        $this->updateFreiChatXdb($first, $custom_mesg);
// 6. Delete user data in FreiChatX Table
        $this->deleteFreiChatXdb();
// 7. Get Appropriate UserData from FreiChatX Table
        if ($this->usr_list_wanted == true) {
            $result = $this->getList();
            return $result;
        }
// 8. Send The final Data back
        return true;
    }

}