<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T16:21:44+07:00
# @Email:  ido.alit@gmail.com
# @Filename: Record.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-20T10:46:32+07:00



namespace Marc;

class Record
{

  protected $fields = array();
  protected $leader;
  protected $selectFields = array();

  public function addField(\Marc\Field $field)
  {
    $this->fields[] = $field;
    return $this;
  }

  public function field($field)
  {
    $fields = explode(',', $field);
    $merge = array_merge_recursive($this->selectFields, $fields);
    $this->selectFields = array_unique($merge);
    return $this;
  }

  public function getField($pattern = false)
  {

    if ($pattern) {
      foreach ($this->fields as $field) {
        if (preg_match("/$pattern/", $field->getTag())) {
          return $field;
        }
      }
    }

    return false;
  }

  public function getFields($pattern = false)
  {
    if ($pattern) {
      $match = array();
      foreach ($this->fields as $field) {
        if (preg_match("/$pattern/", $field->getTag())) {
          $match[] = $field;
        }
      }

      return $match;
    }

    return false;
  }

  public function setLeader($leader)
  {
    $this->leader = $leader;
    return $this;
  }

  public function getLeader()
  {
    return (string)$this->leader;
  }

  public function toArray()
  {
    $fields = array();
    foreach ($this->fields as $field) {
      if (!$field->isEmpty()) {
        $classField = get_class($field);
        $tag = utf8_encode($field->getTag());
        switch ($classField) {
          case 'Marc\ControlField':
            if (!empty($this->selectFields)) {
              if (in_array($tag, $this->selectFields)) {
                $fields[] = array('tag' => $tag, 'data' => utf8_encode($field->getData()));
              }
            } else {
              $fields[] = array('tag' => $tag, 'data' => utf8_encode($field->getData()));
            }
            break;
          case 'Marc\DataField':
            if (!empty($this->selectFields)) {
              if (in_array($tag, $this->selectFields)) {
                $fields[] = $this->getDataField($field);
              }
            } else {
              $fields[] = $this->getDataField($field);
            }
            break;
        }
      }
    }

    $_return = array(
      'leader' => utf8_encode($this->getLeader()),
      'fields' => $fields
    );
    if (!empty($this->selectFields)) {
      unset($_return['leader']);
    }
    // empty selectFields
    $this->selectFields = array();
    if (empty($fields)) {
      return false;
    }
    return $_return;
  }

  public function toJson()
  {
    $_return = json_encode($this->toArray());
    return preg_replace('/("subfields":)(.*?)\["([^\"]+?)"\]/', '\1\2{"0":"\3"}', $_return);
  }

  private function getDataField(\Marc\DataField $field)
  {
    $subfields = array();
    foreach ($field->getSubfield() as $subfield) {
      $subfields[] = array('code' => utf8_encode($subfield->getCode()), 'data' => utf8_encode($subfield->getData()));
    }
    $_return = array(
      'tag' => utf8_encode($field->getTag()),
      'ind1' => utf8_encode($field->getIndicator(1)),
      'ind2' => utf8_encode($field->getIndicator(2)),
      'subfield' => $subfields
    );

    return $_return;
  }
}
