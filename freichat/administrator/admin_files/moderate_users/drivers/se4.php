<?php

require 'base.php';

class se4 extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'displayname';
        $this->row_userid = 'user_id';
    }


}
