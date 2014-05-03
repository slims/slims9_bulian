<?php


########################################
#                                      #
#       DZCP Driver for Freichat       #
#      DZCP Version: 1.5.x, 1.6.x      #
#      ______________________          #
#                                      #
#           Mod by: Richy              #
#        www.my-starmedia.de           #
#                                      #
########################################

require 'base.php';

class DZCP extends base{    
    
public $set_file='';    
public $redir=true;

function info($path_host) {
    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));


    $this->info['addn_info'] = 'Add the placeholder [freichat] in your index.html of your template between the start head tag and close head tag';
		
		$this->info['jsloc'] = 'new text file, and save the file as freichat.php.<br />';
    $this->info['js_where'] = 'Then copy the new "freichat.php" file to the folder:<br /> ../inc/additional-functions/ <br />of your DZCP website';
		$this->info['phpcode'] = '<?php
function freichat()
{   
	global $userid;

	$id = null;
	if ($userid >= 1) $id = $userid;
	$ses = $id;
	
	if(!function_exists("freichatx_get_hash")){
		function freichatx_get_hash($ses){
		
			if(is_file("../freichat/hardcode.php")) {
				require "../freichat/hardcode.php";
				
				$temp_id =  $ses . $uid;
			
				return md5($temp_id);			
			} else {
				return \'<script>alert(module freichatx says: hardcode.php file not found!);</script>\';
				break;
			}
			return 0;
		}
	}
	$freichatx = freichatx_get_hash($ses);
	
	$chat = \'<script type="text/javascript" language="javascipt" src="../freichat/client/main.php?id=\'.$ses.\'&xhash=\'.freichatx_get_hash($ses).\'"></script>		
					  <link rel="stylesheet" href="../freichat/client/jquery/freichat_themes/freichatcss.php" type="text/css">\';
	
	return $chat;
}
?>';
	
    return $this->info;
}

function get_config() {
return null;
}

}
