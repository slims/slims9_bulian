<?php
session_start();
require_once '../../../arg.php';

if(!isset($_SESSION[$uid . 'FreiChatX_init']))exit;


class upload extends FreiChat {

//---------------------------------------------------------------------------------------------
    public function __construct() {

        parent::__construct();
        $this->init_vars();
        $this->get_js_config();
        $this->url = str_replace("upload_mobile.php", "", $this->url);
        $this->p_url = str_replace("plugins/upload/", "", $this->url);
        $this->uploaded = false;
        $this->error = 0;
        $this->filename = null;
        $this->path = 'upload/';
    }

//---------------------------------------------------------------------------------------------

    public function sanitize($filename) {
        $parts = explode('.', $filename);
        $ext = array_pop($parts);
        $filename = implode($parts);
        $filename = preg_replace('#\W#', '', $filename);
        $filename = str_replace(" ", "", $filename);
        $time = substr(time(), 5);
        $filename = $filename . $time;
        $filename = $filename . "." . $ext;
        return $filename;
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

    public function json_encode($a = false) {
        if (!function_exists('json_encode')) {
            if (is_null($a))
                return 'null';
            if ($a === false)
                return 'false';
            if ($a === true)
                return 'true';
            if (is_scalar($a)) {
                if (is_float($a)) {
// Always use "." for floats.
                    return floatval(str_replace(",", ".", strval($a)));
                }

                if (is_string($a)) {
                    static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
                    return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
                }
                else
                    return $a;
            }
            $isList = true;
            for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
                if (key($a) !== $i) {
                    $isList = false;
                    break;
                }
            }
            $result = array();
            if ($isList) {
                foreach ($a as $v)
                    $result[] = json_encode($v);
                return '[' . join(',', $result) . ']';
            } else {
                foreach ($a as $k => $v)
                    $result[] = json_encode($k) . ':' . json_encode($v);
                return '{' . join(',', $result) . '}';
            }
        }
    }

//---------------------------------------------------------------------------------------------

    public function set_constants($xhr2) {
        if ($xhr2 == true) {
            $this->frm_id = $_SERVER['HTTP_X_FROM_ID'];
            $this->usr_name = $_SERVER['HTTP_X_FROM_NAME'];
            $this->to = $_SERVER['HTTP_X_TO_ID'];
            $this->to_name = $_SERVER['HTTP_X_TO_NAME'];
        } else {
            $this->frm_id = $_POST['fromid'];
            $this->usr_name = $_POST['fromname'];
            $this->to = $_POST['toid'];
            $this->to_name = $_POST['toname'];
        }
    }

    public function check_file($file_size, $file_type) {
        $file_ext = explode(",", $this->valid_exts);

        if ($file_size > $this->file_size_limit) {
            $status = 'exceed';
        } else if (!in_array($file_type, $file_ext) && $file_type != 'nofile') {
            $status = 'type';
        } else {
            $status = 'success';
        }
        return $status;
    }

//---------------------------------------------------------------------------------------------
    public function upload() {

        $xhr2 = true;

        if (!isset($_FILES) || !isset($_FILES['file'])) {
            //var_dump($_SERVER);
            //var_dump($_POST);
            $file_name = $this->sanitize($_SERVER['HTTP_X_FILE_NAME']);
            $file_type = strtolower($this->findexts($_SERVER['HTTP_X_FILE_NAME']));
            $file_size = $_SERVER['HTTP_X_FILE_SIZE'];

            //XHR2
        } else {
            $xhr2 = false;
            $file_name = $_FILES['file']['name'];
            $file_type = strtolower($this->findexts($_FILES["file"]["name"]));
            $file_size = $_FILES["file"]["size"];
            //Traditional
        }

        $this->set_constants($xhr2);
        $status = $this->check_file($file_size, $file_type);


        if ($status == "success") {

            if (is_writable($this->path)) {
                $temp_name = time() . rand(22, 333) . "." . $file_type;

                if ($xhr2 == true) {
                    file_put_contents(
                            $this->path . $temp_name, file_get_contents("php://input")
                    );
                } else {
                    move_uploaded_file($_FILES["file"]["tmp_name"], $this->path . $temp_name);
                }

                $this->savetoDB($temp_name, $file_name);
                @chmod($this->path . $temp_name, 0777);
                $message = array($this->url, $temp_name, $file_name); //."</a> [Sent succesfully!]</span>";
                
                if($xhr2 == true)   
                    echo json_encode(array($message,$status));
                else{
                    //ECHO HTML HERE;;
                }
            } else {
                $status = "UNWRITABLE";
            }
        } 
        
        $this->delete_files();
    }

//---------------------------------------------------------------------------------------------
    public function fdie($mesg) {
        $this->error_mesg = $mesg;
    }

//---------------------------------------------------------------------------------------------
    public function savetoDB($filename, $show_name) {
        $fname = $show_name;
        $replace = "_";
        $pattern = "/([[:alnum:]_\.-]*)/";
        $fname = str_replace(str_split(preg_replace($pattern, $replace, $fname)), $replace, $fname);
        $message = "<a target='_blank' href=" . $this->url . "download.php?filename=" . $filename . ">" . $fname . "</a>";
//var_dump($this);
        $message = str_replace("'", "\'", $message);
        $time = time() . str_replace(" ", "", microtime());
        $GMT_time = time();

        $insert_mesg_query = "INSERT INTO frei_chat (frei_chat.\"from\",from_name,frei_chat.\"to\",to_name,message,frei_chat.\"sent\",frei_chat.\"time\",message_type,room_id,GMT_time) VALUES(?,?,?,?,?,?,?,?,?,?)";
        $this->insert_mesg_query = $this->db->prepare($insert_mesg_query);
        
        $this->insert_mesg_query->execute(array($this->frm_id, $this->usr_name, $this->to, $this->to_name, $message, $this->mysql_now, $time, 0, '-1', $GMT_time));
//echo $query;
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
