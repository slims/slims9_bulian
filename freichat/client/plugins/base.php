<?php

session_start(); //Start the session
//Base class for Plugins

require '../../../arg.php';


class base extends FreiChat{

//------------------------------------------------------------------------------------------------

    public function __construct() {
        parent::__construct();
        $this->init_vars();
        $this->get_js_config();
    }
//----------------------------------------------------------------------------------------------
    public function bigintval($value) {
        $value = trim($value);
        if (ctype_digit($value)) {
            return $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $value;
        }
        return 0;
    }
}