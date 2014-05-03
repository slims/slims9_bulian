<?php

date_default_timezone_set('GMT');


/* < Hard code */
require_once 'hardcode.php';
/* Hard code /> */



require_once 'define.php';

class FreiChat {

    public function __construct() {


        global $dsn,$db_user,$db_pass, $driver, $db_prefix, $uid, $debug, $PATH, $smtp_username, $smtp_password;
        $this->dsn = $dsn;
        $this->driver = $driver;
        $this->db_prefix = $db_prefix;
        $this->uid = $uid;
        $this->PATH = $PATH;
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;

        
        $parts = explode(":",$dsn);
        $this->pdo_driver = $parts[0];

        global $use_cookie;
        
        if($use_cookie == 'false') {
            $this->use_cookie = false;
        }else{
            $this->use_cookie = $use_cookie;            
        }
        
        
        
        global $usertable, $row_username, $row_userid, $avatar_field_name, $force_load_jquery;
        $this->usertable = $usertable;
        $this->row_username = $row_username;
        $this->row_userid = $row_userid;
        $this->avatar_field_name = $avatar_field_name;
        $this->force_load_jquery = $force_load_jquery;

        $this->time_string = strtotime(date("Y-m-d H:i:s"));

        $this->online_time = ($this->time_string - 10);
        $this->online_time2 = ($this->time_string - 80);

        global $custom_error_handling;
        $this->custom_error_handling = $custom_error_handling;


        $this->db = DB_conn::get_connection($dsn, $db_user, $db_pass);
        //$this->init_vars();
    }

    public function build_vars() {
        $query = "SELECT * FROM frei_config";
        $variables = $this->db->query($query);
        $variables = $variables->fetchAll();
        $args = array();

        /* require_once 'server/drivers/'.$this->driver.'.php';
          $driver = new $this->driver($this->db);
          $driver->db_prefix = $this->db_prefix; */

        foreach ($variables as $variable) {

            if ($variable['subcat'] != 'NULL') {
                //    var_dump($variable);
                $args[$variable['key']][$variable['cat']][$variable['subcat']] = $variable['val'];
            } else if ($variable['cat'] != 'NULL') {
                $args[$variable['key']][$variable['cat']] = $variable['val'];
            } else {
                $args[$variable['key']] = $variable['val'];
            }
        }

        $args['x_config'] = null; //not req. $driver->x_config();
        $this->db_vars = $args;
        return $args;
    }

    public static function build_paths() {
        if (!defined('RDIR')) {
            define('RDIR', dirname(__FILE__));
            define('PARENTDIR', dirname(RDIR));
        }

        if (@$_SERVER["HTTPS"] == "on") {
            $protocol = "https://";
        } else {
            $protocol = "http://";
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
    }

    public function return_boolean($variable) {
        if ($variable == "true")
            return true;
        return false;
    }

    public function init_vars() {

        $parameters = $this->build_vars();

        $this->url = self::build_paths();
        $this->show_name = $parameters['show_name']; //you can have guest or user
        $this->displayname = $parameters['displayname']; //you can have username / name(nickname)
        $this->debug = $this->return_boolean($parameters['debug']); //option for debugging ,default is false
        $this->freichat_theme = $parameters['freichat_theme'];
        $this->css = $this->freichat_theme; //background color
        $this->color = $this->css; //colour for chatbuttons
        $this->lang = $parameters['lang']; //Language please do not include .php here only file name
        $this->cache = 'disabled';
        $this->ug_ids = $parameters['ug_ids'];

        $this->show_chatroom_plugin = 'enabled';
        $this->show_videochat_plugin = 'disabled';
        $this->show_mobilechat_plugin = 'disabled';
        $this->x_config = $parameters['x_config'];
        $this->chat_time_shown_always = $parameters['chat_time_shown_always'];
        $this->allow_guest_name_change = $parameters['allow_guest_name_change'];
        
        $this->chatroom_expiry = $parameters['plugins']['chatroom']['chatroom_expiry'];

        //long polling
        $this->long_polling = $parameters['polling'];
        $this->poll_time = $parameters['polling_time'];
        $this->chatspeed = $parameters['chatspeed'];

        //link profile
        $this->linkprofile = $parameters['link_profile'];
        $this->sef_link_profile = $parameters['sef_link_profile'];
        //CUSTOM DRIVER
        $this->to_freichat_path = $this->PATH;


        $this->show_avatar = $parameters['show_avatar']; //Can have block or none
        $this->frei_trans = $this->inc_lang();
	$this->mysql_now = date("Y-m-d H:i:s");
        $this->time_string = strtotime($this->mysql_now);

        $this->online_time = ($this->time_string - 10);
        $this->online_time2 = ($this->time_string - 80);
        $this->permanent_id = time() + rand(100000, 500000);
        $this->permanent_name = $this->frei_trans['g_prefix'] . base_convert($this->permanent_id, 6, 36);
    }

    public function get_js_config() {

        $parameters = $this->db_vars;

        $this->fxval = $parameters['fxval']; //Set it to false if you do not want animations
        $this->draggable = $parameters['draggable'];
        $this->conflict = $parameters['conflict']; //Jquery Conflicts 'true' or ''
        $this->msgSendSpeed = $parameters['msgSendSpeed']; //Message are sent after 1 second of post, reducing it will increase FreiChat message sending speed but also will send more requests to the server! NOTE:: Do not decrease it below 1000
        $this->content_height = $parameters['content_height']; //option to auto resize the chatbox content based on content or set fixed height
        $this->user_defined_chatbox_content_status = $parameters['chatbox_status']; //if true , user can permanantly inimze or maximize the chatbox

        $this->load = $parameters['load']; //chatbox
        $this->dyncss = 'disable'; //template patch
        $this->evnixpower = 'visible'; //powered by evnix
        $this->show_chatbox = '';
        $this->time = $parameters['time']; //In seconds
        $this->GZIP_handler = $parameters['GZIP_handler'];
        $this->BOOT = $parameters['BOOT']; // Load freichat -> y or n?
        $this->exit_for_guests = $parameters['exit_for_guests']; // Do not load if guest

        $this->JSdebug = $this->return_boolean($parameters['JSdebug']); // Javascript debug info shown in firebug (firefox extension). No quotes around true or false
        $this->busy_timeOut = $parameters['busy_timeOut']; //In seconds user will be switched to busy status
        $this->offline_timeOut = $parameters['offline_timeOut']; //In seconds user will be switched to offline status
        $this->addedoptions_visibility = $parameters['addedoptions_visibility']; //if the addedoption should be visible when chat window is created


        /* FreiChat plugins */

        // File sending
        $this->show_file_sending_plugin = $parameters['plugins']['file_sender']['show'];
        $this->file_size_limit = ($parameters['plugins']['file_sender']['file_size']) * 1024; //In Kilobytes
        $this->expirytime = $parameters['plugins']['file_sender']['expiry']; //In minutes after which the uploaded files will be deleted
        $this->valid_exts = $parameters['plugins']['file_sender']['valid_exts']; //valid extensions separated by comma
        $this->playsound = $parameters["playsound"];
        //coversation save
        $this->show_save_plugin = 'enabled';

        //smiley plugin
        $this->show_smiley_plugin = 'enabled';

        //send conversation plugin
        $this->show_mail_plugin = 'enabled';

        $this->chatroom_location = $parameters['plugins']['chatroom']['location'];
        $this->chatroom_autoclose = $parameters['plugins']['chatroom']['autoclose'];
        $this->chatroom_offset = $parameters['plugins']['chatroom']['offset'];
        $this->chatroom_label_offset = $parameters['plugins']['chatroom']['label_offset'];
        $this->chatroom_override_positions = $parameters['plugins']['chatroom']['override_positions'];

        $this->mailtype = $parameters["plugins"]["send_conv"]["mailtype"];
        $this->smtp_server = $parameters["plugins"]["send_conv"]["smtp_server"];
        $this->smtp_port = $parameters["plugins"]["send_conv"]["smtp_port"];
        $this->smtp_protocol = $parameters["plugins"]["send_conv"]["smtp_protocol"];
        $this->mail_from_address = $parameters["plugins"]["send_conv"]["from_address"];
        $this->mail_from_name = $parameters["plugins"]["send_conv"]["from_name"];
    }

    public function get_acl() {
        $parameters = $this->db_vars;

        $ACL = array(
            'CHAT' => array(
                'user'  => $parameters['ACL']['chat']['user'],
                'guest' => $parameters['ACL']['chat']['guest']
            ),
            'FILE' => array(
                'user'  => $parameters['ACL']['filesend']['user'],
                'guest' => $parameters['ACL']['filesend']['guest']
            ),
            'TRANSLATE' => array(
                'user'  => 'noallow',
                'guest' => 'noallow'
            ),
            'SAVE' => array(
                'user'  => $parameters['ACL']['save']['user'],
                'guest' => $parameters['ACL']['save']['guest']
            ),
            'SMILEY' => array(
                'user'  => $parameters['ACL']['smiley']['user'],
                'guest' => $parameters['ACL']['smiley']['guest']
            ),
            'MAIL' => array(
                'user'  => $parameters['ACL']['mail']['user'],
                'guest' => $parameters['ACL']['mail']['guest']
            ),
            'VIDEOCHAT' => array(
                'user'  => $parameters['ACL']['video']['user'],
                'guest' => $parameters['ACL']['video']['guest']
            ),
            'CHATROOM' => array(
                'user'  => $parameters['ACL']['chatroom']['user'],
                'guest' => $parameters['ACL']['chatroom']['guest']
            ),
            'MOBILECHAT' => array(
                'user'  => 'allow',
                'guest' => 'allow'
            ),
            'CHATROOM_CRT' => array(
                'user'  => $parameters['ACL']['chatroom_crt']['user'],
                'guest' => $parameters['ACL']['chatroom_crt']['guest']
            ),
            'FORMATTER' => array(
                'user'  => "allow",
                'guest' => "allow"
            )
            
        );
        return $ACL;
    }

    public function get_smileys() {

        $query = "SELECT symbol,image_name FROM frei_smileys";

        $result = $this->db->query($query);
        return $result->fetchAll();
    }

    public function get_all_vars() {
        $this->get_js_config();
        $this->get_acl();
    }

//------------------------------------------------------------------------------------------------
    public function freichat_debug($message) {
        if ($this->debug == true) {
            $dbgfile = fopen("../freixlog.log", "a");
            fwrite($dbgfile, "\n" . date("F j, Y, g:i a") . ": " . $message . "\n");
        }
    }

//----------------------------------------------------------------------------------------------
    public function bigintval($value) {
        $value = trim($value);
        if (ctype_digit($value)) {
            return $value;
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
            return $value;
        }
        return 0;
    }

//----------------------------------------------------------------------------------------------

    public function inc_lang() {

        //$frei_trans declared in  language file

        if ($this->lang != 'english') {
            if (empty($frei_trans)) {
                $EnglishLangInc = require 'lang/english.php';
                if ($EnglishLangInc != 1) {
                    $this->freichat_debug('Enlish language file could not be included');
                }
            } else {
                $this->freichat_debug('frei_trans array already in use!');
            }

            $OtherLangInc = require 'lang/' . $this->lang . '.php';

            if ($OtherLangInc != 1) {
                $this->freichat_debug('Some error while including' . $this->lang . ' language file');
            }
        } else if ($this->lang == 'english') {
            $EnglishLangInc = require 'lang/english.php';

            if ($EnglishLangInc != 1) {
                $this->freichat_debug('path to english language incorrect');
            }
        } else {
            $this->freichat_debug('Wrong filename given in parameter');
        }
        return $frei_trans;
    }

}
