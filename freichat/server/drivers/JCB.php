<?php

require 'Joomla.php';

class JCB extends Joomla {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {
        if ($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') {   //To consume less resources , now the query is made only once in 15 seconds
            //  var_dump($this);
            if ($this->displayname == 'fullname') {

                $query = "SELECT DISTINCT  s.firstname, s.lastname ,j.userid,j.guest
                                    FROM " . DBprefix . "session AS j
                                    LEFT JOIN " . DBprefix . "comprofiler AS s ON j.userid=s.id
                                    WHERE j.session_id=?
                                    AND j.client_id=0  LIMIT 1";
            } else {
                $query = "SELECT DISTINCT s." . $this->displayname . ",j.userid,j.guest
                    FROM " . DBprefix . "session AS j
                    LEFT JOIN " . DBprefix . "users AS s ON j.userid=s.id
                    WHERE j.session_id=?
                    AND j.client_id=0  LIMIT 1";
            }

             $res_obj = $this->db->prepare($query);                       
            $res_obj->execute( array($session_id) );// var_dump($res_obj);
            $res = $res_obj->fetchAll();

            if ($res == null) {
                $this->freichat_debug("Incorrect Query :  " . $query . " \n session id:  ". $session_id ."\n PDO error: ".print_r($this->db->errorInfo(),true));
            }

            foreach ($res as $result) {
                if (isset($result['guest'])) { //To avoid undefined index error. Because empty results were shown sometimes
                    $_SESSION[$this->uid . 'is_guest'] = $result['guest'];
                    if ($result['guest'] == 0) { //To check if the result from query is a guest or not
                        if ($this->displayname == 'fullname') {
                            $_SESSION[$this->uid . 'usr_name'] = $result['firstname'] . " " . $result['lastname'];
                        } else {
                            $_SESSION[$this->uid . 'usr_name'] = $result[$this->displayname];
                        }
                        $_SESSION[$this->uid . 'usr_ses_id'] = $result['userid'];
                    } else if ($result['guest'] == 1) { //When user loggs out his session has to be updated back to old session(the session made before he logged in)
                        $_SESSION[$this->uid . 'usr_name'] = $_SESSION[$this->uid . 'gst_nam'];
                        $_SESSION[$this->uid . 'usr_ses_id'] = $_SESSION[$this->uid . 'gst_ses_id'];
                    } else {
                        $this->freichat_debug('you are neither a guest nor a user , are you a alien?');
                    }
                }
            }
        }
    }

//------------------------------------------------------------------------------
    public function avatar_url($res) {

        $avatar = $res[$this->avatar_field_name];
        
        $murl = str_replace($this->to_freichat_path, "", $this->url);
        $err_msg = "Unresolved Avatar for " . $this->driver;

        if (strpos($avatar, "gallery") == false) {
            $avatar_url = $murl . "images/comprofiler/" . $avatar;
        } else if (strpos($avatar, "gallery") == true) {
            $avatar_url = $murl . "images/comprofiler/tn" . $avatar;
        } else {
            return $err_msg;
        }
        return $avatar_url;
    }

//------------------------------------------------------------------------------
    public function linkprofile_url($result, $r_path, $def_avatar) {
        $id = $result['session_id'];

        $profile_url = $r_path . "index.php?option=com_comprofiler&task=userprofile&user=" . $id;
        if ($this->sef_link_profile == 'enabled')
            $profile_url = $r_path . "index.php/user-profile/userprofile/" . $result['profile_iden'];
        
        $str = "<span id = 'freichat_profile_link_" . $id . "'  class='freichat_linkprofile_s'>
                <a href='" . $profile_url . "'> 
                <img title = '" . $this->frei_trans['profilelink'] . "' class ='freichat_linkprofile' src='" . $def_avatar . "' alt='view' />
                </a></span>";

        return $str;
    }

//------------------------------------------------------------------------------
    public function get_guests() {

        $query = "
        SELECT DISTINCT f.status_mesg,j.avatar,f.username,f.session_id,f.status,f.guest,u.username AS profile_iden,f.in_room
        FROM frei_session AS f
        LEFT JOIN " . DBprefix . "comprofiler AS j ON j.id=f.session_id
        LEFT JOIN " . DBprefix . "users AS u ON u.id=f.session_id
           
        WHERE 
            f.time>" . $this->online_time2 . "
        AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
        AND f.status<>2
        AND f.status<>0";
//echo $query;
        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------     
    public function get_users() {

        $query = "
        SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,j.avatar,u.username AS profile_iden,f.in_room
        FROM frei_session AS f
        LEFT JOIN " . DBprefix . "comprofiler AS j ON j.id=f.session_id
        LEFT JOIN " . DBprefix . "users AS u ON u.id=f.session_id

            
        WHERE 
            f.time>" . $this->online_time2 . "
        AND f.session_id!=" . $_SESSION[$this->uid . 'usr_ses_id'] . "
        AND f.guest=0
        AND f.status<>2
        AND f.status<>0

        AND j.approved=1";

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

    //------------------------------------------------------------------------------
    public function get_buddies() {

        $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest,j.avatar,u.username AS profile_iden,f.in_room
                    FROM " . DBprefix . "comprofiler_members AS m
                    LEFT JOIN frei_session AS f ON m.memberid = f.session_id
                    LEFT JOIN " . DBprefix . "comprofiler AS j ON j.id = f.session_id
                    INNER JOIN " . DBprefix . "comprofiler_members AS d ON d.memberid = m.referenceid
                    LEFT JOIN " . DBprefix . "users AS u ON u.id=f.session_id


                    WHERE                    
                        f.time>" . $this->online_time2 . "
                    AND f.session_id<>" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                    AND f.guest=0
                    AND f.status!=2
                    AND f.status!=0

                    AND d.pending=0
                    AND d.accepted=1

                    AND m.referenceid = " . $_SESSION[$this->uid . 'usr_ses_id'] . "                                   
                    AND m.accepted=1
                    AND m.pending=0

                    AND j.approved=1";

        $list = $this->db->query($query)->fetchAll();
        return $list;
    }

//------------------------------------------------------------------------------    
}