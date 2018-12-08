<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T16:02:58+07:00
# @Email:  ido.alit@gmail.com
# @Filename: Field.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-19T18:04:37+07:00



namespace Marc;

class Field
{

  protected $tag;

  function __construct($tag, $subfield = null, $indicator1 = null, $indicator2 = null)
  {
    $this->tag = $tag;

    if (!preg_match("/^[0-9A-Za-z]{3}$/", $tag)) {
      die("Invalid Tag: '{$tag}'");
    }
  }

  public function getTag()
  {
    return (string)$this->tag;
  }
}
