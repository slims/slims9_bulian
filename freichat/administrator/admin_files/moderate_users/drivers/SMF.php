<?php

require 'base.php';

class SMF extends Moderation {
    
    public function set_db_data() {
        $this->usertable = 'members';
        $this->row_username = 'memberName';
        $this->row_userid = 'ID_MEMBER';
    }


}
