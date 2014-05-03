<?php

require 'base.php';

class SMF2 extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'members';
        $this->row_username = 'member_name';
        $this->row_userid = 'id_member';
    }


}
