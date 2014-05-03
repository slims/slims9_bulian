<?php

require_once '../base.php';

class rtl extends base {

    public function __construct() {
        parent::__construct();
        if($_SESSION[$this->uid . 'rtl'] == true){
            $_SESSION[$this->uid . 'rtl'] = false;
        }else{
            $_SESSION[$this->uid . 'rtl'] = true;
        }
        
    }

}

$rtl = new rtl();

$url = strip_tags($_GET['referer']);
header("Location: " . $url);
?>