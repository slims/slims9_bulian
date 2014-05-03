<?php

require 'base.php';

class Custom extends driver_base {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

    public function get_id() {

        $id = $this->session_id;

        if (isset($_COOKIE[$this->use_cookie]) && $_COOKIE[$this->use_cookie] != null) {
            $id = $_COOKIE[$this->use_cookie];
        }

        if ($_SESSION[$this->uid . 'xhash'] == null)
            $_SESSION[$this->uid . 'xhash'] = md5( $_COOKIE[$this->use_cookie] . $this->uid );

        return $id;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {

        $this->session_id = $session_id;
        if ($session_id == null && $this->use_cookie) {
            $session_id = $this->get_id();
        }

        if ($session_id != null) {
            $userID = strip_tags($session_id);
            $_SESSION[$this->uid . 'is_guest'] = 0;
        } else {
            $_SESSION[$this->uid . 'is_guest'] = 1;
            $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
            $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
        }

        if (($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') && $_SESSION[$this->uid . 'is_guest'] == 0) { //To consume less resources , now the query is made only once in 15 seconds
            if ($this->pdo_driver == "sqlsrv") {
                $query = "SELECT DISTINCT TOP 1 " . $this->row_username . "," . $this->row_userid . "
                      FROM " . DBprefix . $this->usertable . "
                      WHERE " . $this->row_userid . "=?";
            } else {
                $query = "SELECT DISTINCT " . $this->row_username . "," . $this->row_userid . "
                      FROM " . DBprefix . $this->usertable . "
                      WHERE " . $this->row_userid . "=?
                      LIMIT 1";
            }

            $res_obj = $this->db->prepare($query);
            $res_obj->execute(array($userID)); // var_dump($res_obj);
            $res = $res_obj->fetchAll();

            if ($res == null) {
                $this->freichat_debug("Incorrect Query :  " . $query . " \n session id:  " . $session_id . "\n PDO error: " . print_r($this->db->errorInfo(), true));
                $_SESSION[$this->uid . 'is_guest'] = 1;
                $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
                $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
            }

            foreach ($res as $result) {
                if (isset($result[$this->row_username])) { //To avoid undefined index error. Because empty results were shown sometimes
                    $_SESSION[$this->uid . 'usr_name'] = $result[$this->row_username];
                    $_SESSION[$this->uid . 'usr_ses_id'] = $result[$this->row_userid];
                }
            }
        } else {
            $this->freichat_debug("Wrong method defined!");
        }
    }

//------------------------------------------------------------------------------
    public function linkprofile_url($result, $r_path, $def_avatar) {
        $iden = $result['profile_iden']; //additional data
        $id = $result['session_id'];
        $str = "<span id = 'freichat_profile_link_" . $id . "'  class='freichat_linkprofile_s'>";

        $path = "<a href='" . $r_path . "index.php?userid=" . $id . "'&task=viewprofile>";

        $str = $str . $path . "<img title = '" . $this->frei_trans['profilelink'] . "' class ='freichat_linkprofile' src='" . $def_avatar . "' alt='view' />
                </a></span>";

        //return $str;
        return '';
    }

//------------------------------------------------------------------------------
    //AVATAR_URL_START
        public function avatar_url($res) {
            $root = 'http://localhost/j3/images/comprofiler';
            $avatar = $res[$this->avatar_field_name];
            $avatar = str_replace(' ','%20',$avatar);
        if (strpos($avatar, 'http://') === FALSE && strpos($avatar, 'https://') === FALSE) {
                $slash = '/';
                if($avatar[0] == '/') $slash = '';
        
                return $root.$slash.$avatar;
            }else{
                return $avatar;
            }
        }
        //AVATAR_URL_END
//------------------------------------------------------------------------------
    public function getList() {

        $user_list = null;

        if ($this->show_name == 'guest') {
            $user_list = $this->get_guests();
        } else if ($this->show_name == 'user') {
            $user_list = $this->get_users();
        } else if ($this->show_name == 'buddy') {
            $user_list = $this->get_buddies();
        }
        else {
            $this->freichat_debug('USER parameters for show_name are wrong.');
        }
        return $user_list;
    }

//------------------------------------------------------------------------------
    public function get_guests() {

        //do not delete below comment
        //CUSTOM_GUESTS_QUERY_START
            $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room
                   FROM frei_session AS f 
                  WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status!=2
                   AND f.status!=0";
//CUSTOM_GUESTS_QUERY_END
        //do not delete above comment

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------     
    public function get_users() {

        //do not delete below comment
        //CUSTOM_USERS_QUERY_START
            $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room
                   FROM frei_session AS f
                  WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0";
//CUSTOM_USERS_QUERY_END
        //do not delete above comment

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

    //------------------------------------------------------------------------------
    public function get_buddies() {

        //do not delete below comment
        //CUSTOM_BUDDIES_QUERY_START
            $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,f.in_room
                   FROM frei_session AS f
                  WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status!=2
                   AND f.status!=0
                   AND f.guest=0";
//CUSTOM_BUDDIES_QUERY_END
        //do not delete above comment

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