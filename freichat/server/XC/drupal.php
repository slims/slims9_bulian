<?php

$jon = null;


$jon->id = null;
$jon->logged = "false";

session_start();
require("../../arg.php");
require("../../adodb_lite/adodb.inc.php");
//require("../arg.php");
$db = ADONewConnection($con);
$res = $db->Connect($host, $username, $password, $client_db_name);

if ($res === false) {
    $jon->message = 'unable to connect to db';
    echo json_encode($jon);
} else {
    
}

if (isset($_SESSION['username'])) {
    //echo 'al_log';
}
if (isset($_REQUEST['username'])) {
    $username = $_REQUEST['username'];
    $password = $_REQUEST['password'];
    $id = $_REQUEST['password'];

    $result = $db->Execute("SELECT * FROM " . $db_prefix . "users WHERE name='$username'");

    if ($result->fields == false) {
        $jon->message = "invalid u/pwd";
        echo json_encode($jon);
    } else {

        $words = explode(":", $result->fields['pass']);
        if (!isset($words[1])) {
            $words[1] = "";
        }
        $pword = $words[0];
        $hash = $words[1];
        $newhash = md5($password);


        if (md5($password) == $result->fields['pass']) {

            //$_SESSION['username']=$username;
            // $_SESSION['id']=$id=$result->fields['id'];
            $_SESSION[$uid . 'usr_name'] = $username;
            $uid = $_SESSION[$uid . 'usr_ses_id'] = $result->fields['uid'];
            $_SESSION[$uid . 'is_guest'] = 0;
            // $gid=$result->fields['gid'];
            $time = time();
            $sid = $time . $username;
            $jon->logged = "true";

            //check if session exits
            $result = $db->Execute("SELECT * FROM " . $db_prefix . "sessions WHERE uid='$uid'");

            if ($result->fields == false) {//$id=$db->fields['id'];
                $query = "INSERT INTO " . $db_prefix . "sessions (uid,sid)
							VALUES('$uid','$sid')";
                $db->Execute($query);
            } else {
                $query = "UPDATE " . $db_prefix . "sessions SET time='$time' WHERE uid='$uid'";
                $db->Execute($query);
            }

            echo json_encode($jon);
        } else {
            $jon->message = "invalid u/p";
            echo json_encode($jon);
        }
    }
} else {
    $jon->message = "request not sent";
    echo json_encode($jon);
}
?>