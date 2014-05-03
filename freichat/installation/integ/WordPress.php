<?php

require 'base.php';

class WordPress extends base{

public $set_file = "wp-config.php";
public $redir=false;

function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = 'header.php in wp-content/themes/your_default_theme/';
    $this->info['phpcode'] = '

<!--==========================FreiChatX===START========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php
global $current_user;
get_currentuserinfo();
$ses=$current_user->ID;

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
    $this->info['jscode'] = '<script type="text/javascript" language="javascipt"
src="' . $path_host . 'client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>">
</script>';
    $this->info['csscode'] = '<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX====END=========================-->';

    $this->info["integ_url"] = $this->get_integ_module_url();

    return $this->info;
}


function get_integ_module_url(){
    return "http://freichatx-i.googlecode.com/files/freichatx-i4WP.v7.x.zip";
}    
   

 function get_config() {

	$identifiers = array("DB_HOST", "DB_USER", "DB_PASSWORD", "DB_NAME", "\$table_prefix");
	$variables = array("\$table_prefix");

        $str = @file_get_contents($_SESSION['config_path']);
                
        $values = $this->tokenise($identifiers, $variables, $str);
        
        $info = array();
        
        //sort the array according to $identifiers
        //and store in info with index 0,1,2,etc
        foreach($identifiers as $identifier) {
            foreach($values as $key=>$value) {
                if($key == $identifier) {
                    $info[] = $value;
                    break;
                }
            }
        }

        return $info;
    }

}