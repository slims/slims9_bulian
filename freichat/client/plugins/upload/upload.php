<?php
session_start();
require_once '../../../arg.php';

if (!isset($_SESSION[$uid . 'FreiChatX_init']))
    exit("good bye");
error_reporting(-1);
ini_set("display_errors", "1");

class upload extends FreiChat {

    public $is_chatroom;

//---------------------------------------------------------------------------------------------
    public function __construct() {

        parent::__construct();
        $this->init_vars();
        $this->get_js_config();
        $this->url = str_replace("upload.php", "", $this->url);
        $this->uploaded = false;
        $this->error = 0;
        $this->filename = null;
        $this->path = 'upload/';
        $this->is_chatroom = ($_POST['mode'] == 'chatroom');
    }

//---------------------------------------------------------------------------------------------
    function findexts($fn) {
        $str = explode('/', $fn);
        $len = count($str);
        if (strpos($str[($len - 1)], '.') === False)
            return False; // Has not .
        $str2 = explode('.', $str[($len - 1)]);
        $len2 = count($str2);
        $ext = $str2[($len2 - 1)];
        return $ext;
    }

//---------------------------------------------------------------------------------------------
    public function upload() {

        echo "<div class='well'>";
        if (!isset($_FILES['file'])) {
            $this->error = TRUE;
            $this->fdie('Unknown error');
            echo '<br/><br/><a href="html.php">Send another file</a>';
            echo '<br/><br/>Window will be closed in about 6 seconds<script>setTimeout("self.close()",6000);</script></div>';
            exit;
        }


        $file_uploaded_ext = strtolower($this->findexts($_FILES["file"]["name"]));
        $file_ext = explode(",", $this->valid_exts);
        //$this->uploaded = false;
        if (!in_array($file_uploaded_ext, $file_ext)) {
            $this->error = TRUE;
            $this->fdie("Invalid file!<br/><br/>");
        } else if ($_FILES["file"]["size"] > $this->file_size_limit) {
            $this->error = TRUE;
            $this->fdie("File size too large!<br/><br/>");
        } else if ($_FILES["file"]["error"] > 0) {
            $this->error = TRUE;
            $this->fdie("File upload error<br/><br/>Return Code: " . $_FILES["file"]["error"] . "<br />");
        } else if ($_FILES["file"]["error"] == 0) {
            //if(!file_exists($this->path.$_FILES['file']['name']))
            // {
            if (is_writable($this->path)) {
                $this->error = FALSE;
                $temp_name = time() . rand(22, 333) . "." . $file_uploaded_ext;

                move_uploaded_file($_FILES["file"]["tmp_name"], $this->path . $temp_name);
                @chmod($this->path . $temp_name, 0777);
            } else {
                $this->fdie('Upload directory does not have required permissions');
            }
            // }
        } else {
            $this->error = TRUE;
            $this->fdie('Uknown error!<br/>');
        }


        if ($this->error == FALSE) {
            $this->filename = $temp_name; //$_FILES['file']['name'];
            $this->file_real_name = $_FILES['file']['name'];

            if ($this->is_chatroom) {
                echo '<div style="margin-bottom:4px" class="text-success">The file ' . $this->filename . ' has been succesfully sent</div>';
            } else {
                echo '<div style="margin-bottom:4px" class="text-success">The file ' . $this->filename . ' has been succesfully sent to ' . $_POST['toname'] . '</div>';
            }
            echo '<a class="btn btn-block" target="_blank" href=download.php?filename=' . $this->filename . '>Download your file</a>';
            $this->uploaded = true;
            $this->savetoDB($this->filename, $_FILES['file']['name']);
        } else {
            $this->uploaded = false;
            echo '<br/>Failed to upload file!<br/>';
        }


        echo '<a class="btn btn-block" href="html.php">or send another file</a>';
        echo '<div style="margin-top:4px" class="muted">This window will be closed in 6 seconds</div></div>';

        $this->delete_files();
    }

//---------------------------------------------------------------------------------------------
    public function fdie($mesg) {
        echo $mesg;
    }

//---------------------------------------------------------------------------------------------
    public function savetoDB($filename, $show_name) {
        $this->frm_id = $_POST['fromid'];
        $this->usr_name = $_POST['fromname'];
        $this->to = $_POST['toid'];
        $this->to_name = $_POST['toname'];
        $fname = $show_name;
        $replace = "_";
        $pattern = "/([[:alnum:]_\.-]*)/";
        $fname = str_replace(str_split(preg_replace($pattern, $replace, $fname)), $replace, $fname);
        $message = "File uploaded: <a target='_blank' href=" . $this->url . "download.php?filename=" . $filename . ">" . $fname . "</a>";
//var_dump($this);
        $message = str_replace("'", "\'", $message);
        $time = time() . str_replace(" ", "", microtime());
        $GMT_time = time();

        $insert_mesg_query = "INSERT INTO frei_chat (frei_chat.\"from\",from_name,frei_chat.\"to\",to_name,message,frei_chat.\"sent\",frei_chat.\"time\",message_type,room_id,GMT_time) VALUES(?,?,?,?,?,?,?,?,?,?)";
        $this->insert_mesg_query = $this->db->prepare($insert_mesg_query);

        if ($this->is_chatroom) {
            $message_type = 1;
            $in_room = $this->to;
        } else {
            $message_type = 0;
            $in_room = -1;
        }

        $this->insert_mesg_query->execute(array($this->frm_id, $this->usr_name, $this->to, $this->to_name, $message, $this->mysql_now, $time, $message_type, $in_room, $GMT_time));
    }

//---------------------------------------------------------------------------------------------

    public function delete_files() {
        $captchaFolder = $this->path;
        // Filetypes to check (you can also use *.*)
        $fileTypes = '*.*';
        $expire_time = $this->expirytime; //in minutes
        // Find all files of the given file type
        foreach (glob($captchaFolder . $fileTypes) as $Filename) {
            // Read file creation time
            $FileCreationTime = filectime($Filename);

            // Calculate file age in seconds
            $FileAge = time() - $FileCreationTime;

            // Is the file older than the given time span?
            if ($FileAge > ($expire_time * 60)) {
                //   echo "The file $Filename is older than $expire_time minutes\n";
                unlink($Filename);
            }
        }
    }

}

$upload = new upload();
$upload->upload();
?>
<!DOCTYPE html>
<html>
    <head>

        <script src="../lib/js/bootstrap.min.js"></script>
        <link href="../lib/css/bootstrap.min.css" rel="stylesheet" />
        <style>
            .muted {
                color: #999999;
            }
            .text-info {
                color: #3a87ad;
            }
            .text-error {
                color: #b94a48;
            }
            .text-success {
                color: #468847;
            }



        </style>
        <title>
            File Upload Status
        </title>

        <script>
            var $ = window.opener.jQuery;
             setTimeout("self.close()", 6000);

            if ("<?php echo $upload->uploaded; ?>" == true && "<?php echo $upload->error; ?>" == false)
            {

                var id = '<?php echo $upload->to; ?>';
                var div = $("#chatboxcontent_" + id + " .content");
                var message = "<span><a target='_blank' href=<?php echo $upload->url; ?>download.php?filename=<?php echo $upload->filename; ?>><?php echo $upload->file_real_name; ?></a> Sent succesfully.</span>";

                if ('<?php echo $upload->is_chatroom; ?>') {

                    if (opener.freidefines.GEN.reidfrom == opener.FreiChat.last_chatroom_usr_id && opener.FreiChat.chatroom_written[opener.FreiChat.in_room] == true) {//} && FreiChat.first_chatroom_message == false){// && FreiChat.last_in_chatroom == FreiChat.in_room) {
                        $('#' + opener.FreiChat.last_chatroom_msg_id).append("<br/>" + message);
                    } else
                    {
                        var message_div = '<div id = "' + opener.FreiChat.in_room + '_chatroom_message"  class="frei_chatroom_message"><span style="display:none" id="' + opener.FreiChat.in_room + '_message_type">LEFT</span>\n\
                <div class="chatroom_messagefrom_left"><span>' + freidefines.TRANS.chat_message_me + '</span><span class="freichat_time" style="visibility:visible;padding-right:15px">' + opener.FreiChat.getlocal_time(0) + '</span></div>\n\
                <div id="room_msg_' + opener.FreiChat.unique + '" class="frei_chatroom_msgcontent">' + message + '</div>\n\
                </div>';


                        $("#frei_chatroommsgcnt .content").append(message_div)
                        opener.FreiChat.last_chatroom_msg_id = 'room_msg_' + opener.FreiChat.unique;
                        opener.FreiChat.unique++;
                        opener.FreiChat.last_chatroom_usr_id = opener.freidefines.GEN.reidfrom;
                        opener.FreiChat.last_chatroom_msg_type[opener.FreiChat.in_room] = !opener.FreiChat.last_chatroom_msg_type[opener.FreiChat.in_room];
                    }
                } else {
                    var uniqueid = opener.FreiChat.unique++;
                    var data = {
                        from: opener.freidefines.GEN.fromid,
                        from_name: opener.freidefines.GEN.fromname,
                        GMT_time: 0
                    };

                    var mesg_html = opener.FreiChat.generate_mesg(uniqueid, data, message, id);

                    div.append(mesg_html);
                    opener.FreiChat.scroll_down("chatboxcontent_" + id, id);
                }
            }
        </script>
    </head>
    <body>
    </body>
</html>