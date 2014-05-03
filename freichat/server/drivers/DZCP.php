<?php

########################################
#                                      #
#       DZCP Driver for Freichat       #
#      DZCP Version: 1.5.x, 1.6.x      #
#      ______________________          #
#                                      #
#           Mod by: Richy              #
#        www.my-starmedia.de           #
#                                      #
########################################




require 'base.php';

class DZCP extends driver_base {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {

        if ($session_id != null) {
            $userID = strip_tags($session_id);
            $_SESSION[$this->uid . 'is_guest'] = 0;
        } else {
            $_SESSION[$this->uid . 'is_guest'] = 1;
            $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
            $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
        }

        if (($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') && $_SESSION[$this->uid . 'is_guest'] == 0) { //To consume less resources , now the query is made only once in 15 seconds
            $query = "SELECT DISTINCT `id`, `nick`
                        FROM " . $this->db_prefix . "users
                        WHERE `id` = '" . $userID . "'
                        LIMIT 1";

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
                if (isset($result['nick'])) { //To avoid undefined index error. Because empty results were shown sometimes
                    $_SESSION[$this->uid . 'usr_name'] = $result['nick'];
                    $_SESSION[$this->uid . 'usr_ses_id'] = $result['id'];
                }
            }
        } else {
            $this->freichat_debug("Wrong method defined!");
        }
    }

//------------------------------------------------------------------------------
    public function avatar_url($res) {
        $picformat = array("jpg", "gif", "png");
        foreach ($picformat as $endung) {
            if (file_exists('../inc/images/uploads/useravatare/' . $_SESSION[$this->uid . 'usr_ses_id'] . '.' . $endung)) {
                $avatar_url = '../inc/images/uploads/useravatare/' . $_SESSION[$this->uid . 'usr_ses_id'] . '.' . $endung;
                break;
            } else {
                $avatar_url = '../inc/images/noavatar.gif';
            }
        }
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
    public function linkprofile_url($result, $r_path, $def_avatar) {

        $id = $result['session_id'];
        $str = "<span id = 'freichat_profile_link_" . $id . "'  class='freichat_linkprofile_s'>";

        $path = "<a href='../user/?action=user&amp;id=" . $id . "'>";

        $str = $str . $path . "<img title = '" . $this->frei_trans['profilelink'] . "' class ='freichat_linkprofile' src='" . $def_avatar . "' alt='view' />
                </a></span>";

        return $str;
    }

//------------------------------------------------------------------------------     
    public function get_users() {

        $query = "SELECT DISTINCT f.in_room,f.`status_mesg`,f.`username`,f.`session_id`,f.`status`,f.`guest`,u.`nick`
								  FROM `frei_session` AS f
									LEFT JOIN `" . $this->db_prefix . "users` AS u ON f.`session_id` = u.`id`
									WHERE f.`session_id` != '" . $_SESSION[$this->uid . 'usr_ses_id'] . "' 
									AND f.`time` > '" . $this->online_time2 . "'
								  AND f.`guest` = '0'
								  AND f.`status` != '2'
								  AND f.`status` != '0'
									ORDER BY u.`level` DESC, u.`nick`";

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------ 		
    public function get_buddies() {

        $query = "SELECT DISTINCT f.in_room,f.`status_mesg`,f.`username`,f.`session_id`,f.`status`,f.`guest`
                  FROM `" . $this->db_prefix . "userbuddys` AS u
                  LEFT JOIN `frei_session` AS f ON f.`session_id` = u.`buddy`
									WHERE u.`user` = '" . $_SESSION[$this->uid . 'usr_ses_id'] . "' 
									AND f.`time` > '" . $this->online_time2 . "'
                  AND f.`session_id` != '" . $_SESSION[$this->uid . 'usr_ses_id'] . "'
                  AND f.`guest` = '0'
                  AND f.`status` != '2'
                  AND f.`status` != '0'
									ORDER BY u.`level` DESC, u.`nick`";

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