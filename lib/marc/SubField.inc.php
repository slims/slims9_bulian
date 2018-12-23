<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T15:56:12+07:00
# @Email:  ido.alit@gmail.com
# @Filename: SubField.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-19T19:10:10+07:00



namespace Marc;

class SubField
{

  protected $code;
  protected $data;

  function __construct($code, $data)
  {
    $this->code = $code;
    $this->data = $data;
  }

  public function getCode()
  {
    return (string)$this->code;
  }

  public function getData()
  {
    return (string)$this->data;
  }

  public function isEmpty()
  {
    return ($this->subfield) ? false : true;
  }
}
