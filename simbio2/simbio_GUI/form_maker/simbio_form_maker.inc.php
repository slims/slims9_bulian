<?php
/**
 * simbio_form_maker
 * Class for creating form with element based on simbio form elements
 *
 * @author Original code by Ari Nugraha (dicarve@gmail.com)
 * @package Simbio2
 * @subpackage simbio_form_maker
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

require 'simbio_form_element.inc.php';

/**
 * A Helper class for containing anything in form
 */
class simbio_form_maker_anything extends abs_simbio_form_element
{
  public $content = '';

  public function out()
  {
    return $this->content;
  }
}


class simbio_form_maker
{
  public $submit_target = '_self';
  public $add_form_attributes = '';
  public $css_classes = 'simbio_form_maker';
  protected $elements = array();
  protected $hidden_elements = array();
  protected $form_name = '';
  protected $form_method = '';
  protected $form_action = '';
  protected $disable = '';
  protected $enable_upload = true;
  protected $enable_token = true;
  protected $submit_token = null;
  protected $submit_token_name = null;
  protected $custom_btn_layout = '';

  /**
   * Class Constructor
   *
   * @param   string  $str_form_name
   * @param   string  $str_form_action
   * @param   string  $str_form_method
   * @param   boolean $bool_enable_upload
   */
  public function __construct($str_form_name = 'mainForm', $str_form_action = '', $str_form_method = 'post', $bool_enable_upload = true)
  {
    $this->form_name = $str_form_name;
    $this->form_action = $str_form_action;
    $this->form_method = $str_form_method;
    $this->enable_upload = $bool_enable_upload;
  }

  /**
   * Static method to create random form submission token
   *
   * @param   int       $length
   * @return  string
   */
  public static function genRandomToken($length = 32){
    if(!isset($length) || intval($length) <= 8 ) {
      $length = 32;
    }
    if (function_exists('random_bytes')) {
      return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
      return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
      return bin2hex(openssl_random_pseudo_bytes($length));
    }
  }


  /**
   * Static method check validaty of form submission token
   *
   * @return  boolean
   */
  public static function isTokenValid(){
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token']) && isset($_POST['form_name'])) {
      if (($_SESSION['csrf_token'][$_POST['form_name']]??'') === $_POST['csrf_token']) {
        // update token session
        $_SESSION['csrf_token'][$_POST['form_name']] = self::genRandomToken();
        self::updateToken($_POST['form_name'], $_SESSION['csrf_token'][$_POST['form_name']]);
        return true;
      } else {
        // remove token session var
        unset($_SESSION['csrf_token'][$_POST['form_name']]);
        return false;
      }
    }
    return false;
  }

  /**
   * Static method update token in form
   * @param $form_name
   * @param $token
   *
   * @return void
   */
  public static function updateToken($form_name, $token) {
    ?>
    <script type="application/javascript">
      parent.document.querySelector('form[name="<?php echo $form_name; ?>"] > input[name="csrf_token"]')
          .value = '<?php echo $token;?>';
    </script>
    <?php
  }


  /**
   * Method to disable form submission token
   * this method MUST BE called before startForm method call
   *
   * @return  void
   */
  public function disableSubmitToken()
  {
    $this->enable_token = false;
  }

  /**
   * Method to start form
   *
   * @return  string
   */
  public function startForm()
  {
    if ($this->disable) {
      $this->css_classes .= ' disabled';
    }
    $start_form = '<form name="'.$this->form_name.'" id="'.$this->form_name.'" class="'.$this->css_classes.'" '
      .'method="'.$this->form_method.'" '
      .'action="'.$this->form_action.'" target="'.$this->submit_target.'"'.($this->enable_upload?' enctype="multipart/form-data"':' ').$this->add_form_attributes.'>';
    if ($this->enable_token) {
      $this->submit_token = self::genRandomToken();
      $start_form .= '<input type="hidden" name="csrf_token" value="'.$this->submit_token.'" />';
      $start_form .= '<input type="hidden" name="form_name" value="'.$this->form_name.'" />';
      if (isset($_SESSION)) {
        $_SESSION['csrf_token'][$this->form_name] = $this->submit_token;
      }
    }
    return $start_form;
  }


  /**
   * Method to end form
   *
   * @return  string
   */
  public function endForm()
  {
    return '</form>';
  }


  /**
   * Method to printOut form object
   *
   */
  protected function printOut()
  {
    // please extends this method
  }


  /**
   * Method to add text field to form
   *
   * @param   string  $str_elmnt_type
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   string  $str_elmnt_value
   * @param   string  $str_elmnt_attr
   * @param   string  $str_elmnt_info
   * @return  object  the simbio_form_maker instance
   */
  public function addTextField($str_elmnt_type, $str_elmnt_name, $str_elmnt_label, $str_elmnt_value = '', $str_elmnt_attr = '', $str_elmnt_info = '')
  {
    // create instance
    $_form_element = new simbio_fe_text();
    // set form element object properties
    $_form_element->element_type = $str_elmnt_type;
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_value = $str_elmnt_value;
    $_form_element->element_attr = $str_elmnt_attr;
    $_form_element->element_helptext = $str_elmnt_info;
    $this->elements[$str_elmnt_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => $str_elmnt_info);
    return $this;
  }


  /**
   * Method to add select list field to form
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_option
   * @param   string  $str_default_selected
   * @param   string  $str_elmnt_attr
   * @param   string  $str_elmnt_info
   * @return  object  the simbio_form_maker instance
   */
  public function addSelectList($str_elmnt_name, $str_elmnt_label, $array_option, $str_default_selected = '', $str_elmnt_attr = '', $str_elmnt_info = '')
  {
    // create instance
    $_form_element = new simbio_fe_select();
    // set form element object properties
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_options = $array_option;
    $_form_element->element_value = $str_default_selected;
    $_form_element->element_attr = $str_elmnt_attr;
    $_form_element->element_helptext = $str_elmnt_info;
    $this->elements[$str_elmnt_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => $str_elmnt_info);
    return $this;
  }


  /**
   * Method to add checkbox field to form
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_chbox
   * @param   mixed   $default_checked
   * @param   string  $str_elmnt_info
   * @return  object  the simbio_form_maker instance
   */
  public function addCheckBox($str_elmnt_name, $str_elmnt_label, $array_chbox, $default_checked = '', $str_elmnt_info = '')
  {
    // create instance
    $_form_element = new simbio_fe_checkbox();
    // set form element object properties
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_options = $array_chbox;
    $_form_element->element_value = $default_checked;
    $_form_element->element_helptext = $str_elmnt_info;
    $this->elements[$str_elmnt_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => $str_elmnt_info);
    return $this;
  }


  /**
   * Method to add radio button field to form
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_option
   * @param   mixed   $default_checked
   * @param   string  $str_elmnt_info
   * @return  object  the simbio_form_maker instance
   */
  public function addRadio($str_elmnt_name, $str_elmnt_label, $array_option, $default_checked = '', $str_elmnt_info = '')
  {
    // create instance
    $_form_element = new simbio_fe_radio();
    // set form element object properties
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_options = $array_option;
    $_form_element->element_value = $default_checked;
    $_form_element->element_helptext = $str_elmnt_info;
    $this->elements[$str_elmnt_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => $str_elmnt_info);
    return $this;
  }


  /**
   * Method to add date selection field to form
   *
   * @param   string  $str_date_elmnt_name
   * @param   string  $str_month_elmnt_name
   * @param   string  $str_year_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   string  $str_date
   * @param   string  $str_elmnt_info
   * @return  object  the simbio_form_maker instance
   */
  public function addDateField($str_elmnt_name, $str_elmnt_label, $str_elmnt_value = '', $str_elmnt_attr = '', $str_elmnt_info = '')
  {
    $this->addTextField('date', $str_elmnt_name, $str_elmnt_label, $str_elmnt_value, $str_elmnt_attr, $str_elmnt_info);
    return $this;
  }


  /**
   * Method to add hidden fields
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_value
   * @return  object  the simbio_form_maker instance
   */
  public function addHidden($str_elmnt_name, $str_elmnt_value)
  {
    $_form_element = new simbio_fe_text();
    $_form_element->element_type = 'hidden';
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_value = $str_elmnt_value;
    $this->hidden_elements[] = $_form_element;
    return $this;
  }


  /**
   * Method to add anything such as text or other HTML element to form
   *
   * @param   string  $str_elmnt_label
   * @param   string  $str_content
   * @return  object  the simbio_form_maker instance
   */
  public function addAnything($str_elmnt_label, $str_content, $str_elmnt_name = null)
  {
    $_form_element = new simbio_form_maker_anything();
    $_form_element->element_type = 'content';
    $_form_element->content = $str_content;
    if (!is_null($str_elmnt_name)) {
      $_form_element->element_name = $str_elmnt_name;
      $this->elements[$str_elmnt_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => null);
    } else {
      $element_name = preg_replace('/[^\da-z]/i', '', strtolower($str_elmnt_label));
      $_form_element->element_name = $element_name;
      $this->elements[$element_name] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => null);
    }
    return $this;
  }

  /**
   * Method to add simbio form elements object directly
   *
   * @param   string  $str_elmnt_label
   * @param   object  $obj_simbio_fe
   * @return  object  the simbio_form_maker instance
   */
  public function addFormObject($str_elmnt_label, $obj_simbio_fe, $str_elmnt_info = '')
  {
    $this->elements[$obj_simbio_fe->element_name] = array('label' => $str_elmnt_label, 'element' => $obj_simbio_fe, 'info' => $str_elmnt_info);
    return $this;
  }

  /**
   * Method to add custom button layout
   *
   * @param Closure $designer
   * @return void
   */
  public function customBtnLayout($designer)
  {
    if ($designer instanceof Closure) {
      $this->custom_btn_layout = $designer($this);
    }
  }

  /**
   * Getter method to get all registered form elements
   *
   * @return void
   */
  public function &getAllFormElements() {
    return $this->elements;
  }

  /**
   * Getter method to get single registered form elements
   *
   * @param string element_name the name of element
   * @return void
   */
  public function &getFormElement($element_name) {
    if (isset($this->elements[$element_name])) {
      return $this->elements[$element_name];
    }
    return null;
  }

  /**
   * Method to remove single registered form elements
   *
   * @param string element_name the name of element to be removed
   * @return array the array of removed element
   */
  public function removeElements($element_name) {
    if (isset($this->elements[$element_name])) {
      $removed = $this->elements[$element_name];
      unset($this->elements[$element_name]);
      return $removed;
    }
  }
}
