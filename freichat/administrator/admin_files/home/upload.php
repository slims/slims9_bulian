<?php
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['phplogin'])
        || $_SESSION['phplogin'] !== true) {
    header('Location: ../administrator/index.php'); //Replace that if login.php is somewhere else
    exit;
}

        require '../theme_maker/streamer.php';

        
                require '../../../arg.php';

class upload extends FreiChat{

    public function __construct() {
        parent::__construct();
        $this->init_vars();
        $this->get_js_config();
        
        if(isset($_SERVER['HTTP_X_TYPE']) && $_SERVER['HTTP_X_TYPE'] == 'smileys') {
            $this->path = RDIR . '/client/themes/smileys/';
        }else {
            $this->path = RDIR . '/client/themes/' . $this->freichat_theme . '/';            
        }
        $this->max_file_size = 10 * 1024 * 1024 * 1024;
    }

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

    public function sanitize($filename) {
        $parts = explode('.', $filename);
        $ext = array_pop($parts);
        $filename = implode($parts);
        $filename = preg_replace('#\W#', '', $filename);
        $filename = str_replace(" ","",$filename);
        $time = substr(time(),5);
        $filename = $filename.$time;
        $filename = $filename.".".$ext;
        return $filename;
    }
    
   public function findexts($fn) {
        $str = explode('/', $fn);
        $len = count($str);
        if (strpos($str[($len - 1)], '.') === False)
            return False; // Has not .
        $str2 = explode('.', $str[($len - 1)]);
        $len2 = count($str2);
        $ext = $str2[($len2 - 1)];
        return $ext;
    }

    
    public function upload_file() {

        $file_name = $this->sanitize($_SERVER['HTTP_X_FILE_NAME']);
        $file_size = $_SERVER['HTTP_X_FILE_SIZE'];
        $file_type =  strtolower($this->findexts($_SERVER['HTTP_X_FILE_NAME']));       
        $symbol = $_SERVER['HTTP_X_SYMBOL'];
        $action = $_SERVER['HTTP_X_ACTION'];
        
        if($action == 'update')
        $old_image_name = $_SERVER['HTTP_X_IMAGENAME'];

        
        $file_ext = explode(",", $this->valid_exts);
        if ($file_size > $this->max_file_size) {
            $this->freichat_debug('file size exceeded');
            $status = 'exceed';
        } 
        else if(!in_array($file_type, $file_ext) && $file_type != 'nofile'){
            $this->freichat_debug('file type invalid');
            $status = 'type';
        }
        else {
                       
            $ft = new File_Streamer();
            $ft->_fileName = $file_name;
            $ft->setDestination($this->path);
            $ft->receive();
            @chmod($this->path.$file_name,0777);
           // $this->replace_file($original_name,$file_name);
            $status =  $file_name;
            if($action == 'insert')
            $status = $this->insert_smiley($file_name,$symbol);
            else
                $this->update_smiley($file_name,$symbol,$old_image_name,$file_type);
        }
            echo $status;

    }
    
    /*public function update_smiley($file_name,$symbol,$old_image_name,$file_type) {
        
        if($file_type == 'nofile'){
          $query = "UPDATE frei_smileys SET symbol='$symbol' WHERE image_name='$old_image_name'"; 
        }else{
            $query = "UPDATE frei_smileys SET symbol='$symbol', image_name='$file_name' WHERE image_name='$old_image_name'";
        }
        
        
        $this->db->query($query);
    }*/
            


    public function insert_smiley($file_name,$symbol) {
        
        
        $query = "SELECT COUNT(*) FROM frei_smileys WHERE symbol=".$this->db->quote($symbol);
        $res = $this->db->query($query);
        if($res->fetchColumn() > 0)
            return 'exists';
        //}
        $query = "INSERT INTO frei_smileys (symbol,image_name) VALUES(".$this->db->quote($symbol).",'$file_name')";
        
        $this->db->query($query);
        return $file_name;
    }

  /*  public function replace_file($originalname,$newname) {

        $filename = $originalname;
        $file_path = $this->path . "argument.php";
        $file = @file_get_contents($file_path);
        $variable = strip_tags($_SERVER['HTTP_X_VARIABLE_PHP']);

        $string = '$' . $variable . ' = \'' . $filename . '\';';
        $rep = '$' . $variable . ' = \'' . $newname . '\';';
        //echo $string . $rep;
        if ($file) {
            $file = str_replace($string, $rep, $file);
        } else {
            $this->freichat_debug('Unable to get contents of argument.php file');
        }
        file_put_contents($file_path, $file);
    }

    public function freichat_debug($message) {
        if ($this->debug == true) {
            $dbgfile = fopen("../../../freixlog.log", "a");
            fwrite($dbgfile, "\n" . date("F j, Y, g:i a") . ": " . $message . "\n");
        }
    }
*/
}



$upload = new upload();
$upload->upload_file();
?>