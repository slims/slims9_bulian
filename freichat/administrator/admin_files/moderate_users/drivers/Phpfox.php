<?php

require 'base.php';

class Phpfox extends Moderation {

    public function set_db_data() {
        $this->usertable = 'user';
        $this->row_username = 'user_name';
        $this->row_userid = 'user_id';
    }

}
