<?php

require 'base.php';

class Phpvms extends base{   
    
public $set_file='core/local.config.php';    
public $redir=false;    

function info($path_host) {

    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = 'core_htmlhead.tpl in core/templates/';
    $this->info['phpcode'] = '<!--===========================FreiChatX=======START=========================-->
<!--	For uninstalling ME , first remove/comment all FreiChatX related code i.e below code
	 Then remove FreiChatX tables frei_session & frei_chat if necessary
         The best/recommended way is using the module for installation                         -->

<?php

if (isset($_SESSION["userinfo"])) {

    if (strlen($_SESSION["userinfo"]) > 0) {

        $x = unserialize($_SESSION["userinfo"]);

        if (isset($x->pilotid)) {
            //var_dump($x);
            $ses = $x->pilotid;
            
        }
    }
} else {
    $ses = null;
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
?>
';
    $this->info['jscode'] = '<script type="text/javascript" language="javascipt"
src="'.$path_host.'client/main.php?id=<?php echo $ses;?>&xhash=<?php echo freichatx_get_hash($ses); ?>">
</script>';
    $this->info['csscode'] = '<link rel="stylesheet" href="'.$path_host.'client/jquery/freichat_themes/freichatcss.php" type="text/css">
<!--===========================FreiChatX=======END=========================-->';

    return $this->info;
}


public function get_config() {
return null;
}

}
//Except for Elgg users: Add it in footer.php in views/default/page_elements/
