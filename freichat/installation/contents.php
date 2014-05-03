<?php

function get_freichatx_sugarce($path) {
    $contents = '

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
$html=\'<script type="text/javascript" language="javascipt" src="' . $path . 'client/main.php?id=\'.freichatx_get_id().\'&xhash=\'.freichatx_get_hash().\'"></script>
<link rel="stylesheet" href="' . $path . 'client/jquery/freichat_themes/freichatcss.php" type="text/css"></head>\';
$freichatx_html=str_replace("</head>",$html,$freichatx_html);
echo $freichatx_html;';

    return $contents;
}