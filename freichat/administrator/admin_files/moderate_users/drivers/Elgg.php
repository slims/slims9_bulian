<?php

require 'base.php';

class Elgg extends Moderation {

    
    public function set_db_data() {
        $this->usertable = 'users_entity';
        $this->row_username = 'username';
        $this->row_userid = 'guid';
    }

}
