<?php

require 'base.php';

class Etano extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'dating_user_accounts';
        $this->row_username = 'user';
        $this->row_userid = 'user_id';
    }


}
