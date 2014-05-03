<?php

require 'base.php';

class Drupal extends base{

public $set_file="sites/default/settings.php";    
public $redir=false; 


function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


    $this->info['jsloc'] = 'page.tpl.php in themes/your_default_template/';
    $this->info['phpcode'] = '<!--===========================FreiChatX=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php

 $ses=$user->uid;
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
src="' . $path_host . 'client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>"></script>';
    $this->info['csscode'] = '<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';
    $this->info['html_loc'] = 'Please Add the following line in the same file in the body(before </body> tag or directly after <body>)';

    $this->info["integ_url"] = $this->get_integ_module_url();

    return $this->info;
}



 

function get_integ_module_url(){
    return "http://freichatx-i.googlecode.com/files/freichatx-i.v.7.x.zip";
}    
    
    
function get_config() {


        $drupal6_identity = "\$db_url";
        $drupal7_identity = "\$databases";

        $variables = array("\$db_url", "\$db_prefix");
        $str = @file_get_contents( $_SESSION['config_path']);
        if ($str === false) { //invalid filename
            return false;
        }

	$druapl6_array = array("db_host", "db_user", "db_password", "db_name", "\$db_prefix","6");
        $drupal7_array = array("host", "username", "password", "database", "prefix","7");

        if (strpos($str, $drupal6_identity) == TRUE) { //just to see whether the file is valid drupal7 file.
            $identifiers = $druapl6_array; 
        } else if (strpos($str, $drupal7_identity) == TRUE) {
            $identifiers = $drupal7_array;
        } else {
            return false;
        }

        $drupal_ver = end($identifiers);
      
       

        if ($drupal_ver == "7") {
            $values = $this->tokenise( $drupal7_array, null,$str);
            $value_part = array();
            
            $value_part[0] = $values['host'];
            $value_part[1] = $values['username'];
            $value_part[2] = $values['password'];
            $value_part[3] = $values['database'];
            $value_part[4] = $values['prefix'];
            
            $values = $value_part;
        } else {
            $values = $this->tokenise( null, $variables,$str);
            
       $str = $values[$variables[0]];

            $value_part = array();
            $pieces = array();

            $value_part[4] = $values[$variables[1]];
            $pieces = explode("://", $str);

           if (strpos($pieces[1], ":") == TRUE) {
                $pieces = explode(":", $pieces[1]);
                $value_part[1] = $pieces[0];
                $pieces = explode("@", $pieces[1]);
                $value_part[2] = $pieces[0];
            } else {
                $pieces = explode("@", $pieces[1]);
                $value_part[1] = $pieces[0];
                $value_part[2] = null;
            }
            $pieces = explode("/", $pieces[1]);
            $value_part[0] = $pieces[0];
            $value_part[3] = $pieces[1];

            $values = $value_part;
        }

        return $values;
    }



}
