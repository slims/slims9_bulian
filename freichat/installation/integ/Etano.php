<?php

require 'base.php';

class Etano extends base {

    public $set_file = "includes/defines.inc.php";
    public $redir = false;

    function info($path_host) {

        $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

        $this->info['cookie_where'] = 'Add the following line at the end of index.php file in your etano installation directory';
        $this->info['cookie_code'] = '
            
        if(isset($_SESSION[_LICENSE_KEY_]["user"]["user_id"])) {
            $ses=$_SESSION[_LICENSE_KEY_]["user"]["user_id"]; 
        } else {
            $ses=0;
        }
        setcookie("freichat_user", $ses, time()+3600, "/");';


        $this->info['jsloc'] = '~/skins_site/YOUR_THEME/index.html
            ';
        $this->info['phpcode'] = '';
        $this->info['jscode'] = '<script type="text/javascript" language="javascipt"src="' . $path_host . 'client/main.php"></script>';
        $this->info['csscode'] = '<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">';
        $this->info['html_loc'] = 'Add the below lines before </body> tag';


        return $this->info;
    }

    function get_integ_module_url() {
        
    }

    function get_config() {

        require_once $_SESSION['config_path'];

        $conf[0] = _DBHOST_;
        $conf[1] = _DBUSER_;
        $conf[2] = _DBPASS_;
        $conf[3] = _DBNAME_;
        $conf[4] = $dbtable_prefix;

        return $conf;
    }

}