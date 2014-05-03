<?php

require 'base.php';

class Phpfox extends base{
        
    
public $set_file='include/setting/server.sett.php';    
public $redir=false;    

function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = 'In AdminCP , Extensions->Theme->Manage Themes <br/> Edit your theme by clicking edit templates <br/> Select template.html.php file and add ';
    $this->info['phpcode'] = '
<!--===========================FreiChatX=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

{php}



$ses=Phpfox::getUserId();

if(!function_exists("freichatx_get_hash")){
function freichatx_get_hash($ses){

       if(is_file("/opt/lampp/htdocs/phpfox2/freichat/hardcode.php")){

               require "/opt/lampp/htdocs/phpfox2/freichat/hardcode.php";

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
	{/php}';
	
	
 $this->info['jscode'] = '<script type="text/javascript" language="javascipt"src="'.$path_host.'client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>"></script>';
	                                 
$this->info['csscode'] = '<link rel="stylesheet" href="'.$path_host.'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';

    return $this->info;
}

 function get_config() {
	define('PHPFOX',true);
        require_once $_SESSION['config_path'];

        //global $_CONF;

        if (!isset($_CONF)) {
                return false;
            }

        $conf[0] = $_CONF['db']['host'];
        $conf[1] = $_CONF['db']['user'];
        $conf[2] = $_CONF['db']['pass'];
        $conf[3] = $_CONF['db']['name'];
        $conf[4] = $_CONF['db']['prefix'];

        return $conf;
    }


}
//Except for Elgg users: Add it in footer.php in views/default/page_elements/
