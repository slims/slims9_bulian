<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T15:56:01+07:00
# @Email:  ido.alit@gmail.com
# @Filename: ControlField.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-19T18:12:43+07:00



namespace Marc;

class ControlField extends \Marc\Field
{

  protected $data;

  function __construct($tag, $data, $ind1 = null, $ind2 = null)
  {

    parent::__construct($tag);
    $this->data = $data;
  }

  public function getData()
  {
    return (string)$this->data;
  }

  public function isEmpty()
  {
    return ($this->data) ? false : true;
  }

}
