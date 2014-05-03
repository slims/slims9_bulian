<?php

require 'base.php';

class Sugarcrm extends base{
  
    
public $set_file='config.php';    
public $redir=false;    

function info($path_host) {


    $url = str_replace("installation/integ", "", str_replace('\\', '/', dirname(__FILE__)));

    $this->info['jsloc'] = ' SugarCRM index.php file/';
    $this->info['phpcode'] = '


$freichatx_code_written = true;

function freichatx_get_hash(){

       if(is_file("freichat/hardcode.php")){

               require(\'freichat/hardcode.php\');
				if(isset($_SESSION[\'authenticated_user_id\']))
				{

				$temp_id=$_SESSION[\'authenticated_user_id\'].$uid;

				}
				else
				{
				$temp_id=\'0\'.$uid;
				}

               return md5($temp_id);

       }
       else
       {
               echo "<script>alert(\'module freichatx says: hardcode.php file not
found!\');</script>";
       }

       return 0;
}

function freichatx_get_id()
{
	if(isset($_SESSION[\'authenticated_user_id\']))
	{
	 $id = $_SESSION[\'authenticated_user_id\'];
	}
	else
	{
	 $id = \'0\';
	}

 return $id;
}

$freichatx_html=ob_get_clean();
$html=\'<script type="text/javascript" language="javascipt" src="' . $path_host . 'freichat/client/main.php?id=\'.freichatx_get_id().\'&xhash=\'.freichatx_get_hash().\'"></script>
<link rel="stylesheet" href="' . $path_host . 'freichat/client/jquery/freichat_themes/freichatcss.php" type="text/css"></head>\';
$freichatx_html=str_replace("</head>",$html,$freichatx_html);
echo $freichatx_html;';
    
 
    $this->info['js_where'] = "Before close ?> PHP tag in index.php";
    return $this->info;
}

function self_install() {
        $root = dirname($_SESSION['config_path']);
        $handle = $root . "/index.php";

        if (!is_writable($handle)) {
            return false;
        }

        require 'contents.php';
        $freichatx_contents = get_freichatx_sugarce($this->path_host);
        $contents = file_get_contents($handle);

        if (strpos($contents, "freichatx_code_written") == TRUE) {
            return true;
        } else {
            if (strpos($contents, "\$app->execute();") == true) {
                $search = "\$app->execute();";
                $replace = $search . $freichatx_contents;
            } else if (strpos($contents, "?>") == true) {
                $search = "\$app->execute();";
                $replace = $freichatx_contents . $search;
            } else {
                return false;
            }
            $new_contents = str_replace($search, $replace, $contents);
            file_put_contents($handle, $new_contents);
        }
        
      return true;  
}

 function get_config() {
        require_once $_SESSION['config_path'];

        $conf[0] = $sugar_config['dbconfig']['db_host_name'];
        $conf[1] = $sugar_config['dbconfig']['db_user_name'];
        $conf[2] = $sugar_config['dbconfig']['db_password'];
        $conf[3] = $sugar_config['dbconfig']['db_name'];
        $conf[4] = null;

        return $conf;
    }



}
