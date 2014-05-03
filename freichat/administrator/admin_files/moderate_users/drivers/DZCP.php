<?php

########################################
#                                      #
#       DZCP Driver for Freichat       #
#      DZCP Version: 1.5.x, 1.6.x      #
#      ______________________          #
#                                      #
#           Mod by: Richy              #
#        www.my-starmedia.de           #
#                                      #
########################################




require 'base.php';

class DZCP extends Moderation {    
    
    public function set_db_data() {
        $this->usertable = 'users';
        $this->row_username = 'nick';
        $this->row_userid = 'id';
    }


}
