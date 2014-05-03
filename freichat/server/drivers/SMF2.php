<?php

require 'base.php';

class SMF2 extends driver_base {

    public function __construct($db) {
        //parent::__construct();
        $this->db = $db;
    }

//------------------------------------------------------------------------------
    public function getDBdata($session_id, $first) {

        if ($session_id == null) {
            $_SESSION[$this->uid . 'is_guest'] = 1;
        } else {
            $_SESSION[$this->uid . 'is_guest'] = 0;
        }

        if (!$_SESSION[$this->uid . 'is_guest']) {
            //if user

            if ($_SESSION[$this->uid . 'time'] < $this->online_time || isset($_SESSION[$this->uid . 'usr_name']) == false || $first == 'false') { //To consume less resources , now the query is made only once in 15 seconds
                //run every 15 seconds or run on first load
                if ($this->displayname == 'username') {
                    $display_name = 'member_name';
                } else if ($this->displayname == 'name') {
                    $display_name = 'real_name';
                }

                $query = "SELECT DISTINCT $display_name
                      FROM " . DBprefix . "members 
                      WHERE id_member = ?  LIMIT 1";

                $res_obj = $this->db->prepare($query);
                $res_obj->execute(array($session_id)); // var_dump($res_obj);
                $res = $res_obj->fetchAll();

                if ($res == null) {
                    $this->freichat_debug("Incorrect Query :  " . $query . " \n session id:  " . $session_id . "\n PDO error: " . print_r($this->db->errorInfo(), true));
                }

                foreach ($res as $result) {
                    if (isset($result[$display_name])) {
                        $_SESSION[$this->uid . 'usr_name'] = $result[$display_name];
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
    public function avatar_url($res) {

        return $res[$this->avatar_field_name];
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

        $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest AS is_guest,f.in_room,
                   a.id_attach , m.avatar AS avatar_user_url,a.file_hash AS avatar_attachment_url
                   FROM frei_session AS f, " . DBprefix . "members AS m
                   LEFT JOIN " . DBprefix . "attachments AS a ON a.id_member=m.id_member 
                   WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id<>" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status<>2
                   AND f.status<>0
                   AND m.id_member = f.session_id";
//echo $query;

        $list = $this->db->query($query)->fetchAll();
        $murl = str_replace($this->to_freichat_path, "", $this->url);

        $i = 0;
        foreach ($list as $row) {

            if (!$row["is_guest"]) {
                if ($row['avatar_user_url'] != null) {
                    if (strpos($row['avatar_user_url'], "http://") !== FALSE || strpos($row['avatar_user_url'], "https://") !== FALSE) {
                        $list[$i]['avatar'] = $row['avatar_user_url'];
                    } else {
                        $list[$i]['avatar'] = $murl . 'avatars/' . $row['avatar_user_url'];
                    }
                } else {
                    $list[$i]['avatar'] = $murl . 'attachments/' . $row['id_attach'] . '_' . $row['avatar_attachment_url'];
                }
            }

            $i++;
        }

        return $list;
    }

//------------------------------------------------------------------------------   
    public function get_users() {

        $query = "SELECT DISTINCT f.status_mesg,f.username,f.session_id,f.status,f.guest AS is_guest,f.in_room,
                   a.id_attach , m.avatar AS avatar_user_url,a.file_hash AS avatar_attachment_url
                   FROM frei_session AS f, " . DBprefix . "members AS m
                   LEFT JOIN " . DBprefix . "attachments AS a ON a.id_member=m.id_member 
                   WHERE f.time>" . $this->online_time2 . "
                   AND f.session_id<>" . $_SESSION[$this->uid . 'usr_ses_id'] . "
                   AND f.status<>2
                   AND f.status<>0
                   AND guest=0
                   AND m.id_member = f.session_id";
//echo $query;

        $list = $this->db->query($query)->fetchAll();
        $murl = str_replace($this->to_freichat_path, "", $this->url);

        $i = 0;
        foreach ($list as $row) {

            if (!$row["is_guest"]) {
                if ($row['avatar_user_url'] != null) {
                    if (strpos($row['avatar_user_url'], "http://") !== FALSE || strpos($row['avatar_user_url'], "https://") !== FALSE) {
                        $list[$i]['avatar'] = $row['avatar_user_url'];
                    } else {
                        $list[$i]['avatar'] = $murl . 'avatars/' . $row['avatar_user_url'];
                    }
                } else {
                    $list[$i]['avatar'] = $murl . 'attachments/' . $row['id_attach'] . '_' . $row['avatar_attachment_url'];
                }
            }

            $i++;
        }

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