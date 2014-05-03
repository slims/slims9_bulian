<?php

require 'base.php';

class Oxwall extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'base_user';
        $this->row_username = 'username';
        $this->row_userid = 'id';
    }


}
