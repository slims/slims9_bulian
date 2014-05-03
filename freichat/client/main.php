<?php

session_start();

//hi, this is not php , its javascript !
header("content-type:application/x-javascript");


if (!isset($_GET['mobile'])) {
    $mobile = 0;
} else {
    #$mobile = (int) $_GET['mobile'];
    $mobile = $_GET['mobile'];
}

//we need you db connection & settings
require_once '../arg.php';

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
//error_reporting(-1);


$img_req = '';

//COMM_15_DAY_NOTIFY_1



$zlib = false;

$no_get = !isset($_GET['id']) || !isset($_GET['xhash']);

//header("Content-type: text/javascript");
if ($use_cookie == 'false') {
    $use_cookie = false;
} else {
    $use_cookie = $use_cookie;
}

if ($use_cookie) {
//authentication is done via cookie
//so _GET global wont be set
} else {
//_GET must be set

    if ($no_get)
        exit;
}


if ($no_get && $use_cookie) {
    $_SESSION[$uid . 'xhash'] = $session_id = null;
} else {
    $session_id = strip_tags($_GET['id']);
    $_SESSION[$uid . 'xhash'] = $_GET['xhash'];
}


if (!isset($_SESSION[$uid . 'xhash']) || ($_SESSION[$uid . 'xhash'] != $_GET['xhash'])) {
    $_SESSION[$uid . 'is_cached'] = false;
}

//-----------------------------------------------------------------------------
class FCC extends FreiChat {

    public $banned = false;
    public $session_id;

    public function __construct() {
        parent::__construct();
        $this->init_vars();
    }

    public function FCC_init() {
        require_once '../server/drivers/' . $this->driver . '.php';
        $this->freichat_debug("main.php  loaded");


        if (!isset($_SESSION[$this->uid . 'rtl'])) {
            $_SESSION[$this->uid . 'rtl'] = false;
        }

        if (!isset($_SESSION[$this->uid . 'freistatus'])) {
            $_SESSION[$this->uid . 'freistatus'] = 1;
        }

        if (!isset($_SESSION[$this->uid . 'custom_mesg'])) {
            $_SESSION[$this->uid . 'custom_mesg'] = $this->frei_trans['default_status'];
        }

        if (!isset($_SESSION[$this->uid . 'in_room'])) {
            $_SESSION[$this->uid . 'in_room'] = -1;
        }

        if (isset($_SESSION[$this->uid . 'ses_id']) == false) {
            global $session_id;
            $parameters = array(
                "id" => $session_id,
                "custom_mesg" => false,
                "first" => false
            );


            if (!isset($_SESSION[$this->uid . 'in_room'])) {
                $_SESSION[$this->uid . 'in_room'] = -1;
            }
            $sessions = new $this->driver($this->db);
            $sessions->uid = $this->uid;
            $sessions->permanent_name = $this->permanent_name;
            $sessions->permanent_id = $this->permanent_id;
            $sessions->online_time = $this->online_time;
            $sessions->online_time2 = $this->online_time2;
            $sessions->time_string = $this->time_string;
            $sessions->show_name = $this->show_name;
            $sessions->usr_list_wanted = false;
            $sessions->db_prefix = $this->db_prefix;
            $sessions->displayname = $this->displayname;
            $sessions->frei_trans = $this->frei_trans;
            $sessions->debug = $this->debug;
            $sessions->update_usr_info = true;
            $sessions->url = $this->url;
            $sessions->driver = $this->driver;
            $sessions->to_freichat_path = $this->to_freichat_path;
            $sessions->options = $parameters;
            $sessions->row_username = $this->row_username;
            $sessions->row_userid = $this->row_userid;
            $sessions->usertable = $this->usertable;
            $sessions->ug_ids = $this->ug_ids;
            $sessions->use_cookie = $this->use_cookie;
            $sessions->pdo_driver = $this->pdo_driver;

            $sessions->load_driver();
            return $sessions->get_id();

            // print_r($_SESSION);
        }
    }

    public function check_user_agent($type = NULL) {
        $user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if ($type == 'bot') {
            // matches popular bots
            if (preg_match("/googlebot|adsbot|yahooseeker|yahoobot|msnbot|watchmouse|pingdom\.com|feedfetcher-google/", $user_agent)) {
                return true;
                // watchmouse|pingdom\.com are "uptime services"
            }
        } else if ($type == 'browser') {
            // matches core browser types
            if (preg_match("/mozilla\/|opera\//", $user_agent)) {
                return true;
            }
        } else if ($type == 'mobile') {
            // matches popular mobile devices that have small screens and/or touch inputs
            // mobile devices have regional trends; some of these will have varying popularity in Europe, Asia, and America
            // detailed demographics are unknown, and South America, the Pacific Islands, and Africa trends might not be represented, here
            if (preg_match("/phone|iphone|itouch|ipod|symbian|android|htc_|htc-|palmos|blackberry|opera mini|iemobile|windows ce|nokia|fennec|hiptop|kindle|mot |mot-|webos\/|samsung|sonyericsson|^sie-|nintendo/", $user_agent)) {
                // these are the most common
                return true;
            } else if (preg_match("/mobile|pda;|avantgo|eudoraweb|minimo|netfront|brew|teleca|lg;|lge |wap;| wap /", $user_agent)) {
                // these are less common, and might not be worth checking
                return true;
            }
        }
        return false;
    }

}

$cvs = new FCC;
$cvs->session_id = $cvs->FCC_init();

$cvs->get_js_config();

$is_mobile = $cvs->check_user_agent('mobile');

//exit(print_r($cvs));
//print_r($_SESSION);
if ($cvs->BOOT == "no") {

    //Do not load freichat
    exit("freichat is disabled");
}


if ($cvs->exit_for_guests == "yes" && $_SESSION[$uid . 'is_guest'] == 1) {
    //dievar_dump($_SESSION);
    //Do not load freichat
    exit("guests are not allowed");
}


$smileys = str_replace("'", "\'", json_encode($cvs->get_smileys()));

if ($cvs->GZIP_handler == 'ON') {
    $zlib = true;
	if (!ob_start("ob_gzhandler"))
	{
		ob_start();
	}        
}



$_SESSION[$uid . 'is_cached'] = true;

if ($cvs->banned == true)
    exit("you are banned");

//var_dump($construct);

if (isset($_SESSION)) {
    $username = $_SESSION[$cvs->uid . 'usr_name'];
    $id = $_SESSION[$cvs->uid . 'usr_ses_id'];
} else {
    $cvs->freichat_debug("Session Not Yet Created in client side");
}
$frei_trans[] = Array();


//require '../client/jquery/freichat_themes/defarg.php';
require '../client/themes/' . $cvs->freichat_theme . '/argument.php';
$frei_trans = $cvs->frei_trans;

$url = str_replace('client/main.php', '', $cvs->url);


if (isset($_SERVER['HTTP_REFERER'])) {
    $referer_url = $_SERVER['HTTP_REFERER'];
} else {
    $referer_url = $url;
}


if (strpos($referer_url, 'www.') == TRUE) {
    $url = str_replace('http://', 'http://www.', $url);
    $url = str_replace('https://', 'https://www.', $url);
} else {

    $url = str_replace('http://www.', 'http://', $url);
    $url = str_replace('https://www.', 'https://', $url);
}

if (strpos($url, 'www.www.') == TRUE) {
    $url = str_replace('http://www.www.', 'http://www.', $url);
    $url = str_replace('https://www.www.', 'https://www.', $url);
}

$pfromname = str_replace("'", "\'", $_SESSION[$uid . "usr_name"]);

$custom_mesg = "";


if (isset($_SESSION[$uid . "custom_mesg"])) {
    if ($_SESSION[$uid . "custom_mesg"] != "" && $_SESSION[$uid . "custom_mesg"] != "i am null" && $_SESSION[$uid . "custom_mesg"] != null) {
        $custom_mesg = $_SESSION[$uid . "custom_mesg"];
    }
} else {
    $custom_mesg = 'I am available';
}

$ACL = $cvs->get_acl();

$show_chatroom_plugin = 'enabled';

if ($cvs->show_chatroom_plugin == 'enabled') {
    if (($_SESSION[$uid . 'is_guest'] == 0 && $ACL['CHATROOM']['user'] == 'noallow') || ($_SESSION[$uid . 'is_guest'] == 1 && $ACL['CHATROOM']['guest'] == 'noallow')) { //is a user
        $show_chatroom_plugin = 'disabled';
    }
} else {
    $show_chatroom_plugin = 'disabled';
}

require_once 'jsdef.js';
require_once 'jquery/js/jquery.1.8.3.js';
require_once 'jquery/js/jquery.jscrollpane.min.js';


//for website
if ($is_mobile && $mobile != 1 && $cvs->show_mobilechat_plugin == 'enabled') {
    require 'mobile.js'; //can be used for alerts
} else {

    /*
     * Includes 
     *  Effects : Clip , Explode.
     *  Widgets : Draggable
     */
    require_once 'jquery/js/jquery-ui-frontend.js';
    require_once 'jquery/js/combined.min.js'; //include SM 2_.2.97a + slick + dragx
    $_SESSION[$cvs->uid . 'FreiChatX_init'] = true;
    require_once 'plugins.js';
    if ($cvs->show_videochat_plugin == 'enabled') {
        require '../administrator/admin_files/theme_maker/lib/js/md5.js';
        require_once 'plugins/videochat/videochat.js';
    }
    
    if($cvs->show_chatroom_plugin == 'enabled') {
        
        require '../administrator/admin_files/theme_maker/lib/colorpicker/js/colorpicker.min.js';
    }

    
    
    require_once 'freichat.js';
}

//for mobile page
if ($mobile == 1) {
    require_once 'plugins/mobile/chat/mobilechat.js';
}



if ($cvs->GZIP_handler == 'ON') {
    if ($zlib == true) {
        ob_end_flush();
    }
}
$_SESSION[$cvs->uid . 'main_loaded'] = true;
