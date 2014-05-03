<?php

require 'base.php';

class Phpfusion extends Moderation {

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'user_name';
        $this->row_userid = 'user_id';
    }

}
