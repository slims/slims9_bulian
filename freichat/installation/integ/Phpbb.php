<?php

require 'base.php';

class Phpbb extends base {
        
    
public $set_file="config.php";    
public $redir=false;    

function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = 'overall_header.html in styles/your_default_template/template/';
    $this->info['phpcode'] = '<!--===========================FreiChatX=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<!-- PHP -->
if(!function_exists("freichatx_get_hash")){
function freichatx_get_hash(){

global $user;
$ses = $user->session_id;

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
<!-- ENDPHP -->
';
    $this->info['jscode'] = '<script type="text/javascript" language="javascipt"
src="' . $path_host . 'client/main.php?id={SESSION_ID}&xhash=<!-- PHP --> echo freichatx_get_hash(); <!-- ENDPHP -->">
</script>';
    $this->info['csscode'] = '<link rel="stylesheet" href="' . $path_host . 'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';

    $this->info['addn_info'] = "After adding the below code , you need to enable PHP in templates.<br/>You
can do that by going to the ACP in General tab -> Security setting -> Allow PHP in templates <br/>Set it to yes. <br/>
Dont forget to purge the cache in phpBB after changing this setting <br/>
";
    
    
    return $this->info;
}



function get_config() {

$params = array("dbhost", "dbuser", "dbpasswd", "dbname", "table_prefix");
        $i = 0;
        $path = $_SESSION['config_path'];

        if (!is_readable($path)) {
            return false;
        }

        $contents = file_get_contents($path, true);

        foreach ($params as $param) {

            $pattern1 = "/\b" . $param . "\b.*;/";
            $pattern2 = "/'.*'/";

            preg_match($pattern1, $contents, $match);
            preg_match($pattern2, $match[0], $mat);
            $final[$i] = str_replace("'", "", $mat[0]);
            $i++;
        }

        return $final;
    }


}
//Except for Elgg users: Add it in footer.php in views/default/page_elements/
