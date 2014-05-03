<?php

require '../../../arg.php';

class data extends FreiChat {

    public function get_total_messages() {
        $sql = $this->db->query("SELECT count(*) as cnt from frei_chat");
        $res = $sql->fetchAll();
        return $res[0]['cnt'];
    }

    public function get_online_users() {
        $sql = $this->db->query("SELECT count(*) as cnt from frei_session WHERE time>" . $this->online_time2);
        $res = $sql->fetchAll();
        return $res[0]['cnt'];
    }

    public function get_banned_users() {
        $sql = $this->db->query("SELECT count(*) as cnt from frei_banned_users");
        $res = $sql->fetchAll();
        return $res[0]['cnt'];
    }

    public function get_values() {

        $total_messages = $this->get_total_messages();
        $online_users = $this->get_online_users();
        $banned_users = $this->get_banned_users();

        $values = array($online_users, $total_messages, $banned_users);
        echo json_encode($values);
    }

    public function get_mesg_stats() {

        $from_day = $_GET['from_day'];
        $to_day = $_GET['to_day'];
        $query = "SELECT count(message) msg_count,MAX(UNIX_TIMESTAMP(sent))*1000  on_day from frei_chat WHERE sent> '$from_day' AND sent <'$to_day' GROUP BY DAY(sent) ORDER BY sent";
        $res = $this->db->query($query);
        $res = $res->fetchAll();

        if ($res != null) {
            echo json_encode($res);
        } else {
            echo "[]";
        }
    }

}

if (isset($_REQUEST)) {
    $data = new data();

    if ($_REQUEST['data_mode'] == 'get_values') {
        $data->get_values();
    } else if ($_REQUEST['data_mode'] == 'get_mesg_stats') {
        $data->get_mesg_stats();
    }
}