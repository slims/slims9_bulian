<?php
if(!isset($_SESSION))session_start();
error_reporting(E_ALL);

if (get_magic_quotes_gpc()) {
    $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
    while (list($key, $val) = each($process)) {
        foreach ($val as $k => $v) {
            unset($process[$key][$k]);
            if (is_array($v)) {
                $process[$key][stripslashes($k)] = $v;
                $process[] = &$process[$key][stripslashes($k)];
            } else {
                $process[$key][stripslashes($k)] = stripslashes($v);
            }
        }
    }
    unset($process);
}
if (isset($_REQUEST['action'])) {
require_once '../../../arg.php';
}
class theme_maker extends FreiChat{

    public $one_or_three='../../../';
    
//-------------------------------------------------------------------------------------
    public function __construct($oot='../') {
        parent::__construct();
        $this->init_vars();
        $this->one_or_three=$oot;
        
        
        if($oot=='../../../'){
            
            require_once '../home/magic_gpc_unset.php';
            
        }
        
        if(!isset($_SESSION[$this->uid . 'curr_theme'])){
            $_SESSION[$this->uid . 'curr_theme']='basic';
        }
        $this->path = RDIR . '/client/themes/' . $_SESSION[$this->uid . 'curr_theme'];
       // var_dump($_SESSION);
        $this->freichat_theme = $_SESSION[$this->uid . 'curr_theme'];
    }

//-------------------------------------------------------------------------------------
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

//-------------------------------------------------------------------------------------
    public function interchange($source, $destination) {
        $des_contents = file_get_contents($source);
        if ($des_contents) {
            file_put_contents($destination, $des_contents);
            return 'success';
        } else {
            $this->freichat_debug('Unable to get contents of argument.php file');
            return false;
        }
        return false;
    }

//-------------------------------------------------------------------------------------
    public function list_themes() {
        $src = RDIR . '/client/themes';

        $dir = opendir($src);
        $i = 0;
        $themes = array();
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $themes[$i] = $file;
                    $i++;
                }
            }
        }
        closedir($dir);

        return $themes;
    }

//-------------------------------------------------------------------------------------
    public function reset_argfile() {
        $destination = $this->path . '/argument.php';
        $source = $this->path . '/argument_def.php';
        $a = $this->interchange($source, $destination);
        var_dump($a);
        echo $destination.$source;
    }

    public function freichat_debug($message) {
        if ($this->debug == true) {
            $dbgfile = fopen($this->one_or_three."freixlog.log", "a");
            fwrite($dbgfile, "\n" . date("F j, Y, g:i a") . ": " . $message . "\n");
        }
    }

//-------------------------------------------------------------------------------------
    public function chk_project() {
        $file = $this->path . '/author.txt';
        $author = file_get_contents($file);
        if ($author) {
            if (strpos($author, 'FreiChatX') !== FALSE) {
                return 'true';
            } else {
                return 'false';
            }
        } else {
            $this->freichat_debug('Unable to get contents of author.txt file');
        }
        return 'false';
    }

//-------------------------------------------------------------------------------------
    public function save() {
        $source = $this->path . '/argument_def.php';
        $destination = $this->path . '/argument.php';
        echo json_encode($this->interchange($source, $destination));
    }

//-------------------------------------------------------------------------------------
    public function recurse_copy($src, $dst) {
        $dir = opendir($src);
        mkdir($dst);
        chmod($dst, 0777);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                    chmod($dst . '/' . $file, 0777);
                }
            }
        }
        closedir($dir);
    }

//-------------------------------------------------------------------------------------    
    public function recurse_delete($directory) {

        if (!is_readable($directory)) {
            return 'perms';
        } else {
            $handle = opendir($directory);

            while (false !== ( $file = readdir($handle))) {
                if (( $file != '.' ) && ( $file != '..' )) {

                    $path = $directory . '/' . $file;

                    if (is_dir($path)) {
                        $this->recurse_delete($path);
                    } else {
                        unlink($path);
                    }
                }
            }
            @closedir($directory);

            if (!rmdir($directory)) {
                return 'perms';
            }
        }
        return 'success';
    }

//-------------------------------------------------------------------------------------
    public function gateway($theme_name,$author_name) {

        $status = array();

        if (is_dir($this->one_or_three.'client/themes/' . $theme_name)) {
            $status['theme_name'] = 'exists';
        } else {
            $this->create_theme($theme_name, $author_name);

            if (!is_dir($this->one_or_three.'client/themes/' . $theme_name)) {
                $status['theme_name'] = 'perms';
            } else {
                $status['theme_name'] = 'success';
            }
        }

        if ($status['theme_name'] == 'success')
            $_SESSION[$this->uid . 'curr_theme'] = $theme_name;
        return $status['theme_name'];
    }

//-------------------------------------------------------------------------------------
    public function create_theme($theme_name, $author_name) {
        $this->recurse_copy($this->path . '/', $this->one_or_three.'client/themes/' . $theme_name);
        file_put_contents($this->one_or_three.'client/themes/' . $theme_name . '/author.txt', $author_name);
    }

//-------------------------------------------------------------------------------------
    public function change_theme() {
        $theme = strip_tags($_GET['theme']);
        $_SESSION[$this->uid . 'curr_theme'] = $theme;
    }

    //-------------------------------------------------------------------------------------   
    public function delete_theme() {
        
        if(isset($_GET['theme_name'])) {
     
            $this->path = RDIR . '/client/themes/'.$_GET['theme_name'];
        }
        $status = $this->recurse_delete($this->path);
        if ($status == 'success' && isset($_GET['theme_name'])==false)
            $_SESSION[$this->uid . 'curr_theme'] = 'basic';

        echo json_encode($status);
    }

//-------------------------------------------------------------------------------------    
    public function rename_theme() {
        $theme_name = strip_tags($_GET['theme_name']);

        if(!isset($_GET['old_name'])) {
            $old_name = $_SESSION[$this->uid . 'curr_theme'];
        }else{
            $old_name = $_GET['old_name'];
        }
        
        $old_name = $this->one_or_three.'client/themes/' . $old_name;
        $new_name = $this->one_or_three.'client/themes/' . $theme_name;
        $status = array();

        if (is_dir($new_name)) {
            $status['theme_name'] = 'exists';
        } else {
            //$this->create_theme($theme_name, $author_name);

            rename($old_name, $new_name);
            if (!is_dir($new_name)) {
                $status['theme_name'] = 'perms';
            } else {
                $status['theme_name'] = 'success';
            }
        }

        if ($status['theme_name'] == 'success' && isset($_GET['old_name']) == false)
            $_SESSION[$this->uid . 'curr_theme'] = $theme_name;
        echo json_encode($status);
    }

//-------------------------------------------------------------------------------------

    public function explode($del, $arr) {
        $f_array = explode($del, $arr);
        array_pop($f_array);
        return $f_array;
    }
//-------------------------------------------------------------------------------------  

public function replace_tokens($string,$reverse=false) {
    
    $tokens = array(
        ">" => "C_GREATER_THAN",
        "*" => "C_ASTERIX"
        
    );
    
    foreach($tokens as $token=>$value) {        
        if($reverse == false)
            $string = str_replace($token,$value,$string);
        else
            $string = str_replace($value,$token,$string);
            
    }
    
    return $string;
}    
//-------------------------------------------------------------------------------------  
public function parsecss($css)
{
    //$css = file_get_contents($file);
    $css = $this->replace_tokens($css); //bad hack , i know it.
    
    preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
    $result = array();
          
    foreach ($arr[0] as $i => $x){
        $selector = $this->replace_tokens(trim($arr[1][$i]),true);
        $rules = explode(';', trim($arr[2][$i]));
        $rules_arr = array();
        foreach ($rules as $strRule){
            if (!empty($strRule)){
                $rule = explode(":", $strRule);
                
                if(!isset($rule[1])) continue;
                
                $property = trim($rule[0]);
                $value = trim($rule[1]);
                
                if(isset($rules_arr[$property])){
                    //looks like there are same properties with different values
                    //so first make the value itself an array of previous value and push all values into it one by one
                    if(!is_array($rules_arr[$property])){
                        $rules_arr[$property] = array($rules_arr[$property]);//store the previous value as an array                        
                    }
                    
                    array_push($rules_arr[$property], $value);
                }else{
                    $rules_arr[$property] = $value;
                }
            }
        }
        
        $selectors = explode(',', trim($selector));
        foreach ($selectors as $strSel){
            if(!isset($result[$strSel]))
                $result[$strSel] = $rules_arr;
            else{
                $result[$strSel] = array_merge($result[$strSel],$rules_arr);
            }
        }
    }
    return $result;   
}


//-------------------------------------------------------------------------------------
    public function get_css_array() {

        $css = $this->get_css();
        echo json_encode($css);
    }
//-------------------------------------------------------------------------------------
    private function get_css() {

        $file = $this->path.'/styles.css';
        $css = file_get_contents($file);

        return $this->parsecss($css);
    }    
//-------------------------------------------------------------------------------------
    public function save_style_changes(){

      $css = $this->get_css();
      $format = '';
      
      $css_array = $_POST['css_array'];

       foreach($css as $css_key => $css_values) {
           
          if(isset($css_array[$css_key])) {
              
              //this is modified
              $css_values = array_merge($css[$css_key], $css_array[$css_key]);
          }
          
          $format .= $css_key . " {\n";
          $css_key_content = '';
          foreach ($css_values as $css_property => $css_value) {
              
              if(is_array($css_value)) {
                  //duplicate css property is stores as an multi value array
                  foreach($css_value as $value) {
                      $css_key_content .= $css_property . ":" . $value.";\n";                                        
                  }
              }else{
                  $css_key_content .= $css_property . ":" . $css_value.";\n";                  
              }
              
          }
          $format .= $css_key_content;
          
          $format = $format."}\n\n";
     }
     
      $file = strip_tags($_POST['filename']);
      $filename = $this->path.'/'.strip_tags($_POST['filename']);
      
        preg_match_all('/<\?php(.*?)\?>/s',$format,$matches);
       foreach ($matches[0] as $match) {
            $new = str_replace( "^", ";", $match);
            $format = str_replace($match,$new,$format);
        }
        
        $preformat = '';
        if($file == 'css.php' )
        {
            $preformat   = "/*X_CSS_PARSE*/\n";
        }
       //echo $filename;
      $format = $preformat.$format;
      file_put_contents($filename, rtrim($format));
    }
    
    public function set_default() {

        $current_theme = $_POST['current_theme'];
        
        $query = "UPDATE frei_config SET frei_config.val='$current_theme' WHERE frei_config.\"key\"='freichat_theme'";
        $this->db->query($query);
        
    }
//-------------------------------------------------------------------------------------

}    
   


if (isset($_REQUEST['action'])) {
     $theme = new theme_maker('../../../');
    if ($_REQUEST['action'] == 'restore')
        $theme->reset_argfile();
    else if ($_REQUEST['action'] == 'save')
        $theme->save();
    else if ($_REQUEST['action'] == 'list_themes')
        $theme->list_themes();
    else if ($_REQUEST['action'] == 'change_theme')
        $theme->change_theme();
    else if ($_REQUEST['action'] == 'delete_theme')
        $theme->delete_theme();
    else if ($_REQUEST['action'] == 'rename_theme')
        $theme->rename_theme();
    else if ($_REQUEST['action'] == 'get_css_array')
        $theme->get_css_array();
    else if ($_REQUEST['action'] == 'save_style_changes')
        $theme->save_style_changes();
    else if ($_REQUEST['action'] == 'set_default')
        $theme->set_default ();

}

