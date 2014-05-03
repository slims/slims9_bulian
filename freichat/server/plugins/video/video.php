<?php

class video extends Conn{

    public function __construct() {
        parent::__construct();
    }

//-------------------------------------------------------------------
    public function get_video_offers() {

        $rid = (int) $_GET['rid'];
        $time = $_GET['time'];

        $sql = 'SELECT msg_type AS "type" , msg_label AS "label", msg_data AS "data", msg_time AS "time"
                    FROM frei_video_session WHERE from_id!= ? AND rid= ? AND msg_time>?';
        $query = $this->db->prepare($sql);

        $arr = array($this->frm_id, $rid, $time);
        $query->execute($arr);
        $msgs = $query->fetchAll();

        $n_time = $this->get_last_message_time($msgs, $time);

        $result = array(
            "msgs" => $msgs,
            "time" => $n_time
        );

        echo json_encode($result);
    }

//-------------------------------------------------------------------------
    public function get_video_details() {

        $rid = (int) $_GET['rid'];

        $query = "SELECT from_id, msg_data FROM frei_video_session WHERE id=$rid AND msg_label=3";
        $res = $this->db->query($query);

        $arr = array();

        //i know this is confusing :(
        foreach ($res as $result) {

            $data = json_decode($result['msg_data']);
            if ($result['from_id'] == $this->frm_id) {
                $arr['initiator'] = "no";
                $arr['toname'] = $data[3];
                $arr['to'] = $data[2];
                $arr['from'] = $this->frm_id;
                $arr['fromname'] = $this->frm_name;
            } else {
                $arr['initiator'] = "yes";
                $arr['toname'] = $data[1];
                $arr['to'] = $data[0];
                $arr['from'] = $this->frm_id;
                $arr['fromname'] = $this->frm_name;
            }
        }
        echo json_encode($arr);
    }

//-------------------------------------------------------------------------
    public function video() {

        $rid = (int) $_POST['rid'];
        $time = time() . str_replace(" ", "", microtime());
        $fromid = $this->frm_id;

        $query = "INSERT INTO frei_video_session (rid,from_id,msg_type,msg_label,msg_data,msg_time) VALUES (?,?,?,?,?,?)";
        $p_query = $this->db->prepare($query);
        $label = 0;
        if ($_POST['type'] == "candidate") {
            $label = 1;
        }

        $param = array($rid, $fromid, $_POST['type'], $label, $_POST['data'], $time);
        $p_query->execute($param);
        //}
    }

//-------------------------------------------------------------------------

    public function gen_room_id() {
        $query = "INSERT INTO frei_video_session (from_id,msg_type,msg_label,msg_data,msg_time) 
                        VALUES (?, ?, ?, ?, ?)";
        $p_query = $this->db->prepare($query);

        $time = time() . str_replace(" ", "", microtime());
        $to = $this->get_clean_id($_POST['to']);
        $to_name = htmlentities($_POST['to_name'], ENT_QUOTES, "UTF-8");

        $data = json_encode(array(
            $to, $to_name, $this->frm_id, $this->frm_name
        ));

        $p_query->execute(array($to, "new", "3", $data, $time));

        $lastid = (int) $this->db->lastInsertId();

        return $lastid;
    }

//-------------------------------------------------------------------------

    public function create_video_session() {

        $type = $_POST['type'];

        $lastid = (int) $_POST['rid'];

        if ($type == "caller") {
            $lastid = $this->gen_room_id();
        }

        $this->prepare_insert_msg();

        $time = time() . str_replace(" ", "", microtime());
        $usr_name = str_replace("'", "", $this->frm_name);
        $GMT_time = $this->bigintval($_POST['GMT_time']);
        $frm_id = $this->frm_id;
        $to = $this->get_clean_id($_POST['to']);
        $to_name = htmlentities($_POST['to_name'], ENT_QUOTES, "UTF-8");

        $id = $_POST['id'];
        $xhash = $_POST['xhash'];
        $url = $this->url . "client/plugins/videochat/videochat.php?rid=$lastid&id=$id&xhash=$xhash";

        if ($type == "caller") {
            $message = '<div class="frei_video_request">' . $this->frei_trans["vid_req_rec"] . '<a  href="' . $url . '" target="_blank" onClick=\'FreiChat.sendvideo("' . $usr_name . '","' . $frm_id . '",2,' . $lastid . ')\'>' . $this->frei_trans['vid_start_call'] . '</a></div>';
        } else if ($type == "callee") {
            $message = '<div class="frei_video_request">' . $this->frei_trans["vid_req_accept"] . '<a href="' . $url . '" target="_blank" onClick=\'FreiChat.sendvideo("' . $usr_name . '","' . $frm_id . '",3,' . $lastid . ')\'>' . $this->frei_trans['vid_start_call'] . '</a></div>';
        } else {
            $message = 'ERR: 402: BAD REQUEST';
        }


        $this->insert_mesg_query->execute(array($frm_id, $usr_name, $to, $to_name, $message, $this->mysql_now, $time, 2, '-1', $GMT_time));
        echo json_encode($lastid);
    }

}