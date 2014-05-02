<?php
/**
 * simbio_form_maker
 * Class for creating form with element based on simbio form elements
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
  protected $elements = array();
  protected $hidden_elements = array();
  protected $form_name = '';
  protected $form_method = '';
  protected $form_action = '';
  protected $disable = '';
  protected $enable_upload = true;

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
   * Method to start form
   *
   * @return  string
   */
  public function startForm()
  {
    return '<form name="'.$this->form_name.'" id="'.$this->form_name.'" '.($this->disable?'class="disabled"':'')
      .'method="'.$this->form_method.'" '
      .'action="'.$this->form_action.'" target="'.$this->submit_target.'"'.($this->enable_upload?' enctype="multipart/form-data"':'').'>';
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
   * @return  void
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
   * @return  void
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
  }


  /**
   * Method to add checkbox field to form
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_chbox
   * @param   mixed   $default_checked
   * @param   string  $str_elmnt_info
   * @return  void
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
  }


  /**
   * Method to add radio button field to form
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_label
   * @param   array   $array_option
   * @param   mixed   $default_checked
   * @param   string  $str_elmnt_info
   * @return  void
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
   * @return  void
   */
  public function addDateField($str_elmnt_name, $str_elmnt_label, $str_elmnt_value = '', $str_elmnt_attr = '', $str_elmnt_info = '')
  {
    $this->addTextField('date', $str_elmnt_name, $str_elmnt_label, $str_elmnt_value, $str_elmnt_attr, $str_elmnt_info);
  }


  /**
   * Method to add hidden fields
   *
   * @param   string  $str_elmnt_name
   * @param   string  $str_elmnt_value
   * @return  void
   */
  public function addHidden($str_elmnt_name, $str_elmnt_value)
  {
    $_form_element = new simbio_fe_text();
    $_form_element->element_type = 'hidden';
    $_form_element->element_name = $str_elmnt_name;
    $_form_element->element_value = $str_elmnt_value;
    $this->hidden_elements[] = $_form_element;
  }


  /**
   * Method to add anything such as text or other HTML element to form
   *
   * @param   string  $str_elmnt_label
   * @param   string  $str_content
   * @return  void
   */
  public function addAnything($str_elmnt_label, $str_content)
  {
    $_form_element = new simbio_form_maker_anything();
    $_form_element->content = $str_content;
    $this->elements[] = array('label' => $str_elmnt_label, 'element' => $_form_element, 'info' => null);
  }


  /**
   * Method to add simbio form elements object directly
   *
   * @param   string  $str_elmnt_label
   * @param   object  $obj_simbio_fe
   * @return  void
   */
  public function addFormObject($str_elmnt_label, $obj_simbio_fe, $str_elmnt_info = '')
  {
    $this->elements[$obj_simbio_fe->element_name] = array('label' => $str_elmnt_label, 'element' => $obj_simbio_fe, 'info' => $str_elmnt_info);
  }
}
