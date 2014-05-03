<?php

class event extends driver_base{

    private $hooks;

    public function __construct() {
       /* $this->db = parent::$db;
        $this->db_prefix = parent::$db_prefix;
        $this->time_string = parent::$time_string;
        */$this->hooks = array();
        
    }

    public function register_hook($func, $args, $hook) {
        array_push($this->hooks, array($func, $args, $hook));
    }

    public function call_hook($action) {
        $i = 0;
        foreach ($this->hooks as $hook) {
            if ($hook[$i][2] == $action) {
                call_user_func_array($hook[$i][0], $hook[$i][1]);
            }
            $i++;
        }
    }

    public function add_user() {
        print_r($this);
        if(!isset($_SESSION[$this->uid . 'usr_name'])) {
            //some problem
            return false;
        }
        
        $targets = array();
        
        $query = parent::getList();
        
        foreach($targets as $target) {
          
          $data = array($_SESSION[$this->uid . 'usr_name']);  
          $values[] = "(" . $this->db->quote($_SESSION[$this->uid . 'usr_name']) . "," . $target['session_id'] . ",
                          1 ," . json_encode($data) . ", ".$this->time_string.")";
        }     
        
        $values = implode(", ", $values);
        $query = "INSERT INTO frei_notifications (actor,target,type,data,timestamp) VALUES $values)";
        $this->db->query($query);
        
        return true;
    }

    public function remove_user() {
        //similar to user logs out/inactive goes offline
    }

    public function mod_user($action, $type) {
        $this->call_hook($action);

        if ($action == "UADD") {
            $this->add_user();
        } else {
            $this->remove_user();
        }
    }

}
