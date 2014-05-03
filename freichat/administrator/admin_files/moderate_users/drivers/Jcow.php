<?php

require 'base.php';

class Jcow extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'accounts';
        $this->row_username = 'fullname';
        $this->row_userid = 'id';
    }


}
