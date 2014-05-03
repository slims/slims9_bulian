<?php

require 'base.php';

class Joomla extends Moderation {
    

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'username';
        $this->row_userid = 'id';
    }
    
}
