<?php

require 'base.php';

class Custom extends base{    
    
public $set_file='';    
public $redir=false;

function info($path_host) {
    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


    $this->info['jsloc'] = 'Index.php between the start head tag and close head tag';
    $this->info['phpcode'] = '<!--===========================FreiChat=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChat related code i.e below code
	 Then remove FreiChat tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php
$ses=null;

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
?>
';
    
    $this->info['c#_cookie_code'] = '
        Response.Cookies["freichat_user"].Value = null; // tell freichat the current user is guest';
    
    
    $this->info['php_cookie_code'] = '<!--===========================FreiChat=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChat related code i.e below code
	 Then remove FreiChat tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php
    setcookie("freichat_user", null, time()+3600, "/"); // tell freichat the current user is guest
?>
';


    $this->info['jscode'] = '<script type="text/javascript" language="javascipt" src="' . $path_host . 'client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>"></script>';
    $this->info['csscode'] = '
	<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';
    $this->info['html_loc'] = 'Please Add the following line in the same file in the body(before </body> tag or directly after <body>)';

    $this->info['js_cookie_code'] = '<script type="text/javascript" language="javascipt" src="' . $path_host . 'client/main.php"></script>';
    $this->info['css_cookie_code'] = '
        <link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">';

    
    return $this->info;
}

function get_config() {
return null;
}

}
