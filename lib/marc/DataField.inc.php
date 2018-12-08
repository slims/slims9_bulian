<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T16:00:39+07:00
# @Email:  ido.alit@gmail.com
# @Filename: DataField.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-20T09:55:46+07:00



namespace Marc;

class DataField extends \Marc\Field
{

  protected $ind1;
  protected $ind2;
  protected $subfield;

  function __construct($tag, $subfield = null, $indicator1 = null, $indicator2 = null)
  {

    parent::__construct($tag);

    $this->subfield = $subfield;
    $this->ind1 = $indicator1;
    $this->ind2 = $indicator2;
  }

  public function isEmpty()
  {
    return ($this->subfield) ? false : true;
  }

  public function getSubfield($code = null)
  {
    if (is_null($code)) {
      return $this->subfield;
    }

    foreach ($this->subfield as $subfield) {
      if ($subfield->getCode() == $code) {
        return (string)$subfield->getData();
      }
    }
    return false;
  }

  public function getIndicator($index)
  {
    switch ($index) {
      case 1:
        return (string)$this->ind1;
        break;
      case 2:
        return (string)$this->ind2;
        break;
    }
  }

}
