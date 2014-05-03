<?php

require 'base.php';

class EE extends base {

    public $set_file = 'system/expressionengine/config/database.php';
    public $redir = false;

    public function info($path_host) {

        $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


        $this->info['jsloc'] = 'Login to Expression engine Administration backend

Go to Design -> Templates -> Global Variables

Click on html_head and add the following code at the end of Variable Content.';
        $this->info['phpcode'] = '<!--===============FreiChatX========START========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php
$session = JSession::getInstance("none",array());
$host = JURI::root();
$ses=$session->getId();

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
src="<?php echo $host; ?>freichat/client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>">
</script>';
        $this->info['csscode'] = '<link rel="stylesheet" href="<?php echo $host; ?>freichat/client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';

        //$this->info["integ_url"] = $this->get_integ_module_url();

        return $this->info;
    }

    /*
      function get_integ_module_url(){
      return  "http://freichatx-i.googlecode.com/files/mod_freichat_v7.zip";
      }
     */

    function get_config() {

        define('BASEPATH', "Let me access EE");
        require_once $_SESSION['config_path'];

        $conf = array ();
        
        $conf[] = $db['expressionengine']['hostname'];
        $conf[] = $db['expressionengine']['username'];
        $conf[] = $db['expressionengine']['password'];
        $conf[] = $db['expressionengine']['database'];
        $conf[] = $db['expressionengine']['dbprefix'];

        return $conf;
    }

}
