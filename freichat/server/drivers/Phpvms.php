<?php

require 'base.php';

class Phpvms extends driver_base {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {
        if ($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') {   //To consume less resources , now the query is made only once in 15 seconds
            //  var_dump($this);
            if ($session_id == null) {
                $_SESSION[$this->uid . 'is_guest'] = 1;
                $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
                $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
            } else {
                $_SESSION[$this->uid . 'is_guest'] = 0;


                $query = "SELECT DISTINCT firstname,lastname,pilotid FROM " . DBprefix . "pilots WHERE pilotid=? LIMIT 1";
            $res_obj = $this->db->prepare($query);                       
            $res_obj->execute( array($session_id) );// var_dump($res_obj);
            $res = $res_obj->fetchAll();

                if ($res == null) {
                    $this->freichat_debug("Incorrect Query, check parameters");
                }

                foreach ($res as $result) {
                    if (isset($result['firstname'])) { //To avoid undefined index error. Because empty results were shown sometimes                 
                        $_SESSION[$this->uid . 'usr_name'] = $result['firstname'] . " " . $result['lastname'];
                        $_SESSION[$this->uid . 'usr_ses_id'] = $result['pilotid'];
                    } else {
                        $this->freichat_debug("Incorrect Query :  " . $query . " \n session id:  ". $session_id ."\n PDO error: ".print_r($this->db->errorInfo(),true));
                    }
                }
            }
        }
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
    //TODO : Below function!
    public function get_users() {

        $query = "SELECT DISTINCT status_mesg,username,session_id,status,guest,p.pilotid,p.code,in_room
                               FROM frei_session LEFT JOIN " . DBprefix . "pilots AS p ON p.pilotid = session_id
                              WHERE time>" . $this->online_time2 . "
                               AND session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                               AND guest=0
                               AND status!=2
                               AND status!=0";


        $list = $this->db->query($query)->fetchAll();

        $murl = str_replace("freichat/", "", $this->url);
        $avatar_home = $murl . "lib/avatars/";
        $ext = '.png';
        $i = 0;

        foreach ($list as $user) {
            /*
              if($user['pilotid']<10 && $user['pilotid']>0) {
              $format = $user['code'] ."000". $user['pilotid'] . $ext;
              }
              else if ($user['pilotid']<100 && $user['pilotid']>10) {
              $format = $user['code'] ."00". $user['pilotid'] . $ext;
              }
              else if ($user['pilotid']<1000 && $user['pilotid']>100) {
              $format = $user['code'] ."0". $user['pilotid'] . $ext;
              }
              else {
              $format = $user['code'] . $user['pilotid'] . $ext;
              } */

            $id = $user['pilotid'] + 99;
            $format = $user['code'] . $id . $ext;
            $list[$i]['avatar'] = $avatar_home . $format;
            $i++;
        }

        //var_dump($list);

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