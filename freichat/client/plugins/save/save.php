<?php

require_once '../base.php';

class save extends base {

    private $is_chatroom;
    
    public function __construct() {
        parent::__construct();

        $this->url = str_replace("upload.php", "", $this->url);
       
        $this->is_chatroom = $this->is_chatroom_mode();
        
        
    }
    
    public function is_chatroom_mode() {
        
        return (isset($_GET['mode']) && $_GET['mode'] == 'chatroom');
    }
       

    public function writeconv($download) {
        $frm_id = $_SESSION[$this->uid . 'usr_ses_id'];
        $to_id = $this->bigintval($_GET['toid']);

        $to_name = htmlentities($_GET['toname'], ENT_QUOTES, "UTF-8");
        
        if($this->is_chatroom) {
            $title = "chatroom conversation";
        }else{
            $title = "Conversation with $to_name";
            }
            $name = "<html><head><meta http-equiv='content-type' content='text/html; charset=utf-8'><title>$title</title></head><body><center><h3>$title</h3></center>";

        $lines = '<hr/>';
        //$path = "tmp/".$name.".txt";

        if($this->is_chatroom) {
            $query = "SELECT DISTINCT * FROM frei_chat WHERE room_id=? AND message_type=1 order by time DESC";
            $arr = array($to_id);
        }else{
            $query = "SELECT * FROM frei_chat WHERE (frei_chat.\"to\"=? AND frei_chat.\"from\"=?) OR (frei_chat.\"from\"=? AND frei_chat.\"to\"=?) ORDER BY time";            
            $arr = array($frm_id, $to_id, $frm_id, $to_id);
        }
        
        $isset_mesg = $this->db->prepare($query);
        $isset_mesg->execute($arr);
        $messages = $isset_mesg->fetchAll();

        $contents = "";

                $prime = $name . "\n" . $lines . "\n\n";
echo $prime;

?>
<script type="text/javascript">
getlocal_time = function(GMT_time) {


    var d = new Date();
    var offset = d.getTimezoneOffset() * 60000;
    var timestamp = GMT_time - offset;

    var dTime = new Date(timestamp);
    var hours = dTime.getHours();
    var minute = dTime.getMinutes();

    if (minute < 10) {
        minute = "0" + minute;
    }
    
     var period = "AM";
     if (hours > 12) {
     period = "PM"
     }
     else {
     period = "AM";
     }
    hours = ((hours > 12) ? hours - 12 : hours)
    document.write(hours + ":" + minute + " " + period);
};


</script>

<?php

        foreach ($messages as $message) {
             echo "<b>" . $message['from_name'] . " [<script type=\"text/javascript\">getlocal_time(". $message['GMT_time'] .")</script>]:</b>  " . str_replace("\'", "'", $message['message']) . " <br/>\n";
        }

        //$complete_contents = $prime . str_replace("&#44;", ",", $contents) . "<hr/></body></html>";
echo "</body></html>";exit;
        $filename = "$title.html";

        if ($download == true) {
            $this->downloadconv($filename, $complete_contents);
        }
        return $complete_contents;
    }

    public function downloadconv($filename, $contents) {

// Send file headers
        header("Content-type: file");
        header("Content-Disposition: attachment;filename=$filename");
        header("Content-Transfer-Encoding: binary");
        header('Pragma: no-cache');
        header('Expires: 0');
// Send the file contents.
        echo $contents;
        set_time_limit(0);
    }

}

$save = new save();
$save->writeconv(true);
