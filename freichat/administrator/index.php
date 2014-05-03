<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
require '../arg.php';
require 'admin_files/admin_base.php';


if (isset($_SESSION['phplogin']) && $_SESSION['phplogin'] == true) {
    header('Location: admin.php');
}



$path_host = str_replace("administrator/index.php", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);

if (isset($_POST['login'])) {


    $password = $_POST['pswd'];

    if ($password == $admin_pswd) { //Replace mypassword with your password it login
        $_SESSION['phplogin'] = true;
//echo 'ddd';
        header('Location: admin.php'); //Replace index.php with what page you want to go to after succesful login

        exit;
    } else {
        ?>
        <script type="text/javascript">
            
            alert('Wrong Password, Please Try Again')
            
        </script>
        <?php
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
    <head>
        <link rel="stylesheet" href="index.css"/>
        <style type="text/css">

        @font-face {
          font-family: 'Squada One';
          font-style: normal;
          font-weight: 400;
          src: local('Squada One'), local('SquadaOne-Regular'), url(css/font_squada_one.woff) format('woff');
        }
            body{
                background-attachment:fixed;
                background-position:center;
                background-repeat:no-repeat;
                background-color: #ffffff;
            }


        </style>
        <title> FreiChat Backend Login </title>

        <style>
            .adminbutton:hover{
                cursor: pointer;
            }

        </style>
    </head>
    <body>

        <!--<div style="text-align:center">

            <img src="admin_files/home/head.png" height=100  />
        </div>-->



        <div id="main" class="main" >
            <!--<h2>Administration Authentication</h2>-->
            <div id="container" class="container">
                <form method="post" action="index.php">
                    
                    <div class="login_header">
                        <div class="pass_text" >FREICHAT BACKEND</div>                       
                    </div>
                    
                    
                    <div><input id="fc_pass" style="height: 30px;width: 225px;border: 1px solid #0092C8;text-align: center;" type="password" name="pswd" value='' placeholder="password"></div>

                    <span class="info">(defined in freichat/hardcode.php)</span>
                    
                    <div class="actions" >
                        <div><input style="width:100px" class="btn btn-primary" type="submit" name="login" value="Login"></div>
                        
                    </div>
                    
                    

                </form>
            </div>
        </div>

        <script>
            document.getElementById("fc_pass").focus();
        </script>
    </body>
</html>
