<?php

require 'base.php';

class Sugarcrm extends Moderation {

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'user_name';
        $this->row_userid = 'id';
    }

}
