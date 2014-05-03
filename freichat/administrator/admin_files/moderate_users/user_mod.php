<?php

require '../../../arg.php';

class user_mod extends FreiChat {

    public function __construct() {
        parent::__construct();
        $this->init_vars();
        require 'drivers/' . $this->driver . '.php';
        $this->mod = new $this->driver();
        $this->mod->db_prefix = $this->db_prefix;
        $this->mod->row_username = $this->row_username;
        $this->mod->row_userid = $this->row_userid;
        $this->mod->usertable = $this->usertable;
        $this->mod->db = $this->db;
        $this->mod->pdo_driver = $this->pdo_driver;

        $this->mod->set_db_data();
        // $this->connect_db();
    }

    public function get_data() {
        $from = (int)$_POST['lim_from'];
        $no_of_records = (int)$_POST['lim_records'];
        $search_string = $_POST['search_string'];
        $show_what = $_POST['show_what'];
        
        $users = $this->mod->get_users($from,$no_of_records,$search_string,'',$show_what);
        
        echo json_encode($users);
    }

    public function ban($id) {
        $query = 'INSERT INTO frei_banned_users (user_id) VALUES(' . $id . ')';
        $this->db->query($query);
    }

    public function unban($id) {
        $query = 'DELETE FROM frei_banned_users WHERE user_id=' . $id;
        $this->db->query($query);
    }

}

if (isset($_REQUEST['mode'])) {

    $mod = new user_mod();

    if ($_REQUEST['mode'] == 'ban') {
        $mod->ban($_POST['id']);
    } else if ($_REQUEST['mode'] == 'unban') {
        $mod->unban($_POST['id']);
    } else if ($_REQUEST['mode'] == 'get_data') {
        $mod->get_data();
    }
}