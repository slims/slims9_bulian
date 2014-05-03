<?php

require 'base.php';

class Jcow extends base {

    public $set_file = 'my/config.php';
    public $redir = false;

    public function info($path_host) {

        $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


        $this->info['jsloc'] = 'themes/your_theme/page.tpl.php ';
        $this->info['phpcode'] = '<!--===============FreiChatX========START========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php

global $client;

$ses = 0;

if (!empty($client[\'id\'])) 
{
    $ses = $client[\'id\'];
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
               echo "<script>alert(\'module freichatx says: hardcode.php file not found!\');</script>";
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

        return $this->info;
    }

    function get_config() {

        require_once $_SESSION['config_path'];

        $conf[0] = $db_info['host'];
        $conf[1] = $db_info['user'];
        $conf[2] = $db_info['pass'];
        $conf[3] = $db_info['dbname'];
        $conf[4] = $table_prefix;

        return $conf;
    }

}
