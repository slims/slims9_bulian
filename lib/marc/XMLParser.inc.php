<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2017-09-19T11:40:55+07:00
# @Email:  ido.alit@gmail.com
# @Filename: Parser.inc.php
# @Last modified by:   user
# @Last modified time: 2017-09-20T12:19:25+07:00



namespace Marc;

use SLiMS\Http\Client;

/**
 * Parser record Marc XML
 */
class XMLParser
{

  // store parsed xml file
  protected $source;
  // Counter for index record
  protected $counter;
  // set error
  protected $error = false;
  // set messages
  protected $message = '';

  function __construct($source, $type = 'file', $namespace = '', $isPrefix = false)
  {
    try {
      $this->counter = 0;
      $resource = preg_match('/(http|s)\:\/\//i', $source) ? Client::get($source . '&xmlformat=marc21') : file_get_contents($source);
      
      if ($error = $resource?->getError() || empty($resource)) throw new \Exception($error);
      else $content = is_object($resource) ? $resource->getContent() : $resource;
      
      $this->source = \simplexml_load_string($content, 'SimpleXMLElement', 0, $namespace, $isPrefix);

    } catch (\GuzzleHttp\Exception\BadResponseException | \Exception $e) {
      $this->error = true;
      $this->message = isDev() ? $e->getMessage() : __('Can\'t load MARC Source.');
    }
  }

  public static function isSupport()
  {
    return function_exists('simplexml_load_string');
  }

  public function isError()
  {
    return $this->error;
  }

  public function getMessage()
  {
    return $this->message;
  }

  public function count()
  {
    return count($this->source?->record??[]);
  }

  public function next()
  {
    if (isset($this->source->record[$this->counter])) {
      $record = $this->source->record[$this->counter++];
    } elseif ($this->source->getName() == 'record' && $this->counter == 0) {
      $record = $this->source;
      $this->counter++;
    } else {
      return false;
    }

    if ($record) {
      return $this->parsing($record);
    } else {
      return false;
    }
  }

  public function get($index = null)
  {
    if (!is_null($index)) {
      $this->counter = --$index;
    }
    return $this->next();
  }

  public function parsing($data)
  {

    $record = new \Marc\Record;

    // save leader
    $record->setLeader((string)$data->leader);

    // Parsing control Field
    foreach ($data->controlfield as $controlfield) {
      // get Controlfield Attributes
      $cfAttr = $controlfield->attributes();
      // store control field data
      $record->addField(new \Marc\ControlField((string)$cfAttr['tag'], (string)$controlfield));
    }

    // Parsing datafield
    foreach ($data->datafield as $datafield) {
      // get data field attributes
      $dfAttr = $datafield->attributes();
      // store subfield data
      $subfieldData = array();
      foreach ($datafield->subfield as $subfield) {
        // get subfield attributes
        $sfAttr = $subfield->attributes();
        $subfieldData[] = new \Marc\SubField((string)$sfAttr['code'], (string)$subfield);
      }

      // save to datafield
      $record->addField(new \Marc\DataField((string)$dfAttr['tag'], $subfieldData, (string)$dfAttr['ind1'], (string)$dfAttr['ind2']));
    }

    return $record;
  }

  public function debug()
  {
    echo '<pre>'; print_r($this->source); echo '</pre>';
  }
}
