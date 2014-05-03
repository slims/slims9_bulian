<?php
$url = (!empty($_SERVER['HTTPS'])) ? "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] : "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
session_start();

$_SESSION['nocheck'] = true;
$_SESSION['index'] = md5(time() + rand(989, 98999));

require '../hardcode.php';


if ($installed == true) {
    
    $_SESSION['error']="FreiChat is already installed on your server<br/>For security reasons , the installer has been locked.<br/>But if you want to install it again for a fresh reinstall or for any other reason <br/> please change the variable \$installed to false in ~/freichat/hardcode.php and then try again";
    header('Location: error.php');
    exit;
}


date_default_timezone_set('America/Los_Angeles');


require 'header.php';

?>

<div>

<p style="text-align:center"><b>Please Read The Below Document Carefully</b></p>

<br/>

<div style='overflow:scroll;overflow-x:hidden;width:80%;height:300px;text-align:left;font-size:small;padding-left:10%'>
<i>
<?php echo str_replace("\n", "<br/>", file_get_contents('license.txt')); ?>
</i>
</div>

<br/>
<br/>
<form id="theform" action="specific.php?<?php echo $_SESSION['index']; ?>=true" method="post">
    
    <div style="text-align:center">
    <a href="JavaScript:void(0);" onclick="$('#theform').submit()" class="btn btn-xxlarge btn-primary">Accept</a>
    <!--<a href="../index.php" class="btn btn-xxlarge btn-danger">Reject</a>-->
    </div>
</form>

</div>

<?php
require 'footer.php';
?>