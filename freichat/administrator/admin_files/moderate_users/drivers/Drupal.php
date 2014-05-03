<?php

require 'base.php';

class Drupal extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'name';
        $this->row_userid = 'uid';
    }


}
