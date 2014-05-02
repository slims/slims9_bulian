<?php
/**
 * simbio_form_element
 * Collection of Form Element Class
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
  die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
  die("can not access this file directly");
}

/* abstract form element class to be inherited by form element classes */
abstract class abs_simbio_form_element
{
  public $element_type = 'text';
  public $element_name = '';
  public $element_value;
  public $element_options;
  public $element_attr = '';
  public $element_css_class = '';
  public $element_disabled = false;
  public $element_helptext = '';

  /**
   * Below method must be inherited
   *
   * @return  string
   */
  abstract protected function out();
}

/* Text field object */
class simbio_fe_text extends abs_simbio_form_element
{
  public function out()
  {
    $_buffer = '';
    if (!in_array($this->element_type, array('textarea', 'text', 'password', 'button', 'file', 'hidden', 'submit', 'button', 'reset', 'date'))) {
      return 'Unrecognized element type!';
    }
    // check if disabled
    if ($this->element_disabled) {
      $_disabled = ' disabled="disabled"';
    } else { $_disabled = ''; }
    if ($this->element_helptext) {
      $this->element_attr .= ' title="'.$this->element_helptext.'"';
    }
    // maxlength attribute
    if (!stripos($this->element_attr, 'maxlength')) {
      if ($this->element_type == 'text') {
        $this->element_attr .= ' maxlength="256"';
      } else if ($this->element_type == 'textarea') {
        $this->element_attr .= ' maxlength="'.(30*1024).'"';
      }
    }

    // sanitize name for ID
    $_elID = str_replace(array('[', ']', ' '), '', $this->element_name);

    // checking element type
    if ($this->element_type == 'textarea') {
      $_buffer .= '<textarea name="'.$this->element_name.'" id="'.$_elID.'" '.$this->element_attr.''.$_disabled.'>';
      $_buffer .= $this->element_value;
      $_buffer .= '</textarea>'."\n";
    } else if (stripos($this->element_type, 'date', 0) !== false) {
      $_buffer .= '<div class="dateField"><input class="dateInput" type="'.$this->element_type.'" name="'.$this->element_name.'" id="'.$_elID.'" ';
      $_buffer .= 'value="'.$this->element_value.'" '.$this->element_attr.''.$_disabled.' /><a class="calendarLink notAJAX" style="cursor: pointer;" onclick="javascript: dateType = \''.$this->element_type.'\'; openCalendar(\''.$_elID.'\');" title="Open Calendar"></a></div>'."\n";
    } else {
      $_buffer .= '<input type="'.$this->element_type.'" name="'.$this->element_name.'" id="'.$_elID.'" ';
      $_buffer .= 'value="'.$this->element_value.'" '.$this->element_attr.''.$_disabled.' />'."\n";
    }

     return $_buffer;
  }
}

/* Drop Down Select List object */
class simbio_fe_select extends abs_simbio_form_element
{
  public function out()
  {
    // check for $array_option param
    if (!is_array($this->element_options)) {
      return '<select name="'.$this->element_name.'" '.$this->element_attr.'></select>';
    }
    // check if disabled
    if ($this->element_disabled) {
      $_disabled = ' disabled="disabled"';
    } else { $_disabled = ''; }
    if ($this->element_helptext) {
      $this->element_attr .= ' title="'.$this->element_helptext.'"';
    }
    $_buffer = '<select name="'.$this->element_name.'" id="'.$this->element_name.'" '.$this->element_attr.''.$_disabled.'>'."\n";
    foreach ($this->element_options as $option) {
      if (is_string($option)) {
        // if the selected element is an array then
        // the selected option is also multiple to
        if (is_array($this->element_value)) {
          $_buffer .= '<option value="'.$option.'" '.(in_array($option, $this->element_value)?'selected':'').'>';
          $_buffer .= $option.'</option>'."\n";
        } else {
          $_buffer .= '<option value="'.$option.'" '.(($option == $this->element_value)?'selected':'').'>';
          $_buffer .= $option.'</option>'."\n";
        }
      } else {
        if (is_array($this->element_value)) {
          $_buffer .= '<option value="'.$option[0].'" '.(in_array($option[0], $this->element_value)?'selected':'').'>';
          $_buffer .= $option[1].'</option>'."\n";
        } else {
          $_buffer .= '<option value="'.$option[0].'" '.(($option[0] == $this->element_value)?'selected':'').'>';
          $_buffer .= $option[1].'</option>'."\n";
        }
      }
    }
    $_buffer .= '</select>'."\n";

    return $_buffer;
  }
}

/* AJAX drop down select list object */
class simbio_fe_AJAX_select extends abs_simbio_form_element
{
  /**
   * AJAX drop down special properties
   */
  public $handler_URL = 'about:blank';
  public $element_dd_list_class = 'ajaxDDlist';
  public $element_dd_list_default_text = 'SEARCHING...';
  public $additional_params = '';

  public function out()
  {
    $_buffer = '<input type="text" autocomplete="off" id="'.$this->element_name.'" name="'.$this->element_name.'" class="'.$this->element_css_class.' notAJAX" onkeyup="showDropDown(\''.$this->handler_URL.'\', \''.$this->element_name.'\', \''.$this->additional_params.'\')" value="'.$this->element_value.'" />';
    $_buffer .= '<ul class="'.$this->element_dd_list_class.'" id="'.$this->element_name.'List"><li style="padding: 2px; font-weight: bold;">'.$this->element_dd_list_default_text.'</li></ul>';

    return $_buffer;
  }
}

/* Checkbox button groups object */
class simbio_fe_checkbox extends abs_simbio_form_element
{
  public function out()
  {
    // check for $this->element_options param
    if (!is_array($this->element_options)) {
      return 'The radio button options list must be an array';
    } else {
      foreach ($this->element_options as $cbox) {
        // if the $cbox is not an array
        if (!is_array($cbox)) {
          return 'The radio button options list must be a 2 multi-dimensional array';
        }
      }
    }

    $_elmnt_num = count($this->element_options);
    $_row_column = 5;
    $_helptext = '';

    // check if disabled
    if ($this->element_disabled) {
      $_disabled = ' disabled="disabled"';
    } else { $_disabled = ''; }
    if ($this->element_helptext) {
      $_helptext .= ' title="'.$this->element_helptext.'"';
    }
    $_buffer = '';
    if ($_elmnt_num <= $_row_column) {
      foreach ($this->element_options as $_cbox) {
        if (is_array($this->element_value)) {
          $_buffer .= '<div '.$_helptext.'><input type="checkbox" name="'.$this->element_name.'[]"'
            .' value="'.$_cbox[0].'" style="border: 0;" '.(in_array($_cbox[0], $this->element_value)?'checked':'').$_disabled.' />'
            .' '.$_cbox[1]."</div>\n";
        } else {
          $_buffer .= '<div '.$_helptext.'><input type="checkbox" name="'.$this->element_name.'[]"'
            .' value="'.$_cbox[0].'" style="border: 0;" '.(($_cbox[0] == $this->element_value)?'checked':'').$_disabled.' />'
            .' '.$_cbox[1]."</div>\n";
        }
      }
    } else {
      $_column_array = array_chunk($this->element_options, $_row_column);
      $_buffer = '<table '.$_helptext.'>'."\n";
      $_buffer .= '<tr>'."\n";
      foreach ($_column_array as $_chunked_options) {
        $_buffer .= '<td valign="top">'."\n";
        foreach ($_chunked_options as $_cbox) {
          if (is_array($this->element_value)) {
            $_buffer .= '<div><input type="checkbox" name="'.$this->element_name.'[]"'
              .' value="'.$_cbox[0].'" style="border: 0;" '.(in_array($_cbox[0], $this->element_value)?'checked':'').$_disabled.' />'
              .' '.$_cbox[1]."</div>\n";
          } else {
            $_buffer .= '<div><input type="checkbox" name="'.$this->element_name.'[]"'
              .' value="'.$_cbox[0].'" style="border: 0;" '.(($_cbox[0] == $this->element_value)?'checked':'').$_disabled.' />'
              .' '.$_cbox[1]."</div>\n";
          }
        }
        $_buffer .= '</td>'."\n";
      }
      $_buffer .= '</tr>'."\n";
      $_buffer .= '</table>'."\n";
    }

    return $_buffer;
  }
}


/* Radio button groups object */
class simbio_fe_radio extends abs_simbio_form_element
{
  public function out()
  {
    // check for $this->element_options param
    if (!is_array($this->element_options)) {
      return 'The third argument must be an array';
    }

    $_buffer = '';

    // number of element in each column
    if (count($this->element_options) > 10) {
      $_elmnt_each_column = 4;
    } else {
      $_elmnt_each_column = 2;
    }

    $_helptext = '';

    if ($this->element_helptext) {
      $_helptext .= ' title="'.$this->element_helptext.'"';
    }

    // chunk the array into pieces of array
    $_chunked_array = array_chunk($this->element_options, $_elmnt_each_column, true);

    $_buffer .= '<table '.$_helptext.'>'."\n";
    $_buffer .= '<tr>'."\n";
    foreach ($_chunked_array as $_chunk) {
      $_buffer .= '<td valign="top">';
      foreach ($_chunk as $_radio) {
        if ($_radio[0] == $this->element_value) {
          $_buffer .= '<div><input type="radio" name="'.$this->element_name.'" id="'.$this->element_name.'"'
            .' value="'.$_radio[0].'" style="border: 0;" checked />'
            .' '.$_radio[1]."</div>\n";
        } else {
          $_buffer .= '<div><input type="radio" name="'.$this->element_name.'" id="'.$this->element_name.'"'
            .' value="'.$_radio[0].'" style="border: 0;" />'
            .' '.$_radio[1]."</div>\n";
        }
      }
      $_buffer .= '</td>';
    }
    $_buffer .= '</tr>'."\n";
    $_buffer .= '</table>'."\n";

    return $_buffer;
  }
}

/* Date field */
/* Global vars containing date and month array */
$simbio_fe_date_array = array( array('01', strtoupper(__('Date')), '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
  '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
  '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'));
$simbio_fe_date_month_array = array( array('01', strtoupper(__('Month')), array('01', 'January'), array('02', 'February'), array('03', 'March'),
  array('04', 'April'), array('05', 'May'), array('06', 'June'),
  array('07', 'July'), array('08', 'August'), array('09', 'September'),
  array('10', 'October'), array('11', 'November'), array('12', 'December')));

/* Depecrated class for compability with older code */
class simbio_form_element
{
  /**
   * Static Method to create input field form element
   *
   * @param   string  $str_elmnt_type
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_value
   * @param   string  $str_elmnt_attr
   */
  public static function textField($str_elmnt_type, $str_elmnt_name, $str_elmnt_value = '', $str_elmnt_attr = '')
  {
    $_textField = new simbio_fe_text();
    $_textField->element_type = $str_elmnt_type;
    $_textField->element_name = $str_elmnt_name;
    $_textField->element_value = $str_elmnt_value;
    $_textField->element_attr = $str_elmnt_attr;
    return $_textField->out();
  }


  /**
   * Static Method to create form element
   *
   * @param   string  $str_elmnt_name
   * @param   array   $array_option
   * @param   string  $str_default_selected
   * @param   string  $str_elmnt_attr
   * @return  string
   */
  public static function selectList($str_elmnt_name, $array_option, $str_default_selected = '', $str_elmnt_attr = '')
  {
    $_selectList = new simbio_fe_select();
    $_selectList->element_name = $str_elmnt_name;
    $_selectList->element_value = $str_default_selected;
    $_selectList->element_attr = $str_elmnt_attr;
    $_selectList->element_options = $array_option;
    return $_selectList->out();
  }


  /**
   * Static Method to create form element
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_chbox
   * @return  string
   */
  public static function checkBox($str_elmnt_name, $array_chbox, $default_checked = '')
  {
    $_checkBox = new simbio_fe_checkbox();
    $_checkBox->element_name = $str_elmnt_name;
    $_checkBox->element_value = $default_checked;
    $_checkBox->element_options = $array_chbox;
    return $_checkBox->out();
  }


  /**
   * Static Method to create form element
   *
   * @param   string  $str_elmnt_name
   * @param   array   $array_option
   * @param   string  $str_default_selected
   * @return  string
   */
  public static function radioButton($str_elmnt_name, $array_option, $default_checked = '')
  {
    $_radio = new simbio_fe_select();
    $_radio->element_name = $str_elmnt_name;
    $_radio->element_value = $default_checked;
    $_radio->element_options = $array_option;
    return $_radio->out();
  }


  /**
   * Static Method to create date input field form element
   *
   * @param   string  $str_date_elmnt_name
   * @param   string  $str_month_elmnt_name
   * @param   string  $str_year_elmnt_name
   * @param   string  $str_date
   * @return  string
   */
  public static function dateField($str_elmnt_name, $str_elmnt_value = '', $str_elmnt_attr = '')
  {
    return self::textField('date', $str_elmnt_name, $str_elmnt_value, $str_elmnt_attr);
  }


  /**
   * Static Method to create form element
   *
   * @param   string  $str_date_elmnt_name
   * @param   string  $str_elmnt_value
   * @return  string
   */
  public static function hiddenField($str_elmnt_name, $str_elmnt_value)
  {
    $_textField = new simbio_fe_text();
    $_textField->element_type = 'hidden';
    $_textField->element_name = $str_elmnt_name;
    $_textField->element_value = $str_elmnt_value;
    return $_textField->out();
  }
}
