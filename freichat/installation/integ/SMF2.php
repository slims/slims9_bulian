<?php

require 'base.php';

class SMF2 extends base {

    public $set_file = 'Settings.php';
    public $redir = false;

    public function info($path_host) {

        $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


        $this->info['jsloc'] = 'index.template.php in Themes/default after the <head>';
        $this->info['phpcode'] = '
echo "<!--===========================FreiChatX=======START=========================-->";
global $cookiename;

$ses = NULL;

if (!empty($_COOKIE[$cookiename])) 
{
	$data = str_replace("\\\", "", $_COOKIE[$cookiename]);
	$data = @unserialize($data);
	$ses = $data[0];
}


if(!function_exists("freichatx_get_hash")){
function freichatx_get_hash($ses){

       if(is_file("' . $url . 'hardcode.php")){

               require "' . $url . 'hardcode.php";

               $temp_id =  $ses . $uid;

               return md5($temp_id);

       }
       else
       {
               echo "<script>alert(\'module freichatx says: hardcode.php file not
found!\');</script>";
       }

       return 0;
}
}


echo \'
<script type="text/javascript" language="javascipt"src="' . $path_host . 'client/main.php?id=\'.$ses.\'&xhash=\'.freichatx_get_hash($ses).\'"></script>
	<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->                
\';
';

        $this->info["integ_url"] = $this->get_integ_module_url();
        $this->info['jscode'] = '';
        $this->info['csscode'] = '';

        return $this->info;
    }

    function get_integ_module_url() {
        return "http://freichatx-i.googlecode.com/files/freichat_integ.zip";
    }

    function get_config() {

        require $_SESSION['config_path'];

        $conf = array();
        //host
        $conf[0] = $db_server;
        //db username
        $conf[1] = $db_user;
        //db password
        $conf[2] = $db_passwd;
        //db name
        $conf[3] = $db_name;
        //db prefix
        $conf[4] = $db_prefix;

        return $conf;
    }

}
