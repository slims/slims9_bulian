<?php

require 'base.php';

class se4 extends base{

public $set_file='application/settings/database.php';    
public $redir=false;    


public function info($path_host) {

    $this->info['cookie_where'] = 'Add the following line at the end of index.php file in your social engine installation directory';
    $this->info['cookie_code'] = 'setcookie("freichat_user", Engine_Api::_()->user()->getViewer()->getIdentity(), time()+3600, "/");';
    
    $this->info['code_add'] = 'Login to Admin Panel and go to the Layout Editor. Select Site Header then add a new HTML Block.';
    $this->info['jsloc'] = 'In the text area copy paste the following code and save your Site Header.';
    
    $this->info['phpcode'] = '<!--===============FreiChatX========START========================-->';
    
    $this->info['jscode']  = '<script type="text/javascript" language="javascipt" src="' . $path_host . 'client/main.php"></script>';
    $this->info['csscode'] = '<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
                               <!--===========================FreiChatX=======END=========================-->';
    
    return $this->info;
}


function get_config() {

        define('_ENGINE','donotdie');
        
        $config = require $_SESSION['config_path'];
        $conf = $config['params'];
        
        $conf[0] = $conf['host'];
        $conf[1] = $conf['username'];
        $conf[2] = $conf['password'];
        $conf[3] = $conf['dbname'];
        $conf[4] = $config['tablePrefix'];

        return $conf;
}


}
