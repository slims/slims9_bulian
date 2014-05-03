<?php

require 'base.php';

class Phpfusion extends base{   
    
public $set_file='config.php';    
public $redir=false;    

function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = 'in header.php located in themes/templates/';
    $this->info['phpcode'] = '
if(!isset($userdata[\'user_id\'])){
$ses=0;
}
else{
$ses=$userdata[\'user_id\'];
}


if(!function_exists("freichatx_get_hash")){
function freichatx_get_hash($ses){

       if(is_file(THEMES."../freichat/hardcode.php")){

               require THEMES."../freichat/hardcode.php";

               $temp_id =  $ses . $uid;

               return md5($temp_id);

       }
       else
       {
               echo \'<script>alert("module freichatx says: hardcode.php file not found!");</script>\';
       }

       return 0;
}
}

';
    $this->info['jscode'] = 'echo \'<script type="text/javascript" language="javascipt"
src="'.$path_host.'client/main.php?id=\'.$ses.\'&xhash=\'.freichatx_get_hash($ses).\'">
</script>\';';
    $this->info['csscode'] = 'echo \'<link rel="stylesheet" href="'.$path_host.'client/jquery/freichat_themes/freichatcss.php" type="text/css">\';';

    return $this->info;
}


public function get_config() {
    require $_SESSION['config_path'];
    


        $conf[0] = $db_host;
        $conf[1] = $db_user;
        $conf[2] = $db_pass;
        $conf[3] = $db_name;
        $conf[4] = $db_prefix;

        return $conf;
}

}
//Except for Elgg users: Add it in footer.php in views/default/page_elements/
