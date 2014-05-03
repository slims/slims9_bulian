<?php

require 'base.php';

class Phpvms extends Moderation {

    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'firstname';
        $this->row_userid = 'pilotid';
    }

}
