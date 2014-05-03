<?php

/* Make me secure */

if (!isset($_SESSION))
    session_start();

if (isset($_SESSION['nocheck']) && $_SESSION['nocheck']) {
    $_SESSION['nocheck'] = false;
} else {


    if (!isset($_SESSION['FREIX']) || $_SESSION['FREIX'] != 'authenticated') {
        header("Location:index.php");
        exit;
    }
}
/* Now i am secure */

function write_hardcode($con, $db_user, $db_pass, $db_prefix, $admin_pass, $use_cookie) {
    $filepath = "../hardcode.php";
    
    file_put_contents("../cache/perm/request.001", "0");


    $data = '<?php
/* Data base details */
$dsn=\'' . $con . '\'; //DSN
$db_user=\'' . $db_user . '\'; //DB username
$db_pass=\'' . $db_pass . '\'; //DB password    
$driver=\'' . $_SESSION["cms"] . '\'; //Integration driver
$db_prefix=\'' . $db_prefix . '\'; //prefix used for tables in database
$uid=\'' . uniqid() . '\'; //Any random unique number

$PATH = \'' . $_SESSION["freichat_renamed"] . '/\'; // Use this only if you have placed the freichat folder somewhere else
$installed=true; //make it false if you want to reinstall freichat
$admin_pswd=\'' . $admin_pass . '\'; //backend password 

$debug = false;
$custom_error_handling=\'NO\'; // used during custom installation

$use_cookie='.$use_cookie.';

/* email plugin */
$smtp_username = \'\';
$smtp_password = \'\';

$force_load_jquery = \'NO\';

/* Custom driver */
$usertable=\'login\'; //specifies the name of the table in which your user information is stored.
$row_username=\'root\'; //specifies the name of the field in which the user\'s name/display name is stored.
$row_userid=\'loginid\'; //specifies the name of the field in which the user\'s id is stored (usually id or userid)
$avatar_field_name = \'avatar\';';

    file_put_contents($filepath, $data);
}

    function connect($host, $client_db_name, $username, $password, $port, $driver = 'mysql') {

        $keys = array(
            'mysql' => array(
                "host" => "host",
                "dbname" => "dbname",
				"port" => ";port="
            ),
            'sqlsrv' => array(
                "host" => "Server",
                "dbname" => "Database",
				"port" => ","
            )
        );


        if (strpos($host, "/") !== FALSE) {

            if (strpos($host, ":") !== FALSE) {
                //localhost:socket_dir
                $parts = explode(":", $host);
				$host = $keys[$driver]["host"] . "=" . $parts[0];
                $unix_socket = ";unix_socket=" . $parts[1];
            } else {
                //socket_dir
                $unix_socket = "unix_socket=$host";
                $host = '';
            }
        } else {
            //clean host
            $unix_socket = '';
            $host = $keys[$driver]["host"] . "=$host";
        }

        $error = false;

        if ($port != '') {
            $port = $keys[$driver]["port"]."$port;";
        } else {
            $port = ";";
        }



        $dbname = $keys[$driver]["dbname"] . "=";

        $dsn = "$driver:$host$unix_socket$port$dbname$client_db_name";

        try {

            $dbh = new PDO($dsn, $username, $password, array(
                        PDO::ATTR_PERSISTENT => false
                    ));
        } catch (PDOException $e) {

            //self::freichat_debug("unable to connect to database. Error : " . $e->getMessage());
            $error = $e->getMessage();
        }

        if (!$error) {
            return array($dsn, true);
        }
        $dbh = null; //reset connection;
        //if in localhost , host cannot be localhost for unix 

        $_error = false;

        $host = $keys[$driver]["host"] . "=127.0.0.1";

        $dsn = "$driver:$host$unix_socket$port$dbname$client_db_name";

        try {
            $dbh = new PDO($dsn, $username, $password, array(
                        PDO::ATTR_PERSISTENT => false
                    ));
        } catch (PDOException $e) {

            //self::freichat_debug("unable to connect to database. Error : " . $e->getMessage());
            $_error = $e->getMessage();
        }

        if (!$_error) {
            return array($dsn, true);
        }

        return array($error, false);
    }

error_reporting(E_ALL);
if (extension_loaded('PDO') && extension_loaded('pdo_mysql')) {

    $conn = connect($_POST["host"], $_POST["dbname"], $_POST["muser"], $_POST["mpass"], $_POST['port']);

    if ($conn[1]) {
        
        $cms = $_POST['cms'];
        
        if($cms == 'se4' || $cms == 'etano') {
            $use_cookie = '"freichat_user"';
        }else{
            $use_cookie = 'false';
        }
        write_hardcode($conn[0], $_POST["muser"], $_POST['mpass'], $_POST['db_prefix'], $_POST['admin_pass'], $use_cookie);
        echo 'works';
    }else{
        echo "Could not connect: <br/> ".$conn[0];
    }

    
} else {

    echo 'pdo_mysql driver is not installed or enabled';
}
