<?php

require 'base.php';

class WordPress extends Moderation {

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'user_login';
        $this->row_userid = 'ID';
    }
    
}
