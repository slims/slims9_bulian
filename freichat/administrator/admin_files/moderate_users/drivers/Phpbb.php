<?php

require 'base.php';

class Phpbb extends Moderation {

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'username';
        $this->row_userid = 'user_id';
    }
    
}
