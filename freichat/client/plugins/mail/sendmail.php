<?php

require_once '../base.php';
require_once 'phpmailer/class.phpmailer.php';

class sendmail extends base {

    public $mail;

    public function __construct() {
        parent::__construct();
        $this->mail = new PHPMailer();
    }

    public function sendmail() {

        $message = $this->getconv();
        $message = wordwrap($message, 70);
        $this->mail->AddAddress(strip_tags($_POST['mailto']));
        $this->mail->Subject = strip_tags($_POST['subject']);
        $this->mail->Body = $message;
        $this->mail->CharSet = 'UTF-8';


        if ($this->mailtype == 'smtp') {
            $this->mail->IsSMTP(); // enable SMTP
        } else {
            $this->mail->IsMail();
        }

        $this->mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only

        if ($this->smtp_protocol != 'none') {
            $this->mail->SMTPAuth = true;  // authentication enabled
        }

        $this->mail->SMTPSecure = $this->smtp_protocol; // SSL TLS secure transfer enabled REQUIRED for Gmail
        $this->mail->Host = $this->smtp_server;
        $this->mail->Port = $this->smtp_port;
        $this->mail->Username = $this->smtp_username;
        $this->mail->Password = $this->smtp_password;
        $this->mail->SetFrom($this->mail_from_address, $this->mail_from_name);

        if (!$this->mail->Send()) {
            echo '<br/>Message was not sent.<br/>';
            echo '<br/>Mailer error: ' . $this->mail->ErrorInfo;
        } else {
            echo 'SUCCESS';
        }
    }

    public function getconv() {

        $frm_id = $_SESSION[$this->uid . 'usr_ses_id'];
        $to_id = $this->bigintval($_POST['toid']);

        $to_name = htmlentities($_POST['toname'], ENT_QUOTES, "UTF-8");
        $mode = $_POST['mode'];
        
        $is_chatroom = ($mode == "chatroom");
        
        if($is_chatroom) {
            $name = "chatroom conversation";            
        }else{
            $name = "Conversation with " . $to_name;            
        }
        
        $lines = '-----------------------------------------------------------';
        //$path = "tmp/".$name.".txt";

        if($is_chatroom) {
            $query = "SELECT DISTINCT * FROM frei_chat WHERE room_id=? AND message_type=1 order by time DESC";
            $arr = array($to_id);
        }else{
            $query = "SELECT * FROM frei_chat WHERE (frei_chat.\"to\"=? AND frei_chat.\"from\"=?) OR (frei_chat.\"from\"=? AND frei_chat.\"to\"=?) ORDER BY time";            
            $arr = array($frm_id, $to_id, $frm_id, $to_id);
        }
        
        $this->ISmesGquery = $this->db->prepare($query);
        $this->ISmesGquery->execute($arr);
        $messages = $this->ISmesGquery->fetchAll();


        $contents = "Conversation: \n\n";


        foreach ($messages as $message) {
            $contents.= $message['from_name'] . ":  " . str_replace("\'", "'", $message['message']) . " \n";
        }

        $prime = $name . "\n" . $lines . "\n\n";
        $complete_contents = $prime . str_replace("&#44;", ",", $contents);
        $complete_contents = strip_tags($complete_contents);
        $filename = $name . ".txt";

        return $complete_contents;
    }

}

$sendmail = new sendmail();
$sendmail->sendmail();
