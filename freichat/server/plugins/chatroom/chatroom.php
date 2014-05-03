<?php

class chatroom extends Conn {

    
    public function __construct() {
        parent::__construct();
    }
    //----------------------------------------------------------------------

    public function loadchatroom() {

        $freichat = new freichat_data();

        $active_room = (int) $_GET['in_room'];
        $_SESSION[$this->uid . 'in_room'] = $active_room;

        $chatroom_mesg_time = $_GET['chatroom_mesg_time'];


        $query = "UPDATE frei_session set in_room = " . $active_room . " WHERE permanent_id=" . $_SESSION[$this->uid . 'gst_ses_id'];
        $this->db->query($query);

        // commented v8.2
        $options = array(
            "id" => $_GET['id'],
            "custom_mesg" => htmlentities($_GET['custom_mesg'], ENT_QUOTES),
            "usr_list_wanted" => false,
            "first" => true,
            "in_room" => $active_room
        );

        $ob = $this->call_driver($options);
        $users = $ob->getList();

        $chatroom_user_array = array();

        //TODO: Remove this dependency
        require_once RDIR . '/client/themes/' . $this->color . '/argument.php';

        foreach ($users as $res) {

            $guest = $res['username'];
            $guest = strlen($guest) > 30 ? $this->msubstr($guest, 0, 16) . "..." : $guest;
            if ($this->linkprofile == 'enabled' && $res['guest'] == 0 && $_SESSION[$this->uid . "is_guest"] == 0) {
                $guest = strlen($guest) > 18 ? $this->msubstr($guest, 0, 12) . "..." : $guest;
            }


            $avatar_url = "http://www.gravatar.com/avatar/" . md5($guest) . "?s=24&d=wavatar"; //$this->url . "/client/jquery/user.jpeg";

            if (isset($res[$this->avatar_field_name])) {

                if ($res[$this->avatar_field_name] != "") {
                    $avatar_url = $ob->avatar_url($res[$this->avatar_field_name]);
                } else {
                    $avatar_url = "http://www.gravatar.com/avatar/" . md5($guest) . "?s=24&d=wavatar";
                }
            }

            $guest = str_replace("'", " ", $guest);

            if ($active_room == $res['in_room']) {

                $img_url = $this->get_statusimg_url($res['status'], $onlineimg, $busyimg);
                $chatroom_user_array[] = array("username" => $guest, "userid" => $res['session_id'], "avatar" => $avatar_url, "img_url" => $img_url);
            }
        }



        $curr_time = $_GET['time'];


        $messages = $this->get_chatroom_messages($active_room, 'multi', $chatroom_mesg_time);
        $get_mesg = $this->get_messages($_GET['time']);
        $last_mesg_time = $this->get_last_message_time($get_mesg, $curr_time);
        $chatroom_mesg_time = $this->get_last_message_time($messages, $chatroom_mesg_time);
        $this->update_messages($active_room);


        $freichat->chatroom_users_array = $chatroom_user_array;
        $freichat->in_room = $active_room;
        $freichat->chatroom_messages = $messages;
        $freichat->messages = $get_mesg;
        $freichat->time = $last_mesg_time;
        $freichat->chatroom_mesg_time = $chatroom_mesg_time;

        echo json_encode($freichat);
    }

//-------------------------------------------------------------------
    public function get_chatroom_messages($active_room, $all, $time = null) {

        $frm_id = $this->frm_id;

        if ($active_room == -1) {
//            $active_room = 0;
        }
        if (isset($_GET['mobile'])) {
            $room_cond = "";
        } else {
            $room_cond = 'AND f.room_id=' . $active_room;
        }

        $lim = $this->lim_top(50);

        if ($all == 'multi') {
            $get_mesg_query = "SELECT DISTINCT $lim[0] * FROM frei_chat WHERE room_id=" . $active_room . " 
                                AND message_type=1 order by time DESC $lim[1]";
            //echo $get_mesg_query;
        } else if ($all === 'single') {

            $get_mesg_query = "SELECT DISTINCT * FROM frei_chat AS f  WHERE f.\"from\" != " . $frm_id . " " . $room_cond . " 
                                AND f.time> " . $time . "  AND f.message_type=1 order by time DESC";
        } else {
            $get_mesg_query = "SELECT DISTINCT $lim[0] * FROM frei_chat WHERE room_id=1 AND message_type=1 order by time DESC $lim[1]";
        }

        $chatroom_mesgs = array_reverse($this->db->query($get_mesg_query)->fetchAll());
        return $chatroom_mesgs;
    }

//---------------------------------------------------------------------------------

    public function get_rooms($first = 'ajax') {

        if ($first == 'false' || $first == 'ajax') {

            //get all chatrooms on first req
            $query2 = "SELECT  r.room_type,r.room_author,r.room_name,r.id as room_id,count(s.id) as online_count
                    FROM frei_rooms as r
                    LEFT   join  frei_session as s
                    on r.id=s.in_room
                    AND s.time > " . $this->online_time2 . "
                    GROUP BY r.id,r.room_name,r.room_order,r.room_author,r.room_type ORDER BY r.room_order";

            $rooms = $this->db->query($query2)->fetchAll();
            $room_created = time();
        } else {

            //get only mods on subsequent reqs

            $query2 = "SELECT  r.room_created,r.room_type,r.room_author,r.room_name,r.id as room_id
                    FROM frei_rooms as r
                    WHERE r.room_created > " . $_SESSION[$this->uid . "room_created"] . "
                    ORDER BY r.room_created";
            $rooms = $this->db->query($query2)->fetchAll();

            if (!empty($rooms)) {
                $room = end($rooms);
                $room_created = $room["room_created"];
            } else {
                $room_created = $_SESSION[$this->uid . "room_created"];
            }
        }

        $_SESSION[$this->uid . "room_created"] = $room_created;

        if ($first == 'ajax') {

            $data = new freichat_data;
            $data->rooms = $rooms;
            $data->online_cnt = $this->get_online_cnt();
            echo json_encode($data);
        }
        else {
            return $rooms;
        }
    }

//---------------------------------------------------------------------------------
    public function get_online_cnt() {

        $query3 = "SELECT  r.id,count(s.id) as online_count
                    FROM frei_rooms as r
                    LEFT   join  frei_session as s
                    on r.id=s.in_room
                    AND s.time > " . $this->online_time2 . "
                    Group BY r.id,r.room_order,r.id ORDER BY r.room_order";

        return $this->db->query($query3)->fetchAll();
    }

//---------------------------------------------------------------------------------
    public function create_chatroom() {


        /*
         * Room type
         * 
         * 0 -> admin created public chatroom
         * 1 -> admin created private chatroom
         * 2 -> user created public chatroom
         * 3 -> user created private chatroom
         * 
         */


        $room_author = $this->frm_id;
        $room_name = $_POST['name'];
        $room_pass = $_POST['password'];
        $room_created = time();
        $room_last_active = $room_created;
        $room_order = 125;

        if ($room_pass == '') {
            $room_type = 2;
        } else {
            $room_type = 3;
        }

        $qry = "INSERT INTO frei_rooms (room_author,room_name,room_type,room_password,room_created,room_last_active,room_order) VALUES(?,?,?,?,?,?,?)";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array($room_author, $room_name, $room_type, $room_pass, $room_created, $room_last_active, $room_order));

        $lastid = (int) $this->db->lastInsertId();

        echo $lastid;
    }

//---------------------------------------------------------------------------------

    public function delete_chatroom() {

        $room_id = (int) $_POST['room_id'];

        $qry = "DELETE FROM frei_rooms WHERE id=$room_id";
        $this->db->query($qry);
    }

//---------------------------------------------------------------------------------

    public function update_chatroom_activity($room_id) {

        $room_id = (int) $room_id;
        $time = time();
        $qry = "UPDATE frei_rooms SET room_last_active=$time WHERE id=$room_id";
        $this->db->query($qry);
    }

//---------------------------------------------------------------------------------    

    public function delete_inactive_chatrooms() {

        $a_time = time() - $this->chatroom_expiry;
        $qry = "DELETE from frei_rooms WHERE room_last_active < $a_time AND room_type <> 0 AND room_type <> 1";
        $this->db->query($qry);
    }

//---------------------------------------------------------------------------------        

    public function validate_chatroom_password() {

        $password = $_POST['password'];
        $room_id = $_POST['room_id'];

        $qry = "SELECT room_author FROM frei_rooms WHERE room_password=? AND id=?";
        $stmt = $this->db->prepare($qry);
        $stmt->execute(array($password, $room_id));

        if ($stmt->rowCount() > 0) {
            echo "correct";
        }
    }

    public function parseBBcode($text) {
        $text = preg_replace(array(
            /* '/\[b\](.*?)\[\/b\]/ms', 
              '/\[i\](.*?)\[\/i\]/ms',
              '/\[u\](.*?)\[\/u\]/ms',
              '/\[img\](.*?)\[\/img\]/ms',
              '/\[email\](.*?)\[\/email\]/ms',
              '/\[url\="?(.*?)"?\](.*?)\[\/url\]/ms',
              '/\[size\="?(.*?)"?\](.*?)\[\/size\]/ms',
              '/\[youtube\](.*?)\[\/youtube\]/ms', */
            '/\[color\="?(.*?)"?\](.*?)\[\/color\]/ms',
                /* '/\[quote](.*?)\[\/quote\]/ms',
                  '/\[list\=(.*?)\](.*?)\[\/list\]/ms',
                  '/\[list\](.*?)\[\/list\]/ms',
                  '/\[\*\]\s?(.*?)\n/ms' */
                ), array(
            /* '<strong>\1</strong>',
              '<em>\1</em>',
              '<u>\1</u>',
              '<img src="\1" alt="\1" />',
              '<a href="mailto:\1">\1</a>',
              '<a href="\1">\2</a>',
              '<span style="font-size:\1%">\2</span>',
              '<object width="450" height="350"><param name="movie" value="\1"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="\1" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="450" height="350"></embed></object>', */
            '<span style="color:\1">\2</span>'
                /* '<blockquote>\1</blockquote>',
                  '<ol start="\1">\2</ol>',
                  '<ul>\1</ul>',
                  '<li>\1</li>' */
                ), $text);

        return $text;
    }

//---------------------------------------------------------------------------------        
}