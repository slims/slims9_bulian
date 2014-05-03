<?php

if (!isset($_REQUEST['freimode']))
    exit;
if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}

session_start();
error_reporting(-1);


require '../arg.php';


$video_req = array("video", "create_video_session", "get_video_offers", "get_video_details", "video_req", "gen_room_id");
$arr = array("post", "video", "create_video_session", "update_status", "create_chatroom", "delete_chatroom", "validate_chatroom_password");

if (in_array($_REQUEST['freimode'], $arr)) {
    $id = $_POST['id'];
    if (isset($_SESSION[$uid . "is_xc"]) && $_SESSION[$uid . "is_xc"] == true) {
        $_POST['xhash'] = md5($id . $uid);
    }
    $xhash = $_POST['xhash'];
} else {
    $id = $_GET['id'];
    if (isset($_SESSION[$uid . "is_xc"]) && $_SESSION[$uid . "is_xc"] == true) {
        $_GET['xhash'] = md5($id . $uid);
    }
    $xhash = $_GET['xhash'];
}



if (md5($id . $uid) != $xhash) {
    //$_SESSION[$uid . 'usr_ses_id'] = 0; some problem ??
    echo 'invalid install uid';
    $_GET['id'] = 0;
    $_GET['first'] = 'false';
    exit;
}

class freichat_data {
    
}

class Conn extends FreiChat {

    public $userdata;
    public $messages = array();
    public $chatroom_raw_mesgs = array();
    public $isset_video_offer = false;
    public $is_mobile;

    public function __construct() {
        parent::__construct();
        $this->init_vars();
        require_once RDIR . '/server/drivers/' . $this->driver . '.php';

        $this->set_vars();
    }

//-------------------------------------------------------------------------    
    public function set_vars() {
        $this->url = str_replace('server/freichat.php', '', $this->url);
        $this->frm_id = $_SESSION[$this->uid . 'usr_ses_id'];
        $this->frm_name = $_SESSION[$this->uid . 'usr_name'];


        if (isset($_GET['mobile'])) {
            $this->is_mobile = (int) $_GET['mobile'];
        } else {
            $this->is_mobile = 0;
        }

        if ($this->is_mobile == 1) {
            $this->show_videochat_plugin = 'disabled';
        }

        if ($this->debug == true) {

            error_reporting(-1);
        }
    }

//-----------------------------------s--------------------------------------
    private function inc_cls() {

        if ($this->show_chatroom_plugin == 'enabled') {

            require 'plugins/chatroom/chatroom.php';
            $this->chatroom = new chatroom();
        }

        if ($this->show_videochat_plugin == 'enabled') {

            require 'plugins/video/video.php';
            $this->video = new video();
        }
    }

//-------------------------------------------------------------------------
    public function bigintval($value) {
        $value = trim($value);
        if (ctype_digit($value)) {
            return $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $value;
        }
        return 0;
    }

//-------------------------------------------------------
    public function msubstr($str, $from, $len) {
        return preg_replace('#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $from . '}' .
                '((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,' . $len . '}).*#s', '$1', $str);
    }

//-------------------------------------------------------
    public function json_encode($a = false) {
        if (!function_exists('json_encode')) {
            if (is_null($a))
                return 'null';
            if ($a === false)
                return 'false';
            if ($a === true)
                return 'true';
            if (is_scalar($a)) {
                if (is_float($a)) {
// Always use "." for floats.
                    return floatval(str_replace(",", ".", strval($a)));
                }

                if (is_string($a)) {
                    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
                }
                else
                    return $a;
            }
            $isList = true;
            for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
                if (key($a) !== $i) {
                    $isList = false;
                    break;
                }
            }
            $result = array();
            if ($isList) {
                foreach ($a as $v)
                    $result[] = json_encode($v);
                return '[' . join(',', $result) . ']';
            } else {
                foreach ($a as $k => $v)
                    $result[] = json_encode($k) . ':' . json_encode($v);
                return '{' . join(',', $result) . '}';
            }
        }
    }

//----------------------------------------------------------------------
    public function check_perms() {
        if (($this->show_name == 'buddy' || $this->show_name == 'user') && $_SESSION[$this->uid . 'is_guest'] == 1) {
            $status = "guesthasnopermissions";
        } else if ($this->show_name == 'guest' && $_SESSION[$this->uid . 'is_guest'] == 1) {
            $status = "guesthaspermissions";
        } else if (($this->show_name == 'buddy' || $this->show_name == 'user' || $this->show_name == 'guest') && $_SESSION[$this->uid . 'is_guest'] == 0) {
            $status = "userloggedin";
        } else {
            $this->freichat_debug('Something seems to be wrong in ' . RDIR . '/server/freichat.php (get_members)');
        }
        return $status;
    }

//----------------------------------------------------------------------
    public function get_statusimg_url($status, $onlineimg, $busyimg) {


        $img_url = '';
        if ($status == 1 || $status == 4) {
            $status = "online";
            $img_url = $this->url . 'client/themes/' . $this->color . '/' . $onlineimg;
        } else if ($status == 3) {
            $status = "busy";
            $img_url = $this->url . 'client/themes/' . $this->color . '/' . $busyimg;
        } else {
            /* User should be offline or invisible */
        }

        return $img_url;
    }

//-------------------------------------------------------------------
    protected function lim_top($lim) {
//if only the standards provided the limits
//limit -> mysql,postgresql,sqlite
//top -> mssql 

        if ($this->pdo_driver == "sqlsrv") {
            return array(" TOP $lim ", " ");
        } else {
            return array(" ", " LIMIT $lim ");
        }
    }

//----------------------------------------------------------------------
    /*  public function delete_messages() {
      if (isset($_SESSION[$this->uid . 'delay'])) {
      if ($_SESSION[$this->uid . 'delay'] > 50) {
      $_SESSION[$this->uid . 'delay'] = 0;
     * 
     * NOTE: if you want to use this func. keep in mind the **microtime** 

      $delete_mesg_query = "DELETE FROM frei_chat  WHERE recd =1 AND sent < $this->mysql_now-" . $this->time;
      $this->db->query($delete_mesg_query);
      } else {
      $_SESSION[$this->uid . 'delay'] = $_SESSION[$this->uid . 'delay'] + 5;
      }
      } else {
      $_SESSION[$this->uid . 'delay'] = 0;
      }
      }
     */
//----------------------------------------------------------------------
    public function update_messages($active_room) {
        $update_mesg_query = "UPDATE frei_chat set recd = 1 WHERE (frei_chat.\"to\" = " . $this->frm_id . " OR room_id=" . $active_room . ") AND recd = 0";
        $this->db->query($update_mesg_query);
    }

//----------------------------------------------------------------------
    public function get_last_message_time($get_mesg, $time) {


        if ($get_mesg != null) {
            $end_mesg = end($get_mesg);
            $time = $end_mesg['time'];
        }

        if ($time == 0) {
            $time = time() . str_replace(" ", "", microtime());
            return $time;
        }
        return $time;
    }

//----------------------------------------------------------------------
    public function call_driver($options) {

        $update_usr_info = false;

        if ($_SESSION[$this->uid . 'custom_mesg'] != $options['custom_mesg'] || $_SESSION[$this->uid . 'in_room'] != $options['in_room']) {
            $update_usr_info = true;
        }
        
        if($_SESSION[$this->uid . 'usr_name'] != $options['custom_gst_name'] && $_SESSION[$this->uid . 'is_guest'] == 1) {
            $_SESSION[$this->uid . 'usr_name'] = $options['custom_gst_name'];
            $_SESSION[$this->uid . 'gst_nam'] = $_SESSION[$this->uid . 'usr_name'];
            $update_usr_info = true;
        }

        $sessions = new $this->driver($this->db);
        $sessions->uid = $this->uid;
        $sessions->permanent_name = $this->permanent_name;
        $sessions->permanent_id = $this->permanent_id;
        $sessions->online_time = $this->online_time;
        $sessions->online_time2 = $this->online_time2;
        $sessions->time_string = $this->time_string;
        $sessions->show_name = $this->show_name;
        $sessions->options = $options;
        $sessions->usr_list_wanted = $options['usr_list_wanted'];
        $sessions->db_prefix = $this->db_prefix;
        $sessions->displayname = $this->displayname;
        $sessions->frei_trans = $this->frei_trans;
        $sessions->debug = $this->debug;
        $sessions->update_usr_info = $update_usr_info;
        $sessions->url = $this->url;
        $sessions->driver = $this->driver;
        $sessions->to_freichat_path = $this->to_freichat_path;
        $sessions->long_polling = $this->long_polling;
        $sessions->sef_link_profile = $this->sef_link_profile;
        $sessions->ug_ids = $this->ug_ids;
        $sessions->avatar_field_name = $this->avatar_field_name;

        $sessions->row_username = $this->row_username;
        $sessions->row_userid = $this->row_userid;
        $sessions->usertable = $this->usertable;
        $sessions->use_cookie = $this->use_cookie;
        $sessions->pdo_driver = $this->pdo_driver;

        return $sessions;
    }

//----------------------------------------------------------------------
    public function get_messages($time) {

        $frm_id = $this->frm_id;
        $result = array();

        if ($time == 0) {
//$get_mesg_query = "SELECT DISTINCT * FROM frei_chat WHERE frei_chat.\"to\"=" . $frm_id . "AND time<2 order by time";
        } else {
            $get_mesg_query = "SELECT * FROM frei_chat WHERE frei_chat.\"to\"=" . $frm_id . " AND time>" . $time . " AND message_type<>1 order by time ";
            $result = $this->db->query($get_mesg_query)->fetchAll();
        }


        return $result;
    }

//----------------------------------------------------------------------
    public function getmembers() {

        $freichat = new freichat_data();

        $onlcnt = 0;
        $online_time = $this->online_time2;
        $text = array();
        $guest = NULL;
        $freichat->userdata = array();

        if (isset($_SESSION[$this->uid . 'freistatus']) == false) {
            $_SESSION[$this->uid . 'freistatus'] = 1;
        }

        $active_room = (int) $_GET['in_room'];


        $update_usr_info = false;


        $_SESSION[$this->uid . 'in_room'] = $active_room;


        if (!isset($_SESSION[$this->uid . 'custom_mesg'])) {
            $_SESSION[$this->uid . 'custom_mesg'] = $this->frei_trans['default_status'];
        }

        $custom_mesg = htmlentities($_GET['custom_mesg'], ENT_QUOTES, "UTF-8");
        $custom_gst_name = htmlentities($_GET['custom_gst_name'], ENT_QUOTES, "UTF-8");

        
        if ($_GET['custom_mesg'] != 'i am null') {
            $_SESSION[$this->uid . 'custom_mesg'] = $custom_mesg;
        }

        $first = $_GET['first'];

        $options = array(
            "id" => $_GET['id'],
            "custom_mesg" => $custom_mesg,
            "custom_gst_name" => $custom_gst_name,
            "usr_list_wanted" => true,
            "first" => $_GET['first'],
            "in_room" => $active_room
        );

        $object = $this->call_driver($options);
        $result = $object->load_driver();
        //var_dump($result);
        $profile_img = $this->url . 'client/themes/' . $this->color . '/profilelink.png';
        $path = str_replace($this->to_freichat_path, "", $this->url);
//for($i=0;$i<=10;$i++){
        //TODO: remove this dependency
        require_once RDIR . '/client/themes/' . $this->color . '/argument.php';
        $active_room = $_SESSION[$this->uid . 'in_room'];

        $chatroom_user_array = array();

        foreach ($result as $res) {

            $guest = $res['username'];
            $guest = strlen($guest) > 30 ? $this->msubstr($guest, 0, 16) . "..." : $guest;
            $img_url = $this->get_statusimg_url($res['status'], $onlineimg, $busyimg);
            $profile_link = '';
            
            if ($this->linkprofile == 'enabled' && $res['guest'] == 0 && $_SESSION[$this->uid . "is_guest"] == 0) {
                $profile_link = $object->linkprofile_url($res, $path, $profile_img);
                $guest = strlen($guest) > 18 ? $this->msubstr($guest, 0, 12) . "..." : $guest;
            }


            $avatar_url = "http://www.gravatar.com/avatar/" . md5($guest) . "?s=24&d=wavatar"; //$this->url . "/client/jquery/user.jpeg";

            if (isset($res[$this->avatar_field_name])) {

                if ($res[$this->avatar_field_name] != "") {
                    $avatar_url = $object->avatar_url($res);
                } else {
                    $avatar_url = "http://www.gravatar.com/avatar/" . md5($guest) . "?s=24&d=wavatar";
                }
            }

            $guest = str_replace("'", " ", $guest);
            $onlcnt++;

            $show_name = (strlen($guest) > 20 ? $this->msubstr($guest, 0, 10) . "..." : $guest);

            if ((isset($res['in_room']) && $active_room == $res['in_room']) && $this->show_chatroom_plugin == 'enabled') {
                $chatroom_user_array[] = array("username" => $guest, "userid" => $res['session_id'], "avatar" => $avatar_url, "img_url" => $img_url);
            }

            $freichat->userdata[] = array(
                "username" => $guest,
                "userid" => $res['session_id'],
                "avatar" => $avatar_url,
                "img_url" => $img_url,
                "show_name" => $show_name,
                "status_mesg" => $res['status_mesg'],
                "profile_link" => $profile_link
            );


            //$logged = true;
        }
//}

        if ($this->show_chatroom_plugin == 'enabled') {

            $freichat->room_array = $this->get_rooms($first);
            $freichat->room_online_count = $this->get_online_cnt();
            $freichat->chatroom_users_array = $chatroom_user_array;
            $freichat->in_room = $active_room;
            $this->delete_inactive_chatrooms();
        }

        $freichat->islog = $this->check_perms();
        $freichat->status = $_SESSION[$this->uid . 'freistatus'];

        // $freichat->userdata[] = $text;
        $freichat->count = $onlcnt;
        $freichat->username = str_replace("'", "", $this->frm_name);
        $freichat->userid = $this->frm_id;
        $freichat->is_guest = $_SESSION[$this->uid . 'is_guest'];


        if (is_array($_GET['clrchtids'])) {
            if ($_GET['clrchtids'][0] != '') {
                $this->clrcht($_GET["clrchtids"]);
            }
        }

        if ($this->long_polling == "enabled" && $_GET['long_poll'] != 'false' && isset($_SESSION[$this->uid . 'main_loaded'])) {
            session_write_close();
            $new_data = false;

            $time = time();
            while ((time() - $time) < $this->poll_time) {
                // $new_data = array();    



                $freichat = $this->update_message_data($freichat);
                if ($freichat->time > $_GET['time'] /* || $this->isset_video_offer == true */) {
                    // a new message !
                    $new_data = true;
                }


                if ($this->show_chatroom_plugin == "enabled") {
                    if ($freichat->chatroom_mesg_time > $_GET['chatroom_mesg_time']) {
                        // a new message 
                        $new_data = true;
                    }
                }


                if ($new_data == true) {
                    echo json_encode($freichat);
                    break;
                }

                usleep(($this->chatspeed * 1000));
            }

            if ($new_data == false) {
                echo json_encode($freichat);
            }
        } else {


            $freichat = $this->update_message_data($freichat);
            echo json_encode($freichat);
        }
    }

//-------------------------------------------------------------------    
    public function make_array($arr) {
        foreach ($arr as $array) {
            return explode(",", $array);
        }
    }

//-------------------------------------------------------------------
    public function update_message_data($freichat) {


        $curr_time = $_GET['time'];
        $chatroom_mesg_time = $_GET['chatroom_mesg_time'];
        $active_room = $_SESSION[$this->uid . 'in_room'];
        $get_mesg = $this->get_messages($curr_time);

        // $this->delete_messages();
        $this->update_messages($active_room);
        $freichat->time = $this->get_last_message_time($get_mesg, $curr_time);
        $freichat->messages = $get_mesg;
        if ($this->show_chatroom_plugin == 'enabled') {

            if ($_GET['first'] == 'false') {
                $chatroom_messages = $this->get_chatroom_messages($active_room, 'multi', $chatroom_mesg_time);
            } else {
                $chatroom_messages = $this->get_chatroom_messages($active_room, 'single', $chatroom_mesg_time);
            }
            $freichat->chatroom_messages = $chatroom_messages;
            $last_chatroom_message = end($chatroom_messages);
            $freichat->last_chatroom_usr_id = $last_chatroom_message['from'];

            $chatroom_mesg_time = $this->get_last_message_time($chatroom_messages, $chatroom_mesg_time);
            $freichat->chatroom_mesg_time = $chatroom_mesg_time;
        }

        return $freichat;
    }

//-------------------------------------------------------------------
    public function prepare_insert_msg() {
        $insert_mesg_query = "INSERT INTO frei_chat (frei_chat.\"from\",from_name,frei_chat.\"to\",to_name,message,frei_chat.\"sent\",frei_chat.\"time\",message_type,room_id,GMT_time) VALUES(?,?,?,?,?,?,?,?,?,?)";
        $this->insert_mesg_query = $this->db->prepare($insert_mesg_query);
    }

//-------------------------------------------------------------------
    public function post() {
        
        $freichat = new freichat_data();
        
        if(isset($_SESSION[$this->uid . 'is_banned']) && $_SESSION[$this->uid . 'is_banned']) {
            
            //echo json_encode($freichat);
            exit;
        }
        
        $frm_id = $this->frm_id;
        $usr_name = str_replace("'", "", $this->frm_name);
        $room_id = -1;

        if ($_POST['message_type'] == 0 || $_POST['message_type'] == 2) {
            if ($this->driver == "Sugarcrm") {
                $to = $_POST['to'];
            } else {
                $to = $this->bigintval($_POST['to']);
            }
        }

        /*
          0 => normal message
          1 => chatroom message
          2 => video request
         */

        $message_type = null;
        $chatroom_mesg_time = 0.00;
        $active_room = (int) $_POST['in_room'];


        if (isset($_POST['message_type'])) {

            $message_type = strip_tags($_POST['message_type']);
        }

        if (isset($_POST['in_room'])) {
            $room_id = (int) $_POST['in_room'];
        }
        $mesg = $_POST['message'];
        $last_mesg_time = null;
        $get_mesg = null;

        foreach ($mesg as $message) {
            if ($message_type > 1) {
                $messages = array($message);
            } else {
                $messages = explode(',', $message);
            }
        }


        $GMT_time = $this->bigintval($_POST['GMT_time']);
        $this->prepare_insert_msg();

        if ($_POST['to'] == 'FcX_AlIcE') {
            $_REQUEST['oreqmode'] = 'json';
            var_dump($messages);
            $_REQUEST['chatbotmessage'] = $messages[0];
            require '../client/plugins/bot/chat.php';
        }

        foreach ($messages as $message) {
            $message = nl2br($message);

            $to_name = htmlentities($_POST['to_name'], ENT_QUOTES, "UTF-8");
            $time = time() . str_replace(" ", "", microtime());
            if ($message_type == 0 || $message_type == 2) {
                $chatroom_mesg_time = $_POST['chatroom_mesg_time'];
                $this->insert_mesg_query->execute(array($frm_id, $usr_name, $to, $to_name, $message, $this->mysql_now, $time, $message_type, '-1', $GMT_time));
            } else if ($this->show_chatroom_plugin == 'enabled') {
                //$chatroom_mesg_time = $time;
                $message = $this->parseBBcode($message);
                $chatroom_mesg_time = $_POST['chatroom_mesg_time'];
                $this->insert_mesg_query->execute(array($frm_id, $usr_name, $room_id, $room_id, $message, $this->mysql_now, $time, $message_type, $room_id, $GMT_time));
                $this->update_chatroom_activity($room_id);
            } else {
                $this->freichat_debug("undefined message post req");
            }
        }

        $custom_mesg = htmlentities($_POST['custom_mesg'], ENT_QUOTES, "UTF-8");

        if ($_POST['passBYpost'] == true) {


            if (!isset($_SESSION[$this->uid . 'in_room'])) {
                $_SESSION[$this->uid . 'in_room'] = -1;
            }


            $freichat_time = $_POST['time'];
            //settype($freichat_time,"float");

            $get_mesg = $this->get_messages($freichat_time);
            $last_mesg_time = $this->get_last_message_time($get_mesg, $freichat_time);
            // $this->delete_messages();
            $this->update_messages($active_room);

            $_SESSION[$this->uid . 'custom_mesg'] = $custom_mesg;

            $freichat->chatroom_messages = null;

            if ($this->show_chatroom_plugin == 'enabled') {
                $freichat->chatroom_messages = $this->get_chatroom_messages($active_room, 'single', $chatroom_mesg_time);
            }
        }

        $freichat->messages = $get_mesg;
        $freichat->time = $last_mesg_time;
        $freichat->username = $usr_name;
        $freichat->message = $messages;
        $freichat->chatroom_mesg_time = $this->get_last_message_time($freichat->chatroom_messages, $chatroom_mesg_time);

        echo json_encode($freichat);
    }

//--------------------------------------------------------------------------
    public function get_clean_id($id) {
        if (!$this->driver == "Sugarcrm")
            return $this->bigintval($id);
        return $id;
    }

//--------------------------------------------------------------------------
    public function getdata() {
        $freichat = new freichat_data();

        $frm_id = $this->frm_id;
        $exist = false;


        $getdata_query = "SELECT * FROM frei_chat WHERE (frei_chat.\"to\"=" . $frm_id . " OR frei_chat.\"from\"=" . $frm_id . ") 
                            AND message_type<>1 AND message_type<>2 order by time";
        $messages = $this->db->query($getdata_query)->fetchAll();


        foreach ($messages as $analyse) {
            if ($analyse == NULL) {
                $exist = false;
            } else {
                $exist = true;
            }
        }


        $freichat->exist = $exist;
        $freichat->messages = $messages;

        echo json_encode($freichat);
    }

//-------------------------------------------------------------------------------
    public function isset_mesg() {
        $freichat = new freichat_data();
        $frm_id = $this->frm_id;
        $to_id = (int) $_GET['Cid'];


        if (isset($_GET['mobile']) && $_GET['mode'] == "chatroom") {
            $get_mesg = $this->get_chatroom_messages($_GET['active_room'], 'multi');
        } else {
            $isset_mesg_query = "SELECT * FROM frei_chat WHERE ((frei_chat.\"to\"=" . $frm_id . " AND frei_chat.\"from\"=" . $this->db->quote($to_id) . ")
                                    OR (frei_chat.\"from\"=" . $frm_id . " AND frei_chat.\"to\"=" . $this->db->quote($to_id) . ") )
                                    AND message_type <> 2 order by time";
            $get_mesg = $this->db->query($isset_mesg_query)->fetchAll();
        }

        $freichat->messages = $get_mesg;
        $analyze = $freichat->messages;
        $exist = false;
        foreach ($analyze as $analyse) {
            if ($analyse == NULL) {
                $exist = false;
            } else {
                $exist = true;
            }
        }
        $freichat->exist = $exist;
        echo json_encode($freichat);
    }

//-------------------------------------------------------------------------------
    public function clrcht($id) {
        $id = implode(',', $id);

        $clrcht_query = "DELETE FROM frei_chat where (frei_chat.\"to\" IN (" . $this->db->quote($id) . ") AND  frei_chat.\"from\" IN(" . $this->frm_id . ")) OR (frei_chat.\"from\" IN(" . $this->db->quote($id) . ") AND  frei_chat.\"to\" IN(" . $this->frm_id . "))";
        $this->db->query($clrcht_query);
    }

//---------------------------------------------------------------------------------
    public function update_status() {
        $freichat = new freichat_data();
        $user_id = $_SESSION[$this->uid . 'gst_ses_id'];
        $freistatus = (int) $_POST['freistatus'];

        if ($_SESSION[$this->uid . 'freistatus'] != $_POST['freistatus']) {
            $freistatus = ($freistatus == 4) ? 3 : $freistatus;

            $query = "UPDATE frei_session set status=" . $this->db->quote($freistatus) . " WHERE permanent_id=" . $user_id;
            $this->db->query($query);
        }

        $_SESSION[$this->uid . 'freistatus'] = (int) $_POST['freistatus'];

        $freichat->status = (int) $_POST['freistatus'];
        $freichat->id = $user_id;
        echo json_encode($freichat);
    }

//---------------------------------------------------------------------------------
    public function get_new_messages_mobile() {

        //offcourse everything here relates to messages so no prefix message_

        $freichat = new freichat_data();

        $last_rec_time = $_GET['last_rec_time'];

        $messages = $this->get_messages($last_rec_time);
        $last_rec_time = $this->get_last_message_time($messages, $last_rec_time);

        $freichat->messages = $messages;
        $freichat->last_rec_time = $last_rec_time;

        echo json_encode($freichat);
    }

}

$freimode = $_REQUEST['freimode'];

$cls = '';

if (in_array($freimode, $video_req)) {
    $cls = 'video';
    require 'plugins/video/video.php';
} else {
    $cls = 'chatroom';
    require 'plugins/chatroom/chatroom.php';
}



$fc = new $cls();
$fc->$freimode();


if (isset($_SESSION[$fc->uid . 'usr_name']) == false) {
    echo "Unable To Store In session";
    $fc->freichat_debug("Unable to store in session");
    var_dump($_SESSION);
}
